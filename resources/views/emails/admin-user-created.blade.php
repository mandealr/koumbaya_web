<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Koumbaya</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #374151;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-badge {
            background-color: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
        .user-info {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .user-info h3 {
            margin: 0 0 15px;
            color: #374151;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            color: #374151;
        }
        .password-section {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .password-section h4 {
            margin: 0 0 10px;
            color: #92400e;
            font-size: 16px;
        }
        .temporary-password {
            background-color: #ffffff;
            border: 2px dashed #f59e0b;
            border-radius: 6px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #92400e;
            margin: 15px 0;
            word-break: break-all;
        }
        .warning-text {
            color: #92400e;
            font-size: 14px;
            margin: 10px 0 0;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .action-button:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6c4190 100%);
        }
        .steps {
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .steps h4 {
            margin: 0 0 15px;
            color: #1e40af;
            font-size: 16px;
        }
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 0;
        }
        .step-number {
            background-color: #3b82f6;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .step-text {
            color: #374151;
            font-size: 14px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        .security-notice {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .security-notice h5 {
            margin: 0 0 8px;
            color: #dc2626;
            font-size: 14px;
        }
        .security-notice p {
            margin: 0;
            color: #991b1b;
            font-size: 13px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 0;
                box-shadow: none;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
            .action-button {
                display: block;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Bienvenue sur Koumbaya</h1>
            <p>Votre compte a été créé avec succès</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="welcome-badge">{{ $role }}</div>
            
            <p>Bonjour <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>,</p>
            
            <p>Nous avons le plaisir de vous informer qu'un compte administrateur a été créé pour vous sur la plateforme Koumbaya par <strong>{{ $createdBy->first_name }} {{ $createdBy->last_name }}</strong>.</p>

            <!-- User Information -->
            <div class="user-info">
                <h3>Informations de votre compte</h3>
                <div class="info-row">
                    <span class="info-label">Nom complet :</span>
                    <span class="info-value">{{ $user->first_name }} {{ $user->last_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email :</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Téléphone :</span>
                    <span class="info-value">{{ $user->phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Rôle :</span>
                    <span class="info-value">{{ $role }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut :</span>
                    <span class="info-value">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span>
                </div>
            </div>

            <!-- Temporary Password -->
            <div class="password-section">
                <h4>Mot de passe temporaire</h4>
                <p>Un mot de passe temporaire a été généré pour votre compte :</p>
                <div class="temporary-password">{{ $temporaryPassword }}</div>
                <p class="warning-text">Ce mot de passe est temporaire et doit être changé lors de votre première connexion.</p>
            </div>

            <!-- Steps to access account -->
            <div class="steps">
                <h4>Étapes pour accéder à votre compte</h4>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-text">Cliquez sur le bouton ci-dessous pour accéder à la page de connexion</div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">Connectez-vous avec votre email et le mot de passe temporaire</div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">Suivez les instructions pour créer un nouveau mot de passe sécurisé</div>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-text">Explorez votre tableau de bord et commencez à utiliser la plateforme</div>
                </div>
            </div>

            <!-- Action Button -->
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/login" class="action-button">
                    Accéder à mon compte
                </a>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <h5>Conseils de sécurité</h5>
                <p>Pour la sécurité de votre compte, nous vous recommandons de changer votre mot de passe dès votre première connexion et de ne jamais le partager avec des tiers.</p>
            </div>

            <p>Si vous avez des questions ou besoin d'assistance, n'hésitez pas à contacter notre équipe de support.</p>

            <p>Cordialement,<br>
            <strong>L'équipe Koumbaya</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Koumbaya</strong> - Votre marketplace de tombolas</p>
            <p>© {{ date('Y') }} Koumbaya. Tous droits réservés.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
        </div>
    </div>
</body>
</html>