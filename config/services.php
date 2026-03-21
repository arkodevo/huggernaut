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

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY') ?: (function () {
            // MAMP may export empty env vars — fall back to reading .env directly
            $envFile = base_path('.env');
            if (file_exists($envFile)) {
                foreach (file($envFile) as $line) {
                    if (str_starts_with(trim($line), 'ANTHROPIC_API_KEY=')) {
                        return trim(str_replace(['ANTHROPIC_API_KEY=', '"', "'"], '', $line));
                    }
                }
            }
            return null;
        })(),
        'model' => env('ANTHROPIC_MODEL') ?: 'claude-sonnet-4-20250514',
    ],

];
