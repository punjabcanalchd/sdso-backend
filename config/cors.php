<?php

return [

    'paths' => [

        'api/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [

        env('FRONTEND_URL'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [

        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
