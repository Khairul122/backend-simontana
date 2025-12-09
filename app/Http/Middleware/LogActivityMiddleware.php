<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\LogActivity;
use Symfony\Component\HttpFoundation\Response;

class LogActivityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if ($request->user()) {
            try {
                // Generate activity description based on request method and path
                $activity = $this->generateActivityDescription($request);

                // Log the activity
                LogActivity::create([
                    'user_id' => $request->user()->id,
                    'role' => $request->user()->role,
                    'aktivitas' => $activity,
                    'endpoint' => $request->fullUrl(),
                    'ip_address' => $request->ip(),
                    'device_info' => $request->header('User-Agent'),
                    'created_at' => now()
                ]);
            } catch (\Exception $e) {
                // Silent fail for logging to avoid breaking the main flow
            }
        }

        return $response;
    }

    /**
     * Generate activity description based on request
     */
    private function generateActivityDescription(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Common API patterns
        if (strpos($path, 'api/') === 0) {
            $segments = explode('/', $path);
            $resource = $segments[1] ?? 'unknown';
            $id = $segments[2] ?? null;

            switch ($method) {
                case 'GET':
                    if ($id) {
                        return "Mengakses detail {$resource} (ID: {$id})";
                    }
                    if (strpos($path, '/index') !== false || $id === null) {
                        return "Melihat daftar {$resource}";
                    }
                    return "Mengakses {$resource}";

                case 'POST':
                    return "Menambah data {$resource}";

                case 'PUT':
                case 'PATCH':
                    return "Memperbarui {$resource} (ID: {$id})";

                case 'DELETE':
                    return "Menghapus {$resource} (ID: {$id})";

                default:
                    return "Akses {$method} {$resource}";
            }
        }

        // Default description
        return "Akses {$method} {$path}";
    }
}
