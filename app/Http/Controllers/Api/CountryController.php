<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Countries",
 *     description="API endpoints pour la gestion des pays"
 * )
 */
class CountryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/countries",
     *     tags={"Countries"},
     *     summary="Lister tous les pays",
     *     @OA\Parameter(
     *         name="active_only",
     *         in="query",
     *         description="Afficher seulement les pays actifs",
     *         required=false,
     *         @OA\Schema(type="boolean", default=true)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des pays",
     *         @OA\JsonContent(
     *             @OA\Property(property="countries", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Country::query();

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $countries = $query->orderBy('name')->get();

        return response()->json([
            'countries' => $countries
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/countries/{id}",
     *     tags={"Countries"},
     *     summary="Récupérer un pays par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du pays",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du pays",
     *         @OA\JsonContent(
     *             @OA\Property(property="country", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Pays non trouvé")
     * )
     */
    public function show($id)
    {
        $country = Country::findOrFail($id);

        return response()->json([
            'country' => $country
        ]);
    }
}