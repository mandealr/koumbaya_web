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
use App\Http\Controllers\Api\AdminVendorController;
use App\Http\Controllers\Api\AdminCustomerController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PrivilegeController;
use App\Http\Controllers\Api\UserTypeController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminPaymentController;
use App\Http\Controllers\Api\AdminSettingsController;
use App\Http\Controllers\Api\AdminProfileController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\PaymentTrackingController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\MerchantOrderController;
use App\Http\Controllers\Api\VendorProfileController;
use App\Http\Controllers\Api\StatsController;

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

// Routes d'authentification publiques avec rate limiting modéré
Route::group([
    'middleware' => ['throttle.api:100,1'],
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-account', [AuthController::class, 'verifyAccount']);
    Route::get('verify-email/{token}', [AuthController::class, 'verifyAccountByUrl']);
    Route::post('resend-verification', [AuthController::class, 'resendVerificationEmail']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Social Authentication - Redirect only (callback is in web.php)
    Route::get('{provider}/redirect', [AuthController::class, 'redirectToProvider'])->where('provider', 'google|facebook|apple');

    // Mobile OAuth - Validate tokens from native SDKs
    Route::post('social/mobile', [AuthController::class, 'mobileOAuthLogin']);
});

// Routes d'authentification pour utilisateurs connectés avec rate limiting très permissif
Route::group([
    'middleware' => ['auth:sanctum', 'throttle.api:1000,1'],
    'prefix' => 'auth'
], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

// Routes publiques (sans authentification) avec rate limiting permissif
Route::group([
    'middleware' => ['throttle.api:1000,1']
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
    Route::get('products/lottery-duration-constraints', [ProductController::class, 'getLotteryDurationConstraints']);
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

    // Merchant Ratings (public)
    Route::get('merchants/top-rated', [App\Http\Controllers\Api\MerchantRatingController::class, 'topRated']);
    Route::get('merchants/{id}/rating', [App\Http\Controllers\Api\MerchantRatingController::class, 'show']);
    Route::get('merchants/{id}/rating/summary', [App\Http\Controllers\Api\MerchantRatingController::class, 'summary']);
    Route::get('merchants/{id}/rating/history', [App\Http\Controllers\Api\MerchantRatingController::class, 'history']);
    Route::get('merchants/{id}/reviews', [App\Http\Controllers\Api\MerchantRatingController::class, 'reviews']);
});


// Routes protégées (authentification requise) avec rate limiting permissif
Route::group([
    'middleware' => ['auth:sanctum', 'throttle.api:1000,1']
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
    
    // User actions
    Route::post('/user/become-seller', [App\Http\Controllers\Api\UserController::class, 'becomeSeller']);
    Route::post('/user/profile/photo', [App\Http\Controllers\Api\UserController::class, 'updateProfilePhoto']);
    Route::delete('/user/profile/photo', [App\Http\Controllers\Api\UserController::class, 'deleteProfilePhoto']);
    Route::put('/user/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);

    // Product Images Upload
    Route::prefix('products/images')->group(function () {
        Route::post('/', [App\Http\Controllers\Api\ProductImageController::class, 'upload']);
        Route::post('/multiple', [App\Http\Controllers\Api\ProductImageController::class, 'uploadMultiple']);
        Route::delete('/', [App\Http\Controllers\Api\ProductImageController::class, 'delete']);
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
        Route::get('/{order_number}', [OrderTrackingController::class, 'show']);
        Route::get('/{order_number}/invoice', [OrderTrackingController::class, 'invoice']);
        Route::post('/{order_number}/confirm-delivery', [OrderTrackingController::class, 'confirmDelivery']);
        Route::post('/{order_number}/cancel', [OrderTrackingController::class, 'cancel']);
        Route::patch('/{order_number}/status', [OrderTrackingController::class, 'updateStatus']);
    });
    
    // Payment Tracking
    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentTrackingController::class, 'index']);
        Route::get('/stats', [PaymentTrackingController::class, 'stats']);
        Route::get('/search', [PaymentTrackingController::class, 'search']);
        Route::get('/{paymentId}', [PaymentTrackingController::class, 'show']);
    });
    
    // Statistics API
    Route::prefix('stats')->group(function () {
        Route::get('/customer/dashboard', [StatsController::class, 'customerDashboard']);
        Route::get('/customer/tickets', [StatsController::class, 'customerTickets']);
        Route::get('/lotteries/popular', [StatsController::class, 'popularLotteries']);
    });
});

// Routes Marchands seulement avec rate limiting permissif + vérification obligatoire
Route::group([
    'middleware' => ['auth:sanctum', 'merchant', 'verified', 'throttle.api:2000,1']
], function () {
    // Products (Marchands seulement)
    Route::get('merchant/products', [ProductController::class, 'merchantProducts']);
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::get('products/{id}/can-delete', [ProductController::class, 'canDelete']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
    Route::post('products/{id}/create-lottery', [ProductController::class, 'createLottery']);
    
    // Lotteries (Marchands seulement)
    Route::post('lotteries/{id}/draw', [LotteryController::class, 'drawLottery']);
    Route::get('lotteries/{id}/verify-draw', [LotteryController::class, 'verifyDraw']);
    Route::get('lotteries/{id}/draw-history', [LotteryController::class, 'drawHistory']);
    Route::post('lotteries/{id}/cancel', [App\Http\Controllers\Api\LotteryCancellationController::class, 'cancel']);
    Route::get('lotteries/{id}/cancellation-details', [App\Http\Controllers\Api\LotteryCancellationController::class, 'getCancellationDetails']);
    
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
    
    // Merchant Orders Management
    Route::prefix('merchant/orders')->group(function () {
        Route::get('/', [MerchantOrderController::class, 'index']);
        Route::get('/export', [MerchantOrderController::class, 'exportCsv']);
        Route::get('/top-products', [MerchantOrderController::class, 'getTopProducts']);
        Route::get('/metrics', [MerchantOrderController::class, 'metrics']);
        Route::get('/metrics/health', [MerchantOrderController::class, 'metricsHealth']);
        Route::post('/{order_number}/send-delivery-reminder', [MerchantOrderController::class, 'sendDeliveryReminder']);
    });
    
    // Merchant Profile Management
    Route::prefix('merchant/profile')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\MerchantProfileController::class, 'show']);
        Route::put('/', [App\Http\Controllers\Api\MerchantProfileController::class, 'update']);
        Route::post('/avatar', [App\Http\Controllers\Api\MerchantProfileController::class, 'updateAvatar']);
        Route::put('/password', [App\Http\Controllers\Api\MerchantProfileController::class, 'updatePassword']);
        Route::get('/business-stats', [App\Http\Controllers\Api\MerchantProfileController::class, 'businessStats']);
    });

    // Merchant Rating (pour le marchand connecté)
    Route::get('merchant/my-rating', [App\Http\Controllers\Api\MerchantRatingController::class, 'myRating']);

    // Client: Ajouter un avis sur un marchand
    Route::post('merchants/{id}/reviews', [App\Http\Controllers\Api\MerchantRatingController::class, 'storeReview']);

    // Merchant Refund Management
    Route::prefix('merchant/refunds')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\MerchantRefundController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\MerchantRefundController::class, 'store']);
        Route::get('/stats', [App\Http\Controllers\Api\MerchantRefundController::class, 'stats']);
        Route::get('/eligible-transactions', [App\Http\Controllers\Api\MerchantRefundController::class, 'eligibleTransactions']);
        Route::get('/{id}', [App\Http\Controllers\Api\MerchantRefundController::class, 'show']);
        Route::post('/{id}/cancel', [App\Http\Controllers\Api\MerchantRefundController::class, 'cancel']);
    });
    
    // Vendor Profiles Management
    Route::prefix('vendor-profiles')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\VendorProfileController::class, 'index']);
        Route::get('/types', [App\Http\Controllers\Api\VendorProfileController::class, 'types']);
        Route::post('/', [App\Http\Controllers\Api\VendorProfileController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\Api\VendorProfileController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\Api\VendorProfileController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\Api\VendorProfileController::class, 'destroy']);
    });
    
    // Transaction management
    Route::put('transactions/{id}/status', [MerchantDashboardController::class, 'updateTransactionStatus']);
    
    // Merchant Statistics API
    Route::prefix('stats')->group(function () {
        Route::get('/merchant/dashboard', [StatsController::class, 'merchantDashboard']);
        Route::get('/merchant/products', [StatsController::class, 'merchantProducts']);
        Route::get('/merchant/orders', [StatsController::class, 'merchantOrders']);
        Route::get('/merchant/lotteries', [StatsController::class, 'merchantLotteries']);
        Route::get('/merchant/analytics', [StatsController::class, 'merchantAnalytics']);
    });
    
    // Merchant Payout Requests
    Route::prefix('merchant/payout-requests')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\MerchantPayoutController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\MerchantPayoutController::class, 'store']);
        Route::get('/stats', [App\Http\Controllers\Api\MerchantPayoutController::class, 'stats']);
        Route::get('/eligible-orders', [App\Http\Controllers\Api\MerchantPayoutController::class, 'eligibleOrders']);
        Route::get('/eligible-lotteries', [App\Http\Controllers\Api\MerchantPayoutController::class, 'eligibleLotteries']);
        Route::get('/{id}', [App\Http\Controllers\Api\MerchantPayoutController::class, 'show']);
        Route::delete('/{id}', [App\Http\Controllers\Api\MerchantPayoutController::class, 'cancel']);
    });
});

// Routes Admin avec rate limiting permissif
Route::group([
    'middleware' => ['auth:sanctum', 'admin', 'throttle.api:1000,1'],
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
        Route::get('/admin-roles', [AdminUserController::class, 'getAdminRoles']);
        Route::get('/{id}', [AdminUserController::class, 'show']);
        Route::post('/', [AdminUserController::class, 'store']);
        Route::put('/{id}', [AdminUserController::class, 'update']);
        Route::post('/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
    });

    // Admin Vendors Management (Business accounts)
    Route::prefix('vendors')->group(function () {
        Route::get('/', [AdminVendorController::class, 'index']);
        Route::post('/', [AdminVendorController::class, 'store']);
        Route::put('/{id}', [AdminVendorController::class, 'update']);
        Route::delete('/{id}', [AdminVendorController::class, 'destroy']);
    });

    // Admin Customers Management (Particulier role)
    Route::prefix('customers')->group(function () {
        Route::get('/', [AdminCustomerController::class, 'index']);
        Route::get('/statistics', [AdminCustomerController::class, 'statistics']);
        Route::post('/{id}/toggle-status', [AdminCustomerController::class, 'toggleStatus']);
    });

    // Admin Users Management (Admin roles: superadmin, admin, agent)
    Route::prefix('admins')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/statistics', [AdminUserController::class, 'statistics']);
        Route::post('/', [AdminUserController::class, 'store']);
        Route::post('/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
    });

    // Roles Management
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
        Route::get('/user-types', [RoleController::class, 'getUserTypes']);
        Route::get('/privileges', [RoleController::class, 'getPrivileges']);
        Route::get('/statistics', [RoleController::class, 'statistics']);
    });

    // Privileges Management
    Route::prefix('privileges')->group(function () {
        Route::get('/', [PrivilegeController::class, 'index']);
        Route::get('/statistics', [PrivilegeController::class, 'statistics']);
    });

    // User Types Management
    Route::prefix('user-types')->group(function () {
        Route::get('/', [UserTypeController::class, 'index']);
        Route::get('/statistics', [UserTypeController::class, 'statistics']);
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
        Route::get('/{id}', [AdminRefundController::class, 'show']);
        Route::post('/{id}/approve', [AdminRefundController::class, 'approve']);
        Route::post('/{id}/reject', [AdminRefundController::class, 'reject']);
        Route::post('/{id}/retry', [AdminRefundController::class, 'retry']);
    });
    
    // Admin Products Management
    Route::prefix('products')->controller(App\Http\Controllers\Api\AdminProductController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/stats', 'getStats');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::patch('/{id}/status', 'updateStatus');
        Route::delete('/{id}', 'destroy');
    });
    
    // Admin Lotteries Management
    Route::prefix('lotteries')->controller(App\Http\Controllers\Api\AdminLotteryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/statistics', 'statistics');
        Route::get('/eligible-for-draw', 'eligibleForDraw');
        Route::get('/{id}/payments', 'getLotteryPayments');
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

    // Admin Orders Management
    Route::prefix('orders')->controller(AdminOrderController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/stats', 'stats');
        Route::get('/{orderNumber}', 'show');
        Route::put('/{orderNumber}/status', 'updateStatus');
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
    
    // Platform Statistics (Admin only)
    Route::get('/stats/platform', [StatsController::class, 'platformStats']);
    
    // Admin Payout Management
    Route::prefix('payout-requests')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\AdminPayoutController::class, 'index']);
        Route::get('/stats', [App\Http\Controllers\Api\AdminPayoutController::class, 'dashboardStats']);
        Route::get('/merchants-with-payouts', [App\Http\Controllers\Api\AdminPayoutController::class, 'merchantsWithPayouts']);
        Route::get('/{id}', [App\Http\Controllers\Api\AdminPayoutController::class, 'show']);
        Route::post('/{id}/approve', [App\Http\Controllers\Api\AdminPayoutController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Api\AdminPayoutController::class, 'reject']);
        Route::post('/batch-approve', [App\Http\Controllers\Api\AdminPayoutController::class, 'batchApprove']);
    });
    
    // Admin Direct Payouts
    Route::post('/direct-payout', [App\Http\Controllers\Api\AdminPayoutController::class, 'createDirectPayout']);
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

// Note: Social Authentication callback is now in routes/web.php
// The redirect endpoint is already defined above in the auth group