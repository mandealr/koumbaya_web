<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::middleware('web')
                ->prefix('api')
                ->group(base_path('routes/test.php'));
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middlewares nommés seulement (pas de globaux pour l'instant)
        $middleware->alias([
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'merchant' => \App\Http\Middleware\MerchantMiddleware::class,
            'throttle.api' => \App\Http\Middleware\RateLimitMiddleware::class,
            'security' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
            'validate.json' => \App\Http\Middleware\ValidateJsonMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
