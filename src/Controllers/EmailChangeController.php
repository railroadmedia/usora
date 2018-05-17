<?php

namespace Railroad\Usora\Controllers;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Railroad\Usora\Requests\EmailChangeRequest;
use Railroad\Usora\Requests\EmailChangeConfirmationRequest;
use Railroad\Usora\Repositories\EmailChangeRepository;
use Railroad\Usora\Services\ConfigService;

class EmailChangeController extends Controller
{
    /**
     * @var EmailChangeRepository
     */
    private $emailChangeRepository;

    /**
     * ChangeEmailController constructor.
     *
     * @param EmailChangeRepository $emailChangeRepository
     */
    public function __construct(EmailChangeRepository $emailChangeRepository)
    {
        $this->emailChangeRepository = $emailChangeRepository;
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
            'created_at' => Carbon::now()->toDateTimeString()
        ];

        $updateCount = $this->emailChangeRepository->query()
            ->where('user_id', auth()->id())
            ->update($payload);

        if ($updateCount == 0) {

            $payload['user_id'] = auth()->id();

            $this->emailChangeRepository->create($payload);
        }

        $this->sendEmailChangeNotification($payload['token'], $payload['email']);

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->has('redirect'))->with($message) :
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
        // confirmation logic to be implemented

        $message = ['success' => true];

        return $request->has('redirect') ?
            redirect()->away($request->has('redirect'))->with($message) :
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
