<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\MessageBag;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|exists:' .
                    config('usora.database_connection_name') .
                    '.' .
                    config('usora.tables.users') .
                    ',email',
            ]
        );

        $response =
            $this->broker()
                ->sendResetLink(
                    $request->only('email')
                );

        if ($response === Password::RESET_LINK_SENT) {
            return redirect()
                ->to(config('usora.login_page_path'))
                ->with(
                    'successes',
                    new MessageBag(['password' => 'Password reset link has been sent to your email.'])
                );
        }

        return back()->withErrors(
            ['email' => 'Failed to reset password, please double check your email or contact support.']
        );
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
