<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;

class DataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Migre les données existantes vers la nouvelle architecture :
     * - Convertit user_type_id "merchant" (1) vers "customer" (2)
     * - Met à jour les rôles des utilisateurs selon la nouvelle nomenclature
     * - Assure la cohérence entre user_type et roles
     */
    public function run(): void
    {
        echo "🔄 Début de la migration des données...\n\n";

        // Récupérer les user types
        $adminType = UserType::where('code', 'admin')->first();
        $customerType = UserType::where('code', 'customer')->first();

        if (!$adminType || !$customerType) {
            echo "❌ ERREUR : Les user types admin et customer doivent exister. Exécutez d'abord UserTypeSeeder.\n";
            return;
        }

        echo "✓ User types trouvés : admin (id={$adminType->id}), customer (id={$customerType->id})\n\n";

        // ÉTAPE 1 : Migrer les anciens rôles vers les nouveaux
        $this->migrateRoles();

        // ÉTAPE 2 : Corriger les user_type_id des utilisateurs
        $this->migrateUserTypes($adminType->id, $customerType->id);

        echo "\n✅ Migration des données terminée avec succès !\n";
    }

    /**
     * Migrer les anciens rôles vers les nouveaux
     */
    private function migrateRoles(): void
    {
        echo "📋 Migration des rôles d'utilisateurs...\n";

        // Mapping des anciens rôles vers les nouveaux
        $roleMappings = [
            'Particulier' => 'particulier',
            'Business' => 'business_enterprise',
            'Business Enterprise' => 'business_enterprise',
            'Business Individual' => 'business_individual',
            'Agent' => 'agent',
            'Admin' => 'admin',
            'Super Admin' => 'superadmin',
        ];

        foreach ($roleMappings as $oldRoleName => $newRoleName) {
            $oldRole = Role::where('name', $oldRoleName)->first();
            $newRole = Role::where('name', $newRoleName)->first();

            if (!$oldRole) {
                continue; // Rôle ancien n'existe pas, on passe
            }

            if (!$newRole) {
                echo "   ⚠️  Nouveau rôle '{$newRoleName}' non trouvé, création ignorée pour '{$oldRoleName}'\n";
                continue;
            }

            // Récupérer tous les utilisateurs avec l'ancien rôle
            $users = $oldRole->users;

            if ($users->isEmpty()) {
                echo "   · {$oldRoleName} : 0 utilisateur\n";
                continue;
            }

            echo "   · {$oldRoleName} → {$newRoleName} : {$users->count()} utilisateurs\n";

            // Migrer chaque utilisateur vers le nouveau rôle
            foreach ($users as $user) {
                // Vérifier si l'utilisateur n'a pas déjà le nouveau rôle
                if (!$user->roles->contains('id', $newRole->id)) {
                    DB::table('user_roles')->insert([
                        'user_id' => $user->id,
                        'role_id' => $newRole->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Retirer l'ancien rôle
                DB::table('user_roles')
                    ->where('user_id', $user->id)
                    ->where('role_id', $oldRole->id)
                    ->delete();
            }
        }

        echo "✓ Migration des rôles terminée\n\n";
    }

    /**
     * Corriger les user_type_id des utilisateurs
     */
    private function migrateUserTypes(int $adminTypeId, int $customerTypeId): void
    {
        echo "👤 Correction des user_type_id...\n";

        // Tous les utilisateurs avec user_type_id = 1 (ancien "merchant") deviennent customer (type 2)
        $merchantUsers = User::where('user_type_id', 1)->get();
        if ($merchantUsers->isNotEmpty()) {
            echo "   · Conversion de {$merchantUsers->count()} utilisateurs de type 'merchant' vers 'customer'\n";
            User::where('user_type_id', 1)->update(['user_type_id' => $customerTypeId]);
        }

        // Vérifier que tous les utilisateurs avec des rôles admin ont le bon user_type_id
        $adminRoles = Role::whereIn('name', ['agent', 'admin', 'superadmin'])->pluck('id');
        $adminUsers = User::whereHas('roles', function ($query) use ($adminRoles) {
            $query->whereIn('role_id', $adminRoles);
        })->where('user_type_id', '!=', $adminTypeId)->get();

        if ($adminUsers->isNotEmpty()) {
            echo "   · Correction de {$adminUsers->count()} utilisateurs admin avec mauvais user_type_id\n";
            foreach ($adminUsers as $user) {
                $user->update(['user_type_id' => $adminTypeId]);
            }
        }

        // Vérifier que tous les utilisateurs avec des rôles customer ont le bon user_type_id
        $customerRoles = Role::whereIn('name', ['particulier', 'business_individual', 'business_enterprise'])->pluck('id');
        $customerUsers = User::whereHas('roles', function ($query) use ($customerRoles) {
            $query->whereIn('role_id', $customerRoles);
        })->where('user_type_id', '!=', $customerTypeId)->get();

        if ($customerUsers->isNotEmpty()) {
            echo "   · Correction de {$customerUsers->count()} utilisateurs customer avec mauvais user_type_id\n";
            foreach ($customerUsers as $user) {
                $user->update(['user_type_id' => $customerTypeId]);
            }
        }

        echo "✓ Correction des user_type_id terminée\n";
    }
}
