<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwaggerCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Always allow Swagger UI access regardless of origin
        $origin = $request->header('Origin') ?: '*';

        // Set CORS headers for Swagger documentation
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: false'); // Important for Swagger UI
        header('Access-Control-Max-Age: 86400');
        header('Vary: Origin');

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200);
        }

        $response = $next($request);

        // Ensure response has proper CORS headers
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token');
        $response->header('Access-Control-Allow-Credentials', 'false');

        return $response;
    }
}