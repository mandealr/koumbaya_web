<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateJsonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier seulement pour les requêtes POST, PUT, PATCH avec du contenu
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH']) && $request->getContentLength() > 0) {
            
            // Vérifier le Content-Type
            if (!$request->isJson()) {
                return response()->json([
                    'error' => 'Content-Type doit être application/json',
                    'received_content_type' => $request->header('Content-Type')
                ], 400);
            }

            // Vérifier que le JSON est valide
            $content = $request->getContent();
            if (!empty($content)) {
                json_decode($content);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'error' => 'JSON invalide',
                        'json_error' => json_last_error_msg()
                    ], 400);
                }
            }
        }

        // S'assurer que la réponse est en JSON
        $response = $next($request);
        
        // Forcer le Content-Type de la réponse à JSON pour les API
        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
