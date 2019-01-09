<?php

Route::group(
    [
        'prefix' => 'usora',
    ],
    function () {
        Route::post(
            'authenticate/credentials',
            \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaCredentials'
        )
            ->name('usora.authenticate.credentials');

        Route::get(
            'authenticate/verification-token',
            \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaVerificationToken'
        )
            ->name('usora.authenticate.verification-token');

        Route::get(
            'authenticate/third-party',
            \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaThirdParty'
        )
            ->name('usora.authenticate.third-party');

        Route::get(
            'authenticate/post-message-verification-token',
            \Railroad\Usora\Controllers\AuthenticationController::class . '@renderVerificationTokenViaPostMessage'
        )
            ->name('usora.authenticate.post-message-verification-token');

        Route::post(
            'authenticate/set-authentication-cookie',
            \Railroad\Usora\Controllers\AuthenticationController::class . '@setAuthenticationCookieViaVerificationToken'
        )
            ->name('usora.authenticate.set-authentication-cookie');

        Route::get(
            'deauthenticate',
            \Railroad\Usora\Controllers\AuthenticationController::class . '@deauthenticate'
        )
            ->name('usora.deauthenticate');

        Route::put('api/login', \Railroad\Usora\Controllers\ApiController::class . '@login');
        Route::put('api/logout', \Railroad\Usora\Controllers\ApiController::class . '@logout');
        Route::put('api/me', \Railroad\Usora\Controllers\ApiController::class . '@getAuthUser');
    }
);