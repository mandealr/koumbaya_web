<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Origines autorisées. JAMAIS '*' car supports_credentials est à true.
    // Configurable via CORS_ALLOWED_ORIGINS (liste séparée par des virgules),
    // sinon repli sur les domaines connus (prod + dev local).
    'allowed_origins' => env('CORS_ALLOWED_ORIGINS')
        ? array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS')))
        : [
            'https://koumbaya.com',
            'https://www.koumbaya.com',
            'http://localhost:5173',
            'http://localhost:8000',
        ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With', 'X-Platform', 'X-Callback-Token'],

    'exposed_headers' => ['Authorization', 'X-Total-Count'],

    'max_age' => 86400,

    'supports_credentials' => true,

];