<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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

        // Add CORS middleware for API routes
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // Add session middleware for API routes (required for CheckSession middleware)
        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        // Register middleware aliases
        $middleware->alias([
            'check.session' => \App\Http\Middleware\CheckSession::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'api.csrf' => \App\Http\Middleware\ApiMiddleware::class,
            'simple.cors' => \App\Http\Middleware\SimpleCors::class,
            'dynamic.cors' => \App\Http\Middleware\DynamicCors::class,
            'swagger.cors' => \App\Http\Middleware\SwaggerCors::class,
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
