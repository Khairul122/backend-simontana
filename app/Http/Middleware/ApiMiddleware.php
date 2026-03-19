<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class ApiMiddleware extends BaseVerifier
{
    
    protected $except = [
        'api/*',
        'api/auth/*',
        'api/users/*',
        'api/check-token',
        'api/documentation'
    ];
}