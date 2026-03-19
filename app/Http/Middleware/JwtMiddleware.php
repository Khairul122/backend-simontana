<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    private function errorResponse(Request $request, string $message, int $status, string $code, mixed $details = null): Response
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'code' => $code,
        ];

        if ($details !== null) {
            $payload['details'] = $details;
        }

        $requestId = $request->attributes->get('request_id');
        if ($requestId) {
            $payload['request_id'] = $requestId;
        }

        return response()->json($payload, $status);
    }

    public function handle(Request $request, Closure $next)
    {
        $authorization = (string) $request->header('Authorization', '');

        if ($authorization === '' || !str_starts_with($authorization, 'Bearer ')) {
            return $this->errorResponse($request, 'Token tidak ditemukan', Response::HTTP_UNAUTHORIZED, 'TOKEN_MISSING');
        }

        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->errorResponse($request, 'Pengguna tidak ditemukan', Response::HTTP_UNAUTHORIZED, 'USER_NOT_FOUND');
            }

            Auth::guard('api')->setUser($user);
            $request->attributes->set('user', $user);
            $request->setUserResolver(static fn () => $user);
        } catch (TokenExpiredException $e) {
            return $this->errorResponse($request, 'Token telah kadaluarsa', Response::HTTP_UNAUTHORIZED, 'TOKEN_EXPIRED');
        } catch (TokenInvalidException $e) {
            return $this->errorResponse($request, 'Token tidak valid', Response::HTTP_UNAUTHORIZED, 'TOKEN_INVALID');
        } catch (JWTException $e) {
            return $this->errorResponse($request, 'Token tidak dapat diproses', Response::HTTP_UNAUTHORIZED, 'TOKEN_PROCESSING_FAILED');
        }

        return $next($request);
    }
}
