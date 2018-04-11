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
    'authenticate/post-message-authentication-cookie',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@renderAuthenticationCookieViaPostMessage'
)->name('authenticate.post-message-authentication-cookie');