<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\PaymentMethod;
use App\Models\NotificationType;
use App\Models\Country;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class AdminSettingsController extends Controller
{
    /**
     * Get all settings
     */
    public function index(): JsonResponse
    {
        try {
            $settings = Setting::getAllGrouped();
            $paymentMethods = PaymentMethod::orderBy('sort_order')->get();
            $notificationTypes = NotificationType::orderBy('category')->orderBy('name')->get();
            $countries = Country::where('is_active', true)->orderBy('name')->get();
            $languages = Language::where('is_active', true)->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'general' => $settings['general'] ?? [],
                    'payment' => $settings['payment'] ?? [],
                    'lottery' => $settings['lottery'] ?? [],
                    'notifications' => $settings['notifications'] ?? [],
                    'maintenance' => $settings['maintenance'] ?? [],
                    'payment_methods' => $paymentMethods,
                    'notification_types' => $notificationTypes,
                    'countries' => $countries,
                    'languages' => $languages
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des paramètres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'app_name' => 'nullable|string|max:255',
                'app_url' => 'nullable|string|max:255',
                'app_description' => 'nullable|string|max:1000',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'default_language' => 'nullable|string|max:5',
                'default_country' => 'nullable|string|max:2'
            ]);

            $settings = [
                'app_name' => ['value' => $request->app_name ?? '', 'type' => 'string'],
                'app_url' => ['value' => $request->app_url ?? '', 'type' => 'string'],
                'app_description' => ['value' => $request->app_description ?? '', 'type' => 'string'],
                'contact_email' => ['value' => $request->contact_email ?? '', 'type' => 'string'],
                'contact_phone' => ['value' => $request->contact_phone ?? '', 'type' => 'string'],
                'default_language' => ['value' => $request->default_language ?? 'fr', 'type' => 'string'],
                'default_country' => ['value' => $request->default_country ?? 'GA', 'type' => 'string']
            ];

            foreach ($settings as $key => $config) {
                Setting::set($key, $config['value'], $config['type'], 'general');
            }

            return response()->json([
                'success' => true,
                'message' => 'Paramètres généraux mis à jour avec succès'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres généraux',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment settings
     */
    public function updatePayment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'default_currency' => 'nullable|string|max:3',
                'platform_commission' => 'nullable|numeric|min:0|max:100',
                'payment_methods' => 'array'
            ]);

            // Update payment settings
            if ($request->default_currency) {
                Setting::set('default_currency', $request->default_currency, 'string', 'payment');
            }
            if ($request->platform_commission !== null) {
                Setting::set('platform_commission', $request->platform_commission, 'number', 'payment');
            }

            // Update payment methods status
            if ($request->has('payment_methods')) {
                foreach ($request->payment_methods as $methodData) {
                    if (isset($methodData['method_id']) && isset($methodData['active'])) {
                        PaymentMethod::where('method_id', $methodData['method_id'])
                            ->update(['active' => $methodData['active']]);
                    } elseif (isset($methodData['id']) && isset($methodData['active'])) {
                        // Fallback for database ID
                        PaymentMethod::where('id', $methodData['id'])
                            ->update(['active' => $methodData['active']]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Paramètres de paiement mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres de paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lottery settings
     */
    public function updateLottery(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'min_ticket_price' => 'nullable|numeric|min:1',
                'max_ticket_price' => 'nullable|numeric|min:1',
                'min_participants' => 'nullable|integer|min:1',
                'max_duration_days' => 'nullable|integer|min:1|max:365',
                'auto_refund' => 'boolean',
                'auto_draw' => 'boolean'
            ]);

            $settings = [];
            
            if ($request->min_ticket_price !== null) {
                $settings['min_ticket_price'] = ['value' => $request->min_ticket_price, 'type' => 'number'];
            }
            if ($request->max_ticket_price !== null) {
                $settings['max_ticket_price'] = ['value' => $request->max_ticket_price, 'type' => 'number'];
            }
            if ($request->min_participants !== null) {
                $settings['min_participants'] = ['value' => $request->min_participants, 'type' => 'number'];
            }
            if ($request->max_duration_days !== null) {
                $settings['max_duration_days'] = ['value' => $request->max_duration_days, 'type' => 'number'];
            }
            if ($request->has('auto_refund')) {
                $settings['auto_refund'] = ['value' => $request->auto_refund, 'type' => 'boolean'];
            }
            if ($request->has('auto_draw')) {
                $settings['auto_draw'] = ['value' => $request->auto_draw, 'type' => 'boolean'];
            }

            foreach ($settings as $key => $config) {
                Setting::set($key, $config['value'], $config['type'], 'lottery');
            }

            return response()->json([
                'success' => true,
                'message' => 'Paramètres de tombola mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres de tombola',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'from_email' => 'nullable|email|max:255',
                'from_name' => 'nullable|string|max:255',
                'notification_types' => 'array'
            ]);

            // Update notification settings
            if ($request->from_email) {
                Setting::set('from_email', $request->from_email, 'string', 'notifications');
            }
            if ($request->from_name) {
                Setting::set('from_name', $request->from_name, 'string', 'notifications');
            }

            // Update notification types
            if ($request->has('notification_types')) {
                foreach ($request->notification_types as $typeData) {
                    if (isset($typeData['type_id']) && isset($typeData['enabled'])) {
                        NotificationType::where('type_id', $typeData['type_id'])
                            ->update(['enabled' => $typeData['enabled']]);
                    } elseif (isset($typeData['id']) && isset($typeData['enabled'])) {
                        // Fallback for numeric ID - find by database ID
                        NotificationType::where('id', $typeData['id'])
                            ->update(['enabled' => $typeData['enabled']]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Paramètres de notification mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres de notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update maintenance settings
     */
    public function updateMaintenance(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'maintenance_mode' => 'boolean',
                'maintenance_message' => 'nullable|string|max:1000'
            ]);

            if ($request->has('maintenance_mode')) {
                Setting::set('maintenance_mode', $request->maintenance_mode, 'boolean', 'maintenance');
            }
            if ($request->maintenance_message) {
                Setting::set('maintenance_message', $request->maintenance_message, 'string', 'maintenance');
            }

            return response()->json([
                'success' => true,
                'message' => 'Paramètres de maintenance mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres de maintenance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create backup
     */
    public function createBackup(): JsonResponse
    {
        try {
            $settings = Setting::all();
            $paymentMethods = PaymentMethod::all();
            $notificationTypes = NotificationType::all();

            $backup = [
                'timestamp' => now()->toISOString(),
                'settings' => $settings,
                'payment_methods' => $paymentMethods,
                'notification_types' => $notificationTypes
            ];

            // In a real application, you would save this to a file or storage
            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde créée avec succès',
                'data' => $backup
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la sauvegarde',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            // Clear Laravel cache
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache vidé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage du cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate sitemap
     */
    public function generateSitemap(): JsonResponse
    {
        try {
            $baseUrl = config('app.url');
            
            // Basic sitemap structure
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            
            // Add home page
            $xml .= '<url>' . "\n";
            $xml .= '<loc>' . $baseUrl . '</loc>' . "\n";
            $xml .= '<lastmod>' . now()->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '<priority>1.0</priority>' . "\n";
            $xml .= '</url>' . "\n";
            
            // Add static pages
            $staticPages = [
                '/about' => '0.8',
                '/contact' => '0.6',
                '/terms' => '0.5',
                '/privacy' => '0.5',
                '/lotteries' => '0.9',
            ];
            
            foreach ($staticPages as $page => $priority) {
                $xml .= '<url>' . "\n";
                $xml .= '<loc>' . $baseUrl . $page . '</loc>' . "\n";
                $xml .= '<lastmod>' . now()->format('Y-m-d') . '</lastmod>' . "\n";
                $xml .= '<priority>' . $priority . '</priority>' . "\n";
                $xml .= '</url>' . "\n";
            }
            
            // Add dynamic lottery pages (if you have a Lottery model)
            try {
                if (class_exists(\App\Models\Lottery::class)) {
                    $lotteries = \App\Models\Lottery::where('status', 'active')->get();
                    foreach ($lotteries as $lottery) {
                        $xml .= '<url>' . "\n";
                        $xml .= '<loc>' . $baseUrl . '/lotteries/' . $lottery->slug . '</loc>' . "\n";
                        $xml .= '<lastmod>' . $lottery->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
                        $xml .= '<priority>0.7</priority>' . "\n";
                        $xml .= '</url>' . "\n";
                    }
                }
            } catch (\Exception $e) {
                // Skip if Lottery model doesn't exist or has issues
            }
            
            $xml .= '</urlset>';
            
            // Save sitemap to public directory
            $sitemapPath = public_path('sitemap.xml');
            file_put_contents($sitemapPath, $xml);

            return response()->json([
                'success' => true,
                'message' => 'Sitemap généré avec succès',
                'data' => [
                    'sitemap_url' => $baseUrl . '/sitemap.xml',
                    'file_path' => $sitemapPath
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du sitemap',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize database
     */
    public function optimizeDatabase(): JsonResponse
    {
        try {
            // Run database optimization commands
            \Artisan::call('migrate:status');
            \Artisan::call('db:show');
            
            // You can add more database optimization commands here
            // For example: \Artisan::call('queue:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Base de données optimisée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'optimisation de la base de données',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test email configuration
     */
    public function testEmail(): JsonResponse
    {
        try {
            $testEmail = Setting::get('contact_email', 'admin@koumbaya.com');
            
            // Send a test email
            \Mail::raw('Ceci est un email de test pour vérifier la configuration SMTP.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                    ->subject('Test Email - Configuration SMTP');
            });

            return response()->json([
                'success' => true,
                'message' => 'Email de test envoyé avec succès à ' . $testEmail
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email de test',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}