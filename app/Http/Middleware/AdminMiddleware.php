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
        // Support nouveaux noms (snake_case) + anciens noms (rétrocompatibilité)
        $hasAdminRole = $user->roles()->whereIn('name', [
            'admin', 'superadmin', 'agent',  // Nouveaux noms
            'Admin', 'Super Admin', 'Agent'  // Anciens noms (rétrocompatibilité)
        ])->exists();

        if (!$hasAdminRole) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return $next($request);
    }
}