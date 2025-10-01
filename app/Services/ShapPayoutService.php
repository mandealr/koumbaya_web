<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ShapPayoutService
{
    protected $baseUrl;
    protected $apiId;
    protected $apiSecret;
    protected $accessToken;
    protected $tokenExpiresAt;

    public function __construct()
    {
        // Déterminer l'environnement (LAB ou PROD)
        $this->baseUrl = config('app.env') === 'production' 
            ? 'https://staging.billing-easy.net/shap/api/v1/merchant/'
            : 'https://test.billing-easy.net/shap/api/v1/merchant/';
            
        $this->apiId = config('services.shap.api_id');
        $this->apiSecret = config('services.shap.api_secret');
    }

    /**
     * Authentification OAuth2 et récupération du token d'accès
     */
    public function authenticate(): bool
    {
        try {
            // Vérifier si le token est encore valide
            if ($this->isTokenValid()) {
                return true;
            }

            Log::info('Authenticating with SHAP API', [
                'api_id' => $this->apiId,
                'base_url' => $this->baseUrl
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . 'auth', [
                    'api_id' => $this->apiId,
                    'api_secret' => $this->apiSecret
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                $this->tokenExpiresAt = Carbon::now()->addSeconds($data['expires_in'] - 60); // 60s de marge
                
                // Mettre en cache le token
                Cache::put('shap_access_token', $this->accessToken, $this->tokenExpiresAt);
                Cache::put('shap_token_expires_at', $this->tokenExpiresAt, $this->tokenExpiresAt);

                Log::info('SHAP authentication successful', [
                    'token_type' => $data['token_type'],
                    'expires_in' => $data['expires_in']
                ]);

                return true;
            } else {
                Log::error('SHAP authentication failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('SHAP authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Vérifier si le token d'accès est encore valide
     */
    protected function isTokenValid(): bool
    {
        $cachedToken = Cache::get('shap_access_token');
        $expiresAt = Cache::get('shap_token_expires_at');

        if ($cachedToken && $expiresAt && Carbon::now()->lt($expiresAt)) {
            $this->accessToken = $cachedToken;
            $this->tokenExpiresAt = $expiresAt;
            return true;
        }

        return false;
    }

    /**
     * Récupérer le solde par opérateur
     */
    public function getBalance(): array
    {
        if (!$this->authenticate()) {
            throw new \Exception('Failed to authenticate with SHAP API');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . 'balance');

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('SHAP balance retrieved successfully', [
                    'operators_count' => count($data['data'])
                ]);

                return $data['data'];
            } else {
                $error = $response->json();
                Log::error('Failed to get SHAP balance', [
                    'status' => $response->status(),
                    'error' => $error
                ]);
                
                throw new \Exception('Failed to get balance: ' . ($error['error_description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('SHAP balance error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Créer un payout via SHAP API
     */
    public function createPayout(string $paymentSystemName, string $payeeMsisdn, float $amount, string $externalReference, string $payoutType = 'refund'): array
    {
        if (!$this->authenticate()) {
            throw new \Exception('Failed to authenticate with SHAP API');
        }

        try {
            // Vérifier le solde avant le payout
            $balance = $this->getOperatorBalance($paymentSystemName);
            if ($balance < $amount) {
                throw new \Exception("Insufficient balance for {$paymentSystemName}. Available: {$balance} XAF, Required: {$amount} XAF");
            }

            Log::info('Creating SHAP payout', [
                'payment_system' => $paymentSystemName,
                'msisdn' => $payeeMsisdn,
                'amount' => $amount,
                'external_reference' => $externalReference,
                'payout_type' => $payoutType
            ]);

            $payoutData = [
                'payee_msisdn' => $payeeMsisdn,
                'amount' => $amount,
                'external_reference' => $externalReference,
                'payout_type' => $payoutType
            ];

            $response = Http::timeout(60) // Timeout plus long pour les payouts
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . 'payout', [
                    'payment_system_name' => $paymentSystemName,
                    'payout' => json_encode($payoutData)
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('SHAP payout created successfully', [
                    'payout_id' => $data['response']['payout_id'],
                    'transaction_id' => $data['response']['transaction_id'],
                    'state' => $data['response']['state']
                ]);

                return $data['response'];
            } else {
                $error = $response->json();
                Log::error('Failed to create SHAP payout', [
                    'status' => $response->status(),
                    'error' => $error,
                    'request_data' => [
                        'payment_system_name' => $paymentSystemName,
                        'payout_data' => $payoutData
                    ]
                ]);
                
                throw new \Exception('Failed to create payout: ' . ($error['error_description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('SHAP payout error', [
                'error' => $e->getMessage(),
                'payment_system' => $paymentSystemName,
                'msisdn' => $payeeMsisdn,
                'amount' => $amount
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les détails d'un payout
     */
    public function getPayout(string $payeeMsisdn, string $externalReference): array
    {
        if (!$this->authenticate()) {
            throw new \Exception('Failed to authenticate with SHAP API');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . 'payout', [
                    'payee_msisdn' => $payeeMsisdn,
                    'external_reference' => $externalReference
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['payout'];
            } else {
                $error = $response->json();
                Log::error('Failed to get SHAP payout', [
                    'status' => $response->status(),
                    'error' => $error
                ]);
                
                throw new \Exception('Failed to get payout: ' . ($error['error_description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('SHAP get payout error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer le solde d'un opérateur spécifique
     */
    public function getOperatorBalance(string $paymentSystemName): float
    {
        $balances = $this->getBalance();
        
        foreach ($balances as $balance) {
            if ($balance['payment_system_name'] === $paymentSystemName) {
                return (float) $balance['amount'];
            }
        }
        
        throw new \Exception("Operator {$paymentSystemName} not found in balance data");
    }

    /**
     * Mapper les opérateurs Mobile Money locaux vers les noms SHAP
     */
    public function mapOperatorName(string $localOperator): string
    {
        $mapping = [
            'airtel_money' => 'airtelmoney',
            'moov_money' => 'moovmoney4',
            // Ajouter d'autres mappings si nécessaire
        ];

        return $mapping[$localOperator] ?? $localOperator;
    }

    /**
     * Déterminer l'opérateur basé sur le numéro de téléphone
     */
    public function detectOperatorFromPhone(string $phone): string
    {
        // Supprimer les espaces et les caractères spéciaux
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Préfixes Airtel Money
        $airtelPrefixes = ['074', '077', '076'];
        
        // Préfixes Moov Money
        $moovPrefixes = ['065', '062', '066', '060'];
        
        // Vérifier les 3 premiers chiffres
        $prefix = substr($cleanPhone, 0, 3);
        
        if (in_array($prefix, $airtelPrefixes)) {
            return 'airtelmoney';
        } elseif (in_array($prefix, $moovPrefixes)) {
            return 'moovmoney4';
        }
        
        // Par défaut, utiliser Airtel Money
        Log::warning('Could not detect operator from phone number, using default', [
            'phone' => $phone,
            'prefix' => $prefix
        ]);
        
        return 'airtelmoney';
    }

    /**
     * Valider les données avant l'envoi du payout
     */
    public function validatePayoutData(string $payeeMsisdn, float $amount): array
    {
        $errors = [];

        // Valider le numéro de téléphone
        $cleanPhone = preg_replace('/[^0-9]/', '', $payeeMsisdn);
        if (strlen($cleanPhone) < 8 || strlen($cleanPhone) > 15) {
            $errors[] = 'Invalid phone number format';
        }

        // Valider le montant (minimum 100 XAF selon la doc SHAP)
        if ($amount < 100) {
            $errors[] = 'Amount cannot be less than 100 XAF';
        }

        if ($amount > 1000000) { // Limite de sécurité
            $errors[] = 'Amount cannot exceed 1,000,000 XAF';
        }

        return $errors;
    }
}