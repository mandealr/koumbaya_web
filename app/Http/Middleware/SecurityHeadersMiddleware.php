<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Headers de sécurité pour API
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy pour API
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none';");
        
        // HSTS (HTTP Strict Transport Security) - seulement en HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Supprimer les headers qui révèlent des informations
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // Ajouter des headers d'API
        $response->headers->set('X-API-Version', '1.0.0');
        $response->headers->set('X-Request-ID', $request->header('X-Request-ID', uniqid()));

        return $response;
    }
}
