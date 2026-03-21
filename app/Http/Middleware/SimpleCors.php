<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SimpleCors — versi ringan dari DynamicCors untuk endpoint yang memerlukan
 * CORS tanpa credential (misalnya endpoint publik).
 *
 * Production: baca whitelist dari CORS_ALLOWED_ORIGINS di .env.
 * Development: izinkan localhost dan LAN (tidak ada tunnel services).
 */
class SimpleCors
{
    private const ALLOWED_METHODS = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
    private const ALLOWED_HEADERS = 'Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, X-Device-Platform, X-App-Version, X-Mobile-Platform';
    private const EXPOSED_HEADERS = 'X-Total-Count, X-Per-Page, X-Current-Page, X-Total-Pages, Content-Disposition, X-API-Version, X-Response-Time, X-Request-Id';

    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
            if ($origin && $this->isOriginAllowed($origin)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Methods', self::ALLOWED_METHODS);
                $response->headers->set('Access-Control-Allow-Headers', self::ALLOWED_HEADERS);
                $response->headers->set('Access-Control-Max-Age', '86400');
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $response->headers->set('Access-Control-Expose-Headers', self::EXPOSED_HEADERS);
                $response->headers->set('Vary', 'Origin');
            }
            return $response;
        }

        $response = $next($request);

        if ($origin && $this->isOriginAllowed($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', self::EXPOSED_HEADERS);
            $response->headers->set('Vary', 'Origin');
        }

        return $response;
    }

    protected function isOriginAllowed(string $origin): bool
    {
        $env = config('app.env', 'production');

        // Development: izinkan localhost dan LAN (tidak ada tunnel services)
        if (in_array($env, ['local', 'development'], true)) {
            if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/', $origin)) {
                return true;
            }
            if (preg_match('/^https?:\/\/(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)\d+\.\d+(:\d+)?$/', $origin)) {
                return true;
            }
        }

        // Baca whitelist dari .env — berlaku di semua environment
        $allowedOrigins = array_filter(
            array_map('trim', explode(',', (string) config('app.cors_allowed_origins', env('CORS_ALLOWED_ORIGINS', ''))))
        );

        return in_array($origin, $allowedOrigins, true);
    }
}