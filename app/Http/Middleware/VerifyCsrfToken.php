<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken
{
    /**
     * Routes qui ne nécessitent pas de vérification CSRF
     * 
     * @var array
     */
    protected $except = [
        'api/*',
        'webhook/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignorer les routes dans $except
        if ($this->shouldPassThrough($request) || 
            $this->isReading($request) || 
            $this->tokensMatch($request)) {
            return $next($request);
        }

        // Logger la tentative d'attaque CSRF
        Log::warning('Tentative d\'attaque CSRF détectée', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'referer' => $request->header('referer'),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
            'timestamp' => now()
        ]);

        throw new TokenMismatchException('Token CSRF invalide');
    }

    /**
     * Vérifier si la route doit passer sans vérification CSRF
     */
    protected function shouldPassThrough(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/' && $request->is($except)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifier si c'est une requête de lecture (GET, HEAD, OPTIONS)
     */
    protected function isReading(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * Vérifier si les tokens CSRF correspondent
     */
    protected function tokensMatch(Request $request): bool
    {
        $token = $this->getTokenFromRequest($request);
        
        return is_string($request->session()->token()) &&
               is_string($token) &&
               hash_equals($request->session()->token(), $token);
    }

    /**
     * Récupérer le token CSRF de la requête
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        $token = $request->input('_token') ?: 
                 $request->header('X-CSRF-TOKEN') ?: 
                 $request->header('X-XSRF-TOKEN');

        if (!$token && $header = $request->header('X-XSRF-TOKEN')) {
            $token = $this->decryptCookie($header);
        }

        return $token;
    }

    /**
     * Décrypter le cookie XSRF-TOKEN (si utilisé)
     */
    protected function decryptCookie(string $cookie): string
    {
        try {
            return decrypt($cookie, false);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Générer un nouveau token CSRF
     */
    public static function generateToken(): string
    {
        return Str::random(40);
    }

    /**
     * Valider un token manuellement
     */
    public static function validateToken(string $sessionToken, string $requestToken): bool
    {
        return is_string($sessionToken) &&
               is_string($requestToken) &&
               hash_equals($sessionToken, $requestToken);
    }
}
