#!/bin/bash

# Script de déploiement de la base de données complète Koumbaya
# Usage: ./deploy_koumbaya_database_complete.sh [--force]

set -e

echo "🚀 Déploiement de la base de données complète Koumbaya"
echo "====================================================="

# Vérification des prérequis
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé ou pas dans le PATH"
    exit 1
fi

if ! php artisan --version &> /dev/null; then
    echo "❌ Laravel Artisan non disponible"
    exit 1
fi

# Confirmation utilisateur (sauf si --force)
if [[ "$1" != "--force" ]]; then
    echo "⚠️  ATTENTION: Cette opération va:"
    echo "   - Supprimer TOUTES les données existantes"
    echo "   - Installer la structure complète optimisée Koumbaya"
    echo "   - Inclure le système de rôles et privilèges"
    echo "   - Fusionner payments et transactions en une seule table"
    echo ""
    read -p "Êtes-vous sûr de vouloir continuer? (oui/non): " confirm
    
    if [[ $confirm != "oui" ]]; then
        echo "❌ Opération annulée"
        exit 0
    fi
fi

echo ""
echo "📋 Étape 1: Sauvegarde des migrations actuelles"
if [ -d "database/migrations_backup" ]; then
    rm -rf database/migrations_backup
fi
mkdir -p database/migrations_backup
cp database/migrations/* database/migrations_backup/ 2>/dev/null || echo "   Aucune migration à sauvegarder"
echo "✅ Migrations sauvegardées dans database/migrations_backup/"

echo ""
echo "🗑️  Étape 2: Suppression des anciennes migrations"
rm -rf database/migrations/*
echo "✅ Anciennes migrations supprimées"

echo ""  
echo "📁 Étape 3: Installation des nouvelles migrations complètes"
if [ -d "database/migrations_optimized_complete" ]; then
    cp database/migrations_optimized_complete/* database/migrations/
    echo "✅ Migrations complètes Koumbaya copiées (25 tables)"
else
    echo "❌ Dossier database/migrations_optimized_complete introuvable"
    echo "   Restauration des anciennes migrations..."
    cp database/migrations_backup/* database/migrations/
    exit 1
fi

echo ""
echo "🔄 Étape 4: Réinitialisation de la base de données"
php artisan migrate:fresh --force
echo "✅ Base de données réinitialisée avec structure complète"

echo ""
echo "📊 Étape 5: Vérification de la structure"
php artisan migrate:status
echo "✅ Structure vérifiée"

echo ""
echo "🎯 Étape 6: Optimisation des performances"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Optimisations appliquées"

echo ""
echo "📝 Étape 7: Génération du rapport de structure complet"
echo "Structure de la base de données Koumbaya complète:" > database_structure_report_koumbaya.txt
echo "====================================================" >> database_structure_report_koumbaya.txt
echo "" >> database_structure_report_koumbaya.txt

# Liste des tables avec leur nombre de colonnes
php artisan tinker --execute="
\$tables = DB::select('SHOW TABLES');
foreach(\$tables as \$table) {
    \$tableName = array_values((array)\$table)[0];
    \$columns = DB::select('DESCRIBE ' . \$tableName);
    echo \$tableName . ' (' . count(\$columns) . ' colonnes)' . PHP_EOL;
}
" >> database_structure_report_koumbaya.txt 2>/dev/null || echo "Rapport détaillé non disponible"

echo "✅ Rapport généré: database_structure_report_koumbaya.txt"

echo ""
echo "🎉 DÉPLOIEMENT KOUMBAYA TERMINÉ AVEC SUCCÈS!"
echo "============================================"
echo ""
echo "📋 Résumé:"
echo "   - 25 tables optimisées installées"  
echo "   - Système de rôles et privilèges inclus"
echo "   - Table payments unifiée (transactions supprimées)"
echo "   - Architecture order-centric complète"
echo "   - Base de données E-Billing ready"
echo ""
echo "📚 Prochaines étapes recommandées:"
echo "   1. Consulter: DATABASE_STRUCTURE_KOUMBAYA_COMPLETE.md"
echo "   2. Exécuter les tests: php artisan test"
echo "   3. Installer les données de test si nécessaire: php artisan db:seed"
echo "   4. Configurer les rôles et privilèges par défaut"
echo "   5. Vérifier les métriques: visitez /api/merchant/orders/metrics/health"
echo ""
echo "✨ La base de données Koumbaya est prête avec rôles et privilèges!"