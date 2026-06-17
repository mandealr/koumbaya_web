<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Vérifie l'authenticité d'un callback de paiement E-Billing.
 *
 * Deux couches de défense, activées dès qu'elles sont configurées (non-bloquant
 * sur l'existant tant que les variables d'env ne sont pas renseignées) :
 *   1. Allowlist d'adresses IP source       (services.ebilling.allowed_ips)
 *   2. Signature HMAC-SHA256 du payload      (services.ebilling.webhook_secret),
 *      transmise via le header X-Signature (ou X-EBilling-Signature / champ "signature").
 *
 * Tant qu'aucune couche n'est configurée, un avertissement est journalisé à
 * chaque appel afin que l'absence de protection reste visible.
 *
 * @return array{allowed: bool, message: ?string, enforced: bool}
 */
class PaymentCallbackVerifier
{
    public function verify(Request $request): array
    {
        // 1. Allowlist IP (si configurée)
        $allowedIps = $this->normalizeIps(config('services.ebilling.allowed_ips'));
        if (!empty($allowedIps) && !$this->ipAllowed($request, $allowedIps)) {
            $this->logSecurityEvent('ip_not_allowed', $request);
            return ['allowed' => false, 'message' => 'IP not allowed', 'enforced' => true];
        }

        // 2. Signature HMAC (si un secret est configuré)
        $secret = config('services.ebilling.webhook_secret');

        if (empty($secret)) {
            if (empty($allowedIps)) {
                Log::warning('Payment callback processed WITHOUT verification '
                    . '(set EBILLING_WEBHOOK_SECRET and/or EBILLING_ALLOWED_IPS to secure it)', [
                        'ip' => $request->ip(),
                    ]);
            }
            return ['allowed' => true, 'message' => null, 'enforced' => !empty($allowedIps)];
        }

        $signature = $request->header('X-Signature')
            ?? $request->header('X-EBilling-Signature')
            ?? $request->input('signature');

        if (empty($signature)) {
            return ['allowed' => false, 'message' => 'Missing signature', 'enforced' => true];
        }

        // La signature porte sur le corps BRUT de la requête (et non sur
        // $request->all(), qui est pollué par des middlewares ajoutant des
        // clés comme detected_platform).
        $body = $request->getContent();
        if ($body === '' || $body === null) {
            $body = json_encode($request->json()->all() ?: []);
        }
        $expected = hash_hmac('sha256', $body, $secret);

        if (!is_string($signature) || !hash_equals($expected, $signature)) {
            $this->logSecurityEvent('signature_mismatch', $request);
            return ['allowed' => false, 'message' => 'Invalid signature', 'enforced' => true];
        }

        return ['allowed' => true, 'message' => null, 'enforced' => true];
    }

    /**
     * @param mixed $raw
     * @return string[]
     */
    private function normalizeIps($raw): array
    {
        if (empty($raw)) {
            return [];
        }

        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw)));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
    }

    /**
     * @param string[] $allowedIps
     */
    private function ipAllowed(Request $request, array $allowedIps): bool
    {
        $candidates = array_filter(array_merge([$request->ip()], $request->ips()));

        foreach ($candidates as $ip) {
            if (in_array($ip, $allowedIps, true)) {
                return true;
            }
        }

        return false;
    }

    private function logSecurityEvent(string $reason, Request $request): void
    {
        Log::warning('Payment callback security event', [
            'reason' => $reason,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reference' => $request->input('reference'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
