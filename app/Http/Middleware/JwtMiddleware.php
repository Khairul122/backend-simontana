<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\Pengguna;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan'
            ], 401);
        }

        // Remove "Bearer " prefix if exists
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        try {
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));

            // Get user from token
            $user = Pengguna::find($decoded->sub);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 401);
            }

            // Set user to request for later use
            $request->merge(['user' => $user]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);

        } catch (ExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token telah kadaluarsa'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid: ' . $e->getMessage()
            ], 401);
        }
    }
}
