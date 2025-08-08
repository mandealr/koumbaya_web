<?php

use Illuminate\Support\Facades\Route;

// Route pour servir l'application Vue.js (SPA)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$'); // Exclut les routes API
