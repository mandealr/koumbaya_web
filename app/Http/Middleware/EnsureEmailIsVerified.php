<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez vérifier votre compte pour effectuer cette action.',
                'error_code' => 'EMAIL_NOT_VERIFIED',
                'requires_verification' => true,
                'verification_type' => $this->getVerificationType($request),
                'email' => $this->maskEmail($request->user()->email)
            ], 403);
        }

        return $next($request);
    }

    /**
     * Détermine le type de vérification selon le client
     */
    private function getVerificationType(Request $request): string
    {
        $userAgent = $request->header('User-Agent', '');
        $isFlutterApp = str_contains($userAgent, 'Dart/') || $request->has('is_mobile_app');
        
        return $isFlutterApp ? 'otp' : 'email_link';
    }

    /**
     * Masquer l'email pour l'affichage public
     */
    private function maskEmail($email): string
    {
        $parts = explode('@', $email);
        $localPart = $parts[0];
        $domain = $parts[1];
        
        if (strlen($localPart) <= 2) {
            return str_repeat('*', strlen($localPart)) . '@' . $domain;
        }
        
        $maskedLocal = substr($localPart, 0, 2) . str_repeat('*', strlen($localPart) - 2);
        return $maskedLocal . '@' . $domain;
    }
}