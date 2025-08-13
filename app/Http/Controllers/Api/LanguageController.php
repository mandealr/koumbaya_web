<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Languages",
 *     description="API endpoints pour la gestion des langues"
 * )
 */
class LanguageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/languages",
     *     tags={"Languages"},
     *     summary="Lister toutes les langues",
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Afficher seulement les langues actives",
     *         required=false,
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des langues",
     *         @OA\JsonContent(
     *             @OA\Property(property="languages", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Language::query();

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $languages = $query->orderBy('name')->get();

        return response()->json([
            'languages' => $languages
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/languages/{id}",
     *     tags={"Languages"},
     *     summary="Récupérer une langue par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la langue",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la langue",
     *         @OA\JsonContent(
     *             @OA\Property(property="language", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Langue non trouvée")
     * )
     */
    public function show($id)
    {
        $language = Language::findOrFail($id);

        return response()->json([
            'language' => $language
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/languages/default",
     *     tags={"Languages"},
     *     summary="Récupérer la langue par défaut",
     *     @OA\Response(
     *         response=200,
     *         description="Langue par défaut",
     *         @OA\JsonContent(
     *             @OA\Property(property="language", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Aucune langue par défaut définie")
     * )
     */
    public function default()
    {
        // Essayer de trouver une langue marquée comme par défaut
        $language = Language::where('is_default', true)
            ->where('is_active', true)
            ->first();

        // Si aucune langue par défaut n'est trouvée, prendre la première langue active
        if (!$language) {
            $language = Language::where('is_active', true)
                ->orderBy('name')
                ->first();
        }

        // Si toujours aucune langue trouvée, retourner une erreur appropriée
        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune langue disponible'
            ], 404);
        }

        return response()->json([
            'language' => $language
        ]);
    }

    /**
     * Initialize default languages if none exist
     */
    public function initialize()
    {
        // Vérifier s'il y a déjà des langues
        $existingCount = Language::count();
        
        if ($existingCount > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Langues already exist',
                'count' => $existingCount
            ]);
        }

        // Créer les langues par défaut
        $languages = [
            [
                'name' => 'Français',
                'code' => 'fr',
                'native_name' => 'Français',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Anglais',
                'code' => 'en',
                'native_name' => 'English',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        $created = [];
        foreach ($languages as $language) {
            $created[] = Language::create($language);
        }

        return response()->json([
            'success' => true,
            'message' => 'Langues initialized successfully',
            'languages' => $created,
            'count' => count($created)
        ]);
    }
}