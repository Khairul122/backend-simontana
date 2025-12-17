<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DynamicCors
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

        // If there's an origin, check if it's allowed
        if ($origin) {
            if ($this->isOriginAllowed($origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Vary: Origin');
                header('Access-Control-Allow-Credentials: true');
            }
        }

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            if ($origin && $this->isOriginAllowed($origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Device-Platform, X-App-Version, X-Client-Info, X-Mobile-Platform');
                header('Access-Control-Max-Age: 86400');
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Expose-Headers: X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time');
            }

            return response('', 200);
        }

        // Handle actual request
        $response = $next($request);

        // Add CORS headers to the response
        if ($origin && $this->isOriginAllowed($origin)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Vary', 'Origin');
            $response->header('Access-Control-Allow-Credentials', true);
            $response->header('Access-Control-Expose-Headers', 'X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time');
        }

        return $response;
    }

    /**
     * Check if the origin is allowed
     *
     * @param string $origin
     * @return bool
     */
    protected function isOriginAllowed($origin)
    {
        // For development environment, allow all origins
        $env = config('app.env', 'local');
        if ($env === 'local' || $env === 'development') {
            return true;
        }

        // Production domains
        $allowedDomains = [
            'localhost',
            '127.0.0.1',
            'simonta-bencana.com',
            'www.simonta-bencana.com',
            'app.simonta-bencana.com',
            'simonta.id'
        ];

        // Check against allowed domains
        foreach ($allowedDomains as $domain) {
            if (str_contains($origin, $domain)) {
                return true;
            }
        }

        // Check against development tunneling services
        $tunnelServices = ['ngrok.io', 'xip.io', 'localto.net', 'local.webhook.moran.com', 'ngrok-free.app', 'serveo.net'];
        foreach ($tunnelServices as $service) {
            if (str_contains($origin, $service)) {
                return true;
            }
        }

        // Check against localhost with any port
        if (preg_match('/^https?:\/\/localhost:\d+$/', $origin)) {
            return true;
        }

        // Check against 127.0.0.1 with any port
        if (preg_match('/^https?:\/\/127\.0\.0\.1:\d+$/', $origin)) {
            return true;
        }

        // Check against local network IPs
        if (preg_match('/^https?:\/\/(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.).*:\d*$/', $origin)) {
            return true;
        }

        // Allow any origin that matches our custom patterns
        $patterns = [
            '/^exp:\/\/.*$/',          // Expo React Native
            '/^simonta:\/\/.*$/',     // Custom deep links
            '/^https?:\/\/.*\.' . $env . '\.com$/', // Environment-specific
            '/^https?:\/\/.*\.localto\.net$/', // Localtonet
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }
}