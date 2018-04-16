<?php

return [
    'database_connection_name' => 'mysql',
    'connection_mask_prefix' => 'usora_',
    'table_prefix' => 'usora_',

    // host does the db migrations, clients do not
    'data_mode' => 'host', // 'host' or 'client'

    'domains_to_authenticate_on' => [
        'dev.domain1.com',
    ],

    'domains_to_check_for_authentication' => [
        'dev.domain2.com',
    ],

    'login_page_path' => 'login',
    'login_success_redirect_path' => 'my-restricted-area',

    'remember_me' => true,

    // if you have any of these middleware classes in your global http kernel, they must be removed from this array
    'authentication_controller_middleware' => [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Railroad\Usora\Middleware\ReFlashSession::class,
    ],
];