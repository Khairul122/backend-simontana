<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string[]  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau tidak ditemukan',
                'code' => 'NO_AUTHENTICATED_USER'
            ], 401);
        }

        $userRole = $user->role;

        // Check if user has required role
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengakses resource ini',
                'required_roles' => $roles,
                'user_role' => $userRole,
                'code' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        return $next($request);
    }
}