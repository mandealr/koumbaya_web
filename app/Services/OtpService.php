<?php

namespace App\Services;

use App\Models\Otp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Générer et envoyer un OTP par email
     */
    public static function sendEmailOtp($email, $purpose = Otp::PURPOSE_REGISTRATION, $expirationMinutes = 30)
    {
        try {
            // Vérifier que l'email est valide
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Tentative d\'envoi OTP avec email invalide', [
                    'email' => $email,
                    'purpose' => $purpose
                ]);

                return [
                    'success' => false,
                    'message' => 'Adresse email invalide'
                ];
            }

            // Pour la réinitialisation de mot de passe, vérifier que l'utilisateur existe
            if ($purpose === Otp::PURPOSE_PASSWORD_RESET || $purpose === 'password_reset') {
                $user = \App\Models\User::where('email', $email)->first();

                if (!$user) {
                    Log::warning('Tentative de réinitialisation pour email inexistant', [
                        'email' => $email
                    ]);

                    // Pour la sécurité, on retourne un message générique
                    // (ne pas révéler si l'email existe ou non)
                    return [
                        'success' => true,
                        'message' => 'Si cette adresse email existe, un code de vérification a été envoyé',
                        'masked_identifier' => self::maskEmail($email),
                        'expires_at' => now()->addMinutes($expirationMinutes)->format('Y-m-d H:i:s')
                    ];
                }

                // Log pour les utilisateurs OAuth
                $isOAuthUser = !empty($user->google_id) || !empty($user->facebook_id) || !empty($user->apple_id);
                if ($isOAuthUser) {
                    Log::info('Réinitialisation de mot de passe pour utilisateur OAuth', [
                        'email' => $email,
                        'user_id' => $user->id,
                        'has_google_id' => !empty($user->google_id),
                        'has_facebook_id' => !empty($user->facebook_id),
                        'has_apple_id' => !empty($user->apple_id)
                    ]);
                }
            }

            // Générer le code OTP
            $otp = Otp::generate($email, Otp::TYPE_EMAIL, $purpose, $expirationMinutes);

            // Envoyer l'email
            $sent = self::sendEmailCode($email, $otp->code, $purpose);

            if ($sent) {
                Log::info('OTP Email envoyé avec succès', [
                    'email' => $email,
                    'purpose' => $purpose,
                    'code' => $otp->code, // À retirer en production
                    'identifier_stored' => $otp->identifier,
                    'expires_at' => $otp->expires_at,
                    'otp_id' => $otp->id
                ]);

                return [
                    'success' => true,
                    'message' => 'Code de vérification envoyé par email',
                    'masked_identifier' => $otp->masked_identifier,
                    'expires_at' => $otp->expires_at->format('Y-m-d H:i:s')
                ];
            }

            Log::error('Échec envoi email OTP - sendEmailCode a retourné false', [
                'email' => $email,
                'purpose' => $purpose,
                'otp_id' => $otp->id
            ]);

            return [
                'success' => false,
                'message' => 'Impossible d\'envoyer l\'email. Veuillez réessayer dans quelques instants.'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur exception lors de l\'envoi OTP email', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi du code. Veuillez contacter le support si le problème persiste.'
            ];
        }
    }

    /**
     * Masquer une adresse email pour la sécurité
     */
    private static function maskEmail($email)
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
     * Générer et envoyer un OTP par SMS
     */
    public static function sendSmsOtp($phone, $purpose = Otp::PURPOSE_REGISTRATION, $expirationMinutes = 30)
    {
        try {
            // Nettoyer et valider le numéro de téléphone
            $cleanPhone = self::cleanPhoneNumber($phone);

            if (!$cleanPhone) {
                return [
                    'success' => false,
                    'message' => 'Numéro de téléphone invalide'
                ];
            }

            // Générer le code OTP
            $otp = Otp::generate($cleanPhone, Otp::TYPE_SMS, $purpose, $expirationMinutes);

            // Envoyer le SMS
            $sent = self::sendSmsCode($cleanPhone, $otp->code, $purpose);

            if ($sent) {
                Log::info('OTP SMS envoyé avec succès', [
                    'phone' => $cleanPhone,
                    'purpose' => $purpose,
                    'code' => $otp->code // À retirer en production
                ]);

                return [
                    'success' => true,
                    'message' => 'Code de vérification envoyé par SMS',
                    'masked_identifier' => $otp->masked_identifier,
                    'expires_at' => $otp->expires_at->format('Y-m-d H:i:s')
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur envoi OTP SMS', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code de vérification'
            ];
        }
    }

    /**
     * Vérifier un code OTP
     */
    public static function verifyOtp($identifier, $code, $purpose)
    {
        try {
            $originalIdentifier = $identifier;

            // Nettoyer l'identifiant si c'est un téléphone
            if (self::isPhoneNumber($identifier)) {
                $identifier = self::cleanPhoneNumber($identifier);
            }

            // Debug: vérifier les codes existants pour cet identifiant
            $existingOtps = Otp::where('identifier', $identifier)
                ->where('purpose', $purpose)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Tentative de vérification OTP', [
                'original_identifier' => $originalIdentifier,
                'cleaned_identifier' => $identifier,
                'code' => $code,
                'purpose' => $purpose,
                'existing_otps_count' => $existingOtps->count(),
                'existing_otps' => $existingOtps->map(function ($otp) {
                    return [
                        'id' => $otp->id,
                        'code' => $otp->code,
                        'is_used' => $otp->is_used,
                        'expires_at' => $otp->expires_at,
                        'is_expired' => $otp->isExpired(),
                        'created_at' => $otp->created_at
                    ];
                })
            ]);

            // Vérifier si le code existe et analyser le problème spécifique
            $otp = Otp::where('identifier', $identifier)
                ->where('code', $code)
                ->where('purpose', $purpose)
                ->first();

            if (!$otp) {
                Log::warning('OTP invalide', [
                    'identifier' => $identifier,
                    'code' => $code,
                    'purpose' => $purpose,
                    'debug_info' => 'Code inexistant'
                ]);

                return [
                    'success' => false,
                    'message' => 'Code de vérification incorrect'
                ];
            }

            if ($otp->is_used) {
                Log::warning('OTP déjà utilisé', [
                    'identifier' => $identifier,
                    'code' => $code,
                    'purpose' => $purpose,
                    'debug_info' => 'Code déjà utilisé'
                ]);

                return [
                    'success' => false,
                    'message' => 'Ce code a déjà été utilisé'
                ];
            }

            if ($otp->isExpired()) {
                Log::warning('OTP expiré', [
                    'identifier' => $identifier,
                    'code' => $code,
                    'purpose' => $purpose,
                    'expires_at' => $otp->expires_at,
                    'debug_info' => 'Code expiré'
                ]);

                return [
                    'success' => false,
                    'message' => 'Code expiré. Demandez un nouveau code de vérification.'
                ];
            }

            // Le code est valide, le marquer comme utilisé
            $otp->is_used = true;
            $otp->save();

            Log::info('OTP validé avec succès', [
                'identifier' => $identifier,
                'purpose' => $purpose
            ]);

            return [
                'success' => true,
                'message' => 'Code de vérification validé avec succès'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur validation OTP', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la validation du code'
            ];
        }
    }

    /**
     * Envoyer le code par email
     */
    private static function sendEmailCode($email, $code, $purpose)
    {
        $subject = self::getEmailSubject($purpose);

        try {
            // Log pour debug (mais continue l'envoi réel)
            Log::info('OTP Email envoi en cours', [
                'to' => $email,
                'subject' => $subject,
                'code' => $code,
                'env' => config('app.env'),
                'mail_mailer' => config('mail.default'),
                'purpose' => $purpose
            ]);

            // Utiliser la classe Mailable pour un envoi plus fiable
            if ($purpose === 'password_reset') {
                // Trouver l'utilisateur ou créer un objet fictif pour le template
                $user = \App\Models\User::where('email', $email)->first();

                if (!$user) {
                    Log::warning('Tentative de réinitialisation pour email inexistant', [
                        'email' => $email
                    ]);
                    // Créer un objet utilisateur fictif pour le template
                    $user = (object) [
                        'first_name' => 'Utilisateur',
                        'last_name' => ''
                    ];
                } else {
                    // Vérifier si c'est un utilisateur OAuth sans mot de passe défini
                    $isOAuthUser = !empty($user->google_id) || !empty($user->facebook_id) || !empty($user->apple_id);

                    Log::info('Envoi de code de réinitialisation', [
                        'email' => $email,
                        'user_id' => $user->id,
                        'is_oauth_user' => $isOAuthUser,
                        'has_google_id' => !empty($user->google_id),
                        'has_facebook_id' => !empty($user->facebook_id),
                        'has_apple_id' => !empty($user->apple_id),
                        'verified_at' => $user->verified_at ? true : false
                    ]);

                    // S'assurer que first_name et last_name sont définis
                    if (empty($user->first_name)) {
                        $user->first_name = 'Utilisateur';
                    }
                    if (empty($user->last_name)) {
                        $user->last_name = '';
                    }
                }

                $resetUrl = config('app.frontend_url', 'https://koumbaya.com') . '/reset-password-verify?identifier=' . urlencode($email) . '&method=email';

                Mail::send('emails.password-reset', [
                    'user' => $user,
                    'otp' => $code,
                    'resetUrl' => $resetUrl
                ], function ($mail) use ($email, $subject) {
                    $mail->to($email)->subject($subject);
                });
            } else {
                // Utiliser la classe Mailable OtpVerificationEmail
                \Mail::to($email)->send(new \App\Mail\OtpVerificationEmail($code, $purpose));
            }

            Log::info('OTP Email envoyé avec succès', [
                'email' => $email,
                'purpose' => $purpose,
                'code' => $code
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur envoi email OTP', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Envoyer le code par SMS
     */
    private static function sendSmsCode($phone, $code, $purpose)
    {
        $message = self::getSmsMessage($code, $purpose);

        try {
            // En mode développement
            if (config('app.env') === 'local') {
                Log::info('OTP SMS (DEV MODE)', [
                    'to' => $phone,
                    'message' => $message,
                    'code' => $code
                ]);
                return true; // Simuler l'envoi réussi en développement
            }

            // En production, utiliser l'API SMS (à adapter selon le fournisseur)
            return self::sendSmsViaApi($phone, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi SMS OTP', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Envoyer un SMS simple (non-OTP)
     */
    public function sendSMS($phone, $message)
    {
        try {
            // En mode développement
            if (config('app.env') === 'local') {
                Log::info('SMS Notification (DEV MODE)', [
                    'to' => $phone,
                    'message' => $message
                ]);
                return true; // Simuler l'envoi réussi en développement
            }

            // En production, utiliser l'API SMS
            return self::sendSmsViaApi($phone, $message);
        } catch (\Exception $e) {
            Log::error('Erreur envoi SMS notification', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Envoyer SMS via API (exemple avec un fournisseur générique)
     */
    private static function sendSmsViaApi($phone, $message)
    {
        $apiUrl = config('services.sms.api_url');
        $apiKey = config('services.sms.api_key');
        $sender = config('services.sms.sender', 'Koumbaya');

        if (!$apiUrl || !$apiKey) {
            Log::warning('Configuration SMS manquante');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post($apiUrl, [
                'to' => $phone,
                'from' => $sender,
                'text' => $message
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Erreur API SMS', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Nettoyer et formater le numéro de téléphone
     */
    private static function cleanPhoneNumber($phone)
    {
        // Supprimer tous les caractères non numériques
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Vérifications pour le Gabon
        if (strlen($cleaned) === 8 && str_starts_with($cleaned, '0')) {
            // Format local: 07XXXXXX -> +24107XXXXXX
            return '+241' . $cleaned;
        } elseif (strlen($cleaned) === 11 && str_starts_with($cleaned, '241')) {
            // Format international sans +: 24107XXXXXX -> +24107XXXXXX
            return '+' . $cleaned;
        } elseif (strlen($cleaned) === 9 && str_starts_with($cleaned, '7')) {
            // Format sans 0: 7XXXXXX -> +2417XXXXXX
            return '+241' . $cleaned;
        }

        // Autres validations selon le pays
        if (strlen($cleaned) >= 8 && strlen($cleaned) <= 15) {
            if (!str_starts_with($cleaned, '+')) {
                return '+' . $cleaned;
            }
            return $cleaned;
        }

        return null; // Numéro invalide
    }

    /**
     * Vérifier si c'est un numéro de téléphone
     */
    private static function isPhoneNumber($identifier)
    {
        return preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $identifier);
    }

    /**
     * Obtenir le sujet de l'email selon le purpose
     */
    private static function getEmailSubject($purpose)
    {
        return match ($purpose) {
            Otp::PURPOSE_REGISTRATION => 'Koumbaya - Code de vérification d\'inscription',
            Otp::PURPOSE_PASSWORD_RESET => 'Koumbaya - Code de réinitialisation',
            Otp::PURPOSE_LOGIN => 'Koumbaya - Code de connexion',
            Otp::PURPOSE_PAYMENT => 'Koumbaya - Code de sécurité paiement',
            default => 'Koumbaya - Code de vérification'
        };
    }

    /**
     * Obtenir le message email selon le purpose
     */
    private static function getEmailMessage($code, $purpose)
    {
        $baseMessage = match ($purpose) {
            Otp::PURPOSE_REGISTRATION => 'Bienvenue sur Koumbaya ! Votre code de vérification d\'inscription est :',
            Otp::PURPOSE_PASSWORD_RESET => 'Vous avez demandé une réinitialisation de mot de passe. Votre code de vérification est :',
            Otp::PURPOSE_LOGIN => 'Code de sécurité pour votre connexion Koumbaya :',
            Otp::PURPOSE_PAYMENT => 'Code de sécurité pour confirmer votre paiement :',
            default => 'Votre code de vérification Koumbaya est :'
        };

        return $baseMessage . "\n\n" . $code . "\n\nCe code expire dans 30 minutes.\n\nÉquipe Koumbaya";
    }

    /**
     * Obtenir un message email formaté en HTML pour le fallback
     */
    private static function getFormattedEmailMessage($code, $purpose)
    {
        $title = match ($purpose) {
            Otp::PURPOSE_REGISTRATION => 'Bienvenue sur Koumbaya !',
            Otp::PURPOSE_PASSWORD_RESET => 'Réinitialisation de votre mot de passe',
            Otp::PURPOSE_LOGIN => 'Connexion sécurisée',
            Otp::PURPOSE_PAYMENT => 'Confirmation de paiement',
            default => 'Code de vérification Koumbaya'
        };

        $description = match ($purpose) {
            Otp::PURPOSE_REGISTRATION => 'Votre code de vérification d\'inscription est :',
            Otp::PURPOSE_PASSWORD_RESET => 'Votre code de réinitialisation de mot de passe est :',
            Otp::PURPOSE_LOGIN => 'Votre code de connexion sécurisée est :',
            Otp::PURPOSE_PAYMENT => 'Votre code de confirmation de paiement est :',
            default => 'Votre code de vérification est :'
        };

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #0099cc, #0088bb); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: #fff; border: 2px solid #0099cc; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
                .code { font-size: 32px; font-weight: bold; color: #0099cc; letter-spacing: 4px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; color: #666; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$title}</h1>
            </div>
            <div class='content'>
                <p>Bonjour,</p>
                <p>{$description}</p>

                <div class='code-box'>
                    <div class='code'>{$code}</div>
                    <p><strong>Ce code expire dans 30 minutes.</strong></p>
                </div>

                <div class='warning'>
                    🔒 <strong>Sécurité :</strong> Si vous n'avez pas demandé ce code, ignorez cet email. Votre compte reste sécurisé.
                </div>

                <p><strong>Besoin d'aide ?</strong> Contactez-nous à support@koumbaya.com</p>

                <div class='footer'>
                    <p>Cordialement,<br><strong>L'équipe Koumbaya</strong> 💙</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Obtenir le message SMS selon le purpose
     */
    private static function getSmsMessage($code, $purpose)
    {
        return match ($purpose) {
            Otp::PURPOSE_REGISTRATION => "Koumbaya: Votre code d'inscription est {$code}. Valide 30 min.",
            Otp::PURPOSE_PASSWORD_RESET => "Koumbaya: Code de réinitialisation {$code}. Valide 30 min.",
            Otp::PURPOSE_LOGIN => "Koumbaya: Code de connexion {$code}. Valide 30 min.",
            Otp::PURPOSE_PAYMENT => "Koumbaya: Code de paiement {$code}. Valide 30 min.",
            default => "Koumbaya: Code de vérification {$code}. Valide 5 min."
        };
    }

    /**
     * Nettoyer les anciens codes expirés
     */
    public static function cleanup()
    {
        $deleted = Otp::cleanup();
        Log::info('OTP cleanup effectué', ['deleted_count' => $deleted]);
        return $deleted;
    }
}
