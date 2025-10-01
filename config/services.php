<?php

use App\Models\Follower;

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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'paytm' => [
        'env' => env('PAYTM_ENVIRONMENT'), // values : (local | production)
        'merchant_id' => env('PAYTM_MERCHANT_ID'),
        'merchant_key' => env('PAYTM_MERCHANT_KEY'),
        'merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
        'channel' => env('PAYTM_CHANNEL'),
        'industry_type' => env('PAYTM_INDUSTRY_TYPE'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'phone' => env('TWILIO_PHONE_NUMBER')
    ],

    'stripe' => [
        'model' => Follower::class,
        'key' => 'pk_test_51QxaPkBFx3eDWa5rXhe5yeINptLvMNxXWcxKHBPfB5YyoWyy2hTZlvQwiFewk2X1oTOjeLBr8ER9iWRVbPfbVtmV00DiWRs1Ix',
        'secret' => 'sk_test_51QxaPkBFx3eDWa5rjI0PvwVwz40tU2oJx7V20bYxW13A5XrK0TaAuChEiIdyHgRo3FukOtEWP2yfuxjhOnYlOyPa00Evz3RMoC',
        'webhook' => [
            'secret' => 'whsec_6KDTcKP2F7NWOdEgCHvXzauRWhxSbIY6',
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
        'currency' => env('STRIPE_CURRENCY'),
    ],

    'chat' => [
        'base_url' => env('CHAT_BASE_URL'),
    ],

    'aws' => [
        'base_url' => env('AWS_S3_URL'),
    ],
    'env' => [
        'app_url' => env('APP_URL'),
    ]
];
