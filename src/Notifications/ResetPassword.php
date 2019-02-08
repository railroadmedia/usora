<?php

namespace Railroad\Usora\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordBase;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends ResetPasswordBase
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return (new MailMessage)->line(
            'You are receiving this email because we received a password reset request for your account.'
        )
            ->action(
                'Reset Password',
                url()->route(
                    config('usora.password_reset_form_route_name'),
                    ['token' => $this->token, 'email' => request('email')]
                )
            )
            ->line('If you did not request a password reset, no further action is required.');
    }
}
