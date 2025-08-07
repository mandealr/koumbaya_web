<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API endpoints pour la gestion des catégories"
 * )
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Lister toutes les catégories",
     *     @OA\Parameter(
     *         name="parent_only",
     *         in="query",
     *         description="Afficher seulement les catégories parentes",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des catégories",
     *         @OA\JsonContent(
     *             @OA\Property(property="categories", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Category::active();

        if ($request->boolean('parent_only')) {
            $query->parents();
        }

        $categories = $query->with('children')->orderBy('name')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Récupérer une catégorie par ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la catégorie",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la catégorie",
     *         @OA\JsonContent(
     *             @OA\Property(property="category", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Catégorie non trouvée")
     * )
     */
    public function show($id)
    {
        $category = Category::with(['children', 'products' => function($query) {
            $query->active()->with('merchant');
        }])->findOrFail($id);

        return response()->json([
            'category' => $category
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}/products",
     *     tags={"Categories"},
     *     summary="Récupérer les produits d'une catégorie",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la catégorie",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", maximum=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produits de la catégorie",
     *         @OA\JsonContent(
     *             @OA\Property(property="products", type="object"),
     *             @OA\Property(property="category", type="object")
     *         )
     *     )
     * )
     */
    public function products($id, Request $request)
    {
        $category = Category::findOrFail($id);
        
        $perPage = min($request->get('per_page', 15), 50);
        
        $products = $category->products()
            ->active()
            ->with(['merchant', 'activeLottery', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'products' => $products,
            'category' => $category
        ]);
    }
}
