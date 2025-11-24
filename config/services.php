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

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'gemini'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    'ebay' => [
        'client_id' => env('EBAY_CLIENT_ID', env('EBAY_APP_ID')),
        'client_secret' => env('EBAY_CLIENT_SECRET', env('EBAY_CERT_ID')),
        'refresh_token' => env('EBAY_REFRESH_TOKEN'),
        'environment' => env('EBAY_ENVIRONMENT', 'sandbox'),
        // Business policies for listings
        'fulfillment_policy_id' => env('EBAY_FULFILLMENT_POLICY_ID'),
        'payment_policy_id' => env('EBAY_PAYMENT_POLICY_ID'),
        'return_policy_id' => env('EBAY_RETURN_POLICY_ID'),
        // Legacy for Browse API
        'app_id' => env('EBAY_APP_ID'),
        'sandbox_app_id' => env('EBAY_SANDBOX_APP_ID'),
    ],

    'shopify' => [
        'shop_domain' => env('SHOPIFY_SHOP_DOMAIN'), // your-shop.myshopify.com
        'access_token' => env('SHOPIFY_ACCESS_TOKEN'),
        'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),
    ],

    'pochtoy' => [
        'api_url' => env('POCHTOY_API_URL', 'https://pochtoy-test.pochtoy3.ru/api/garage-tg/store'),
        'api_token' => env('POCHTOY_API_TOKEN'),
    ],

    'google' => [
        'vision_api_key' => env('GOOGLE_VISION_API_KEY'),
        'search_api_key' => env('GOOGLE_CUSTOM_SEARCH_API_KEY'),
        'search_engine_id' => env('GOOGLE_CUSTOM_SEARCH_ENGINE_ID'),
    ],

    'fashn' => [
        'key' => env('FASHN_API_KEY'),
    ],

];
