<?php

return [
    // Sanctum CSRF cookie + API + broadcasting auth.
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    // Locked to Remindo domains (and localhost for development). No wildcard.
    'allowed_origins' => array_filter(explode(',', (string) env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:3000,http://127.0.0.1:3000,https://app.remindo.me,https://remindo.me'
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Required for cookie-based (stateful) Sanctum auth.
    'supports_credentials' => true,
];
