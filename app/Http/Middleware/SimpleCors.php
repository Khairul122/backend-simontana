<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SimpleCors
{
    
    public function handle(Request $request, Closure $next)
    {
        
        $origin = $request->header('Origin');

        
        $env = config('app.env', 'local');
        if ($env === 'local' || $env === 'development') {
            
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Device-Platform, X-App-Version, X-Client-Info, X-Mobile-Platform');
            header('Access-Control-Allow-Credentials: false'); 
            header('Access-Control-Max-Age: 86400');
        } else {
            
            if ($origin && $this->isOriginAllowed($origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Vary: Origin');
                header('Access-Control-Allow-Credentials: true');
            }
        }

        
        if ($request->isMethod('OPTIONS')) {
            if ($env === 'local' || $env === 'development' || ($origin && $this->isOriginAllowed($origin))) {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Device-Platform, X-App-Version, X-Client-Info, X-Mobile-Platform');
                header('Access-Control-Max-Age: 86400');
                header('Access-Control-Allow-Credentials: false');
                header('Access-Control-Expose-Headers: X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time');
                header('Content-Length: 0');
                header('Content-Type: text/plain');
            }

            return response('', 200);
        }

        
        $response = $next($request);

        
        if ($env === 'local' || $env === 'development') {
            
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Device-Platform, X-App-Version, X-Client-Info, X-Mobile-Platform');
            $response->header('Access-Control-Allow-Credentials', 'false');
            $response->header('Access-Control-Expose-Headers', 'X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time');
        } else if ($origin && $this->isOriginAllowed($origin)) {
            
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Vary', 'Origin');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Expose-Headers', 'X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time');
        }

        return $response;
    }

    
    protected function isOriginAllowed($origin)
    {
        
        $allowedDomains = [
            'simonta-bencana.com',
            'www.simonta-bencana.com',
            'app.simonta-bencana.com',
            'simonta.id',
            'www.simonta.id'
        ];

        
        foreach ($allowedDomains as $domain) {
            if (str_contains($origin, $domain)) {
                return true;
            }
        }

        
        if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1):\d+$/', $origin)) {
            return true;
        }

        
        $tunnelServices = ['ngrok.io', 'xip.io', 'localto.net', 'serveo.net'];
        foreach ($tunnelServices as $service) {
            if (str_contains($origin, $service)) {
                return true;
            }
        }

        
        $patterns = [
            '/^exp:\/\/.*$/',          
            '/^simonta:\/\/.*$/',     
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }
}