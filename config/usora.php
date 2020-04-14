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
    'default_timezone' => 'America/Los_Angeles',

    // tables
    'tables' => [
        'users' => 'usora_users',
        'user_fields' => 'usora_user_fields',
        'email_changes' => 'usora_email_changes',
        'password_resets' => 'usora_password_resets',
        'remember_tokens' => 'usora_remember_tokens',
        'firebase_tokens' => 'usora_user_firebase_tokens'
    ],

    // routes
    'autoload_all_routes' => true,
    'route_middleware_public_groups' => ['usora_public'],
    'route_middleware_logged_in_groups' => ['usora_logged_in'],
    'route_middleware_app_logged_in_groups' => ['app_authed'],
    'route_prefix' => 'usora',

    // authentication domains
    // if you dont set a subdomain, the current request subdomain will be used
    'domains_to_authenticate_on' => [
        'domain1.com',
        'sub.domain2.com',
    ],

    'domains_to_check_for_authentication' => [
        'domain2.com',
        'sub.domain2.com',
    ],

    // this must be used on drumeo website, on others the setting may be omited
    'post_verification_token_path' => 'usora/authenticate/render-post-message-verification-token',

    // authentication
    'login_page_path' => 'login',
    'login_success_redirect_path' => 'my-restricted-area',
    'force_remember' => true,

    // how long until the remember tokens expire in seconds
    'remember_me_token_expiration_time' => 31536000, // 1 year

    // password reset
    'password_reset_form_route_name' => 'reset-password',
    'password_reset_notification_class' => \Railroad\Usora\Notifications\ResetPassword::class,
    'password_reset_notification_channel' => 'mail',

    'email_change_notification_class' => \Railroad\Usora\Notifications\EmailChange::class,
    'email_change_notification_channel' => 'mail',
    'email_change_token_ttl' => 24, // hours unit
    'email_change_confirmation_success_redirect_path' => 'members',

    // file uploading
    'file_upload_aws_s3_access_key' => '',
    'file_upload_aws_s3_access_secret' => '',
    'file_upload_aws_s3_region' => '',
    'file_upload_aws_s3_bucket' => '',
    'file_upload_aws_s3_bucket_cloud_front_url' => '',

    // the uploaded file URL will be injected in to the request with the 'file_' removed from the key
    // keys MUST start with 'file_', ex: 'file_my_profile_picture'
    'allowed_file_upload_request_keys' => [
        'file_profile_picture_url' => [
            'path' => '/path/inside/bucket/',
        ],
        'file_other_image_key' => [
            'path' => '/path/inside/bucket/',
        ],
    ],
];