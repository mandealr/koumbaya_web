#!/bin/bash

# Script de seeding complet pour la BD Koumbaya optimisée
# Usage: ./seed_koumbaya_optimized.sh [--fresh]

set -e

echo "🌱 Seeding de la base de données Koumbaya optimisée"
echo "================================================="

# Vérification des prérequis
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé ou pas dans le PATH"
    exit 1
fi

if ! php artisan --version &> /dev/null; then
    echo "❌ Laravel Artisan non disponible"
    exit 1
fi

# Si --fresh, réinitialiser d'abord la BD
if [[ "$1" == "--fresh" ]]; then
    echo "🔄 Réinitialisation complète de la BD..."
    ./deploy_koumbaya_database_complete.sh --force
    echo ""
fi

echo "📋 Étape 1: Vérification de la structure de base"
# Vérifier que les tables principales existent avec Laravel
php artisan tinker --execute="
\$tables = ['user_types', 'roles', 'privileges', 'users', 'orders', 'payments'];
foreach (\$tables as \$table) {
    if (!DB::getSchemaBuilder()->hasTable(\$table)) {
        echo \"❌ Table \$table manquante\n\";
        exit(1);
    }
}
echo \"✅ Structure de base vérifiée\n\";
"

echo ""
echo "🌱 Étape 2: Exécution du seeding"
php artisan db:seed --class=DatabaseSeeder

echo ""
echo "📊 Étape 3: Vérification des données créées"
echo "===========================================

# Compter les enregistrements par table
php artisan tinker --execute="
echo \"📋 DONNÉES CRÉÉES:\n\";
echo \"   - Types d'utilisateurs: \" . DB::table('user_types')->count() . \"\n\";
echo \"   - Rôles: \" . DB::table('roles')->count() . \"\n\";
echo \"   - Privilèges: \" . DB::table('privileges')->count() . \"\n\";
echo \"   - Utilisateurs: \" . DB::table('users')->count() . \"\n\";
echo \"   - Portefeuilles: \" . DB::table('user_wallets')->count() . \"\n\";
echo \"   - Catégories: \" . DB::table('categories')->count() . \"\n\";
echo \"   - Produits: \" . DB::table('products')->count() . \"\n\";
echo \"   - Méthodes paiement: \" . DB::table('payment_methods')->count() . \"\n\";
echo \"   - Paramètres: \" . DB::table('settings')->count() . \"\n\";
"

echo ""
echo "🔐 Étape 4: Vérification du système de permissions"
php artisan tinker --execute="
echo \"🛡️ SYSTÈME DE PERMISSIONS:\n\";

// Compter les associations rôles-privilèges
\$rolePrivileges = DB::table('role_privileges')->count();
echo \"   - Associations rôles-privilèges: \$rolePrivileges\n\";

// Compter les utilisateurs avec rôles
\$userRoles = DB::table('user_roles')->count();
echo \"   - Utilisateurs avec rôles: \$userRoles\n\";

// Afficher quelques détails
\$superAdmin = DB::table('users')->where('email', 'superadmin@koumbaya.ga')->first();
if (\$superAdmin) {
    \$roleCount = DB::table('user_roles')->where('user_id', \$superAdmin->id)->count();
    echo \"   - Super Admin a \$roleCount rôle(s)\n\";
}

\$merchant = DB::table('users')->where('email', 'merchant1@koumbaya.ga')->first();
if (\$merchant) {
    \$balance = DB::table('user_wallets')->where('user_id', \$merchant->id)->first()->balance ?? 0;
    echo \"   - Marchand 1 solde: \" . number_format(\$balance) . \" FCFA\n\";
}
"

echo ""
echo "🎉 SEEDING TERMINÉ AVEC SUCCÈS!"
echo "=============================="
echo ""
echo "📋 Résumé:"
echo "   - Structure BD optimisée avec rôles/privilèges"
echo "   - Utilisateurs de test avec portefeuilles"
echo "   - Données de démonstration (produits, catégories)"
echo "   - Configuration système (paramètres, méthodes paiement)"
echo ""
echo "🔗 Prochaines étapes:"
echo "   1. Tester les connexions avec les comptes créés"
echo "   2. Vérifier les permissions par rôle"
echo "   3. Tester l'architecture order-centric"
echo "   4. Configurer E-Billing avec les méthodes de paiement"
echo ""
echo "✨ La base de données Koumbaya optimisée est prête à l'emploi!"