<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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

// Route pour servir l'application Vue.js (SPA)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$'); // Exclut les routes API
