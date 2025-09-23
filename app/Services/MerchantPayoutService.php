<?php

namespace App\Services;

use App\Models\MerchantPayoutRequest;
use App\Models\Payout;
use App\Models\User;
use App\Models\Order;
use App\Models\Lottery;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MerchantPayoutService
{
    protected $refundService;
    protected $notificationService;
    
    public function __construct(RefundService $refundService, NotificationService $notificationService)
    {
        $this->refundService = $refundService;
        $this->notificationService = $notificationService;
    }

    /**
     * Créer une demande de payout pour remboursement
     */
    public function createPayoutRequest(User $merchant, array $data): MerchantPayoutRequest
    {
        // Validation des données
        $this->validatePayoutRequest($data);
        
        // Vérifier que le marchand est bien propriétaire de la commande/tombola
        $this->validateOwnership($merchant, $data);
        
        $request = MerchantPayoutRequest::create([
            'merchant_id' => $merchant->id,
            'order_id' => $data['order_id'] ?? null,
            'lottery_id' => $data['lottery_id'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'refund_type' => $data['refund_type'],
            'reason' => $data['reason'],
            'refund_amount' => $data['refund_amount'],
            'customer_id' => $data['customer_id'],
            'customer_phone' => $this->formatPhone($data['customer_phone'], $data['payment_operator']),
            'payment_operator' => $data['payment_operator'],
            'status' => 'pending'
        ]);
        
        $request->request_number = $request->generateRequestNumber();
        $request->save();
        
        // Notifier les admins
        $this->notifyAdminsOfNewRequest($request);
        
        Log::info('Nouvelle demande de payout créée', [
            'request_id' => $request->id,
            'merchant_id' => $merchant->id,
            'amount' => $request->refund_amount
        ]);
        
        return $request;
    }

    /**
     * Approuver et traiter une demande de payout
     */
    public function approveAndProcess(MerchantPayoutRequest $request, User $admin, string $notes = null): bool
    {
        DB::beginTransaction();
        
        try {
            // 1. Marquer comme approuvé
            $request->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_notes' => $notes
            ]);
            
            // 2. Créer le payout via SHAP
            $payout = $this->createPayoutViaSHAP($request, $admin);
            
            if ($payout) {
                $request->update([
                    'status' => 'completed',
                    'payout_id' => $payout->id
                ]);
                
                // Notifier le marchand et le client
                $this->notifyPayoutCompleted($request);
                
                DB::commit();
                
                Log::info('Payout approuvé et traité', [
                    'request_id' => $request->id,
                    'payout_id' => $payout->id,
                    'admin_id' => $admin->id
                ]);
                
                return true;
            }
            
            // Si échec, repasser en pending
            $request->update(['status' => 'pending']);
            DB::rollback();
            
            Log::warning('Échec du traitement payout', [
                'request_id' => $request->id
            ]);
            
            return false;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur approbation payout', [
                'request_id' => $request->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Créer un payout direct (admin only)
     */
    public function createDirectPayout(User $admin, array $data): ?Payout
    {
        // Validation
        $this->validateDirectPayout($data);
        
        Log::info('Création payout direct par admin', [
            'admin_id' => $admin->id,
            'customer_id' => $data['customer_id'],
            'amount' => $data['refund_amount']
        ]);
        
        // Créer directement via SHAP
        return $this->processSHAPPayout([
            'operator' => $data['payment_operator'],
            'phone' => $this->formatPhone($data['customer_phone'], $data['payment_operator']),
            'amount' => $data['refund_amount'],
            'reference' => 'DIR-' . time() . '-' . $data['customer_id'],
            'initiated_by' => $admin->id,
            'category' => 'system_refund',
            'customer_id' => $data['customer_id']
        ]);
    }

    /**
     * Traiter le payout via SHAP API
     */
    protected function createPayoutViaSHAP(MerchantPayoutRequest $request, User $initiator): ?Payout
    {
        return $this->processSHAPPayout([
            'operator' => $request->payment_operator,
            'phone' => $request->formatPhoneNumber(),
            'amount' => $request->refund_amount,
            'reference' => $request->request_number,
            'initiated_by' => $initiator->id,
            'category' => 'merchant_refund',
            'merchant_payout_request_id' => $request->id,
            'customer_id' => $request->customer_id
        ]);
    }

    /**
     * Logique commune pour SHAP
     */
    protected function processSHAPPayout(array $data): ?Payout
    {
        try {
            // 1. Auth SHAP
            $auth = Http::post(env('SHAP_URL') . 'auth', [
                "api_id" => env('API_PAYOUT_ID'),
                "api_secret" => env('API_PAYOUT_SECRET'),
            ]);

            if ($auth->status() !== 200) {
                Log::error('Échec authentification SHAP', [
                    'status' => $auth->status(),
                    'response' => $auth->body()
                ]);
                return null;
            }

            $token = json_decode($auth->body())->access_token;

            // 2. Créer payout
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . $token,
            ])->post(env('SHAP_URL') . 'payout', [
                "payment_system_name" => $data['operator'],
                "payout" => [
                    "payee_msisdn" => $data['phone'],
                    "amount" => $data['amount'],
                    "external_reference" => $data['reference'],
                    "payout_type" => "refund",
                ],
            ]);

            if ($response->status() === 200) {
                $body = json_decode($response->body());
                
                if ($body->response->state === "success") {
                    // Créer l'enregistrement Payout
                    $payout = Payout::create([
                        'external_reference' => $body->response->external_reference,
                        'payment_system_name' => $body->response->payment_system_name,
                        'payee_msisdn' => $data['phone'],
                        'amount' => $body->response->amount,
                        'payout_type' => "refund",
                        'payout_id' => $body->response->payout_id,
                        'transaction_id' => $body->response->transaction_id,
                        'message' => $body->success_message,
                        'status' => STATUT_DO,
                        'user_id' => $data['customer_id'],
                        'payout_category' => $data['category'],
                        'initiated_by' => $data['initiated_by'],
                        'merchant_payout_request_id' => $data['merchant_payout_request_id'] ?? null
                    ]);

                    Log::info('Payout SHAP créé avec succès', [
                        'payout_id' => $payout->id,
                        'transaction_id' => $body->response->transaction_id,
                        'amount' => $body->response->amount
                    ]);

                    return $payout;
                } else {
                    Log::error('Payout SHAP échoué - état non success', [
                        'response' => $body
                    ]);
                }
            } else {
                Log::error('Payout SHAP échoué', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception lors du payout SHAP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null;
    }

    /**
     * Rejeter une demande
     */
    public function rejectRequest(MerchantPayoutRequest $request, User $admin, string $reason): bool
    {
        $request->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $admin->id,
            'approved_at' => now()
        ]);

        // Notifier le marchand
        $this->notifyPayoutRejected($request);
        
        Log::info('Demande de payout rejetée', [
            'request_id' => $request->id,
            'admin_id' => $admin->id,
            'reason' => $reason
        ]);

        return true;
    }

    /**
     * Valider les données de la demande de payout
     */
    protected function validatePayoutRequest(array $data): void
    {
        if ($data['refund_amount'] < 500 || $data['refund_amount'] > 500000) {
            throw new \Exception('Le montant doit être entre 500 et 500,000 FCFA');
        }

        if (!in_array($data['payment_operator'], ['airtelmoney', 'moovmoney4'])) {
            throw new \Exception('Opérateur non supporté');
        }
        
        if (strlen($data['reason']) < 10) {
            throw new \Exception('La raison doit contenir au moins 10 caractères');
        }
    }

    /**
     * Vérifier que le marchand possède la commande/tombola
     */
    protected function validateOwnership(User $merchant, array $data): void
    {
        if (isset($data['order_id'])) {
            $order = Order::find($data['order_id']);
            if (!$order) {
                throw new \Exception('Commande non trouvée');
            }
            
            // Vérifier que le marchand possède le produit de la commande
            if ($order->product->user_id !== $merchant->id) {
                throw new \Exception('Vous n\'êtes pas autorisé à rembourser cette commande');
            }
        }

        if (isset($data['lottery_id'])) {
            $lottery = Lottery::find($data['lottery_id']);
            if (!$lottery) {
                throw new \Exception('Tombola non trouvée');
            }
            
            // Vérifier que le marchand possède le produit de la tombola
            if ($lottery->product->user_id !== $merchant->id) {
                throw new \Exception('Vous n\'êtes pas autorisé à rembourser cette tombola');
            }
        }
    }

    /**
     * Valider les données pour un payout direct
     */
    protected function validateDirectPayout(array $data): void
    {
        if ($data['refund_amount'] < 500 || $data['refund_amount'] > 500000) {
            throw new \Exception('Le montant doit être entre 500 et 500,000 FCFA');
        }

        if (!in_array($data['payment_operator'], ['airtelmoney', 'moovmoney4'])) {
            throw new \Exception('Opérateur non supporté');
        }
        
        // Vérifier que le client existe
        $customer = User::find($data['customer_id']);
        if (!$customer) {
            throw new \Exception('Client non trouvé');
        }
    }

    /**
     * Formater le numéro de téléphone selon l'opérateur
     */
    protected function formatPhone($phone, $operator): string
    {
        // Retirer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Si le numéro commence par 0, le retirer
        if (substr($phone, 0, 1) == '0') {
            $phone = substr($phone, 1);
        }
        
        // Si le numéro fait 8 chiffres, ajouter le préfixe du Gabon
        if (strlen($phone) == 8) {
            $phone = '241' . $phone;
        }
        
        // Validation spécifique par opérateur
        if ($operator == "airtelmoney") {
            // Vérifier que le numéro commence bien par les préfixes Airtel Gabon
            if (!preg_match('/^241(04|05|07)[0-9]{6}$/', $phone)) {
                throw new \Exception('Numéro Airtel Money invalide');
            }
        } elseif ($operator == "moovmoney4") {
            // Vérifier que le numéro commence bien par les préfixes Moov Gabon
            if (!preg_match('/^241(06|62|65|66)[0-9]{6}$/', $phone)) {
                throw new \Exception('Numéro Moov Money invalide');
            }
        }
        
        return $phone;
    }

    /**
     * Notifier les admins d'une nouvelle demande
     */
    protected function notifyAdminsOfNewRequest(MerchantPayoutRequest $request): void
    {
        $admins = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'new_payout_request',
                'title' => 'Nouvelle demande de remboursement',
                'message' => "Le marchand {$request->merchant->name} demande un remboursement de {$request->refund_amount} FCFA",
                'data' => [
                    'request_id' => $request->id,
                    'merchant_name' => $request->merchant->name,
                    'amount' => $request->refund_amount,
                    'customer_name' => $request->customer->name
                ],
                'related_type' => MerchantPayoutRequest::class,
                'related_id' => $request->id,
                'sent_at' => now(),
                'status' => 'sent'
            ]);
        }
        
        Log::info('Notifications envoyées aux admins', [
            'request_id' => $request->id,
            'admin_count' => $admins->count()
        ]);
    }

    /**
     * Notifier de l'approbation du payout
     */
    protected function notifyPayoutCompleted(MerchantPayoutRequest $request): void
    {
        // Notification marchand
        Notification::create([
            'user_id' => $request->merchant_id,
            'type' => 'payout_approved',
            'title' => 'Remboursement approuvé',
            'message' => "Votre demande de remboursement #{$request->request_number} a été approuvée et traitée",
            'data' => [
                'request_id' => $request->id,
                'amount' => $request->refund_amount,
                'customer_name' => $request->customer->name
            ],
            'related_type' => MerchantPayoutRequest::class,
            'related_id' => $request->id,
            'sent_at' => now(),
            'status' => 'sent'
        ]);

        // Notification client
        Notification::create([
            'user_id' => $request->customer_id,
            'type' => 'refund_processed',
            'title' => 'Remboursement en cours',
            'message' => "Un remboursement de {$request->refund_amount} FCFA est en cours vers votre {$request->payment_operator}",
            'data' => [
                'amount' => $request->refund_amount,
                'operator' => $request->payment_operator,
                'merchant_name' => $request->merchant->name
            ],
            'related_type' => MerchantPayoutRequest::class,
            'related_id' => $request->id,
            'sent_at' => now(),
            'status' => 'sent'
        ]);
        
        // Email au client (si service email disponible)
        try {
            if ($request->customer->email) {
                $this->notificationService->sendRefundNotification($request->customer, [
                    'amount' => $request->refund_amount,
                    'operator' => $request->payment_operator,
                    'reference' => $request->request_number
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Échec envoi email remboursement', [
                'error' => $e->getMessage(),
                'customer_id' => $request->customer_id
            ]);
        }
    }

    /**
     * Notifier du rejet du payout
     */
    protected function notifyPayoutRejected(MerchantPayoutRequest $request): void
    {
        Notification::create([
            'user_id' => $request->merchant_id,
            'type' => 'payout_rejected',
            'title' => 'Remboursement rejeté',
            'message' => "Votre demande de remboursement #{$request->request_number} a été rejetée: {$request->rejection_reason}",
            'data' => [
                'request_id' => $request->id,
                'rejection_reason' => $request->rejection_reason
            ],
            'related_type' => MerchantPayoutRequest::class,
            'related_id' => $request->id,
            'sent_at' => now(),
            'status' => 'sent'
        ]);
    }

    /**
     * Obtenir les statistiques des payouts pour un marchand
     */
    public function getMerchantStats(User $merchant): array
    {
        return [
            'total_requests' => MerchantPayoutRequest::byMerchant($merchant->id)->count(),
            'pending_requests' => MerchantPayoutRequest::byMerchant($merchant->id)->pending()->count(),
            'approved_requests' => MerchantPayoutRequest::byMerchant($merchant->id)->approved()->count(),
            'rejected_requests' => MerchantPayoutRequest::byMerchant($merchant->id)->rejected()->count(),
            'total_amount_refunded' => MerchantPayoutRequest::byMerchant($merchant->id)
                ->where('status', 'completed')
                ->sum('refund_amount'),
            'total_amount_pending' => MerchantPayoutRequest::byMerchant($merchant->id)
                ->pending()
                ->sum('refund_amount')
        ];
    }
}