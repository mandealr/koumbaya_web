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
        // Middleware global pour la sécurité
        $middleware->web(append: [
            \App\Http\Middleware\ForceHttpsMiddleware::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\InputValidationMiddleware::class,
        ]);

        // Middleware global pour l'API
        $middleware->api(prepend: [
            \App\Http\Middleware\DetectPlatformMiddleware::class,
            \App\Http\Middleware\ApiAuditMiddleware::class,
        ]);

        // Middlewares nommés
        $middleware->alias([
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'merchant' => \App\Http\Middleware\MerchantMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'throttle.api' => \App\Http\Middleware\RateLimitMiddleware::class,
            'security' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
            'validate.json' => \App\Http\Middleware\ValidateJsonMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'csrf' => \App\Http\Middleware\VerifyCsrfToken::class,
            'detect.platform' => \App\Http\Middleware\DetectPlatformMiddleware::class,
            'api.audit' => \App\Http\Middleware\ApiAuditMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
