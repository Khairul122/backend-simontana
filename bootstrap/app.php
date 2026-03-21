<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);

        // Security headers — harus sebelum HandleCors agar CORS + security terkirim bersama
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->append(\App\Http\Middleware\RequestIdMiddleware::class);

        
        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        
        $middleware->alias([
            'check.session' => \App\Http\Middleware\CheckSession::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'api.csrf' => \App\Http\Middleware\ApiMiddleware::class,
            'simple.cors' => \App\Http\Middleware\SimpleCors::class,
            'dynamic.cors' => \App\Http\Middleware\DynamicCors::class,
            'swagger.cors' => \App\Http\Middleware\SwaggerCors::class,
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
            'request.id' => \App\Http\Middleware\RequestIdMiddleware::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            $requestId = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'code' => 'VALIDATION_ERROR',
                'errors' => $e->errors(),
                'details' => $e->errors(),
                'request_id' => $requestId,
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            $requestId = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');

            return response()->json([
                'success' => false,
                'message' => 'Tidak terautentikasi',
                'code' => 'UNAUTHORIZED',
                'request_id' => $requestId,
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            $requestId = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');

            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak',
                'code' => 'FORBIDDEN',
                'request_id' => $requestId,
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            $requestId = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');

            return response()->json([
                'success' => false,
                'message' => 'Resource tidak ditemukan',
                'code' => 'RESOURCE_NOT_FOUND',
                'request_id' => $requestId,
            ], 404);
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, $request) {
            $requestId = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');

            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan',
                'code' => 'RATE_LIMITED',
                'request_id' => $requestId,
            ], 429);
        });

        $exceptions->render(function (\Throwable $e, $request) {
            $requestId = $request->attributes->get('request_id') ?? $request->headers->get('X-Request-Id');

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server',
                'code' => 'INTERNAL_SERVER_ERROR',
                'request_id' => $requestId,
            ], 500);
        });
    })->create();
