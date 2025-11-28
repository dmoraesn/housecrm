<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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

    // ========================================
    // OPENAI – 100% seguro para artisan commands
    // ========================================
    'openai' => [
        'key'         => function_exists('setting') ? setting('openai_api_key') : env('OPENAI_API_KEY'),
        'model'       => function_exists('setting') ? setting('openai_model', 'gpt-4o-mini') : env('OPENAI_MODEL', 'gpt-4o-mini'),
        'temperature' => function_exists('setting') ? (float)setting('ai_temperature', '0.7') : 0.7,
    ],

    'gemini' => [
        'key' => function_exists('setting') ? setting('gemini_api_key') : env('GEMINI_API_KEY'),
    ],

    'anthropic' => [
        'key' => function_exists('setting') ? setting('anthropic_api_key') : env('ANTHROPIC_API_KEY'),
    ],

    'grok' => [
        'key' => function_exists('setting') ? setting('grok_api_key') : env('GROK_API_KEY'),
    ],

];