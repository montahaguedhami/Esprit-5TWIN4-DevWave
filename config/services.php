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

    'failure_risk' => [
        'api_key' => env('FAILURE_RISK_AI_API_KEY'),
        'model' => env('FAILURE_RISK_AI_MODEL', 'gpt-4o-mini'),
        'endpoint' => env('FAILURE_RISK_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'timeout_seconds' => (int) env('FAILURE_RISK_AI_TIMEOUT', 5),
        'cache_hours' => 24,
        'max_requests_per_minute' => 1,
    ],

    'recommendation_ai' => [
        'api_key' => env('RECOMMENDATION_AI_API_KEY'),
        'model' => env('RECOMMENDATION_AI_MODEL', 'gpt-4o-mini'),
        'endpoint' => env('RECOMMENDATION_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'timeout_seconds' => (int) env('RECOMMENDATION_AI_TIMEOUT', 8),
        'cache_hours' => 24,
        'max_requests_per_minute' => 1,
    ],

];
