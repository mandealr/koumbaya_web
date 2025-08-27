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
 *     name="Payment Callbacks",
 *     description="Webhooks et callbacks de paiement orchestrés avec sécurité renforcée"
 * )
 * 
 * @OA\Tag(
 *     name="Orders",
 *     description="Gestion des commandes, suivi et facturation PDF"
 * )
 * 
 * @OA\Tag(
 *     name="Merchant Orders",
 *     description="Gestion des commandes côté marchand avec export CSV et filtres avancés"
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
 * 
 * @OA\Schema(
 *     schema="OrderSummary",
 *     title="Résumé de commande",
 *     description="Structure de données pour l'affichage en liste des commandes",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD-67890ABCDE"),
 *     @OA\Property(property="type", type="string", enum={"lottery", "direct"}, example="lottery"),
 *     @OA\Property(property="status", type="string", enum={"pending", "awaiting_payment", "paid", "failed", "cancelled", "fulfilled", "refunded"}, example="paid"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=5000),
 *     @OA\Property(property="currency", type="string", example="XAF"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="fulfilled_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="product", type="object", nullable=true),
 *     @OA\Property(property="lottery", type="object", nullable=true),
 *     @OA\Property(property="payments_count", type="integer", example=1),
 *     @OA\Property(property="latest_payment_status", type="string", example="paid", nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="ProductSummary",
 *     title="Résumé produit",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Loterie Jackpot"),
 *     @OA\Property(property="image", type="string", example="lottery-image.jpg")
 * )
 * 
 * @OA\Schema(
 *     schema="LotterySummary",
 *     title="Résumé loterie",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="lottery_number", type="string", example="LOT-001"),
 *     @OA\Property(property="title", type="string", example="Loterie du Nouvel An"),
 *     @OA\Property(property="draw_date", type="string", format="date-time")
 * )
 * 
 * 
 * @OA\Schema(
 *     schema="PaymentCallbackRequest",
 *     title="Requête callback de paiement",
 *     required={"reference", "amount", "transactionid", "paymentsystem"},
 *     @OA\Property(property="reference", type="string", example="KMB-PAY-20240115103000-0001", description="Référence unique du paiement"),
 *     @OA\Property(property="amount", type="number", format="float", example=5000, description="Montant en FCFA"),
 *     @OA\Property(property="transactionid", type="string", example="EB-1234567890", description="ID transaction E-Billing"),
 *     @OA\Property(property="paymentsystem", type="string", example="airtelmoney", description="Système de paiement"),
 *     @OA\Property(property="status", type="string", example="success", description="Statut retourné par la gateway"),
 *     @OA\Property(property="message", type="string", example="Transaction successful", nullable=true),
 *     @OA\Property(property="ebilling_id", type="string", example="EB-REF-123456", nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Réponse d'erreur standard",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Une erreur s'est produite"),
 *     @OA\Property(property="error", type="object", nullable=true,
 *         @OA\Property(property="type", type="string", example="validation_error"),
 *         @OA\Property(property="details", type="string", example="Champ requis manquant")
 *     )
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
