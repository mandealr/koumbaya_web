<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VendorProfileController extends Controller
{
    /**
     * Liste les profils vendeur de l'utilisateur authentifié
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $profiles = VendorProfile::where('user_id', $user->id)
            ->when($request->boolean('active_only'), function ($query) {
                return $query->where('is_active', true);
            })
            ->orderBy('name')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $profiles->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'type' => $profile->type,
                    'is_active' => $profile->is_active,
                    'constraints' => $profile->getConstraintsAttribute(),
                    'products_count' => $profile->products()->count(),
                    'created_at' => $profile->created_at,
                    'updated_at' => $profile->updated_at,
                ];
            }),
            'total' => $profiles->count()
        ]);
    }
    
    /**
     * Créer un nouveau profil vendeur
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est un marchand
        if (!$user->is_merchant) {
            return response()->json(['error' => 'Seuls les marchands peuvent créer des profils vendeur'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(VendorProfile::TYPES)],
            'constraints' => 'nullable|array',
            'constraints.min_ticket_price' => 'nullable|numeric|min:0',
            'constraints.max_ticket_price' => 'nullable|numeric|min:0',
            'constraints.allowed_ticket_prices' => 'nullable|array',
            'constraints.allowed_ticket_prices.*' => 'numeric|min:0',
            'constraints.forbidden_ticket_prices' => 'nullable|array',
            'constraints.forbidden_ticket_prices.*' => 'numeric|min:0',
            'constraints.min_product_value' => 'nullable|numeric|min:0',
            'constraints.max_product_value' => 'nullable|numeric|min:0',
            'constraints.commission_rate' => 'nullable|numeric|min:0|max:1',
            'constraints.margin_rate' => 'nullable|numeric|min:0|max:1',
            'constraints.max_lottery_duration' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Validation spécifique selon le type
        $type = $request->type;
        $constraints = $request->constraints ?? [];
        
        // Valeurs par défaut selon le type
        $defaultConstraints = VendorProfile::getDefaultConstraints($type);
        $constraints = array_merge($defaultConstraints, $constraints);
        
        // Validation de cohérence
        if (isset($constraints['min_ticket_price']) && isset($constraints['max_ticket_price'])) {
            if ($constraints['min_ticket_price'] > $constraints['max_ticket_price']) {
                return response()->json([
                    'error' => 'Le prix minimum ne peut pas être supérieur au prix maximum'
                ], 422);
            }
        }
        
        if (isset($constraints['min_product_value']) && isset($constraints['max_product_value'])) {
            if ($constraints['min_product_value'] > $constraints['max_product_value']) {
                return response()->json([
                    'error' => 'La valeur minimum du produit ne peut pas être supérieure à la valeur maximum'
                ], 422);
            }
        }
        
        $profile = VendorProfile::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'type' => $type,
            'constraints' => $constraints,
            'is_active' => $request->boolean('is_active', true),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Profil vendeur créé avec succès',
            'data' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'type' => $profile->type,
                'is_active' => $profile->is_active,
                'constraints' => $profile->getConstraintsAttribute(),
                'created_at' => $profile->created_at,
            ]
        ], 201);
    }
    
    /**
     * Afficher un profil vendeur spécifique
     */
    public function show($id)
    {
        $user = auth()->user();
        
        $profile = VendorProfile::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'type' => $profile->type,
                'is_active' => $profile->is_active,
                'constraints' => $profile->getConstraintsAttribute(),
                'products_count' => $profile->products()->count(),
                'active_products_count' => $profile->products()->where('is_active', true)->count(),
                'created_at' => $profile->created_at,
                'updated_at' => $profile->updated_at,
            ]
        ]);
    }
    
    /**
     * Mettre à jour un profil vendeur
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        $profile = VendorProfile::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'constraints' => 'nullable|array',
            'constraints.min_ticket_price' => 'nullable|numeric|min:0',
            'constraints.max_ticket_price' => 'nullable|numeric|min:0',
            'constraints.allowed_ticket_prices' => 'nullable|array',
            'constraints.allowed_ticket_prices.*' => 'numeric|min:0',
            'constraints.forbidden_ticket_prices' => 'nullable|array',
            'constraints.forbidden_ticket_prices.*' => 'numeric|min:0',
            'constraints.min_product_value' => 'nullable|numeric|min:0',
            'constraints.max_product_value' => 'nullable|numeric|min:0',
            'constraints.commission_rate' => 'nullable|numeric|min:0|max:1',
            'constraints.margin_rate' => 'nullable|numeric|min:0|max:1',
            'constraints.max_lottery_duration' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Si on modifie les contraintes, valider la cohérence
        if ($request->has('constraints')) {
            $constraints = array_merge($profile->constraints ?? [], $request->constraints);
            
            if (isset($constraints['min_ticket_price']) && isset($constraints['max_ticket_price'])) {
                if ($constraints['min_ticket_price'] > $constraints['max_ticket_price']) {
                    return response()->json([
                        'error' => 'Le prix minimum ne peut pas être supérieur au prix maximum'
                    ], 422);
                }
            }
            
            if (isset($constraints['min_product_value']) && isset($constraints['max_product_value'])) {
                if ($constraints['min_product_value'] > $constraints['max_product_value']) {
                    return response()->json([
                        'error' => 'La valeur minimum du produit ne peut pas être supérieure à la valeur maximum'
                    ], 422);
                }
            }
        }
        
        $updateData = [];
        if ($request->has('name')) $updateData['name'] = $request->name;
        if ($request->has('is_active')) $updateData['is_active'] = $request->boolean('is_active');
        if ($request->has('constraints')) {
            $updateData['constraints'] = array_merge($profile->constraints ?? [], $request->constraints);
        }
        
        $profile->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Profil vendeur mis à jour avec succès',
            'data' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'type' => $profile->type,
                'is_active' => $profile->is_active,
                'constraints' => $profile->getConstraintsAttribute(),
                'updated_at' => $profile->updated_at,
            ]
        ]);
    }
    
    /**
     * Supprimer un profil vendeur
     */
    public function destroy($id)
    {
        $user = auth()->user();
        
        $profile = VendorProfile::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
            
        // Vérifier qu'aucun produit n'utilise ce profil
        if ($profile->products()->exists()) {
            return response()->json([
                'error' => 'Impossible de supprimer un profil utilisé par des produits',
                'products_count' => $profile->products()->count()
            ], 422);
        }
        
        $profile->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Profil vendeur supprimé avec succès'
        ]);
    }
    
    /**
     * Obtenir les types de profils disponibles avec leurs descriptions
     */
    public function types()
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'type' => VendorProfile::TYPE_ELECTRONICS,
                    'label' => 'Électronique',
                    'description' => 'Pour les vendeurs de produits électroniques',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_ELECTRONICS),
                ],
                [
                    'type' => VendorProfile::TYPE_FASHION,
                    'label' => 'Mode',
                    'description' => 'Pour les vendeurs de vêtements et accessoires',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_FASHION),
                ],
                [
                    'type' => VendorProfile::TYPE_HOME,
                    'label' => 'Maison',
                    'description' => 'Pour les vendeurs d\'articles de maison',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_HOME),
                ],
                [
                    'type' => VendorProfile::TYPE_BEAUTY,
                    'label' => 'Beauté',
                    'description' => 'Pour les vendeurs de produits de beauté',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_BEAUTY),
                ],
                [
                    'type' => VendorProfile::TYPE_SPORTS,
                    'label' => 'Sport',
                    'description' => 'Pour les vendeurs d\'articles de sport',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_SPORTS),
                ],
                [
                    'type' => VendorProfile::TYPE_TOYS,
                    'label' => 'Jouets',
                    'description' => 'Pour les vendeurs de jouets',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_TOYS),
                ],
                [
                    'type' => VendorProfile::TYPE_BOOKS,
                    'label' => 'Livres',
                    'description' => 'Pour les vendeurs de livres',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_BOOKS),
                ],
                [
                    'type' => VendorProfile::TYPE_AUTO,
                    'label' => 'Automobile',
                    'description' => 'Pour les vendeurs d\'accessoires automobile',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_AUTO),
                ],
                [
                    'type' => VendorProfile::TYPE_HEALTH,
                    'label' => 'Santé',
                    'description' => 'Pour les vendeurs de produits de santé',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_HEALTH),
                ],
                [
                    'type' => VendorProfile::TYPE_OTHER,
                    'label' => 'Autre',
                    'description' => 'Pour les autres types de vendeurs',
                    'default_constraints' => VendorProfile::getDefaultConstraints(VendorProfile::TYPE_OTHER),
                ],
            ]
        ]);
    }
}