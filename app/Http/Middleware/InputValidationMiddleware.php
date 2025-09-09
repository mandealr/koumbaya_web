<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class InputValidationMiddleware
{
    /**
     * Patterns XSS dangereux
     */
    private array $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript:[^\s]*/i',
        '/on\w+\s*=\s*["\'][^"\']*["\']?/i',
        '/<iframe[^>]*>/i',
        '/<object[^>]*>/i',
        '/<embed[^>]*>/i',
        '/<form[^>]*>/i',
        '/vbscript:/i',
        '/data:.*base64/i',
        '/<img[^>]*on\w+[^>]*>/i',
        '/<[^>]*style[^>]*expression[^>]*>/i',
    ];

    /**
     * Patterns d'injection SQL
     */
    private array $sqlPatterns = [
        '/(\s|^)(union|select|insert|update|delete|drop|create|alter|exec|execute)\s/i',
        '/(\s|^)(or|and)\s+[\w\'"]+\s*=\s*[\w\'"]+/i',
        '/[\'";]\s*(or|and)\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+/i',
        '/[\'";]\s*(union|select)\s+/i',
        '/--\s*$/m',
        '/\/\*.*?\*\//s',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Valider toutes les entrées de la requête
        $validationResult = $this->validateRequestInputs($request);
        
        if (!$validationResult['isValid']) {
            return $this->createSecurityErrorResponse($validationResult);
        }

        // Logger les tentatives suspectes
        if (!empty($validationResult['warnings'])) {
            $this->logSecurityWarning($request, $validationResult['warnings']);
        }

        return $next($request);
    }

    /**
     * Valider toutes les entrées de la requête
     */
    private function validateRequestInputs(Request $request): array
    {
        $result = [
            'isValid' => true,
            'errors' => [],
            'warnings' => [],
            'threats' => []
        ];

        // Valider les paramètres GET
        foreach ($request->query->all() as $key => $value) {
            $validation = $this->validateInput($key, $value, 'query');
            $this->mergeValidationResult($result, $validation);
        }

        // Valider les données POST/PUT/PATCH
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            foreach ($request->request->all() as $key => $value) {
                $validation = $this->validateInput($key, $value, 'body');
                $this->mergeValidationResult($result, $validation);
            }

            // Valider le JSON dans le body
            if ($request->isJson()) {
                $jsonData = $request->json()->all();
                foreach ($jsonData as $key => $value) {
                    $validation = $this->validateInput($key, $value, 'json');
                    $this->mergeValidationResult($result, $validation);
                }
            }
        }

        // Valider les headers sensibles
        $sensibleHeaders = ['user-agent', 'referer', 'x-forwarded-for'];
        foreach ($sensibleHeaders as $header) {
            $headerValue = $request->header($header);
            if ($headerValue) {
                $validation = $this->validateInput($header, $headerValue, 'header');
                $this->mergeValidationResult($result, $validation, false); // Warnings only for headers
            }
        }

        return $result;
    }

    /**
     * Valider une entrée spécifique
     */
    private function validateInput(string $key, $value, string $source): array
    {
        $result = [
            'isValid' => true,
            'errors' => [],
            'warnings' => [],
            'threats' => []
        ];

        // Ignorer les valeurs non-string
        if (!is_string($value)) {
            return $result;
        }

        // Vérifier les patterns XSS
        foreach ($this->xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $result['isValid'] = false;
                $result['errors'][] = "XSS pattern detected in {$source} field '{$key}'";
                $result['threats'][] = [
                    'type' => 'XSS',
                    'field' => $key,
                    'source' => $source,
                    'pattern' => $pattern
                ];
                break;
            }
        }

        // Vérifier les patterns d'injection SQL
        foreach ($this->sqlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $result['isValid'] = false;
                $result['errors'][] = "SQL injection pattern detected in {$source} field '{$key}'";
                $result['threats'][] = [
                    'type' => 'SQL_INJECTION',
                    'field' => $key,
                    'source' => $source,
                    'pattern' => $pattern
                ];
                break;
            }
        }

        // Vérifications spécifiques par type de champ
        $this->validateFieldSpecific($key, $value, $result);

        // Vérifier la longueur maximale
        if (strlen($value) > 10000) {
            $result['warnings'][] = "Unusually long input in {$source} field '{$key}' (" . strlen($value) . " chars)";
        }

        return $result;
    }

    /**
     * Validations spécifiques par type de champ
     */
    private function validateFieldSpecific(string $key, string $value, array &$result): void
    {
        // Validation pour les emails
        if (str_contains($key, 'email')) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !empty($value)) {
                $result['warnings'][] = "Invalid email format in field '{$key}'";
            }
        }

        // Validation pour les URLs
        if (str_contains($key, 'url') || str_contains($key, 'link')) {
            if (!filter_var($value, FILTER_VALIDATE_URL) && !empty($value)) {
                $result['warnings'][] = "Invalid URL format in field '{$key}'";
            }
        }

        // Validation pour les prix
        if (str_contains($key, 'price') || str_contains($key, 'amount')) {
            $numValue = filter_var($value, FILTER_VALIDATE_FLOAT);
            if ($numValue === false && !empty($value)) {
                $result['warnings'][] = "Invalid numeric value in field '{$key}'";
            } elseif ($numValue < 0 || $numValue > 10000000) {
                $result['warnings'][] = "Suspicious price value in field '{$key}': {$numValue}";
            }
        }

        // Validation pour les numéros de téléphone
        if (str_contains($key, 'phone') || str_contains($key, 'tel')) {
            if (!preg_match('/^\+?[\d\s\-()]{8,15}$/', $value) && !empty($value)) {
                $result['warnings'][] = "Invalid phone format in field '{$key}'";
            }
        }
    }

    /**
     * Fusionner les résultats de validation
     */
    private function mergeValidationResult(array &$main, array $validation, bool $strict = true): void
    {
        if (!$validation['isValid'] && $strict) {
            $main['isValid'] = false;
        }
        
        $main['errors'] = array_merge($main['errors'], $validation['errors']);
        $main['warnings'] = array_merge($main['warnings'], $validation['warnings']);
        $main['threats'] = array_merge($main['threats'], $validation['threats']);
    }

    /**
     * Créer une réponse d'erreur de sécurité
     */
    private function createSecurityErrorResponse(array $validationResult): JsonResponse
    {
        // Logger l'incident de sécurité
        \Log::critical('Security threat blocked', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->getMethod(),
            'errors' => $validationResult['errors'],
            'threats' => $validationResult['threats'],
            'timestamp' => now(),
        ]);

        return response()->json([
            'error' => 'Request blocked for security reasons',
            'message' => 'Votre requête contient des éléments non autorisés et a été bloquée.',
            'code' => 'SECURITY_VIOLATION',
            'timestamp' => now()->toISOString(),
        ], 400);
    }

    /**
     * Logger les avertissements de sécurité
     */
    private function logSecurityWarning(Request $request, array $warnings): void
    {
        \Log::warning('Security warnings detected', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->getMethod(),
            'warnings' => $warnings,
            'timestamp' => now(),
        ]);
    }
}