<?php

use Illuminate\Support\Facades\Route;

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

// Route pour servir l'application Vue.js (SPA)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$'); // Exclut les routes API
