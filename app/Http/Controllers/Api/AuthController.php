<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\OtpController;
use App\Mail\VerificationEmail;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\UserLoginHistory;
use App\Models\Role;
use App\Models\AuditLog;
use App\Services\OtpService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints pour l'authentification utilisateur"
 * )
 */
class AuthController extends Controller
{
    use ApiResponses;
    // Les middlewares sont maintenant définis dans les routes (Laravel 11+)

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     tags={"Authentication"},
     *     summary="Inscription d'un nouvel utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","phone","password","role"},
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="last_name", type="string", example="Dupont"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="phone", type="string", example="+24177123456"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"Particulier","Business"}, example="Particulier")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur créé avec succès"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|same:password',
            'role' => 'nullable|in:Particulier,Business,customer,merchant,admin',
            'company_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|integer|exists:countries,id',
            'language_id' => 'nullable|integer|exists:languages,id',
            'otp_code' => 'nullable|string|size:6',
            'skip_otp' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            AuditLog::logAuth('auth.register.validation_failed', null, [
                'email' => $request->email,
                'errors' => $validator->errors()->toArray()
            ]);
            return $this->validationErrorResponse($validator);
        }

        // TODO: Réimplémenter la vérification OTP si nécessaire
        // Pour le moment, on skip l'OTP pour permettre l'inscription directe

        // Mapper le rôle frontend vers user_type_id
        $userTypeId = 2; // Client par défaut
        if ($request->role === 'Business') {
            $userTypeId = 1; // Marchand
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type_id' => $userTypeId,
            'company_name' => $request->company_name,
            'city' => $request->city,
            'address' => $request->address,
            'country_id' => $request->country_id,
            'language_id' => $request->language_id,
            'is_active' => true,
            'source_ip_address' => $request->ip(),
            'source_server_info' => $request->userAgent(),
        ]);

        // Assigner les rôles selon la logique des seeders
        $this->assignUserRoles($user, $request);

        // Déclencher l'événement d'inscription
        \App\Events\UserRegistered::dispatch($user);

        // Créer le portefeuille utilisateur
        UserWallet::create([
            'user_id' => $user->id,
            'balance' => 0.00,
            'currency' => 'XAF',
        ]);

        $token = $user->createAuthToken('registration-token');

        $this->logUserLogin($user, $request->ip(), 'success');

        // Déterminer le type de vérification selon le client
        $userAgent = $request->header('User-Agent', '');
        $platform = $request->header('X-Platform', 'web');
        $isFlutterApp = str_contains($userAgent, 'Dart/') || 
                       str_contains($userAgent, 'KoumbayaFlutter') || 
                       $platform === 'mobile' || 
                       $request->has('is_mobile_app');

        if ($isFlutterApp) {
            // Mobile: Envoyer OTP
            try {
                $otpResult = OtpService::sendEmailOtp($user->email, 'registration');
                if (!$otpResult['success']) {
                    \Log::warning('Échec envoi OTP après inscription', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $otpResult['message']
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur envoi OTP après inscription', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }

            $message = 'Utilisateur créé avec succès. Un code de vérification a été envoyé à votre email.';
            $responseData = [
                'requires_verification' => true,
                'verification_sent_to' => $this->maskEmail($user->email),
                'verification_type' => 'otp'
            ];
        } else {
            // Web: Envoyer email avec lien de vérification
            try {
                $this->sendVerificationEmail($user);
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email de vérification', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }

            $message = 'Utilisateur créé avec succès. Un email de vérification a été envoyé à votre adresse.';
            $responseData = [
                'requires_verification' => true,
                'verification_sent_to' => $this->maskEmail($user->email),
                'verification_type' => 'email_link'
            ];
        }

        // Charger les relations nécessaires
        $user->load(['wallet', 'roles']);

        // Log succès
        AuditLog::logAuth('auth.register.success', $user, [
            'role' => $request->role,
            'verification_type' => $isFlutterApp ? 'otp' : 'email_link'
        ]);

        return $this->successResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ] + $responseData, $message, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     summary="Connexion d'un utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants invalides")
     * )
     */
    public function login(Request $request)
    {
        // Déterminer si on utilise email ou téléphone
        $loginField = $request->has('email') ? 'email' : 'phone';
        $identifier = $request->input($loginField);

        $rules = [
            'password' => 'required|string|min:6',
        ];

        if ($loginField === 'email') {
            $rules['email'] = 'required|email';
        } else {
            $rules['phone'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            AuditLog::logAuth('auth.login.validation_failed', null, [
                'identifier' => $identifier,
                'login_field' => $loginField
            ]);
            return $this->validationErrorResponse($validator);
        }

        // Préparer les credentials pour l'authentification
        $credentials = [
            $loginField => $identifier,
            'password' => $request->password
        ];

        // Tentative d'authentification avec le guard web
        if (!Auth::guard('web')->attempt($credentials)) {
            $this->logFailedLogin($identifier, $request->ip());
            AuditLog::logAuth('auth.login.failed', null, [
                'identifier' => $identifier,
                'login_field' => $loginField
            ]);
            return $this->errorResponse('Identifiants invalides', 401);
        }

        $user = User::with('roles', 'wallet')->find(Auth::guard('web')->user()->id);

        // Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            AuditLog::logAuth('auth.login.account_disabled', $user);
            return $this->errorResponse('Compte désactivé', 403);
        }

        // Mettre à jour la dernière connexion
        $user->last_login_date = now();
        $user->source_ip_address = $request->ip();
        $user->save();

        // Créer un token d'accès
        $token = $user->createAuthToken('login-token');

        $this->logUserLogin($user, $request->ip(), 'success');
        
        // Log succès
        AuditLog::logAuth('auth.login.success', $user, [
            'login_field' => $loginField
        ]);

        return $this->successResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 'Connexion réussie');
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     tags={"Authentication"},
     *     summary="Récupérer les informations de l'utilisateur connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['wallet', 'userType', 'roles']);

        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/verify-account",
     *     tags={"Authentication"},
     *     summary="Vérifier un compte utilisateur avec OTP",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","otp_code"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="otp_code", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte vérifié avec succès"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Code invalide"),
     *     @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    public function verifyAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            AuditLog::logAuth('auth.verify_account.validation_failed', auth()->user(), [
                'email' => $request->email,
                'errors' => $validator->errors()->toArray()
            ]);
            return $this->validationErrorResponse($validator);
        }

        // Vérifier que l'utilisateur existe
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            AuditLog::logAuth('auth.verify_account.user_not_found', null, [
                'email' => $request->email
            ]);
            return $this->errorResponse('Utilisateur non trouvé', 404);
        }

        // Vérifier le code OTP
        $result = OtpService::verifyOtp($request->email, $request->otp_code, 'registration');
        
        if (!$result['success']) {
            AuditLog::logAuth('auth.verify_account.otp_failed', $user, [
                'email' => $request->email,
                'error' => $result['message']
            ]);
            return $this->errorResponse($result['message'], 400);
        }

        // Marquer le compte comme vérifié
        $user->verified_at = now();
        $user->save();

        // Charger les relations
        $user->load(['wallet', 'roles']);

        // Log succès
        AuditLog::logAuth('auth.verify_account.success', $user, [
            'email' => $request->email
        ]);

        return $this->successResponse([
            'user' => $user
        ], 'Compte vérifié avec succès');
    }

    /**
     * Masquer une adresse email pour la sécurité
     */
    private function maskEmail($email)
    {
        $parts = explode('@', $email);
        if (count($parts) != 2) return $email;
        
        $localPart = $parts[0];
        $domain = $parts[1];
        
        if (strlen($localPart) <= 2) {
            return str_repeat('*', strlen($localPart)) . '@' . $domain;
        }
        
        $maskedLocal = substr($localPart, 0, 2) . str_repeat('*', strlen($localPart) - 2);
        return $maskedLocal . '@' . $domain;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Authentication"},
     *     summary="Déconnexion de l'utilisateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        // Supprimer le token actuel
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Rafraîchir le token JWT",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        // Avec Sanctum, on peut créer un nouveau token et supprimer l'ancien
        $user = $request->user();

        // Supprimer le token actuel
        $request->user()->currentAccessToken()->delete();

        // Créer un nouveau token
        $newToken = $user->createAuthToken('refresh-token');

        return response()->json([
            'message' => 'Token rafraîchi avec succès',
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Enregistrer une connexion réussie
     */
    private function logUserLogin($user, $ipAddress, $state)
    {
        UserLoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'state' => $state,
        ]);
    }

    /**
     * Enregistrer une tentative de connexion échouée
     */
    private function logFailedLogin($email, $ipAddress)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            UserLoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'state' => 'failed',
            ]);
        }
    }

    /**
     * Redirect to social provider
     */
    public function redirectToProvider($provider)
    {
        $validProviders = ['facebook', 'google', 'apple'];
        
        if (!in_array($provider, $validProviders)) {
            return response()->json(['error' => 'Provider not supported'], 400);
        }

        try {
            $redirect_url = Socialite::driver($provider)->redirect()->getTargetUrl();
            return response()->json(['redirect_url' => $redirect_url]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to redirect to provider'], 500);
        }
    }

    /**
     * Handle social provider callback
     */
    public function handleProviderCallback($provider, Request $request)
    {
        $validProviders = ['facebook', 'google', 'apple'];
        
        if (!in_array($provider, $validProviders)) {
            return response()->json(['error' => 'Provider not supported'], 400);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Check if user exists with this social ID
            $user = User::where($provider . '_id', $socialUser->getId())->first();
            
            if (!$user) {
                // Check if user exists with same email
                $user = User::where('email', $socialUser->getEmail())->first();
                
                if ($user) {
                    // Link social account to existing user
                    $user->{$provider . '_id'} = $socialUser->getId();
                    $user->save();
                } else {
                    // Create new user
                    $names = $this->parseName($socialUser->getName());
                    
                    $user = User::create([
                        'first_name' => $names['first_name'],
                        'last_name' => $names['last_name'],
                        'email' => $socialUser->getEmail(),
                        'verified_at' => now(),
                        $provider . '_id' => $socialUser->getId(),
                        'avatar_url' => $socialUser->getAvatar(),
                        'user_type_id' => 2, // Client par défaut
                        'is_active' => true,
                        'source_ip_address' => $request->ip(),
                        'source_server_info' => $request->userAgent(),
                        'password' => Hash::make(Str::random(16)), // Random password
                    ]);

                    // Create user wallet
                    UserWallet::create([
                        'user_id' => $user->id,
                        'balance' => 0.00,
                        'currency' => 'XAF',
                    ]);
                }
            }

            // Generate auth token
            $token = $user->createAuthToken('social-login-token');
            
            $this->logUserLogin($user, $request->ip(), 'success');

            return response()->json([
                'message' => 'Connexion réussie',
                'user' => $user->load('wallet'),
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier le compte via URL (Web)
     */
    public function verifyAccountByUrl($token)
    {
        // Décoder le token de vérification
        try {
            $data = json_decode(base64_decode($token), true);
            if (!$data || !isset($data['email'], $data['expires_at'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lien de vérification invalide'
                ], 400);
            }

            // Vérifier l'expiration (24h)
            if (now()->isAfter($data['expires_at'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lien de vérification expiré'
                ], 400);
            }

            // Trouver et vérifier l'utilisateur
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Si déjà vérifié
            if ($user->verified_at) {
                return response()->json([
                    'success' => true,
                    'message' => 'Compte déjà vérifié',
                    'already_verified' => true
                ]);
            }

            // Marquer comme vérifié
            $user->update(['verified_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Compte vérifié avec succès !',
                'user' => $user->load(['wallet', 'roles'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ], 500);
        }
    }


    /**
     * Envoyer l'email de vérification avec lien
     */
    private function sendVerificationEmail($user)
    {
        // Générer le token de vérification
        $verificationData = [
            'email' => $user->email,
            'expires_at' => now()->addHours(24)->toISOString()
        ];
        $verificationToken = base64_encode(json_encode($verificationData));
        
        // URL de vérification
        $verificationUrl = config('app.frontend_url') . '/verify-email?token=' . urlencode($verificationToken);
        
        // Envoyer l'email (implémentation simple pour le moment)
        \Log::info('Email de vérification généré', [
            'user_id' => $user->id,
            'email' => $user->email,
            'verification_url' => $verificationUrl,
            'expires_at' => $verificationData['expires_at']
        ]);
        
        try {
            // Envoyer l'email avec le nouveau template Mailable
            Mail::to($user->email, $user->first_name . ' ' . $user->last_name)
                ->send(new VerificationEmail($user, $verificationUrl));
            
            \Log::info('Email de vérification envoyé avec succès', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de l\'email de vérification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Ne pas faire échouer l'inscription si l'email ne peut pas être envoyé
        }
        
        return true;
    }

    /**
     * Renvoyer l'email de vérification
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun compte trouvé avec cette adresse email.'
            ], 404);
        }

        if ($user->verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte est déjà vérifié.'
            ], 400);
        }

        try {
            $this->sendVerificationEmail($user);

            return response()->json([
                'success' => true,
                'message' => 'Un nouvel email de vérification a été envoyé.',
                'verification_sent_to' => $user->email
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de l\'email de vérification:', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email de vérification.'
            ], 500);
        }
    }

    /**
     * Assigner les rôles selon la logique simplifiée :
     * - Particulier = client uniquement (achats, tombolas)
     * - Business = marchand uniquement (vente, gestion)
     */
    private function assignUserRoles(User $user, Request $request): void
    {
        // Système de rôles basé sur user_type_id :
        // - user_type_id = 2 (Client) → rôle 'Particulier' (achats, participation aux tombolas)
        // - user_type_id = 1 (Marchand) → rôle 'Business Individual' ou 'Business Enterprise'
        
        if ($user->user_type_id === 1) {
            // Pour les marchands, différencier selon s'ils ont une entreprise ou non
            $hasCompanyName = !empty($request->company_name) || !empty($request->business_name);
            
            if ($hasCompanyName) {
                // Si nom d'entreprise fourni → Business Enterprise (dashboard complet)
                $roleName = 'Business Enterprise';
            } else {
                // Sinon → Business Individual (simple dashboard)
                $roleName = 'Business Individual';
            }
        } else {
            // Client (par défaut) = rôle Particulier uniquement
            $roleName = 'Particulier';
        }

        // Log pour debug
        \Log::info('Attribution de rôle lors de l\'inscription', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type_id' => $user->user_type_id,
            'request_role' => $request->role,
            'role_name' => $roleName
        ]);

        // Assigner le rôle approprié
        $role = Role::where('name', $roleName)->first();
        if ($role && !$user->roles->contains($role->id)) {
            $user->roles()->attach($role->id);
        } else if (!$role) {
            \Log::error('Rôle non trouvé lors de l\'inscription', [
                'role_name' => $roleName,
                'user_id' => $user->id
            ]);
        }
    }


    /**
     * Parse full name into first and last name
     */
    private function parseName($fullName)
    {
        $names = explode(' ', trim($fullName));
        $firstName = $names[0];
        $lastName = count($names) > 1 ? implode(' ', array_slice($names, 1)) : '';

        return [
            'first_name' => $firstName ?: 'User',
            'last_name' => $lastName ?: 'Account'
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Réinitialiser le mot de passe",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier", "otp", "password", "password_confirmation"},
     *             @OA\Property(property="identifier", type="string", example="user@example.com"),
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erreur de validation ou code OTP invalide"),
     *     @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier le code OTP
            $otpController = new OtpController();
            $otpRequest = new Request([
                'identifier' => $request->identifier,
                'code' => $request->otp,
                'purpose' => 'password_reset'
            ]);

            $otpResponse = $otpController->verify($otpRequest);
            $otpData = $otpResponse->getData(true);

            if (!$otpData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $otpData['message'] ?? 'Code OTP invalide'
                ], 400);
            }

            // Trouver l'utilisateur par email ou téléphone
            $user = User::where('email', $request->identifier)
                       ->orWhere('phone', $request->identifier)
                       ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            // Réinitialiser le mot de passe
            $user->password = Hash::make($request->password);
            $user->save();

            // Log de l'activité
            Log::info('Password reset successful', [
                'user_id' => $user->id,
                'identifier' => $request->identifier,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset error', [
                'error' => $e->getMessage(),
                'identifier' => $request->identifier,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la réinitialisation'
            ], 500);
        }
    }
}
