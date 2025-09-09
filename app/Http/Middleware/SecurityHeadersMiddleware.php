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

        // Déterminer le type de réponse pour ajuster les headers
        $isApiRequest = $request->is('api/*') || $request->wantsJson();
        $isWebRequest = !$isApiRequest;

        // Headers de sécurité communs
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy améliorée
        $permissionsPolicy = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'magnetometer=()',
            'gyroscope=()',
            'speaker=()',
            'vibrate=()',
            'fullscreen=(self)',
            'payment=(self)'
        ];
        $response->headers->set('Permissions-Policy', implode(', ', $permissionsPolicy));

        // Content Security Policy adaptatif
        if ($isApiRequest) {
            // CSP strict pour API
            $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none';");
        } elseif ($isWebRequest) {
            // CSP pour application web Vue.js
            $cspDirectives = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Note: 'unsafe-eval' requis pour Vue.js dev
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "img-src 'self' data: https: blob:",
                "font-src 'self' https://fonts.gstatic.com",
                "connect-src 'self' https://api.koumbaya.com wss:",
                "media-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "frame-ancestors 'none'",
                "form-action 'self'",
                "upgrade-insecure-requests"
            ];
            
            // En production, retirer 'unsafe-eval'
            if (config('app.env') === 'production') {
                $cspDirectives[1] = "script-src 'self' 'unsafe-inline'";
            }
            
            $response->headers->set('Content-Security-Policy', implode('; ', $cspDirectives));
        }

        // HSTS (HTTP Strict Transport Security)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Feature Policy (deprecated but still supported)
        $response->headers->set('Feature-Policy', 
            "geolocation 'none'; microphone 'none'; camera 'none'; payment 'self'"
        );

        // Cross-Origin Policies
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // Supprimer les headers qui révèlent des informations sensibles
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->remove('X-Laravel-Session');

        // Headers spécifiques aux API
        if ($isApiRequest) {
            $response->headers->set('X-API-Version', '1.0.0');
            $response->headers->set('X-Request-ID', $request->header('X-Request-ID', uniqid()));
            $response->headers->set('X-RateLimit-Remaining', $this->getRemainingRateLimit($request));
        }

        // Cache Control pour les ressources statiques
        if ($request->is('*.css') || $request->is('*.js') || $request->is('*.png') || $request->is('*.jpg')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        // Logging des requêtes suspectes
        $this->logSuspiciousActivity($request);

        return $response;
    }

    /**
     * Obtenir le nombre de requêtes restantes pour le rate limiting
     */
    private function getRemainingRateLimit(Request $request): int
    {
        $key = 'rate_limit:' . $request->ip();
        $remaining = cache()->get($key, 100);
        return max(0, $remaining);
    }

    /**
     * Logger l'activité suspecte
     */
    private function logSuspiciousActivity(Request $request): void
    {
        $suspiciousPatterns = [
            'script[^>]*>.*?</script>',
            'javascript:',
            'on\w+\s*=',
            'eval\s*\(',
            '<\s*iframe',
            'document\.cookie',
            'window\.location',
        ];

        $userAgent = $request->userAgent() ?? '';
        $queryString = $request->getQueryString() ?? '';
        $content = $request->getContent();

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $userAgent . $queryString . $content)) {
                \Log::warning('Suspicious activity detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $userAgent,
                    'url' => $request->fullUrl(),
                    'pattern_matched' => $pattern,
                    'timestamp' => now(),
                ]);
                break;
            }
        }
    }
}
