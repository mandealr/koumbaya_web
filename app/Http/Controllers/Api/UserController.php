<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

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
            
            // Activer can_sell
            $user->update(['can_sell' => true]);
            
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
}