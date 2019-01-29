<?php

return [
    // doctrine dev mode toggle
    'development_mode' => true,

    // database
    'database_connection_name' => 'my_connection',
    'database_name' => 'mydb',
    'database_user' => 'root',
    'database_password' => 'root',
    'database_host' => 'mysql',
    'database_driver' => 'pdo_mysql',
    'database_in_memory' => false,

    'data_mode' => 'host', // 'host' or 'client', hosts do the db migrations, clients do not

    // cache
    'redis_host' => 'redis',
    'redis_port' => 6379,

    // entities
    'entities' => [
        [
            'path' => __DIR__ . '/../src/Entities',
            'namespace' => 'Railroad\Usora\Entities',
        ],
    ],

    // users
    'default_profile_picture_url' => 'https://picsum.photos/250/250',

    // tables
    'tables' => [
        'users' => 'usora_users',
        'email_changes' => 'usora_email_changes',
        'password_resets' => 'usora_password_resets',
    ],

    // routes
    'autoload_all_routes' => true,
    'route_middleware_public_groups' => ['usora_public'],
    'route_middleware_logged_in_groups' => ['usora_logged_in'],
    'route_prefix' => 'usora',

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
];