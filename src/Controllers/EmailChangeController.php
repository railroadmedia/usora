<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Railroad\Usora\Events\EmailChangeRequest as EmailChangeRequestEvent;
use Railroad\Usora\Repositories\EmailChangeRepository;
use Railroad\Usora\Repositories\UserRepository;
use Railroad\Usora\Requests\EmailChangeConfirmationRequest;
use Railroad\Usora\Requests\EmailChangeRequest;
use Railroad\Usora\Services\ConfigService;

class EmailChangeController extends Controller
{
    /**
     * @var EmailChangeRepository
     */
    private $emailChangeRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ChangeEmailController constructor.
     *
     * @param EmailChangeRepository $emailChangeRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        EmailChangeRepository $emailChangeRepository,
        UserRepository $userRepository
    ) {
        $this->emailChangeRepository = $emailChangeRepository;
        $this->userRepository = $userRepository;
        $this->middleware(ConfigService::$authenticationControllerMiddleware);
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
            'created_at' => Carbon::now()->toDateTimeString(),
        ];

        $updateCount = $this->emailChangeRepository->query()
            ->where('user_id', auth()->id())
            ->update($payload);

        if ($updateCount == 0) {

            $payload['user_id'] = auth()->id();

            $this->emailChangeRepository->updateOrCreate($payload);
        }

        event(new EmailChangeRequestEvent($payload['token'], $payload['email']));

        $this->sendEmailChangeNotification($payload['token'], $payload['email']);

        $message =
            [
                'successes' => new MessageBag(
                    ['password' => 'An email confirmation link has been sent to your new email address.']
                ),
            ];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
    }

    /**
     * Perform an email change confirmation action.
     *
     * @param  EmailChangeConfirmationRequest $request
     * @return RedirectResponse
     */
    public function confirm(EmailChangeConfirmationRequest $request)
    {
        $emailChangeData = $this->emailChangeRepository->query()
            ->where('token', $request->get('token'))
            ->first();


        if (Carbon::parse($emailChangeData['created_at']) < Carbon::now()->subHours(ConfigService::$emailChangeTtl)) {
            return redirect()->back()->withErrors(['token' => 'Your email reset token has expired.']);
        }

        $this->userRepository
            ->update(
                $emailChangeData->user_id,
                ['email' => $emailChangeData->email]
            );

        $this->emailChangeRepository->destroy($emailChangeData->id);

        $message =
            [
                'successes' => new MessageBag(
                    ['password' => 'Your email has been updated successfully.']
                ),
            ];

        return $request->has('redirect') ?
            redirect()->away($request->get('redirect'))->with($message) :
            redirect()->back()->with($message);
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
        (new AnonymousNotifiable)
            ->route(ConfigService::$emailChangeNotificationChannel, $email)
            ->notify(new ConfigService::$emailChangeNotificationClass($token));
    }
}
