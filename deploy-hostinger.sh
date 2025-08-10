#!/bin/bash

echo "🚀 Préparation du déploiement pour Hostinger..."

# 1. Build des assets de production
echo "📦 Construction des assets de production..."
npm run build

# 2. Optimisation Laravel
echo "⚡ Optimisation Laravel..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Cache des configurations (optionnel en production)
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

echo "✅ Préparation terminée !"
echo ""
echo "📋 Instructions pour Hostinger :"
echo "1. Uploadez tout le projet via FTP/SFTP"
echo "2. Copiez .env.production vers .env et configurez :"
echo "   - APP_KEY (générez avec 'php artisan key:generate')"
echo "   - Base de données MySQL"
echo "   - Domaine dans APP_URL"
echo "   - JWT_SECRET (générez un token sécurisé)"
echo "3. Pointez votre domaine vers le dossier /public"
echo "4. Exécutez les migrations : php artisan migrate"
echo "5. Créez les tables de cache : php artisan cache:table && php artisan session:table"