<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProductShareController;

// Route pour obtenir le token CSRF
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token()
    ]);
});

// Route publique pour servir les images de produits (pas d'auth requise)
Route::get('api/products/images/{year}/{month}/{filename}', [App\Http\Controllers\Api\ProductImageController::class, 'serve'])
    ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{2}', 'filename' => '.*'])
    ->middleware(['throttle:200,1']); // Rate limiting léger pour les images

// Social Authentication Callbacks (must be web routes, not API)
Route::get('auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])
    ->where('provider', 'google|facebook|apple');

// Routes de partage pour les réseaux sociaux (meta tags Open Graph dynamiques)
// Toutes ces routes détectent les bots sociaux et servent des meta tags dynamiques

// Route explicite de partage
Route::get('share/product/{id}', [ProductShareController::class, 'show'])
    ->where('id', '[0-9]+');

// Route produit public /products/{slug}
Route::get('products/{slug}', [ProductShareController::class, 'showBySlug']);

// Route produit client /customer/products/{slug}
Route::get('customer/products/{slug}', [ProductShareController::class, 'showBySlug']);

// Route tombola /lotteries/{id}
Route::get('lotteries/{id}', [ProductShareController::class, 'showLottery'])
    ->where('id', '[0-9]+');

// Route tombola /lottery/{id}
Route::get('lottery/{id}', [ProductShareController::class, 'showLottery'])
    ->where('id', '[0-9]+');

// Route pour servir l'application Vue.js (SPA)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$'); // Exclut les routes API
