<?php

use Illuminate\Support\Facades\Route;

// Route pour obtenir le token CSRF
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token()
    ]);
});

// Route pour servir l'application Vue.js (SPA)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$'); // Exclut les routes API
