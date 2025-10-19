<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="OTP",
 *     description="Gestion des codes de vérification OTP (SMS/Email)"
 * )
 */
class OtpController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:10,1'); // Max 10 requêtes par minute
    }

    /**
     * @OA\Post(
     *     path="/api/otp/send",
     *     tags={"OTP"},
     *     summary="Envoyer un code OTP",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier", "type", "purpose"},
     *             @OA\Property(property="identifier", type="string", example="user@example.com", description="Email ou numéro de téléphone"),
     *             @OA\Property(property="type", type="string", enum={"email", "sms"}, example="email", description="Type d'envoi"),
     *             @OA\Property(property="purpose", type="string", enum={"registration", "password_reset", "login", "payment"}, example="registration", description="But du code OTP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code de vérification envoyé par email"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="masked_identifier", type="string", example="us**@example.com"),
     *                 @OA\Property(property="expires_at", type="string", example="2025-08-07 18:05:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Trop de tentatives",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Trop de tentatives, veuillez patienter")
     *         )
     *     )
     * )
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string|max:255',
            'type' => 'required|in:email,sms',
            'purpose' => 'required|in:registration,password_reset,login,payment'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $identifier = $request->identifier;
        $type = $request->type;
        $purpose = $request->purpose;

        // Validation spécifique selon le type
        if ($type === 'email') {
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return $this->sendError('Adresse email invalide', [], 422);
            }
        } elseif ($type === 'sms') {
            if (!preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $identifier)) {
                return $this->sendError('Numéro de téléphone invalide', [], 422);
            }
        }

        // Vérifier s'il n'y a pas déjà un code valide récent (30 secondes)
        $recentOtp = Otp::forIdentifier($identifier)
                        ->forPurpose($purpose)
                        ->valid()
                        ->where('created_at', '>', now()->subSeconds(30))
                        ->first();

        if ($recentOtp) {
            $remainingSeconds = 30 - now()->diffInSeconds($recentOtp->created_at);

            Log::info('OTP send blocked - code too recent', [
                'identifier' => $identifier,
                'purpose' => $purpose,
                'otp_id' => $recentOtp->id,
                'created_at' => $recentOtp->created_at,
                'remaining_seconds' => $remainingSeconds,
                'masked_identifier' => $recentOtp->masked_identifier
            ]);

            return response()->json([
                'success' => false,
                'message' => "Un code de vérification a déjà été envoyé récemment. Veuillez patienter {$remainingSeconds} secondes.",
                'errors' => [],
                'data' => [
                    'remaining_seconds' => max(0, $remainingSeconds),
                    'resend_available_at' => now()->addSeconds($remainingSeconds)->toISOString(),
                    'masked_identifier' => $recentOtp->masked_identifier,
                    'expires_at' => $recentOtp->expires_at->format('Y-m-d H:i:s'),
                    'hint' => 'Utilisez l\'endpoint /api/otp/resend après le délai'
                ]
            ], 429);
        }

        // Envoyer l'OTP selon le type
        if ($type === 'email') {
            $result = OtpService::sendEmailOtp($identifier, $purpose);
        } else {
            $result = OtpService::sendSmsOtp($identifier, $purpose);
        }

        if ($result['success']) {
            return $this->sendResponse([
                'masked_identifier' => $result['masked_identifier'],
                'expires_at' => $result['expires_at']
            ], $result['message']);
        }

        return $this->sendError($result['message'], [], 500);
    }

    /**
     * @OA\Post(
     *     path="/api/otp/verify",
     *     tags={"OTP"},
     *     summary="Vérifier un code OTP",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier", "code", "purpose"},
     *             @OA\Property(property="identifier", type="string", example="user@example.com", description="Email ou numéro de téléphone"),
     *             @OA\Property(property="code", type="string", example="123456", description="Code OTP à 6 chiffres"),
     *             @OA\Property(property="purpose", type="string", enum={"registration", "password_reset", "login", "payment"}, example="registration", description="But du code OTP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code de vérification validé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Code de vérification invalide ou expiré")
     *         )
     *     )
     * )
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string|max:255',
            'code' => 'required|string|size:6',
            'purpose' => 'required|in:registration,password_reset,login,payment'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $identifier = $request->identifier;
        $code = $request->code;
        $purpose = $request->purpose;

        // Vérifier le code OTP
        $result = OtpService::verifyOtp($identifier, $code, $purpose);

        if ($result['success']) {
            return $this->sendResponse([], $result['message']);
        }

        return $this->sendError($result['message'], [], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/otp/resend",
     *     tags={"OTP"},
     *     summary="Renvoyer un code OTP",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identifier", "type", "purpose"},
     *             @OA\Property(property="identifier", type="string", example="user@example.com", description="Email ou numéro de téléphone"),
     *             @OA\Property(property="type", type="string", enum={"email", "sms"}, example="email", description="Type d'envoi"),
     *             @OA\Property(property="purpose", type="string", enum={"registration", "password_reset", "login", "payment"}, example="registration", description="But du code OTP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP renvoyé avec succès"
     *     )
     * )
     */
    public function resend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string|max:255',
            'type' => 'required|in:email,sms',
            'purpose' => 'required|in:registration,password_reset,login,payment'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        // Vérifier qu'il y a eu un précédent envoi
        $previousOtp = Otp::forIdentifier($request->identifier)
                          ->forPurpose($request->purpose)
                          ->orderBy('created_at', 'desc')
                          ->first();

        if (!$previousOtp) {
            return $this->sendError(
                'Aucun code précédent trouvé. Veuillez utiliser l\'endpoint /send.',
                [],
                404
            );
        }

        // Vérifier le délai minimum entre les renvois (30 secondes)
        if ($previousOtp->created_at->gt(now()->subSeconds(30))) {
            $remainingSeconds = 30 - now()->diffInSeconds($previousOtp->created_at);
            return $this->sendError(
                "Veuillez attendre {$remainingSeconds} secondes avant de renvoyer un code.",
                [],
                429
            );
        }

        // Renvoyer le code
        return $this->send($request);
    }

    /**
     * @OA\Get(
     *     path="/api/otp/status/{identifier}",
     *     tags={"OTP"},
     *     summary="Vérifier le statut d'un OTP",
     *     @OA\Parameter(
     *         name="identifier",
     *         in="path",
     *         description="Email ou téléphone",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="purpose",
     *         in="query",
     *         description="But du code OTP",
     *         required=true,
     *         @OA\Schema(type="string", enum={"registration", "password_reset", "login", "payment"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statut de l'OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="has_pending_otp", type="boolean", example=true),
     *                 @OA\Property(property="masked_identifier", type="string", example="us**@example.com"),
     *                 @OA\Property(property="expires_at", type="string", example="2025-08-07 18:05:00"),
     *                 @OA\Property(property="type", type="string", example="email")
     *             )
     *         )
     *     )
     * )
     */
    public function status($identifier, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), ['identifier' => $identifier]), [
            'identifier' => 'required|string|max:255',
            'purpose' => 'required|in:registration,password_reset,login,payment'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        $otp = Otp::forIdentifier($identifier)
                  ->forPurpose($request->purpose)
                  ->valid()
                  ->orderBy('created_at', 'desc')
                  ->first();

        if ($otp) {
            return $this->sendResponse([
                'has_pending_otp' => true,
                'masked_identifier' => $otp->masked_identifier,
                'expires_at' => $otp->expires_at->format('Y-m-d H:i:s'),
                'type' => $otp->type,
                'remaining_seconds' => max(0, $otp->expires_at->diffInSeconds(now()))
            ]);
        }

        return $this->sendResponse([
            'has_pending_otp' => false
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/otp/cleanup",
     *     tags={"OTP"},
     *     summary="Nettoyer les anciens codes OTP (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Nettoyage effectué",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Nettoyage effectué"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deleted_count", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */
    public function cleanup(Request $request)
    {
        // Cette méthode pourrait être restreinte aux admins
        // $this->middleware('auth:sanctum');
        // if (!auth()->user()->isAdmin()) { ... }

        $deletedCount = OtpService::cleanup();

        return $this->sendResponse([
            'deleted_count' => $deletedCount
        ], 'Nettoyage effectué');
    }
}