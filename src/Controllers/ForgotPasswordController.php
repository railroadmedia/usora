<?php

namespace Railroad\Usora\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Railroad\Usora\Services\ConfigService;

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
                    ConfigService::$databaseConnectionName .
                    '.' .
                    ConfigService::$tableUsers . ',email'
            ]
        );

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        if ($response === Password::RESET_LINK_SENT) {
            return back()->with('successes', ['Password reset link has been sent to your email.']);
        }

        return back()->withErrors(
            ['email' => 'Failed to reset password, please double check your email or contact support.']
        );
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
