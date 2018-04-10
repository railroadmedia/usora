<?php

Route::post(
    'authenticate/credentials',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaCredentials'
)->name('authenticate.credentials');

Route::get(
    'authenticate/token',
    \Railroad\Usora\Controllers\AuthenticationController::class . '@authenticateViaToken'
)->name('authenticate.token');