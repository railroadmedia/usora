<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Railroad\Usora\Entities\EmailChange;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Events\EmailChangeRequest as EmailChangeRequestEvent;
use Railroad\Usora\Managers\UsoraEntityManager;
use Railroad\Usora\Repositories\EmailChangeRepository;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\EmailChangeRequest;

class EmailChangeController extends Controller
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EmailChangeRepository
     */
    private $emailChangeRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * EmailChangeController constructor.
     *
     * @param Hasher $hasher
     * @param EntityManager $entityManager
     */
    public function __construct(
        Hasher $hasher,
        UsoraEntityManager $entityManager
    ) {
        $this->hasher = $hasher;
        $this->entityManager = $entityManager;

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->emailChangeRepository = $this->entityManager->getRepository(EmailChange::class);
    }

    /**
     * Perform an email change request action.
     *
     * @param  EmailChangeRequest $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function request(EmailChangeRequest $request)
    {
        $user = $this->userRepository->find(auth()->id());

        if (
            !$this->hasher->check($request->get('user_password'), $user->getPassword())
            && !WpPassword::check(trim($request->get('user_password')), $user->getPassword())
        ) {
            return back()
                ->withInput($request->except('user_password'))
                ->withErrors(
                    ['user_password' => 'The current password you entered is incorrect.']
                );
        }

        $payload = [
            'email' => $request->get('email'),
            'token' => $this->createNewToken($request->get('email')),
            'created_at' => Carbon::now()
                ->toDateTimeString(),
        ];

        $emailChange = $this->emailChangeRepository->findOneBy(['user' => $user->getId()]);

        if (!$emailChange) {
            $emailChange = new EmailChange();
        }

        $emailChange->setEmail($payload['email']);
        $emailChange->setToken($payload['token']);
        $emailChange->setUser($user);

        $this->entityManager->persist($emailChange);
        $this->entityManager->flush();

        event(new EmailChangeRequestEvent($payload['token'], $payload['email']));

        $this->sendEmailChangeNotification($payload['token'], $payload['email']);

        $message = [
            'successes' => new MessageBag(
                ['password' => 'An email confirmation link has been sent to your new email address.']
            ),
        ];

        return $request->has('redirect') ?
            redirect()
                ->away($request->get('redirect'))
                ->with($message) :
            redirect()
                ->back()
                ->with($message);
    }

    /**
     * Perform an email change confirmation action.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function confirm(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'code' => 'bail|required|string|exists:' .
                    config('usora.database_connection_name') .
                    '.' .
                    config('usora.tables.email_changes') .
                    ',token',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator);
        }

        $emailChangeData = $this->emailChangeRepository->findOneBy(['token' => $request->get('code')]);

        if (Carbon::parse(
                $emailChangeData->getUpdatedAt()
                    ->format('Y-m-d H:i:s')
            ) <
            Carbon::now()
                ->subHours(config('usora.email_change_token_ttl'))) {

            return redirect()
                ->back()
                ->withErrors(['code' => 'Your email reset code has expired.']);
        }

        $user = $this->userRepository->findOneBy(
            [
                'id' => $emailChangeData->getUser()
                    ->getId(),
            ]
        );
        $user->setEmail($emailChangeData->getEmail());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->entityManager->remove($emailChangeData);
        $this->entityManager->flush();

        $message = [
            'successes' => new MessageBag(
                ['password' => 'Your email has been updated successfully.']
            ),
        ];

        return $request->has('redirect') ?
            redirect()
                ->away($request->get('redirect'))
                ->with($message) :
            redirect()
                ->to(config('usora.email_change_confirmation_success_redirect_path'))
                ->with($message);
    }

    /**
     * Generates a token
     * Similar with Illuminate\Auth\Passwords\DatabaseTokenRepository::createNewToken
     *
     * @param string $hash
     * @return string
     */
    public function createNewToken($hash)
    {
        return hash_hmac('sha256', Str::random(40), $hash);
    }

    /**
     * @param $token
     * @param $email
     */
    public function sendEmailChangeNotification($token, $email)
    {
        $class = config('usora.email_change_notification_class');

        (new AnonymousNotifiable)->route(config('usora.email_change_notification_channel'), $email)
            ->notify(new $class($token));
    }
}
