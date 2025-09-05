<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketPriceCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Ticket Price Calculator",
 *     description="API endpoints pour le calcul automatique des prix de tickets"
 * )
 */
class TicketPriceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/calculate-ticket-price",
     *     tags={"Ticket Price Calculator"},
     *     summary="Calculer le prix d'un ticket de tombola",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_price"},
     *             @OA\Property(property="product_price", type="number", format="float", example=750000, description="Prix du produit en FCFA"),
     *             @OA\Property(property="number_of_tickets", type="integer", example=1000, description="Nombre de tickets (défaut: 1000)"),
     *             @OA\Property(property="commission_rate", type="number", format="float", example=0.10, description="Taux de commission (défaut: 10%)"),
     *             @OA\Property(property="margin_rate", type="number", format="float", example=0.15, description="Taux de marge (défaut: 15%)"),
     *             @OA\Property(property="vendor_profile_id", type="integer", example=1, description="ID du profil vendeur à utiliser (optionnel)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prix du ticket calculé avec détails",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="product_price", type="number", example=750000),
     *                 @OA\Property(property="commission_rate", type="number", example=0.10),
     *                 @OA\Property(property="margin_rate", type="number", example=0.15),
     *                 @OA\Property(property="commission_amount", type="number", example=75000),
     *                 @OA\Property(property="margin_amount", type="number", example=112500),
     *                 @OA\Property(property="total_amount", type="number", example=937500),
     *                 @OA\Property(property="number_of_tickets", type="integer", example=1000),
     *                 @OA\Property(property="calculated_ticket_price", type="number", example=937.5),
     *                 @OA\Property(property="final_ticket_price", type="number", example=938),
     *                 @OA\Property(property="total_potential_revenue", type="number", example=938000),
     *                 @OA\Property(property="koumbaya_profit", type="number", example=187500),
     *                 @OA\Property(property="merchant_revenue", type="number", example=750000)
     *             ),
     *             @OA\Property(property="validation", type="object",
     *                 @OA\Property(property="is_valid", type="boolean", example=true),
     *                 @OA\Property(property="warnings", type="array", @OA\Items(type="string"))
     *             ),
     *             @OA\Property(property="formatted", type="object",
     *                 @OA\Property(property="ticket_price", type="string", example="938 FCFA"),
     *                 @OA\Property(property="total_revenue", type="string", example="938 000 FCFA"),
     *                 @OA\Property(property="koumbaya_profit", type="string", example="187 500 FCFA")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation"),
     *     @OA\Response(response=400, description="Paramètres invalides")
     * )
     */
    public function calculate(Request $request): JsonResponse
    {
        // Validation des paramètres
        $validated = $request->validate([
            'product_price' => 'required|numeric|min:1',
            'number_of_tickets' => 'nullable|integer|min:1|max:10000',
            'commission_rate' => 'nullable|numeric|min:0|max:1',
            'margin_rate' => 'nullable|numeric|min:0|max:1',
            'vendor_profile_id' => 'nullable|exists:vendor_profiles,id',
        ]);

        try {
            // Paramètres par défaut
            $productPrice = $validated['product_price'];
            $numberOfTickets = $validated['number_of_tickets'] ?? config('koumbaya.ticket_calculation.default_tickets', 1000);
            $commissionRate = $validated['commission_rate'] ?? config('koumbaya.ticket_calculation.commission_rate', 0.10);
            $marginRate = $validated['margin_rate'] ?? config('koumbaya.ticket_calculation.margin_rate', 0.15);
            $vendorProfileId = $validated['vendor_profile_id'] ?? null;

            // Si un profil vendeur est fourni, l'utilisateur doit être authentifié
            $vendor = null;
            if ($vendorProfileId) {
                $user = auth('sanctum')->user();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Authentification requise pour utiliser un profil vendeur',
                    ], 401);
                }
                
                // Vérifier que le profil appartient bien à l'utilisateur
                $vendor = \App\Models\VendorProfile::where('id', $vendorProfileId)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if (!$vendor) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Profil vendeur non trouvé ou non autorisé',
                    ], 403);
                }
            }

            // Calcul des détails complets avec le vendeur si disponible
            $calculationDetails = TicketPriceCalculator::getCalculationDetails(
                $productPrice,
                $numberOfTickets,
                $commissionRate,
                $marginRate,
                $vendor
            );

            // Validation du prix calculé avec le vendeur
            $validation = TicketPriceCalculator::validateTicketPrice(
                $calculationDetails['final_ticket_price'],
                $vendor
            );

            // Formatage pour l'affichage
            $formatted = $this->formatPricesForDisplay($calculationDetails);

            return response()->json([
                'success' => true,
                'data' => $calculationDetails,
                'validation' => $validation,
                'formatted' => $formatted,
                'vendor_profile' => $vendor ? [
                    'id' => $vendor->id,
                    'type' => $vendor->type,
                    'constraints' => $vendor->getConstraintsAttribute(),
                ] : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/ticket-price-suggestions",
     *     tags={"Ticket Price Calculator"},
     *     summary="Obtenir des suggestions de prix pour différents nombres de tickets",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_price"},
     *             @OA\Property(property="product_price", type="number", format="float", example=750000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Suggestions de prix",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="suggestions", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="tickets", type="integer", example=1000),
     *                     @OA\Property(property="price", type="number", example=938),
     *                     @OA\Property(property="total_revenue", type="number", example=938000),
     *                     @OA\Property(property="is_recommended", type="boolean", example=true),
     *                     @OA\Property(property="formatted_price", type="string", example="938 FCFA"),
     *                     @OA\Property(property="formatted_revenue", type="string", example="938 000 FCFA")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_price' => 'required|numeric|min:1',
            'vendor_profile_id' => 'nullable|exists:vendor_profiles,id',
        ]);

        try {
            $productPrice = $validated['product_price'];
            $vendorProfileId = $validated['vendor_profile_id'] ?? null;
            
            // Si un profil vendeur est fourni, l'utilisateur doit être authentifié
            $vendor = null;
            if ($vendorProfileId) {
                $user = auth('sanctum')->user();
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Authentification requise pour utiliser un profil vendeur',
                    ], 401);
                }
                
                // Vérifier que le profil appartient bien à l'utilisateur
                $vendor = \App\Models\VendorProfile::where('id', $vendorProfileId)
                    ->where('user_id', $user->id)
                    ->first();
                    
                if (!$vendor) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Profil vendeur non trouvé ou non autorisé',
                    ], 403);
                }
            }
            
            $currentTicketPrice = TicketPriceCalculator::calculateTicketPrice($productPrice, 1000, null, null, $vendor);
            $suggestions = TicketPriceCalculator::getSuggestions($productPrice, $currentTicketPrice, $vendor);

            // Formater les suggestions
            $formattedSuggestions = array_map(function ($suggestion) {
                return array_merge($suggestion, [
                    'formatted_price' => $this->formatCurrency($suggestion['price']),
                    'formatted_revenue' => $this->formatCurrency($suggestion['total_revenue']),
                ]);
            }, $suggestions);

            return response()->json([
                'success' => true,
                'suggestions' => $formattedSuggestions,
                'vendor_profile' => $vendor ? [
                    'id' => $vendor->id,
                    'type' => $vendor->type,
                    'constraints' => $vendor->getConstraintsAttribute(),
                ] : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des suggestions: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/ticket-calculation-config",
     *     tags={"Ticket Price Calculator"},
     *     summary="Obtenir la configuration du calculateur de prix",
     *     @OA\Response(
     *         response=200,
     *         description="Configuration du calculateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="config", type="object",
     *                 @OA\Property(property="commission_rate", type="number", example=0.10),
     *                 @OA\Property(property="margin_rate", type="number", example=0.15),
     *                 @OA\Property(property="default_tickets", type="integer", example=1000),
     *                 @OA\Property(property="min_tickets", type="integer", example=100),
     *                 @OA\Property(property="max_tickets", type="integer", example=5000),
     *                 @OA\Property(property="min_ticket_price", type="number", example=100),
     *                 @OA\Property(property="max_ticket_price", type="number", example=50000),
     *                 @OA\Property(property="currency", type="string", example="FCFA")
     *             )
     *         )
     *     )
     * )
     */
    public function config(): JsonResponse
    {
        $parameters = TicketPriceCalculator::getCalculationParameters();
        $config = array_merge($parameters, [
            'currency' => config('koumbaya.marketplace.currency', 'FCFA'),
            'currency_symbol' => config('koumbaya.marketplace.currency_symbol', 'FCFA'),
            'auto_calculate_enabled' => config('koumbaya.features.auto_calculate_ticket_price', true),
            'show_details' => config('koumbaya.features.show_calculation_details', true),
        ]);

        return response()->json([
            'success' => true,
            'config' => $config,
        ]);
    }

    /**
     * Formater les prix pour l'affichage
     */
    private function formatPricesForDisplay(array $calculationDetails): array
    {
        return [
            'product_price' => $this->formatCurrency($calculationDetails['product_price']),
            'ticket_price' => $this->formatCurrency($calculationDetails['final_ticket_price']),
            'commission_amount' => $this->formatCurrency($calculationDetails['commission_amount']),
            'margin_amount' => $this->formatCurrency($calculationDetails['margin_amount']),
            'total_revenue' => $this->formatCurrency($calculationDetails['total_potential_revenue']),
            'koumbaya_profit' => $this->formatCurrency($calculationDetails['koumbaya_profit']),
            'merchant_revenue' => $this->formatCurrency($calculationDetails['merchant_revenue']),
        ];
    }

    /**
     * Formater une valeur en devise locale
     */
    private function formatCurrency(float $amount): string
    {
        $currency = config('koumbaya.marketplace.currency_symbol', 'FCFA');
        return number_format($amount, 0, ',', ' ') . ' ' . $currency;
    }
}