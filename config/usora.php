<?php

return [
    // database
    'database_connection_name' => 'mysql',
    'connection_mask_prefix' => 'usora_',

    // host does the db migrations, clients do not
    'data_mode' => 'host', // 'host' or 'client'

    // tables
    'table_prefix' => 'usora_',
    'tables' => [
        'email_changes' => 'email_changes',
        'users' => 'users',
        'password_resets' => 'password_resets',
        'user_fields' => 'user_fields',
    ],

    // authentication domains
    'domains_to_authenticate_on' => [
        'dev.domain1.com',
    ],

    'domains_to_check_for_authentication' => [
        'dev.domain2.com',
    ],

    // authentication
    'login_page_path' => 'login',
    'login_success_redirect_path' => 'my-restricted-area',

    // use remember me by default?
    'remember_me' => true,

    // password reset
    'password_reset_notification_class' => \Railroad\Usora\Notifications\ResetPassword::class,
    'password_reset_notification_channel' => 'mail',

    'email_change_notification_class' => \Railroad\Usora\Notifications\EmailChange::class,
    'email_change_notification_channel' => 'mail',
    'email_change_token_ttl' => 24, // hours unit

    // if you have any of these middleware classes in your global http kernel, they must be removed from this array
    'authentication_controller_middleware' => [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Railroad\Usora\Middleware\ReFlashSession::class,
    ],
];