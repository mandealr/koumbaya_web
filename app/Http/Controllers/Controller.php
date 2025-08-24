<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Koumbaya Marketplace API",
 *     version="1.0.0",
 *     description="API REST pour la marketplace Koumbaya avec système de tombolas et paiements mobile money",
 *     @OA\Contact(
 *         email="dev@koumbaya.com",
 *         name="Équipe Koumbaya"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Serveur de développement Koumbaya"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Authentification via token Bearer JWT"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Laravel Sanctum authentication token (Bearer token)"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints d'authentification et gestion utilisateurs"
 * )
 * 
 * @OA\Tag(
 *     name="Categories", 
 *     description="Gestion des catégories de produits"
 * )
 * 
 * @OA\Tag(
 *     name="Products",
 *     description="Gestion des produits et catalogue"
 * )
 * 
 * @OA\Tag(
 *     name="Lotteries",
 *     description="Gestion des tombolas et tirages"
 * )
 * 
 * @OA\Tag(
 *     name="Payments",
 *     description="Système de paiement E-Billing et Mobile Money"
 * )
 * 
 * @OA\Tag(
 *     name="Countries",
 *     description="Gestion des pays et localisation"
 * )
 * 
 * @OA\Tag(
 *     name="Languages",
 *     description="Gestion des langues et internationalisation"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Success response helper
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error response helper
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendError($message = 'Error', $errors = [], $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Validation error response helper
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendValidationError($validator)
    {
        return $this->sendError('Validation failed', $validator->errors(), 422);
    }
}
