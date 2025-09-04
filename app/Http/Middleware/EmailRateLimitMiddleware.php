<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EmailRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 60): Response
    {
        $email = $this->getEmailFromRequest($request);
        
        if (!$email) {
            return $next($request);
        }

        $key = "email_rate_limit:" . md5($email);
        $attempts = Cache::get($key, 0);

        // Vérifier si la limite est atteinte
        if ($attempts >= $maxAttempts) {
            Log::warning('Email rate limit exceeded', [
                'email' => $email,
                'attempts' => $attempts,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Trop de tentatives d\'envoi d\'email. Veuillez patienter avant de réessayer.',
                'retry_after' => $decayMinutes * 60
            ], 429);
        }

        // Incrémenter le compteur
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        return $next($request);
    }

    /**
     * Extraire l'email de la requête
     */
    private function getEmailFromRequest(Request $request): ?string
    {
        // Chercher l'email dans différents champs possibles
        return $request->input('email') ?? 
               $request->input('user_email') ?? 
               $request->input('to_email') ?? 
               ($request->user() ? $request->user()->email : null);
    }
}
