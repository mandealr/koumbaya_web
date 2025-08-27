<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\PaymentCallbackController;
use App\Http\Controllers\Api\MerchantDashboardController;
use App\Http\Controllers\Api\PublicResultsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\AdminRefundController;
use App\Http\Controllers\Api\TicketPriceController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminPaymentController;
use App\Http\Controllers\Api\AdminSettingsController;
use App\Http\Controllers\Api\AdminProfileController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\PaymentTrackingController;
use App\Http\Controllers\Api\AvatarController;

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

// Route publique pour les avatars
Route::get('/avatars/{filename}', [AvatarController::class, 'show'])
    ->name('avatar.show')
    ->where('filename', '.*');

// Routes d'authentification avec rate limiting
Route::group([
    'middleware' => ['throttle.api:30,1'],
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-account', [AuthController::class, 'verifyAccount']);
    Route::get('verify-email/{token}', [AuthController::class, 'verifyAccountByUrl']);
    Route::post('resend-verification', [AuthController::class, 'resendVerificationEmail']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    
    // Social Authentication Routes
    Route::get('{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('{provider}/callback', [AuthController::class, 'handleProviderCallback']);
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
    Route::post('languages/initialize', [LanguageController::class, 'initialize']);
    
    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/latest', [ProductController::class, 'latest']);
    Route::get('products/latest-direct', [ProductController::class, 'latestDirect']);
    Route::get('products/latest-lottery', [ProductController::class, 'latestLottery']);
    Route::get('products/latest-lottery-only', [ProductController::class, 'latestLotteryOnly']);
    Route::get('products/by-sale-mode/{mode}', [ProductController::class, 'getBySaleMode']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    
    // Lotteries (routes spécifiques avant les routes avec paramètres)
    Route::get('lotteries', [LotteryController::class, 'index']);
    Route::get('lotteries/active', [LotteryController::class, 'active']);
    // Route avec paramètre {id} doit être après les routes spécifiques
    Route::get('lotteries/{id}', [LotteryController::class, 'show']);
    
    // Payment callbacks (public)
    Route::post('payment/callback', [PaymentCallbackController::class, 'handleCallback']);
    Route::post('payment/return', [PaymentCallbackController::class, 'handleReturn']);
    Route::post('payment/test-callback', [PaymentCallbackController::class, 'testCallback']);
    
    // Legacy payment callbacks
    Route::post('payments/callback', [PaymentController::class, 'callback']);
    Route::post('payments/notify', [PaymentController::class, 'notify']);
    Route::get('payments/success', [PaymentController::class, 'success']);
    
    // OTP endpoints (public)
    Route::post('otp/send', [App\Http\Controllers\Api\OtpController::class, 'send']);
    Route::post('otp/verify', [App\Http\Controllers\Api\OtpController::class, 'verify']);
    Route::post('otp/resend', [App\Http\Controllers\Api\OtpController::class, 'resend']);
    Route::get('otp/status/{identifier}', [App\Http\Controllers\Api\OtpController::class, 'status']);
    Route::delete('otp/cleanup', [App\Http\Controllers\Api\OtpController::class, 'cleanup']);
    
    // Public Results
    Route::prefix('public/results')->group(function () {
        Route::get('/', [PublicResultsController::class, 'index']);
        Route::get('/stats', [PublicResultsController::class, 'stats']);
        Route::get('/recent-winners', [PublicResultsController::class, 'recentWinners']);
        Route::get('/verify/{code}', [PublicResultsController::class, 'verify']);
        Route::get('/{lottery}', [PublicResultsController::class, 'show']);
    });

    // Ticket Price Calculator (public)
    Route::post('calculate-ticket-price', [TicketPriceController::class, 'calculate']);
    Route::post('ticket-price-suggestions', [TicketPriceController::class, 'suggestions']);
    Route::get('ticket-calculation-config', [TicketPriceController::class, 'config']);
});

// Routes protégées (authentification requise) avec rate limiting
Route::group([
    'middleware' => ['auth:sanctum', 'throttle.api:200,1']
], function () {
    // Dashboard utilisateur
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    
    // Tickets (Nouveau système d'achat) - Nécessite vérification
    Route::post('tickets/purchase', [TicketController::class, 'purchase'])->middleware('verified');
    Route::get('tickets/my-tickets', [TicketController::class, 'myTickets']);
    Route::get('tickets/{id}', [TicketController::class, 'show']);
    Route::post('tickets/{id}/cancel', [TicketController::class, 'cancel']);
    
    // Transaction status
    Route::get('transactions/{transactionId}/status', [PaymentCallbackController::class, 'checkStatus']);
    
    // Lotteries (Utilisateurs) - Legacy - Nécessite vérification
    Route::post('lotteries/{id}/buy-ticket', [LotteryController::class, 'buyTicket'])->middleware('verified');
    Route::get('lotteries/{id}/my-tickets', [LotteryController::class, 'myTickets']);
    
    // Payments - Legacy
    Route::post('payments/initiate', [PaymentController::class, 'initiate']);
    Route::post('payments/initiate-from-transaction', [PaymentController::class, 'initiateFromTransaction']);
    Route::post('payments/ussd-push', [PaymentController::class, 'pushUssd']);
    Route::post('payments/retry-ussd', [PaymentController::class, 'retryUssd']);
    Route::get('payments/kyc', [PaymentController::class, 'kyc']);
    Route::get('payments/status/{billId}', [PaymentController::class, 'status']);
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    });
    
    // Refunds
    Route::prefix('refunds')->group(function () {
        Route::get('/', [RefundController::class, 'index']);
        Route::post('/', [RefundController::class, 'store']);
        Route::get('/stats', [RefundController::class, 'stats']);
        Route::get('/{id}', [RefundController::class, 'show']);
        Route::post('/{id}/cancel', [RefundController::class, 'cancel']);
    });
    
    // Order Tracking
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderTrackingController::class, 'index']);
        Route::get('/stats', [OrderTrackingController::class, 'stats']);
        Route::get('/search', [OrderTrackingController::class, 'search']);
        Route::get('/{transactionId}', [OrderTrackingController::class, 'show']);
    });
    
    // Payment Tracking
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentTrackingController::class, 'index']);
        Route::get('/stats', [PaymentTrackingController::class, 'stats']);
        Route::get('/search', [PaymentTrackingController::class, 'search']);
        Route::get('/{paymentId}', [PaymentTrackingController::class, 'show']);
    });
});

// Routes Marchands seulement avec rate limiting strict
Route::group([
    'middleware' => ['auth:sanctum', 'merchant', 'throttle.api:100,1']
], function () {
    // Products (Marchands seulement)
    Route::get('merchant/products', [ProductController::class, 'merchantProducts']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::post('products/{id}/create-lottery', [ProductController::class, 'createLottery']);
    
    // Lotteries (Marchands seulement)
    Route::post('lotteries/{id}/draw', [LotteryController::class, 'drawLottery']);
    Route::get('lotteries/{id}/verify-draw', [LotteryController::class, 'verifyDraw']);
    Route::get('lotteries/{id}/draw-history', [LotteryController::class, 'drawHistory']);
    
    // Merchant Dashboard
    Route::prefix('merchant/dashboard')->group(function () {
        Route::get('stats', [MerchantDashboardController::class, 'getStats']);
        Route::get('sales-chart', [MerchantDashboardController::class, 'getSalesChart']);
        Route::get('top-products', [MerchantDashboardController::class, 'getTopProducts']);
        Route::get('recent-transactions', [MerchantDashboardController::class, 'getRecentTransactions']);
        Route::get('lottery-performance', [MerchantDashboardController::class, 'getLotteryPerformance']);
        Route::get('export-orders', [MerchantDashboardController::class, 'exportOrders']);
        Route::get('lotteries', [MerchantDashboardController::class, 'getLotteries']);
    });
    
    // Transaction management
    Route::put('transactions/{id}/status', [MerchantDashboardController::class, 'updateTransactionStatus']);
});

// Routes Admin avec rate limiting strict
Route::group([
    'middleware' => ['auth:sanctum', 'admin', 'throttle.api:200,1'],
    'prefix' => 'admin'
], function () {
    // Admin Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [AdminDashboardController::class, 'getStats']);
    });
    
    // Admin Dashboard Data Endpoints
    Route::get('/lotteries/recent', [AdminDashboardController::class, 'getRecentLotteries']);
    Route::get('/activities/recent', [AdminDashboardController::class, 'getRecentActivities']);
    Route::get('/products/top', [AdminDashboardController::class, 'getTopProducts']);
    
    // Admin Users Management
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/{id}', [AdminUserController::class, 'show']);
        Route::post('/', [AdminUserController::class, 'store']);
        Route::put('/{id}', [AdminUserController::class, 'update']);
        Route::post('/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
    });
    
    // Admin Products Management
    Route::prefix('products')->group(function () {
        Route::get('/', [AdminProductController::class, 'index']);
        Route::get('/{id}', [AdminProductController::class, 'show']);
        Route::put('/{id}', [AdminProductController::class, 'update']);
        Route::delete('/{id}', [AdminProductController::class, 'destroy']);
    });
    
    // Admin Refunds
    Route::prefix('refunds')->group(function () {
        Route::get('/', [AdminRefundController::class, 'index']);
        Route::get('/stats', [AdminRefundController::class, 'stats']);
        Route::get('/eligible-lotteries', [AdminRefundController::class, 'eligibleLotteries']);
        Route::post('/process-automatic', [AdminRefundController::class, 'processAutomatic']);
        Route::post('/{id}/approve', [AdminRefundController::class, 'approve']);
        Route::post('/{id}/reject', [AdminRefundController::class, 'reject']);
    });
    
    // Admin Lotteries Management
    Route::prefix('lotteries')->controller(App\Http\Controllers\Api\AdminLotteryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/statistics', 'statistics');
        Route::get('/eligible-for-draw', 'eligibleForDraw');
        Route::get('/{id}', 'show');
        Route::post('/{id}/draw', 'draw');
        Route::post('/batch-draw', 'batchDraw');
        Route::post('/{id}/cancel', 'cancel');
        Route::put('/{id}', 'update');
        Route::get('/{id}/export', 'export');
    });
    
    // Admin Payments Management
    Route::prefix('payments')->controller(App\Http\Controllers\Api\AdminPaymentController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/stats', 'stats');
        Route::get('/{id}', 'show');
        Route::post('/{id}/refund', 'refund');
        Route::get('/export', 'export');
    });
    
    // Admin Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index']);
        Route::put('/general', [AdminSettingsController::class, 'updateGeneral']);
        Route::put('/payment', [AdminSettingsController::class, 'updatePayment']);
        Route::put('/lottery', [AdminSettingsController::class, 'updateLottery']);
        Route::put('/notifications', [AdminSettingsController::class, 'updateNotifications']);
        Route::put('/maintenance', [AdminSettingsController::class, 'updateMaintenance']);
        Route::post('/backup', [AdminSettingsController::class, 'createBackup']);
        Route::post('/cache/clear', [AdminSettingsController::class, 'clearCache']);
        Route::post('/sitemap/generate', [AdminSettingsController::class, 'generateSitemap']);
        Route::post('/database/optimize', [AdminSettingsController::class, 'optimizeDatabase']);
        Route::post('/email/test', [AdminSettingsController::class, 'testEmail']);
    });

    // Admin Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [AdminProfileController::class, 'index']);
        Route::put('/', [AdminProfileController::class, 'update']);
        Route::put('/password', [AdminProfileController::class, 'updatePassword']);
        Route::put('/preferences', [AdminProfileController::class, 'updatePreferences']);
        Route::post('/2fa/toggle', [AdminProfileController::class, 'toggle2FA']);
        Route::post('/sessions/terminate', [AdminProfileController::class, 'terminateSession']);
        Route::post('/sessions/terminate-all', [AdminProfileController::class, 'terminateAllSessions']);
        Route::post('/image', [AdminProfileController::class, 'uploadImage']);
        Route::get('/activity-log/export', [AdminProfileController::class, 'exportActivityLog']);
    });
});

// User profile routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
    Route::put('/user/password', [UserProfileController::class, 'updatePassword']);
    Route::post('/user/avatar', [UserProfileController::class, 'uploadAvatar']);
    Route::post('/user/test-upload', [UserProfileController::class, 'testUpload']);
    Route::get('/user/preferences', [UserProfileController::class, 'getPreferences']);
    Route::put('/user/preferences', [UserProfileController::class, 'updatePreferences']);
    Route::put('/user/preferences/detailed', [UserProfileController::class, 'updateDetailedPreferences']);
    Route::get('/user/sessions', [UserProfileController::class, 'getSessions']);
    Route::post('/user/2fa/toggle', [UserProfileController::class, 'toggleTwoFactor']);
    Route::post('/user/sessions/{id}/revoke', [UserProfileController::class, 'revokeSession']);
});

