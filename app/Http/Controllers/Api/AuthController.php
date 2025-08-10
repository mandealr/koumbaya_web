<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\UserLoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
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
     *             @OA\Property(property="role", type="string", enum={"MERCHANT","RESELLER","PARTNER"}, example="MERCHANT")
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
            'role' => 'required|in:MERCHANT,RESELLER,PARTNER',
            'otp_code' => 'nullable|string|size:6',
            'skip_otp' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Vérification OTP si requis (sauf en dev mode avec skip_otp)
        if (!$request->skip_otp || config('app.env') !== 'local') {
            if (!$request->otp_code) {
                // Envoyer OTP par email
                $otpResult = OtpService::sendEmailOtp($request->email, Otp::PURPOSE_REGISTRATION);
                
                if ($otpResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vérification OTP requise',
                        'otp_required' => true,
                        'identifier' => $request->email,
                        'masked_identifier' => $otpResult['masked_identifier'],
                        'expires_at' => $otpResult['expires_at']
                    ], 400);
                }
                
                return $this->sendError('Erreur lors de l\'envoi du code de vérification');
            }

            // Vérifier le code OTP
            $otpVerification = OtpService::verifyOtp($request->email, $request->otp_code, Otp::PURPOSE_REGISTRATION);
            
            if (!$otpVerification['success']) {
                return $this->sendError($otpVerification['message'], [], 400);
            }
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
            'source_ip_address' => $request->ip(),
            'source_server_info' => $request->userAgent(),
        ]);

        // Créer le portefeuille utilisateur
        UserWallet::create([
            'user_id' => $user->id,
            'balance' => 0.00,
            'currency' => 'XAF',
        ]);

        $token = $user->createAuthToken('registration-token');

        $this->logUserLogin($user, $request->ip(), 'success');

        // Charger les relations nécessaires
        $user->load(['wallet', 'roles']);

        return $this->sendResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 'Utilisateur créé avec succès', 201);
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
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Tentative d'authentification avec le guard web
        if (!Auth::guard('web')->attempt($credentials)) {
            $this->logFailedLogin($request->email, $request->ip());
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        $user = User::with('roles', 'wallet')->find(Auth::guard('web')->user()->id);

        // Vérifier si l'utilisateur est actif
        if (!$user->is_active) {
            return response()->json(['error' => 'Compte désactivé'], 403);
        }

        // Mettre à jour la dernière connexion
        $user->last_login_date = now();
        $user->source_ip_address = $request->ip();
        $user->save();

        // Créer un token d'accès
        $token = $user->createAuthToken('login-token');

        $this->logUserLogin($user, $request->ip(), 'success');

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
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
                        'email_verified_at' => now(),
                        $provider . '_id' => $socialUser->getId(),
                        'avatar_url' => $socialUser->getAvatar(),
                        'account_type' => 'personal',
                        'can_sell' => false,
                        'can_buy' => true,
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
}
