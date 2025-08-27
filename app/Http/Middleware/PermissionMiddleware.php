<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        if (!$this->userHasPermission($user, $permission)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return $next($request);
    }

    /**
     * Vérifier si l'utilisateur a une permission
     */
    private function userHasPermission($user, string $permission): bool
    {
        // Super Admin a toutes les permissions
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Vérifier si l'utilisateur a la permission via ses rôles
        foreach ($user->roles as $role) {
            if ($role->privileges()->where('name', $permission)->exists()) {
                return true;
            }
        }

        return false;
    }
}