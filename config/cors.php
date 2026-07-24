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

    'paths' => ['public/*', 'api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => env_csv('CORS_ALLOWED_METHODS', ['GET', 'POST', 'OPTIONS']),

    'allowed_origins' => env_csv('CORS_ALLOWED_ORIGINS', [env('APP_URL', 'http://localhost')]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-Requested-With',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => filter_var(env('CORS_SUPPORTS_CREDENTIALS', false), FILTER_VALIDATE_BOOLEAN),

];
