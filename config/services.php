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

    'google' => [
        'client_id' => trim((string) env('GOOGLE_CLIENT_ID', '')),
        'client_secret' => trim((string) env('GOOGLE_CLIENT_SECRET', '')),
        // Empty = TenantGoogleAuthController builds loopback http://127.0.0.1:PORT/auth/google/callback for *.localhost.
        // Set GOOGLE_REDIRECT_URI to match Google Cloud exactly if you need a fixed URI (must match Console).
        'redirect' => trim((string) env('GOOGLE_REDIRECT_URI', '')),
        // Optional: when port cannot be detected, use this (e.g. 8080). Usually leave unset; local defaults to 8000.
        'loopback_port' => (int) env('GOOGLE_OAUTH_LOOPBACK_PORT', 0),
    ],

    'recaptcha' => [
        'enabled' => (bool) env('RECAPTCHA_ENABLED', false),
        'version' => env('RECAPTCHA_VERSION', 'v3'),
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),
        // TLS to https://www.google.com/recaptcha/api/siteverify — Windows cURL error 60: use RECAPTCHA_CAINFO (curl.se ca bundle) or local-only RECAPTCHA_HTTP_SSL_VERIFY=false
        'ca_bundle' => env('RECAPTCHA_CAINFO', ''),
        'http_ssl_verify' => filter_var(env('RECAPTCHA_HTTP_SSL_VERIFY', 'true'), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    | Stripe (tenant application fee / future billing). Set keys in .env when ready.
    */
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'application_fee_currency' => env('STRIPE_APPLICATION_FEE_CURRENCY', 'php'),
        'application_fee_amount' => env('STRIPE_APPLICATION_FEE_AMOUNT'), // smallest currency unit, e.g. cents
    ],

    'github' => [
        'token' => env('GITHUB_TOKEN'),
        'owner' => env('GITHUB_OWNER'),
        'repo' => env('GITHUB_REPO'),
        'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
        // TLS to https://api.github.com — Windows cURL error 60: set GITHUB_CAINFO (recommended) or local-only GITHUB_HTTP_SSL_VERIFY=false
        'ca_bundle' => env('GITHUB_CAINFO', ''),
        'http_ssl_verify' => filter_var(env('GITHUB_HTTP_SSL_VERIFY', 'true'), FILTER_VALIDATE_BOOLEAN),
    ],

];
