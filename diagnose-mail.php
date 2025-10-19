#!/usr/bin/env php
<?php

/**
 * Script de diagnostic pour l'envoi d'emails
 *
 * Usage: php diagnose-mail.php [email-destination]
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "\n╔═══════════════════════════════════════════════════════════╗\n";
echo "║   KOUMBAYA - Diagnostic de configuration mail            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// 1. Vérifier la configuration
echo "📋 VÉRIFICATION DE LA CONFIGURATION\n";
echo str_repeat("-", 60) . "\n";

$config = [
    'MAIL_MAILER' => config('mail.default'),
    'MAIL_HOST' => config('mail.mailers.smtp.host'),
    'MAIL_PORT' => config('mail.mailers.smtp.port'),
    'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
    'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
    'MAIL_FROM_ADDRESS' => config('mail.from.address'),
    'MAIL_FROM_NAME' => config('mail.from.name'),
    'APP_ENV' => config('app.env'),
];

$hasPassword = !empty(config('mail.mailers.smtp.password'));

foreach ($config as $key => $value) {
    $status = $value ? '✅' : '❌';
    $displayValue = $value ?: '(non défini)';

    // Masquer les informations sensibles
    if ($key === 'MAIL_USERNAME' && $value) {
        $displayValue = substr($value, 0, 3) . '***' . substr($value, -10);
    }

    echo sprintf("%-25s : %s %s\n", $key, $status, $displayValue);
}

echo sprintf("%-25s : %s %s\n",
    'MAIL_PASSWORD',
    $hasPassword ? '✅' : '❌',
    $hasPassword ? '(défini)' : '(non défini)'
);

echo "\n";

// 2. Vérifier les templates d'email
echo "📄 VÉRIFICATION DES TEMPLATES\n";
echo str_repeat("-", 60) . "\n";

$templates = [
    'emails.password-reset' => resource_path('views/emails/password-reset.blade.php'),
    'emails.otp-verification' => resource_path('views/emails/otp-verification.blade.php'),
];

foreach ($templates as $name => $path) {
    $exists = file_exists($path);
    $status = $exists ? '✅' : '❌';
    echo sprintf("%-30s : %s\n", $name, $status);
}

echo "\n";

// 3. Test de connexion SMTP
if (config('mail.default') === 'smtp') {
    echo "🔌 TEST DE CONNEXION SMTP\n";
    echo str_repeat("-", 60) . "\n";

    $host = config('mail.mailers.smtp.host');
    $port = config('mail.mailers.smtp.port');

    echo "Tentative de connexion à {$host}:{$port}...\n";

    $connection = @fsockopen($host, $port, $errno, $errstr, 10);

    if ($connection) {
        echo "✅ Connexion SMTP réussie\n";
        fclose($connection);
    } else {
        echo "❌ Échec de connexion SMTP\n";
        echo "   Erreur: {$errstr} (Code: {$errno})\n";
    }

    echo "\n";
}

// 4. Test d'envoi d'email réel
$testEmail = $argv[1] ?? null;

if ($testEmail && filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    echo "📧 TEST D'ENVOI D'EMAIL\n";
    echo str_repeat("-", 60) . "\n";
    echo "Destination: {$testEmail}\n";
    echo "Envoi en cours...\n\n";

    try {
        Mail::raw('Ceci est un email de test depuis Koumbaya.', function ($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('Koumbaya - Test de configuration mail');
        });

        echo "✅ Email envoyé avec succès !\n";
        echo "   Vérifiez votre boîte mail (y compris spam/courrier indésirable)\n";

    } catch (\Exception $e) {
        echo "❌ Échec de l'envoi d'email\n";
        echo "   Erreur: " . $e->getMessage() . "\n";
        echo "   Classe: " . get_class($e) . "\n";
        echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";

        // Trace détaillée
        echo "\n📋 TRACE DÉTAILLÉE:\n";
        echo str_repeat("-", 60) . "\n";
        echo $e->getTraceAsString() . "\n";
    }

    echo "\n";
} else {
    echo "💡 CONSEIL\n";
    echo str_repeat("-", 60) . "\n";
    echo "Pour tester l'envoi d'email, exécutez :\n";
    echo "   php diagnose-mail.php votre-email@example.com\n\n";
}

// 5. Logs récents
echo "📜 LOGS RÉCENTS\n";
echo str_repeat("-", 60) . "\n";

$logFile = storage_path('logs/laravel.log');

if (file_exists($logFile)) {
    echo "Dernières erreurs OTP (20 dernières lignes):\n\n";

    $handle = @fopen($logFile, 'r');
    if ($handle) {
        $lines = [];
        while (($line = fgets($handle)) !== false) {
            if (stripos($line, 'OTP') !== false || stripos($line, 'mail') !== false) {
                $lines[] = $line;
                if (count($lines) > 20) {
                    array_shift($lines);
                }
            }
        }
        fclose($handle);

        if (empty($lines)) {
            echo "   Aucune erreur OTP/Mail trouvée récemment\n";
        } else {
            foreach ($lines as $line) {
                echo $line;
            }
        }
    } else {
        echo "   ⚠️  Impossible d'ouvrir le fichier de logs\n";
    }
} else {
    echo "   ⚠️  Fichier de logs non trouvé: {$logFile}\n";
}

echo "\n";

// 6. Recommandations
echo "💡 RECOMMANDATIONS\n";
echo str_repeat("=", 60) . "\n\n";

$issues = [];

if (empty(config('mail.mailers.smtp.host'))) {
    $issues[] = "MAIL_HOST non défini dans .env";
}

if (empty(config('mail.mailers.smtp.username'))) {
    $issues[] = "MAIL_USERNAME non défini dans .env";
}

if (!$hasPassword) {
    $issues[] = "MAIL_PASSWORD non défini dans .env";
}

if (config('mail.default') !== 'smtp') {
    $issues[] = "MAIL_MAILER devrait être 'smtp' pour utiliser Gmail/SMTP";
}

if (!empty($issues)) {
    echo "❌ PROBLÈMES DÉTECTÉS:\n\n";
    foreach ($issues as $i => $issue) {
        echo "   " . ($i + 1) . ". " . $issue . "\n";
    }
    echo "\n";
}

echo "✅ CONFIGURATION RECOMMANDÉE POUR GMAIL:\n\n";
echo <<<'EOT'
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx  # Mot de passe d'application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@koumbaya.com
MAIL_FROM_NAME="Koumbaya Marketplace"

EOT;

echo "\n📚 ÉTAPES POUR CONFIGURER GMAIL:\n\n";
echo "   1. Activer l'authentification à 2 facteurs sur votre compte Google\n";
echo "      → https://myaccount.google.com/security\n\n";
echo "   2. Créer un mot de passe d'application\n";
echo "      → https://myaccount.google.com/apppasswords\n";
echo "      → Sélectionner 'Mail' et copier le mot de passe généré\n\n";
echo "   3. Mettre à jour votre fichier .env avec ces informations\n\n";
echo "   4. Redémarrer votre serveur/application\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "Script terminé. Pour toute question, contactez le support.\n";
echo "═══════════════════════════════════════════════════════════\n\n";
