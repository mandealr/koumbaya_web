<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Permet à un utilisateur de devenir vendeur individuel
     */
    public function becomeSeller(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur n'est pas déjà marchand
        if ($user->isMerchant()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà un vendeur.'
            ], 400);
        }
        
        $request->validate([
            'seller_type' => 'required|in:individual'
        ]);
        
        try {
            // Trouver le rôle Business Individual
            $businessIndividualRole = Role::where('name', 'Business Individual')->first();

            if (!$businessIndividualRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rôle vendeur individuel non trouvé.'
                ], 500);
            }

            // Assigner le rôle Business Individual à l'utilisateur
            $user->assignRole('Business Individual');
            
            return response()->json([
                'success' => true,
                'message' => 'Vous êtes maintenant un vendeur individuel !',
                'data' => [
                    'user' => $user->fresh()->load('roles'),
                    'seller_constraints' => [
                        'fixed_tickets' => 500,
                        'can_customize_tickets' => false,
                        'min_product_price' => 100000
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation du statut vendeur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour la photo de profil de l'utilisateur
     */
    public function updateProfilePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            // Supprimer l'ancienne photo si elle existe
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Sauvegarder la nouvelle photo
            $path = $request->file('avatar')->store('avatars', 'public');

            // Mettre à jour l'utilisateur
            $user->update(['avatar' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil mise à jour avec succès',
                'data' => [
                    'avatar' => $path,
                    'avatar_url' => Storage::url($path)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la photo : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer la photo de profil de l'utilisateur
     */
    public function deleteProfilePhoto(Request $request)
    {
        try {
            $user = Auth::user();

            // Supprimer la photo si elle existe
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Mettre à jour l'utilisateur
            $user->update(['avatar' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la photo : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil utilisateur
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'sometimes|string|max:500',
            'company_name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update($request->only([
                'first_name',
                'last_name',
                'phone',
                'email',
                'bio',
                'company_name',
                'address',
                'city'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => $user->fresh()->load('roles', 'wallet')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ], 500);
        }
    }
}