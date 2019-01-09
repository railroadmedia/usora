<?php

Route::group(
    [
        'prefix' => 'usora',
    ],
    function () {
        // password
        Route::post(
            'password/send-reset-email',
            \Railroad\Usora\Controllers\ForgotPasswordController::class . '@sendResetLinkEmail'
        )
            ->name('usora.password.send-reset-email');

        Route::post(
            'password/reset',
            \Railroad\Usora\Controllers\ResetPasswordController::class . '@reset'
        )
            ->name('usora.password.reset');

        Route::patch(
            'user/update-password',
            \Railroad\Usora\Controllers\PasswordController::class . '@update'
        )
            ->name('usora.user-password.update');

        // email
        Route::post(
            'email-change/request',
            \Railroad\Usora\Controllers\EmailChangeController::class . '@request'
        )
            ->name('usora.email-change.request');

        Route::get(
            'email-change/confirm',
            \Railroad\Usora\Controllers\EmailChangeController::class . '@confirm'
        )
            ->name('usora.email-change.confirm');

    }
);