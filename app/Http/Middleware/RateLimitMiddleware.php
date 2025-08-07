<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 100, $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Trop de requêtes. Réessayez dans ' . $retryAfter . ' secondes.',
                'retry_after' => $retryAfter
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Ajouter des headers de rate limiting
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - RateLimiter::attempts($key)));
        
        return $response;
    }

    /**
     * Résoudre la signature de la requête pour le rate limiting
     */
    protected function resolveRequestSignature($request)
    {
        $user = $request->user();
        
        if ($user) {
            return 'rate_limit:user:' . $user->id;
        }

        return 'rate_limit:ip:' . $request->ip();
    }
}
