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

        if (!$user || $user->role !== 'MANAGER') {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return $next($request);
    }
}