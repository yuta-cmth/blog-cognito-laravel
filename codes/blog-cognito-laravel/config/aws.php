<?php

return [
    'account_id' => env('AWS_ACCOUNT_ID'),
    'region' => env('AWS_REGION'),
    'cognito' => [
        'user_pool_id' => env('AWS_COGNITO_USER_POOL_ID'),
        'client_id' => env('AWS_COGNITO_CLIENT_ID'),
        'client_secret' => env('AWS_COGNITO_CLIENT_SECRET'),
        'hosted_ui_domain' => env('AWS_COGNITO_HOSTED_UI_DOMAIN'),
    ],
];
