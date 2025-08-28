<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Merchant Profile",
 *     description="API endpoints pour la gestion du profil marchand"
 * )
 */
class MerchantProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/merchant/profile",
     *     tags={"Merchant Profile"},
     *     summary="Récupérer le profil du marchand",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil du marchand récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();
        $user->load(['userType', 'roles', 'wallet']);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/merchant/profile",
     *     tags={"Merchant Profile"},
     *     summary="Mettre à jour le profil du marchand",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="last_name", type="string", example="Dupont"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="phone", type="string", example="+24177123456"),
     *             @OA\Property(property="company_name", type="string", example="SARL Dupont & Fils"),
     *             @OA\Property(property="company_registration", type="string", example="RC-123456789"),
     *             @OA\Property(property="tax_id", type="string", example="NIF-987654321"),
     *             @OA\Property(property="address", type="string", example="123 Rue du Commerce"),
     *             @OA\Property(property="city", type="string", example="Douala"),
     *             @OA\Property(property="postal_code", type="string", example="00000"),
     *             @OA\Property(property="country_id", type="integer", example=1),
     *             @OA\Property(property="bank_details", type="string", example="Afriland First Bank - 123456789"),
     *             @OA\Property(property="payment_phone", type="string", example="+24177123456"),
     *             @OA\Property(property="preferred_payment_method", type="string", example="mtn_money")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil mis à jour avec succès"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function update(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            // Informations personnelles
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'bio' => 'sometimes|string|max:500',
            
            // Informations business
            'company_name' => 'sometimes|string|max:255',
            'company_registration' => 'sometimes|string|max:100',
            'tax_id' => 'sometimes|string|max:100',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country_id' => 'sometimes|integer|exists:countries,id',
            'language_id' => 'sometimes|integer|exists:languages,id',
            
            // Informations bancaires et paiement
            'bank_details' => 'sometimes|string|max:500',
            'payment_phone' => 'sometimes|string|max:20',
            'preferred_payment_method' => 'sometimes|string|in:mtn_money,orange_money,airtel_money,bank_transfer',
            
            // Préférences
            'notification_preferences' => 'sometimes|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->fill($validator->validated());
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => $user->fresh(['userType', 'roles', 'wallet'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/merchant/profile/avatar",
     *     tags={"Merchant Profile"},
     *     summary="Mettre à jour la photo de profil",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="avatar", type="string", format="binary", description="Image de profil (max 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo de profil mise à jour avec succès"
     *     )
     * )
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            // Supprimer l'ancienne photo si elle existe
            if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Upload de la nouvelle photo
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            
            $user->avatar_url = $avatarPath;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil mise à jour avec succès',
                'avatar_url' => Storage::url($avatarPath),
                'user' => $user->fresh(['userType', 'roles', 'wallet'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement de la photo'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/merchant/profile/password",
     *     tags={"Merchant Profile"},
     *     summary="Changer le mot de passe",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password","password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe changé avec succès"
     *     )
     * )
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect'
            ], 422);
        }

        try {
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de mot de passe'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/merchant/business-stats",
     *     tags={"Merchant Profile"},
     *     summary="Statistiques business du marchand",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques business récupérées avec succès"
     *     )
     * )
     */
    public function businessStats(): JsonResponse
    {
        $user = auth()->user();

        // Calculer des statistiques business
        $totalProducts = $user->products()->count();
        $activeProducts = $user->products()->where('is_active', true)->count();
        $totalSales = $user->products()->withSum('lotteryTickets', 'price')->get()->sum('lottery_tickets_sum_price');
        $totalRevenue = $totalSales * 0.95; // 95% après commission Koumbaya

        return response()->json([
            'success' => true,
            'stats' => [
                'total_products' => $totalProducts,
                'active_products' => $activeProducts,
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'commission_rate' => 5,
                'profile_completion' => $this->calculateProfileCompletion($user)
            ]
        ]);
    }

    /**
     * Calculer le pourcentage de completion du profil
     */
    private function calculateProfileCompletion(User $user): int
    {
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone',
            'company_name', 'address', 'city', 'bank_details'
        ];

        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($user->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($requiredFields)) * 100);
    }
}