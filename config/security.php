<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuration de Sécurité Koumbaya
    |--------------------------------------------------------------------------
    |
    | Configuration globale pour tous les aspects de sécurité de l'application
    |
    */

    'https' => [
        'force_https' => env('FORCE_HTTPS', env('APP_ENV') === 'production'),
        'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 an
        'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('HSTS_PRELOAD', true),
    ],

    'headers' => [
        'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('X_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=()'),
        
        'csp' => [
            'default_src' => env('CSP_DEFAULT_SRC', "'self'"),
            'script_src' => env('CSP_SCRIPT_SRC', "'self' 'unsafe-inline' 'unsafe-eval'"),
            'style_src' => env('CSP_STYLE_SRC', "'self' 'unsafe-inline'"),
            'img_src' => env('CSP_IMG_SRC', "'self' data: https:"),
            'font_src' => env('CSP_FONT_SRC', "'self' data:"),
            'connect_src' => env('CSP_CONNECT_SRC', "'self'"),
            'media_src' => env('CSP_MEDIA_SRC', "'self'"),
            'object_src' => env('CSP_OBJECT_SRC', "'none'"),
            'child_src' => env('CSP_CHILD_SRC', "'self'"),
            'frame_ancestors' => env('CSP_FRAME_ANCESTORS', "'none'"),
            'form_action' => env('CSP_FORM_ACTION', "'self'"),
            'upgrade_insecure_requests' => env('CSP_UPGRADE_INSECURE', true),
        ],
    ],

    'csrf' => [
        'enabled' => env('CSRF_ENABLED', true),
        'token_lifetime' => env('CSRF_TOKEN_LIFETIME', 3600), // 1 heure
        'regenerate_on_login' => env('CSRF_REGENERATE_LOGIN', true),
        'except_routes' => [
            'api/*',
            'webhook/*',
        ],
    ],

    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),
        'max_attempts' => env('RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('RATE_LIMIT_DECAY', 1),
        'api_max_attempts' => env('API_RATE_LIMIT_MAX_ATTEMPTS', 100),
        'api_decay_minutes' => env('API_RATE_LIMIT_DECAY', 1),
    ],

    'input_validation' => [
        'enabled' => env('INPUT_VALIDATION_ENABLED', true),
        'xss_detection' => env('XSS_DETECTION_ENABLED', true),
        'sql_injection_detection' => env('SQL_INJECTION_DETECTION_ENABLED', true),
        'max_request_size' => env('MAX_REQUEST_SIZE', 10240), // 10MB
        'suspicious_patterns' => [
            'xss' => [
                '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
                '/javascript:/i',
                '/on\w+\s*=/i',
                '/expression\s*\(/i',
                '/vbscript:/i',
            ],
            'sql_injection' => [
                '/(\bunion\b|\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b)/i',
                '/(\bor\b|\band\b)\s+\d+\s*=\s*\d+/i',
                '/\'\s*(or|and)\s+\'\w*\'\s*=\s*\'\w*\'/i',
                '/;.*(\bdrop\b|\bdelete\b|\binsert\b)/i',
            ],
        ],
    ],

    'encryption' => [
        'algorithm' => env('SECURITY_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
        'key_rotation_days' => env('KEY_ROTATION_DAYS', 90),
        'secure_headers_encryption' => env('SECURE_HEADERS_ENCRYPTION', true),
    ],

    'logging' => [
        'security_events' => env('LOG_SECURITY_EVENTS', true),
        'failed_auth_attempts' => env('LOG_FAILED_AUTH', true),
        'suspicious_activity' => env('LOG_SUSPICIOUS_ACTIVITY', true),
        'max_log_retention_days' => env('SECURITY_LOG_RETENTION', 90),
    ],

    'monitoring' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),
        'alert_threshold_attempts' => env('ALERT_THRESHOLD_ATTEMPTS', 10),
        'alert_threshold_time' => env('ALERT_THRESHOLD_TIME', 300), // 5 minutes
        'notification_channels' => env('SECURITY_NOTIFICATION_CHANNELS', 'mail,slack'),
    ],

    'allowed_domains' => [
        'api' => array_filter(explode(',', env('ALLOWED_API_DOMAINS', ''))),
        'embed' => array_filter(explode(',', env('ALLOWED_EMBED_DOMAINS', ''))),
        'redirect' => array_filter(explode(',', env('ALLOWED_REDIRECT_DOMAINS', ''))),
    ],

    'session' => [
        'secure_cookies' => env('SESSION_SECURE_COOKIES', env('APP_ENV') === 'production'),
        'http_only_cookies' => env('SESSION_HTTP_ONLY', true),
        'same_site_cookies' => env('SESSION_SAME_SITE', 'lax'),
        'regenerate_on_login' => env('SESSION_REGENERATE_LOGIN', true),
        'timeout_minutes' => env('SESSION_TIMEOUT', 120), // 2 heures
    ],

];