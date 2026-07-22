<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'sandes' => [
        'url' => env('SANDES_URL'),
        'username' => env('SANDES_USERNAME'),
        'password' => env('SANDES_PASSWORD'),
    ],
    'sms' => [
        'gateway_url' => env('SMS_GATEWAY_URL'),
        'username' => env('SMS_USERNAME'),
        'pin' => env('SMS_PIN'),
        'signature' => env('SMS_SIGNATURE'),
        'entity_id' => env('SMS_DLT_ENTITY_ID'),
        // 'template_id' => env('SMS_TEMPLATE_ID'),
    ],
];
