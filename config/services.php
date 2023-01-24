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

    'google' => [
        'client_id' => '669810699352-5v8mqnj3phkns1mnnfmouv1vot602ubh.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect' => 'https://upstart.brainfors.am/api/v1/auth/callback/google',
    ],
    'facebook' => [
        'client_id' => '699694041559853',
        'client_secret' => '540eb2bd620001535bd6adcbdee379aa',
        'redirect' => 'https://upstart.brainfors.am/api/v1/auth/callback/facebook',
    ],
];
