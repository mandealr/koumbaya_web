<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification Koumbaya</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f7;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f7;">
        <tr>
            <td style="padding: 40px 0;">
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">Koumbaya Marketplace</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 24px; font-weight: 600;">Code de vérification</h2>

                            <p style="margin: 0 0 20px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Bonjour,
                            </p>

                            <p style="margin: 0 0 30px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                @if (isset($purpose) && $purpose === 'registration')
                                    Bienvenue sur <strong>Koumbaya Marketplace</strong> !
                                @elseif (isset($purpose) && $purpose === 'login')
                                    Connexion sécurisée à votre compte <strong>Koumbaya</strong>.
                                @elseif (isset($purpose) && $purpose === 'payment')
                                    Confirmation de votre paiement sur <strong>Koumbaya</strong>.
                                @else
                                    Voici votre code de vérification pour <strong>Koumbaya</strong>.
                                @endif
                            </p>

                            <p style="margin: 0 0 20px 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                Voici votre code de vérification à usage unique :
                            </p>

                            <!-- OTP Code Panel -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 0 0 30px 0;">
                                <tr>
                                    <td style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 30px; border-radius: 4px;">
                                        <p style="margin: 0 0 10px 0; color: #333333; font-size: 36px; font-weight: 700; text-align: center; letter-spacing: 8px; font-family: 'Courier New', monospace;">
                                            {{ $code }}
                                        </p>
                                        <p style="margin: 0; color: #e63946; font-size: 14px; font-weight: 600; text-align: center;">
                                            Ce code expire dans 30 minutes.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Instructions -->
                            <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px; font-weight: 600;">Comment utiliser ce code ?</h3>

                            <ol style="margin: 0 0 30px 0; padding-left: 20px; color: #555555; font-size: 16px; line-height: 1.8;">
                                <li style="margin-bottom: 8px;"><strong>Retournez sur la page Koumbaya</strong></li>
                                <li style="margin-bottom: 8px;"><strong>Saisissez ce code : {{ $code }}</strong></li>
                                <li style="margin-bottom: 8px;"><strong>Validez pour continuer</strong></li>
                            </ol>

                            <!-- Security Notice -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 0 0 30px 0;">
                                <tr>
                                    <td style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 4px;">
                                        <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                            <strong>🔒 Important :</strong> Si vous n'avez pas demandé ce code, ignorez cet email. Votre compte reste sécurisé.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 20px;">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 10px 0; color: #555555; font-size: 14px; line-height: 1.6;">
                                            <strong>Besoin d'aide ?</strong> Contactez-nous à <a href="mailto:support@koumbaya.com" style="color: #667eea; text-decoration: none;">support@koumbaya.com</a>
                                        </p>

                                        <p style="margin: 0; color: #555555; font-size: 16px; line-height: 1.6;">
                                            Cordialement,<br>
                                            <strong>L'équipe Koumbaya</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #888888; font-size: 12px; line-height: 1.6;">
                                Cet email a été envoyé automatiquement. Merci de ne pas y répondre.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Email Footer -->
                <table role="presentation" style="width: 100%; max-width: 600px; margin: 20px auto 0; text-align: center;">
                    <tr>
                        <td>
                            <p style="margin: 0; color: #999999; font-size: 12px; line-height: 1.6;">
                                © {{ date('Y') }} Koumbaya Marketplace. Tous droits réservés.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
