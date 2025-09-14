<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Contrôleur temporaire pour résoudre le problème de lottery-duration-constraints
 */
class TemporaryConstraintsController extends Controller
{
    /**
     * Retourne les contraintes de durée de loterie
     * Route temporaire pour débloquer le frontend
     */
    public function getLotteryDurationConstraints()
    {
        $constraints = [
            'type' => 'standard',
            'can_customize' => true,
            'fixed_duration' => null,
            'min_days' => 1,
            'max_days' => 60,
            'description' => 'Configurez la durée entre 1 et 60 jours',
            'default_duration' => 30,
            'ticket_pricing' => [
                'min_ticket_price' => 200,
                'max_ticket_price' => 50000,
                'currency' => 'FCFA',
                'rules' => [
                    'Le prix de ticket minimum est de 200 FCFA',
                    'Le prix est calculé automatiquement selon le prix du produit et la durée',
                    'Plus le prix du produit est élevé, plus le prix du ticket sera élevé'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'constraints' => $constraints,
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ]);
    }
}