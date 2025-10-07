<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification en cours...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(255, 255, 255, 1) 50%, rgba(239, 68, 68, 0.1) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            width: 64px;
            height: 64px;
            border: 4px solid #f3f4f6;
            border-top-color: #ef4444;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        h1 {
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        p {
            color: #6b7280;
            font-size: 1rem;
        }

        .error-container {
            max-width: 28rem;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .error-icon {
            width: 64px;
            height: 64px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .error-icon svg {
            width: 32px;
            height: 32px;
            color: #dc2626;
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .error-message {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #ef4444;
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    @if(isset($error))
        <div class="error-container">
            <div class="error-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h2 class="error-title">Erreur d'authentification</h2>
            <p class="error-message">{{ $error }}</p>
            <a href="{{ $frontendUrl }}/login" class="btn">Retour à la connexion</a>
        </div>
    @else
        <div class="container">
            <div class="spinner"></div>
            <h1>Authentification en cours...</h1>
            <p>Vous allez être redirigé dans un instant</p>
        </div>

        <script>
            // Redirect to frontend with token
            const frontendUrl = "{{ $frontendUrl }}";
            const token = "{{ $token }}";
            const provider = "{{ $provider }}";
            const needsProfileCompletion = {{ $needsProfileCompletion ? 'true' : 'false' }};

            // Build redirect URL
            let redirectUrl = `${frontendUrl}/auth/callback?token=${encodeURIComponent(token)}&provider=${encodeURIComponent(provider)}`;

            if (needsProfileCompletion) {
                redirectUrl += '&needs_profile=1';
            }

            // Redirect immediately
            window.location.href = redirectUrl;
        </script>

        <noscript>
            <div style="margin-top: 2rem;">
                <p>JavaScript est désactivé. <a href="{{ $frontendUrl }}/auth/callback?token={{ $token }}&provider={{ $provider }}" class="btn">Cliquez ici pour continuer</a></p>
            </div>
        </noscript>
    @endif
</body>
</html>
