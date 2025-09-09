<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsMiddleware
{
    /**
     * Forcer l'utilisation d'HTTPS et ajouter les headers HSTS
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si HTTPS doit être forcé
        if (config('security.https.force_https') && !$request->isSecure()) {
            // Redirection vers HTTPS
            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        // Ajouter les headers HTTPS/HSTS seulement si la connexion est sécurisée
        if ($request->isSecure()) {
            $this->addSecurityHeaders($response);
        }

        return $response;
    }

    /**
     * Ajouter les headers de sécurité HTTPS
     */
    private function addSecurityHeaders(Response $response): void
    {
        // Header HSTS (HTTP Strict Transport Security)
        $hstsMaxAge = config('security.https.hsts_max_age', 31536000);
        $hstsDirectives = "max-age={$hstsMaxAge}";

        if (config('security.https.include_subdomains', true)) {
            $hstsDirectives .= '; includeSubDomains';
        }

        if (config('security.https.preload', true)) {
            $hstsDirectives .= '; preload';
        }

        $response->headers->set('Strict-Transport-Security', $hstsDirectives);

        // Header pour éviter le mixed content
        $response->headers->set('Content-Security-Policy', 'upgrade-insecure-requests;');

        // Header pour indiquer que le site supporte HTTPS
        $response->headers->set('Vary', 'X-Forwarded-Proto');
    }

    /**
     * Vérifier si l'URL est en HTTPS même derrière un proxy
     */
    private function isSecureConnection(Request $request): bool
    {
        return $request->isSecure() || 
               $request->header('X-Forwarded-Proto') === 'https' ||
               $request->header('X-Forwarded-Ssl') === 'on' ||
               $request->header('CloudFront-Forwarded-Proto') === 'https';
    }

    /**
     * Obtenir l'URL HTTPS équivalente
     */
    private function getHttpsUrl(Request $request): string
    {
        $url = $request->getUri();
        return str_replace('http://', 'https://', $url);
    }

    /**
     * Vérifier si la requête provient d'un bot/crawler
     */
    private function isBotRequest(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');
        
        $botPatterns = [
            'googlebot',
            'bingbot',
            'slurp',
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'facebookexternalhit',
            'twitterbot',
            'whatsapp',
            'telegrambot'
        ];

        foreach ($botPatterns as $pattern) {
            if (strpos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Logger les redirections HTTPS pour le monitoring
     */
    private function logHttpsRedirect(Request $request): void
    {
        \Log::info('Redirection HTTPS forcée', [
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'is_bot' => $this->isBotRequest($request),
            'timestamp' => now()
        ]);
    }
}