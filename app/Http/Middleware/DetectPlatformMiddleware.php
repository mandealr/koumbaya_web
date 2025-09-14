<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectPlatformMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Détecter la plateforme depuis les headers ou User-Agent
        $userAgent = $request->header('User-Agent', '');
        $platform = 'web'; // défaut
        
        // Flutter/Dart app
        if (str_contains($userAgent, 'Dart/') || str_contains($userAgent, 'Flutter')) {
            $platform = 'mobile';
        }
        
        // Headers personnalisés
        if ($request->hasHeader('X-Platform')) {
            $platform = $request->header('X-Platform');
        }
        
        // Mobile app flag
        if ($request->has('is_mobile_app') || $request->header('X-Mobile-App')) {
            $platform = 'mobile';
        }
        
        // Ajouter les headers pour le reste de l'application
        $request->headers->set('X-Platform', $platform);
        $request->merge(['detected_platform' => $platform]);
        
        // Définir des headers par défaut pour mobile
        if ($platform === 'mobile') {
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('Content-Type', 'application/json');
        }
        
        return $next($request);
    }
}