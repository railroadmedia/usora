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

// change email
Route::post(
    'email-change/request',
    \Railroad\Usora\Controllers\EmailChangeController::class . '@request'
)->name('usora.email-change.request');

Route::get(
    'email-change/confirm',
    \Railroad\Usora\Controllers\EmailChangeController::class . '@confirm'
)->name('usora.email-change.confirm');

// -----------------------
Route::group(
    [
        'prefix' => 'usora',
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

        Route::patch(
            'user-field/update-or-create-by-key',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@updateOrCreateByKey'
        )->name('usora.api.user-field.update-or-create-by-key');

        Route::patch(
            'user-field/update-or-create-multiple-by-key',
            \Railroad\Usora\Controllers\UserFieldJsonController::class . '@updateOrCreateMultipleByKey'
        )->name('usora.api.user-field.update-or-create-multiple-by-key');

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

// user password
Route::patch(
    'user/update-password',
    \Railroad\Usora\Controllers\PasswordController::class . '@update'
)->name('usora.user-password.update');

// user fields
Route::put(
    'user-field/store',
    \Railroad\Usora\Controllers\UserFieldController::class . '@store'
)->name('usora.user-field.store');

Route::patch(
    'user-field/update/{id}',
    \Railroad\Usora\Controllers\UserFieldController::class . '@update'
)->name('usora.user-field.update');

Route::patch(
    'user-field/update-or-create-by-key',
    \Railroad\Usora\Controllers\UserFieldController::class . '@updateOrCreateByKey'
)->name('usora.user-field.update-or-create-by-key');

Route::patch(
    'user-field/update-or-create-multiple-by-key',
    \Railroad\Usora\Controllers\UserFieldController::class . '@updateOrCreateMultipleByKey'
)->name('usora.user-field.update-or-create-multiple-by-key');

Route::delete(
    'user-field/delete/{id}',
    \Railroad\Usora\Controllers\UserFieldController::class . '@delete'
)->name('usora.user-field.delete');

Route::post('api/login', \Railroad\Usora\Controllers\ApiController::class . '@login');
Route::post('api/logout', \Railroad\Usora\Controllers\ApiController::class . '@logout');
Route::post('api/me', \Railroad\Usora\Controllers\ApiController::class . '@getAuthUser');