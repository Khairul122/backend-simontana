<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwaggerCors
{
    
    public function handle(Request $request, Closure $next)
    {
        
        $origin = $request->header('Origin') ?: '*';

        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: false'); 
        header('Access-Control-Max-Age: 86400');
        header('Vary: Origin');

        
        if ($request->isMethod('OPTIONS')) {
            return response('', 200);
        }

        $response = $next($request);

        
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token');
        $response->header('Access-Control-Allow-Credentials', 'false');

        return $response;
    }
}