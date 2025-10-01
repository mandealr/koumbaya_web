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

    /*
    |--------------------------------------------------------------------------
    | SHAP Payout API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SHAP payout API integration for refunds
    |
    */

    'shap' => [
        'api_id' => env('API_PAYOUT_ID'),
        'api_secret' => env('API_PAYOUT_SECRET'),
        'base_url_lab' => 'https://test.billing-easy.net/shap/api/v1/merchant/',
        'base_url_prod' => 'https://staging.billing-easy.net/shap/api/v1/merchant/',
        'timeout' => 60,
        'operators' => [
            'airtel_money' => 'airtelmoney',
            'moov_money' => 'moovmoney4',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | E-Billing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for E-Billing payment gateway integration
    |
    */

    'ebilling' => [
        'username' => env('EBILLING_USERNAME'),
        'shared_key' => env('EBILLING_SHARED_KEY'),
        'server_url' => env('EBILLING_SERVER_URL', 'https://lab.billing-easy.net/api/v1/merchant/e_bills'),
        'url' => env('EBILLING_URL', 'https://lab.billing-easy.net/api/v1/'),
        'transfer_username' => env('EBILLING_TRANSFER_USERNAME'),
        'transfer_shared_key' => env('EBILLING_TRANSFER_SHARED_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile Money Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Mobile Money operators in Gabon
    |
    */

    'mobile_money' => [
        'airtel' => [
            'name' => 'Airtel Money',
            'code' => 'airtel_money',
            'enabled' => env('AIRTEL_MONEY_ENABLED', true),
        ],
        'moov' => [
            'name' => 'Moov Money',
            'code' => 'moov_money', 
            'enabled' => env('MOOV_MONEY_ENABLED', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Fees Configuration
    |--------------------------------------------------------------------------
    |
    | Default fees applied to various payment types
    |
    */

    'payment_fees' => [
        'lottery_ticket' => env('LOTTERY_TICKET_FEES', 0),
        'product_purchase' => env('PRODUCT_PURCHASE_FEES', 0),
        'ebilling_percentage' => env('EBILLING_PERCENTAGE_FEE', 2), // 2% fee
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SMS service provider
    |
    */

    'sms' => [
        'api_url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
        'sender' => env('SMS_SENDER', 'Koumbaya'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for social authentication providers
    |
    */

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/auth/facebook/callback'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI', env('APP_URL') . '/auth/apple/callback'),
    ],

];
