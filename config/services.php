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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],

    'mtn' => [
        'api_key' => env('MTN_API_KEY'),
        'api_secret' => env('MTN_API_SECRET'),
        'merchant_id' => env('MTN_MERCHANT_ID'),
        'environment' => env('MTN_ENVIRONMENT', 'sandbox'), // sandbox ou production
    ],

    'orange' => [
        'client_id' => env('ORANGE_CLIENT_ID'),
        'client_secret' => env('ORANGE_CLIENT_SECRET'),
        'merchant_key' => env('ORANGE_MERCHANT_KEY'),
        'environment' => env('ORANGE_ENVIRONMENT', 'sandbox'),
    ],
];
