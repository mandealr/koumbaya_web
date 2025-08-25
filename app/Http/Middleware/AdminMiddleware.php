<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'Authentification requise'], 401);
        }

        // Vérifier si l'utilisateur a le rôle Admin ou Super Admin
        $hasAdminRole = $user->roles()->whereIn('name', ['Admin', 'Super Admin'])->exists();
        
        if (!$hasAdminRole) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return $next($request);
    }
}