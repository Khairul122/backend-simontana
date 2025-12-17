<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
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
        // Check if the request has an authorization header
        if (!$request->header('Authorization')) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $request->bearerToken();

        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Add user to request for later use
            $request->attributes->set('user', $user);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token telah kadaluarsa'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak dapat diproses'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}