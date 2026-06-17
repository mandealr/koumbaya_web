<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SafeRateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    protected $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = new SafeRateLimiter();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 100, $decayMinutes = 1): Response
    {
        // La clé inclut la limite (maxAttempts/decay) pour que des throttles
        // empilés (groupe + route) utilisent des seaux indépendants au lieu de
        // partager le même compteur et de se double-compter.
        $key = $this->resolveRequestSignature($request) . ':' . $maxAttempts . ':' . $decayMinutes;

        if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->rateLimiter->availableIn($key);
            
            return response()->json([
                'error' => 'Trop de requêtes. Réessayez dans ' . $retryAfter . ' secondes.',
                'retry_after' => $retryAfter
            ], 429);
        }

        $this->rateLimiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Ajouter des headers de rate limiting
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $this->rateLimiter->attempts($key)));
        
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
