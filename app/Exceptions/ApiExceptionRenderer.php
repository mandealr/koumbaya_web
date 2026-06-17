<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Rendu JSON centralisé et homogène des exceptions pour l'API.
 *
 * Garantit un contrat de réponse d'erreur unique :
 *   { "success": false, "message": string, "errors"?: object, "error_messages"?: string[] }
 * (le bloc error_messages n'est ajouté que pour les clients mobiles, header X-Platform).
 */
class ApiExceptionRenderer
{
    public static function render(Throwable $e, Request $request): JsonResponse
    {
        [$status, $message, $errors] = self::map($e);

        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;

            if ($request->header('X-Platform') === 'mobile') {
                $payload['error_messages'] = self::flatten($errors);
            }
        }

        // En debug, exposer le détail technique pour les 500 (jamais en prod).
        if ($status >= 500 && config('app.debug')) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return response()->json($payload, $status);
    }

    /**
     * @return array{0:int,1:string,2:array|null}
     */
    private static function map(Throwable $e): array
    {
        if ($e instanceof ValidationException) {
            return [422, 'Erreur de validation', $e->errors()];
        }

        if ($e instanceof AuthenticationException) {
            return [401, 'Non authentifié', null];
        }

        if ($e instanceof AuthorizationException) {
            return [403, $e->getMessage() ?: 'Action non autorisée', null];
        }

        if ($e instanceof ModelNotFoundException) {
            return [404, 'Ressource introuvable', null];
        }

        if ($e instanceof NotFoundHttpException) {
            return [404, 'Ressource ou route introuvable', null];
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return [405, 'Méthode HTTP non autorisée', null];
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            return [$status, $e->getMessage() ?: self::defaultMessageFor($status), null];
        }

        // Erreur non gérée : message générique en prod, détaillé en debug.
        $message = config('app.debug') ? $e->getMessage() : 'Une erreur interne est survenue';

        return [500, $message ?: 'Une erreur interne est survenue', null];
    }

    private static function defaultMessageFor(int $status): string
    {
        return match ($status) {
            400 => 'Requête invalide',
            403 => 'Action non autorisée',
            404 => 'Ressource introuvable',
            429 => 'Trop de requêtes',
            default => 'Erreur',
        };
    }

    /**
     * Aplatit les erreurs de validation en une liste de messages (clients mobiles).
     *
     * @param  array<string,mixed>  $errors
     * @return string[]
     */
    private static function flatten(array $errors): array
    {
        $flat = [];
        foreach ($errors as $messages) {
            foreach ((array) $messages as $message) {
                $flat[] = $message;
            }
        }

        return array_values(array_unique($flat));
    }
}
