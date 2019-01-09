<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Railroad\Usora\Entities\EmailChange;
use Railroad\Usora\Entities\User;
use Railroad\Usora\Events\EmailChangeRequest as EmailChangeRequestEvent;
use Railroad\Usora\Requests\EmailChangeConfirmationRequest;
use Railroad\Usora\Requests\EmailChangeRequest;
use Railroad\Usora\Services\ConfigService;

class EmailChangeController extends Controller
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    private $emailChangeRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     */
    private $userRepository;

    /**
     * EmailChangeController constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;

        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->emailChangeRepository = $this->entityManager->getRepository(EmailChange::class);
    }

    /**
     * Perform an email change request action.
     *
     * @param  EmailChangeRequest $request
     * @return RedirectResponse
     */
    public function request(EmailChangeRequest $request)
    {
        $payload = [
            'email' => $request->get('email'),
            'token' => $this->createNewToken($request->get('email')),
            'created_at' => Carbon::now()
                ->toDateTimeString(),
        ];

        $user = $this->userRepository->find(auth()->id());

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
     * @param  EmailChangeConfirmationRequest $request
     * @return RedirectResponse
     */
    public function confirm(Request $request)
    {
        $validator = validator(
            $request->all(),
            [
                'token' => 'bail|required|string|exists:' .

                    ConfigService::$databaseConnectionName . '.' . ConfigService::$tableEmailChanges,
                'token',
            ]
        );

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator);
        }

        $emailChangeData = $this->emailChangeRepository->findOneBy(['token' => $request->get('token')]);

        if (Carbon::parse(
                $emailChangeData->getCreatedAt()
                    ->format('Y-m-d H:i:s')
            ) <
            Carbon::now()
                ->subHours(ConfigService::$emailChangeTtl)) {
            return redirect()
                ->back()
                ->withErrors(['token' => 'Your email reset token has expired.']);
        }

        $user =
            $this->userRepository->findOneBy(
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
                ->back()
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

    public function sendEmailChangeNotification($token, $email)
    {
        (new AnonymousNotifiable)->route(ConfigService::$emailChangeNotificationChannel, $email)
            ->notify(new ConfigService::$emailChangeNotificationClass($token));
    }
}
