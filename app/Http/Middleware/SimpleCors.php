<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SimpleCors
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
        // Get the origin from the request
        $origin = $request->header('Origin');

        // For development environment, allow all origins
        $env = config('app.env', 'local');
        if ($env === 'local' || $env === 'development') {
            // Always allow all origins in development
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Device-Platform, X-App-Version, X-Client-Info, X-Mobile-Platform');
            header('Access-Control-Allow-Credentials: false'); // Set to false for development to avoid CORS issues
            header('Access-Control-Max-Age: 86400');
        } else {
            // For production, check if origin is allowed
            if ($origin && $this->isOriginAllowed($origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Vary: Origin');
                header('Access-Control-Allow-Credentials: true');
            }
        }

        // Handle preflight OPTIONS request
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

        // Handle actual request
        $response = $next($request);

        // Add CORS headers to the response
        if ($env === 'local' || $env === 'development') {
            // Always add CORS headers in development
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Device-Platform, X-App-Version, X-Client-Info, X-Mobile-Platform');
            $response->header('Access-Control-Allow-Credentials', 'false');
            $response->header('Access-Control-Expose-Headers', 'X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time');
        } else if ($origin && $this->isOriginAllowed($origin)) {
            // Add CORS headers for allowed origins in production
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Vary', 'Origin');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Expose-Headers', 'X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time');
        }

        return $response;
    }

    /**
     * Check if the origin is allowed (for production)
     *
     * @param string $origin
     * @return bool
     */
    protected function isOriginAllowed($origin)
    {
        // Production domains
        $allowedDomains = [
            'simonta-bencana.com',
            'www.simonta-bencana.com',
            'app.simonta-bencana.com',
            'simonta.id',
            'www.simonta.id'
        ];

        // Check against allowed domains
        foreach ($allowedDomains as $domain) {
            if (str_contains($origin, $domain)) {
                return true;
            }
        }

        // Check against localhost (for local production testing)
        if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1):\d+$/', $origin)) {
            return true;
        }

        // Check against development tunneling services
        $tunnelServices = ['ngrok.io', 'xip.io', 'localto.net', 'serveo.net'];
        foreach ($tunnelServices as $service) {
            if (str_contains($origin, $service)) {
                return true;
            }
        }

        // Allow mobile app deep links
        $patterns = [
            '/^exp:\/\/.*$/',          // Expo React Native
            '/^simonta:\/\/.*$/',     // Custom deep links
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }
}