<?php

Route::post(
    'authenticate/credentials',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaCredentials'
)->name('authenticate.credentials');

Route::get(
    'authenticate/token',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaToken'
)->name('authenticate.token');

Route::get(
    'authenticate/third-party',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaThirdParty'
)->name('authenticate.third-party');

Route::get(
    'authenticate/post-message-remember-token',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@renderRememberTokenViaPostMessage'
)->name('authenticate.post-message-remember-token');

Route::post(
    'authenticate/set-authentication-cookie',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@setAuthenticationCookieViaRememberToken'
)->name('authenticate.set-authentication-cookie');

Route::get(
    'deauthenticate',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@deauthenticate'
)->name('deauthenticate');