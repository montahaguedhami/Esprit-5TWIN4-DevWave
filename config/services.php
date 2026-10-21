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

    /*
    |--------------------------------------------------------------------------
    | Google OAuth (Laravel Socialite)
    |--------------------------------------------------------------------------
    |
    | Create the credentials on https://console.cloud.google.com/apis/credentials
    | (OAuth 2.0 Client ID, Web application) then set them here via .env.
    | NEVER commit real values.
    |
    | GOOGLE_CLIENT_ID=
    | GOOGLE_CLIENT_SECRET=
    | GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
    |
    */

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    // Service IA des collègues (analyse risque infrastructure)
    'gemini' => [
        'api_key'       => env('GEMINI_API_KEY'),
        'model'         => env('GEMINI_MODEL', 'gemini-3.8-flash'),
        // Modèle de secours utilisé si le principal est surchargé (503), en quota (429) ou introuvable (404)
        'fallback_model' => env('GEMINI_FALLBACK_MODEL', 'gemini-3.5-flash-lite'),
        'base_url'      => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout'       => (int) env('GEMINI_TIMEOUT', 30),
        'cache_minutes' => (int) env('GEMINI_CACHE_MINUTES', 360),
    ],

    'failure_risk' => [
        'api_key'                 => env('FAILURE_RISK_AI_API_KEY'),
        'model'                   => env('FAILURE_RISK_AI_MODEL', 'gpt-4o-mini'),
        'endpoint'                => env('FAILURE_RISK_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'timeout_seconds'         => (int) env('FAILURE_RISK_AI_TIMEOUT', 5),
        'cache_hours'             => 24,
        'max_requests_per_minute' => 1,
    ],

];
