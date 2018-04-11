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

    'login_page_url' => 'https://www.domain.com/login',
    'login_success_redirect_url' => 'https://www.domain.com/my-restricted-area',
];