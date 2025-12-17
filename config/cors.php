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

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'api/documentation'
    ],

    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS'
    ],

    // Allow all origins dynamically - this is the most flexible approach
    'allowed_origins' => ['*'],

    // Dynamic patterns for different environments
    'allowed_origins_patterns' => [
        // Local development - allow any localhost with any port
        '/^http:\/\/localhost:\d+$/',
        '/^https:\/\/localhost:\d+$/',
        '/^http:\/\/127\.0\.0\.1:\d+$/',
        '/^https:\/\/127\.0\.0\.1:\d+$/',

        // Development servers
        '/^http:\/\/.*\.xip\.io:\d*$/',
        '/^https:\/\/.*\.xip\.io:\d*$/',
        '/^http:\/\/.*\.ngrok\.io$/',
        '/^https:\/\/.*\.ngrok\.io$/',
        '/^https?:\/\/.*\.localto\.net$/', // Localtonet
        '/^https?:\/\/.*\.local\.webhook\.moran\.com$/', // Local webhook services
        '/^https?:\/\/.*\.ngrok-free\.app$/', // Ngrok free
        '/^https?:\/\/.*\.serveo\.net$/', // Serveo

        // Production domains (customize these)
        '/^https?:\/\/(.+\.)?simonta-bencana\.com$/',
        '/^https?:\/\/(.+\.)?simonta\.id$/',

        // Mobile app deep links
        '/^exp:\/\/.*$/',
        '/^simonta:\/\/.*$/',

        // Development environments with environment variable
        '/^https?:\/\/.*\.' . env('APP_ENV', 'local') . '\.com$/',

        // Any IP address for local network testing
        '/^http:\/\/192\.168\.\d+\.\d+:\d*$/',
        '/^https:\/\/192\.168\.\d+\.\d+:\d*$/',
        '/^http:\/\/10\.\d+\.\d+\.\d+:\d*$/',
        '/^https:\/\/10\.\d+\.\d+\.\d+:\d*$/',
        '/^http:\/\/172\.(1[6-9]|2[0-9]|3[0-1])\.\d+\.\d+:\d*$/',
        '/^https:\/\/172\.(1[6-9]|2[0-9]|3[0-1])\.\d+\.\d+:\d*$/',
    ],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-CSRF-Token',
        'Accept',
        'Origin',
        'Access-Control-Request-Method',
        'Access-Control-Request-Headers',
        'X-Device-Platform',
        'X-App-Version',
        'X-Client-Info',
        'X-Mobile-Platform'
    ],

    'exposed_headers' => [
        'X-Total-Count',
        'X-Per-Page',
        'X-Current-Page',
        'X-Total-Pages',
        'Content-Disposition',
        'X-API-Version',
        'X-Response-Time'
    ],

    'max_age' => 86400, // 24 hours

    // Enable credentials for token authentication
    'supports_credentials' => true,

];