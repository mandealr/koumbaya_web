<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\LanguageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Routes d'authentification avec rate limiting
Route::group([
    'middleware' => ['throttle.api:30,1'],
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Routes publiques (sans authentification) avec rate limiting modéré
Route::group([
    'middleware' => ['throttle.api:120,1']
], function () {
    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::get('categories/{id}/products', [CategoryController::class, 'products']);
    
    // Countries
    Route::get('countries', [CountryController::class, 'index']);
    Route::get('countries/{id}', [CountryController::class, 'show']);
    
    // Languages
    Route::get('languages', [LanguageController::class, 'index']);
    Route::get('languages/{id}', [LanguageController::class, 'show']);
    Route::get('languages/default', [LanguageController::class, 'default']);
    
    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    
    // Lotteries
    Route::get('lotteries', [LotteryController::class, 'index']);
    Route::get('lotteries/active', [LotteryController::class, 'active']);
    Route::get('lotteries/{id}', [LotteryController::class, 'show']);
    
    // Payments callbacks (public)
    Route::post('payments/callback', [PaymentController::class, 'callback']);
    Route::get('payments/success', [PaymentController::class, 'success']);
});

// Routes protégées (authentification requise) avec rate limiting
Route::group([
    'middleware' => ['auth:sanctum', 'throttle.api:200,1']
], function () {
    // Dashboard utilisateur
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    
    // Lotteries (Utilisateurs)
    Route::post('lotteries/{id}/buy-ticket', [LotteryController::class, 'buyTicket']);
    Route::get('lotteries/{id}/my-tickets', [LotteryController::class, 'myTickets']);
    
    // Payments
    Route::post('payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('payments/{id}/status', [PaymentController::class, 'status']);
});

// Routes Marchands seulement avec rate limiting strict
Route::group([
    'middleware' => ['auth:sanctum', 'merchant', 'throttle.api:100,1']
], function () {
    // Products (Marchands seulement)
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::post('products/{id}/create-lottery', [ProductController::class, 'createLottery']);
    
    // Lottery draw (Marchands seulement)
    Route::post('lotteries/{id}/draw', [LotteryController::class, 'drawLottery']);
});