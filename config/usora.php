<?php

return [
    'database_connection_name' => 'mysql',
    'connection_mask_prefix' => 'usora_',
    'table_prefix' => 'usora_',

    'domains_to_authenticate_on' => [
        'dev.domain1.com',
    ],

    'domains_to_check_for_authentication' => [
        'dev.domain2.com',
    ],

    'login_page_path' => 'login',
    'login_success_redirect_path' => 'my-restricted-area',

    'remember_me' => true,
];