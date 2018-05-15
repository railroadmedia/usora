<?php

// authentication
Route::post(
    'authenticate/credentials',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaCredentials'
)->name('usora.authenticate.credentials');

Route::get(
    'authenticate/verification-token',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaVerificationToken'
)->name('usora.authenticate.verification-token');

Route::get(
    'authenticate/third-party',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaThirdParty'
)->name('usora.authenticate.third-party');

Route::get(
    'authenticate/post-message-verification-token',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@renderVerificationTokenViaPostMessage'
)->name('usora.authenticate.post-message-verification-token');

Route::post(
    'authenticate/set-authentication-cookie',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@setAuthenticationCookieViaVerificationToken'
)->name('usora.authenticate.set-authentication-cookie');

Route::get(
    'deauthenticate',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@deauthenticate'
)->name('usora.deauthenticate');

// password reset
Route::post(
    'password/send-reset-email',
    \Railroad\Usora\Controllers\ForgotPasswordController::class . '@sendResetLinkEmail'
)->name('usora.password.send-reset-email');

Route::post(
    'password/reset',
    \Railroad\Usora\Controllers\ResetPasswordController::class . '@reset'
)->name('usora.password.reset');

// -----------------------
Route::group(
    [
        'prefix' => 'api',
    ],
    function () {
        // user api
        Route::get(
            'user/index',
            \Railroad\Usora\Controllers\UserJsonController::class . '@index'
        )->name('usora.api.user.index');

        Route::get(
            'user/show/{id}',
            \Railroad\Usora\Controllers\UserJsonController::class . '@show'
        )->name('usora.api.user.show');

        Route::put(
            'user/store',
            \Railroad\Usora\Controllers\UserJsonController::class . '@store'
        )->name('usora.api.user.store');

        Route::patch(
            'user/update/{id}',
            \Railroad\Usora\Controllers\UserJsonController::class . '@update'
        )->name('usora.api.user.update');

        Route::delete(
            'user/delete/{id}',
            \Railroad\Usora\Controllers\UserJsonController::class . '@delete'
        )->name('usora.api.user.delete');

        // user field api
        Route::get(
            'user-field/index/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@index'
        )->name('usora.api.user-field.index');

        Route::get(
            'user-field/show/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@show'
        )->name('usora.api.user-field.show');

        Route::put(
            'user-field/store',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@store'
        )->name('usora.api.user-field.store');

        Route::patch(
            'user-field/update/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@update'
        )->name('usora.api.user-field.update');

        Route::delete(
            'user-field/delete/{id}',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@delete'
        )->name('usora.api.user-field.delete');
    });

// user
Route::put(
    'user/store',
    \Railroad\Usora\Controllers\UserController::class . '@store'
)->name('usora.user.store');

Route::patch(
    'user/update/{id}',
    \Railroad\Usora\Controllers\UserController::class . '@update'
)->name('usora.user.update');

Route::delete(
    'user/delete/{id}',
    \Railroad\Usora\Controllers\UserController::class . '@delete'
)->name('usora.user.delete');

// user fields
Route::put(
    'user-field/store',
    \Railroad\Usora\Controllers\UserFieldController::class . '@store'
)->name('usora.user-field.store');

Route::patch(
    'user-field/update/{id}',
    \Railroad\Usora\Controllers\UserFieldController::class . '@update'
)->name('usora.user-field.update');

Route::delete(
    'user-field/delete/{id}',
    \Railroad\Usora\Controllers\UserFieldController::class . '@delete'
)->name('usora.user-field.delete');
