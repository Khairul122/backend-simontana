<?php

$allowedOrigins = array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000,http://127.0.0.1:3000,http://localhost:5173,http://127.0.0.1:5173')))));

return [

    

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'api/documentation',
        'docs',
        'l5-swagger'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-Total-Count',
        'X-Per-Page',
        'X-Current-Page',
        'X-Total-Pages',
        'Content-Disposition',
        'X-API-Version',
        'X-Response-Time'
    ],

    'max_age' => 0,

    'supports_credentials' => false, 

];
