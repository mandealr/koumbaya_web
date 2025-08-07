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
        $language = Language::where('is_default', true)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'language' => $language
        ]);
    }
}