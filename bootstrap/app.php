<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware applied to all HTTP requests
        $middleware->append([
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Web-only middleware
        $middleware->web(append: [
            \App\Http\Middleware\SetTimezoneFromSettings::class,
        ]);

        // Route middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'log.exam.ratelimit' => \App\Http\Middleware\LogExamRateLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
