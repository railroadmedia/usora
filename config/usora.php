<?php

return [
    'database_connection_name' => 'mysql',
    'connection_mask_prefix' => 'usora_',
    'table_prefix' => 'usora_',

    'domains_to_authenticate_on' => [
        'dev.musora.com',
        'dev.recordeo.com',
    ],

    'login_page_url' => 'https://www.domain.com/login',
    'login_success_redirect_url' => 'https://www.domain.com/my-restricted-area',
];