<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\MessageBag;
use Railroad\Usora\Services\ConfigService;
use Railroad\Usora\Services\UserService;

class ResetPasswordController extends Controller
{

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Hasher
     */
    private $hasher;

    /**
     * CookieController constructor.
     *
     * @param UserService $userService
     * @param Hasher $hasher
     */
    public function __construct(UserService $userService, Hasher $hasher)
    {
        $this->userService = $userService;
        $this->hasher = $hasher;

        $this->middleware(ConfigService::$authenticationControllerMiddleware);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate(
            [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6',
            ]
        );

        $response = $this->broker()->reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function ($user, $password) {
                $hashedPassword = $this->hasher->make($password);

                $this->userService->updateOrCreate(['id' => $user['id']], ['password' => $hashedPassword]);

                $user['password'] = $hashedPassword;

                event(new PasswordReset($user));

                auth()->loginUsingId($user['id']);
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            session()->put('skip-third-party-auth-check', true);

            return redirect()->to(ConfigService::$loginSuccessRedirectPath)
                ->with(
                    'successes',
                    new MessageBag(['password' => 'Your password has been reset successfully.'])
                );
        }

        return redirect()->back()->withErrors(['password' => 'Password reset failed, please try again.']);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
