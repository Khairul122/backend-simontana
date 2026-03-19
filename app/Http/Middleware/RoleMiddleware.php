<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    private function errorResponse(Request $request, string $message, int $status, string $code, array $extra = []): Response
    {
        $payload = array_merge([
            'success' => false,
            'message' => $message,
            'code' => $code,
        ], $extra);

        $requestId = $request->attributes->get('request_id');
        if ($requestId) {
            $payload['request_id'] = $requestId;
        }

        return response()->json($payload, $status);
    }

    
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse($request, 'Token tidak valid atau tidak ditemukan', 401, 'NO_AUTHENTICATED_USER');
        }

        $userRole = $user->role;

        
        if (!in_array($userRole, $roles)) {
            return $this->errorResponse($request, 'Anda tidak memiliki izin untuk mengakses resource ini', 403, 'INSUFFICIENT_PERMISSIONS', [
                'required_roles' => $roles,
                'user_role' => $userRole,
            ]);
        }

        return $next($request);
    }
}
