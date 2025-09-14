<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;

trait ApiResponses
{
    /**
     * Réponse de succès standardisée
     */
    protected function successResponse($data = null, string $message = 'Opération réussie', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        // Log si c'est une action importante
        if (in_array($code, [201, 202])) {
            $this->logApiAction($message, $data);
        }

        return response()->json($response, $code);
    }

    /**
     * Réponse d'erreur standardisée
     */
    protected function errorResponse(string $message, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        // Formatage des erreurs pour mobile et web
        if ($errors !== null) {
            if (is_array($errors) || is_object($errors)) {
                $response['errors'] = $errors;
                
                // Format mobile-friendly: tableau simple des erreurs
                if (request()->header('X-Platform') === 'mobile') {
                    $response['error_messages'] = $this->flattenValidationErrors($errors);
                }
            } else {
                $response['error'] = $errors;
            }
        }

        // Log des erreurs importantes
        if ($code >= 500) {
            Log::error('API Error', [
                'message' => $message,
                'code' => $code,
                'errors' => $errors,
                'url' => request()->fullUrl(),
                'user_id' => auth()->id(),
            ]);
        }

        // Audit log pour les erreurs d'authentification
        if ($code === 401 || $code === 403) {
            AuditLog::logApiError('api.error.' . $code, $message, $code);
        }

        return response()->json($response, $code);
    }

    /**
     * Réponse pour les erreurs de validation
     */
    protected function validationErrorResponse($validator): JsonResponse
    {
        $errors = $validator->errors()->toArray();
        $message = 'Erreur de validation';
        
        // Message personnalisé selon la plateforme
        if (request()->header('X-Platform') === 'mobile') {
            $firstError = $validator->errors()->first();
            $message = $firstError ?: $message;
        }

        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Aplatir les erreurs de validation pour mobile
     */
    private function flattenValidationErrors($errors): array
    {
        $flat = [];
        
        if (is_array($errors) || is_object($errors)) {
            foreach ($errors as $field => $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $flat[] = $message;
                    }
                } else {
                    $flat[] = $messages;
                }
            }
        }
        
        return array_unique($flat);
    }

    /**
     * Logger une action API
     */
    private function logApiAction($event, $data = null)
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => $event,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'tags' => ['api', 'success'],
                'metadata' => [
                    'platform' => request()->header('X-Platform', 'web'),
                    'app_version' => request()->header('X-App-Version'),
                    'data_preview' => is_array($data) ? array_slice($data, 0, 3) : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create audit log: ' . $e->getMessage());
        }
    }
}