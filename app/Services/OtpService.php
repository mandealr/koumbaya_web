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
    public static function sendEmailOtp($email, $purpose = Otp::PURPOSE_REGISTRATION, $expirationMinutes = 5)
    {
        try {
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

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur envoi OTP email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code de vérification'
            ];
        }
    }

    /**
     * Générer et envoyer un OTP par SMS
     */
    public static function sendSmsOtp($phone, $purpose = Otp::PURPOSE_REGISTRATION, $expirationMinutes = 5)
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

            $isValid = Otp::verify($identifier, $code, $purpose);

            if ($isValid) {
                Log::info('OTP validé avec succès', [
                    'identifier' => $identifier,
                    'purpose' => $purpose
                ]);

                return [
                    'success' => true,
                    'message' => 'Code de vérification validé avec succès'
                ];
            }

            Log::warning('OTP invalide', [
                'identifier' => $identifier,
                'code' => $code,
                'purpose' => $purpose,
                'debug_info' => 'Code non trouvé ou conditions non remplies'
            ]);

            return [
                'success' => false,
                'message' => 'Code de vérification invalide ou expiré'
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
            // En mode développement, on peut utiliser log
            if (config('app.env') === 'local') {
                Log::info('OTP Email (DEV MODE)', [
                    'to' => $email,
                    'subject' => $subject,
                    'code' => $code
                ]);
                return true; // Simuler l'envoi réussi en développement
            }

            // Essayer d'utiliser les templates, avec fallback en cas d'erreur
            try {
                if ($purpose === 'password_reset') {
                    // Créer un utilisateur fictif pour le template si pas d'utilisateur trouvé
                    $user = \App\Models\User::where('email', $email)->first();
                    if (!$user) {
                        $user = (object) ['first_name' => 'Utilisateur', 'last_name' => ''];
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
                    // Pour les autres purposes, utiliser le template OTP
                    Mail::send('emails.otp-verification', [
                        'code' => $code,
                        'purpose' => $purpose
                    ], function ($mail) use ($email, $subject) {
                        $mail->to($email)->subject($subject);
                    });
                }
            } catch (\Exception $templateError) {
                Log::warning('Erreur template email, utilisation du fallback', [
                    'error' => $templateError->getMessage()
                ]);

                // Fallback: utiliser un email simple mais bien formaté
                $message = self::getFormattedEmailMessage($code, $purpose);
                Mail::send([], [], function ($mail) use ($email, $subject, $message) {
                    $mail->to($email)
                        ->subject($subject)
                        ->html($message);
                });
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur envoi email OTP', [
                'email' => $email,
                'error' => $e->getMessage()
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

        return $baseMessage . "\n\n" . $code . "\n\nCe code expire dans 5 minutes.\n\nÉquipe Koumbaya";
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
                    <p><strong>Ce code expire dans 5 minutes.</strong></p>
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
            Otp::PURPOSE_REGISTRATION => "Koumbaya: Votre code d'inscription est {$code}. Valide 5 min.",
            Otp::PURPOSE_PASSWORD_RESET => "Koumbaya: Code de réinitialisation {$code}. Valide 5 min.",
            Otp::PURPOSE_LOGIN => "Koumbaya: Code de connexion {$code}. Valide 5 min.",
            Otp::PURPOSE_PAYMENT => "Koumbaya: Code de paiement {$code}. Valide 5 min.",
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
