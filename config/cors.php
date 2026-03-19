<?php

return [

    

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'api/documentation',
        'docs',
        'l5-swagger'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

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