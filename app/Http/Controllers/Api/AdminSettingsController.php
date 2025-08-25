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
                'app_name' => 'required|string|max:255',
                'app_url' => 'required|url|max:255',
                'app_description' => 'required|string|max:1000',
                'contact_email' => 'required|email|max:255',
                'contact_phone' => 'required|string|max:20',
                'default_language' => 'required|string|max:5',
                'default_country' => 'required|string|max:2'
            ]);

            $settings = [
                'app_name' => ['value' => $request->app_name, 'type' => 'string'],
                'app_url' => ['value' => $request->app_url, 'type' => 'string'],
                'app_description' => ['value' => $request->app_description, 'type' => 'string'],
                'contact_email' => ['value' => $request->contact_email, 'type' => 'string'],
                'contact_phone' => ['value' => $request->contact_phone, 'type' => 'string'],
                'default_language' => ['value' => $request->default_language, 'type' => 'string'],
                'default_country' => ['value' => $request->default_country, 'type' => 'string']
            ];

            foreach ($settings as $key => $config) {
                Setting::set($key, $config['value'], $config['type'], 'general');
            }

            return response()->json([
                'success' => true,
                'message' => 'Paramètres généraux mis à jour avec succès'
            ]);
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
                'default_currency' => 'required|string|max:3',
                'platform_commission' => 'required|numeric|min:0|max:100',
                'payment_methods' => 'array'
            ]);

            // Update payment settings
            Setting::set('default_currency', $request->default_currency, 'string', 'payment');
            Setting::set('platform_commission', $request->platform_commission, 'number', 'payment');

            // Update payment methods status
            if ($request->has('payment_methods')) {
                foreach ($request->payment_methods as $methodData) {
                    if (isset($methodData['id']) && isset($methodData['active'])) {
                        PaymentMethod::where('method_id', $methodData['id'])
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
                'min_ticket_price' => 'required|numeric|min:1',
                'max_ticket_price' => 'required|numeric|min:1',
                'min_participants' => 'required|integer|min:1',
                'max_duration_days' => 'required|integer|min:1|max:365',
                'auto_refund' => 'boolean',
                'auto_draw' => 'boolean'
            ]);

            $settings = [
                'min_ticket_price' => ['value' => $request->min_ticket_price, 'type' => 'number'],
                'max_ticket_price' => ['value' => $request->max_ticket_price, 'type' => 'number'],
                'min_participants' => ['value' => $request->min_participants, 'type' => 'number'],
                'max_duration_days' => ['value' => $request->max_duration_days, 'type' => 'number'],
                'auto_refund' => ['value' => $request->auto_refund ?? false, 'type' => 'boolean'],
                'auto_draw' => ['value' => $request->auto_draw ?? false, 'type' => 'boolean']
            ];

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
                'from_email' => 'required|email|max:255',
                'from_name' => 'required|string|max:255',
                'notification_types' => 'array'
            ]);

            // Update notification settings
            Setting::set('from_email', $request->from_email, 'string', 'notifications');
            Setting::set('from_name', $request->from_name, 'string', 'notifications');

            // Update notification types
            if ($request->has('notification_types')) {
                foreach ($request->notification_types as $typeData) {
                    if (isset($typeData['id']) && isset($typeData['enabled'])) {
                        NotificationType::where('type_id', $typeData['id'])
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
                'maintenance_message' => 'required|string|max:1000'
            ]);

            Setting::set('maintenance_mode', $request->maintenance_mode ?? false, 'boolean', 'maintenance');
            Setting::set('maintenance_message', $request->maintenance_message, 'string', 'maintenance');

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
}