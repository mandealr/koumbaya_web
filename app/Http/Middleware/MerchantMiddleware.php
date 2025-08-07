<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MerchantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Vérifier que l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['error' => 'Authentification requise'], 401);
        }

        // Vérifier que l'utilisateur est un marchand
        if (!$user->is_merchant) {
            return response()->json([
                'error' => 'Accès refusé. Seuls les marchands peuvent accéder à cette ressource.',
                'required_role' => 'MERCHANT',
                'user_role' => $user->role
            ], 403);
        }

        // Vérifier que le compte est actif
        if (!$user->is_active) {
            return response()->json(['error' => 'Compte marchand désactivé'], 403);
        }

        return $next($request);
    }
}
