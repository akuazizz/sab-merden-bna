<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias untuk role-based access control (Spatie Permission)
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureHasRole::class,
        ]);

        // Kecualikan webhook dari CSRF agar Midtrans bisa POST tanpa token
        $middleware->validateCsrfTokens(except: [
            '/webhook/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
