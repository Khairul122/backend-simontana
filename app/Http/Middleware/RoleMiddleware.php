<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    private function errorResponse(string $message, int $status, string $code, array $extra = []): Response
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message,
            'code' => $code,
        ], $extra), $status);
    }

    
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Token tidak valid atau tidak ditemukan', 401, 'NO_AUTHENTICATED_USER');
        }

        $userRole = $user->role;

        
        if (!in_array($userRole, $roles)) {
            return $this->errorResponse('Anda tidak memiliki izin untuk mengakses resource ini', 403, 'INSUFFICIENT_PERMISSIONS', [
                'required_roles' => $roles,
                'user_role' => $userRole,
            ]);
        }

        return $next($request);
    }
}
