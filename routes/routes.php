<?php

Route::post(
    'authenticate/credentials',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaCredentials'
)->name('authenticate.credentials');

Route::get(
    'authenticate/verification-token',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaVerificationToken'
)->name('authenticate.verification-token');

Route::get(
    'authenticate/third-party',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaThirdParty'
)->name('authenticate.third-party');

Route::get(
    'authenticate/post-message-verification-token',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@renderVerificationTokenViaPostMessage'
)->name('authenticate.post-message-verification-token');

Route::post(
    'authenticate/set-authentication-cookie',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@setAuthenticationCookieViaVerificationToken'
)->name('authenticate.set-authentication-cookie');

Route::get(
    'deauthenticate',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@deauthenticate'
)->name('deauthenticate');