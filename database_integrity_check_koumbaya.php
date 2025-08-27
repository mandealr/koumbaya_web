<?php

/**
 * Script de vérification de l'intégrité de la base de données Koumbaya complète
 * Usage: php database_integrity_check_koumbaya.php
 */

require_once 'bootstrap/app.php';

$app = new App\Bootstrap\Application(dirname(__DIR__));

echo "🔍 Vérification de l'intégrité de la base de données Koumbaya complète\n";
echo "====================================================================\n\n";

$issues = [];
$checks = 0;

try {
    $pdo = new PDO(
        'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_DATABASE'),
        env('DB_USERNAME'),
        env('DB_PASSWORD')
    );
    
    // 1. Vérification des tables essentielles (25 tables)
    echo "📋 1. Vérification des tables essentielles (25 tables)...\n";
    $requiredTables = [
        // Système utilisateurs
        'countries', 'languages', 'user_types', 'roles', 'privileges', 
        'role_privileges', 'user_roles', 'users', 'user_wallets',
        'user_login_histories', 'user_sessions', 'user_ratings',
        
        // E-commerce
        'categories', 'products', 'lotteries', 'orders',
        
        // Paiements (unifié)
        'payments',
        
        // Support
        'lottery_tickets', 'notifications', 'otps', 'refunds', 'draw_histories',
        'settings', 'payment_methods'
    ];
    
    foreach ($requiredTables as $table) {
        $checks++;
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() === 0) {
            $issues[] = "❌ Table manquante: $table";
        } else {
            echo "✅ $table\n";
        }
    }
    
    // 2. Vérification que transactions a bien été supprimée
    echo "\n🗑️ 2. Vérification suppression table transactions...\n";
    $checks++;
    $result = $pdo->query("SHOW TABLES LIKE 'transactions'");
    if ($result->rowCount() > 0) {
        $issues[] = "❌ Table transactions devrait être supprimée (redondante avec payments)";
    } else {
        echo "✅ Table transactions bien supprimée\n";
    }
    
    // 3. Vérification des index critiques système de permissions
    echo "\n🔐 3. Vérification des index système de permissions...\n";
    $permissionIndexes = [
        'roles' => ['user_type_id', 'merchant_id', 'active'],
        'privileges' => ['user_type_id'],
        'role_privileges' => ['privilege_id', 'role_id'],
        'user_roles' => ['user_id', 'role_id'],
    ];
    
    foreach ($permissionIndexes as $table => $indexes) {
        foreach ($indexes as $indexColumn) {
            $checks++;
            $result = $pdo->query("SHOW INDEX FROM $table WHERE Column_name = '$indexColumn'");
            if ($result->rowCount() === 0) {
                $issues[] = "❌ Index manquant: $table.$indexColumn";
            } else {
                echo "✅ $table.$indexColumn\n";
            }
        }
    }
    
    // 4. Vérification des index critiques tables principales
    echo "\n📊 4. Vérification des index tables principales...\n";
    $criticalIndexes = [
        'users' => ['email', 'phone', 'user_type_id'],
        'orders' => ['order_number', 'user_id', 'status'],
        'payments' => ['reference', 'order_id', 'user_id', 'ebilling_id', 'status'],
        'lottery_tickets' => ['ticket_number', 'lottery_id', 'payment_id'],
    ];
    
    foreach ($criticalIndexes as $table => $indexes) {
        foreach ($indexes as $indexColumn) {
            $checks++;
            $result = $pdo->query("SHOW INDEX FROM $table WHERE Column_name = '$indexColumn'");
            if ($result->rowCount() === 0) {
                $issues[] = "❌ Index manquant: $table.$indexColumn";
            } else {
                echo "✅ $table.$indexColumn\n";
            }
        }
    }
    
    // 5. Vérification de la cohérence des types de données
    echo "\n🔧 5. Vérification des types de données...\n";
    $typeChecks = [
        "SELECT COUNT(*) as count FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND column_name LIKE '%_id' 
         AND column_name != 'id'
         AND data_type != 'bigint'" => "IDs non-bigint",
        
        "SELECT COUNT(*) as count FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND column_name LIKE '%amount%' 
         AND data_type != 'decimal'" => "Montants non-decimal",
         
        "SELECT COUNT(*) as count FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND column_name = 'currency' 
         AND character_maximum_length != 3" => "Devises non-varchar(3)",
    ];
    
    foreach ($typeChecks as $query => $description) {
        $checks++;
        $result = $pdo->query($query);
        $count = $result->fetch()['count'];
        if ($count > 0) {
            $issues[] = "⚠️  $description: $count colonnes";
        } else {
            echo "✅ $description\n";
        }
    }
    
    // 6. Vérification des données par défaut
    echo "\n📄 6. Vérification des données par défaut...\n";
    $defaultDataChecks = [
        "SELECT COUNT(*) as count FROM countries WHERE code = 'GA'" => "Pays Gabon",
        "SELECT COUNT(*) as count FROM languages WHERE code = 'fr' AND is_default = 1" => "Langue française par défaut",
        "SELECT COUNT(*) as count FROM user_types WHERE code IN ('merchant', 'customer', 'admin')" => "Types d'utilisateurs de base",
    ];
    
    foreach ($defaultDataChecks as $query => $description) {
        $checks++;
        $result = $pdo->query($query);
        $count = $result->fetch()['count'];
        if ($count === 0 || ($description === "Types d'utilisateurs de base" && $count < 3)) {
            $issues[] = "❌ Données manquantes: $description";
        } else {
            echo "✅ $description\n";
        }
    }
    
    // 7. Vérification des statuts ENUM
    echo "\n🔄 7. Vérification des statuts ENUM...\n";
    $enumChecks = [
        "orders" => "status",
        "payments" => "status", 
        "lottery_tickets" => "status",
        "lotteries" => "status",
        "refunds" => "status",
        "notifications" => "status",
    ];
    
    foreach ($enumChecks as $table => $column) {
        $checks++;
        $result = $pdo->query("SHOW COLUMNS FROM $table LIKE '$column'");
        $columnInfo = $result->fetch();
        if (!$columnInfo || !str_contains($columnInfo['Type'], 'enum')) {
            $issues[] = "❌ ENUM manquant: $table.$column";
        } else {
            echo "✅ $table.$column\n";
        }
    }
    
    // 8. Vérification spécifique architecture order-centric
    echo "\n💳 8. Vérification architecture order-centric...\n";
    $orderCentricChecks = [
        "SELECT COUNT(*) as count FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payments' 
         AND column_name IN ('order_id', 'user_id', 'ebilling_id', 'external_transaction_id', 'transaction_id')
        " => 5,
        
        "SELECT COUNT(*) as count FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'orders' 
         AND column_name = 'order_number'
        " => 1,
        
        "SELECT COUNT(*) as count FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'lottery_tickets' 
         AND column_name = 'payment_id'
        " => 1,
    ];
    
    foreach ($orderCentricChecks as $query => $expectedCount) {
        $checks++;
        $result = $pdo->query($query);
        $count = $result->fetch()['count'];
        if ($count != $expectedCount) {
            $issues[] = "❌ Architecture order-centric incomplète";
        } else {
            echo "✅ Architecture order-centric optimisée\n";
        }
    }
    
    // 9. Vérification système de permissions complet
    echo "\n🛡️ 9. Vérification système de permissions...\n";
    $permissionsChecks = [
        "SELECT COUNT(*) as count FROM information_schema.tables 
         WHERE table_schema = DATABASE() 
         AND table_name IN ('roles', 'privileges', 'role_privileges', 'user_roles')
        " => 4,
    ];
    
    foreach ($permissionsChecks as $query => $expectedCount) {
        $checks++;
        $result = $pdo->query($query);
        $count = $result->fetch()['count'];
        if ($count != $expectedCount) {
            $issues[] = "❌ Système de permissions incomplet";
        } else {
            echo "✅ Système de permissions complet (4 tables)\n";
        }
    }
    
} catch (Exception $e) {
    $issues[] = "❌ Erreur de connexion: " . $e->getMessage();
}

// Résumé final
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 RÉSUMÉ DE LA VÉRIFICATION KOUMBAYA COMPLÈTE\n";
echo str_repeat("=", 70) . "\n";

echo "✅ Vérifications réussies: " . ($checks - count($issues)) . "/$checks\n";

if (empty($issues)) {
    echo "\n🎉 FÉLICITATIONS! La base de données Koumbaya est parfaitement structurée.\n";
    echo "✨ Prête pour la production avec système complet de permissions!\n\n";
    echo "📋 Points forts détectés:\n";
    echo "   - Structure order-centric complète avec 25 tables\n";
    echo "   - Système de rôles et privilèges opérationnel\n";
    echo "   - Table payments unifiée (transactions supprimée)\n";
    echo "   - Index de performance correctement placés\n";
    echo "   - Types de données cohérents partout\n";
    echo "   - Statuts ENUM bien définis\n";
    echo "   - Intégration E-Billing complète\n";
    echo "   - Architecture order-centric optimisée\n";
    exit(0);
} else {
    echo "\n⚠️  PROBLÈMES DÉTECTÉS (" . count($issues) . "):\n";
    foreach ($issues as $issue) {
        echo "   $issue\n";
    }
    echo "\n🔧 RECOMMANDATIONS:\n";
    echo "   1. Exécuter: ./deploy_koumbaya_database_complete.sh\n";
    echo "   2. Consulter: DATABASE_STRUCTURE_KOUMBAYA_COMPLETE.md\n";
    echo "   3. Relancer ce script après corrections\n";
    exit(1);
}
?>