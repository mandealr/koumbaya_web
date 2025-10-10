<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserType;

class MigrateExistingUsersSeeder extends Seeder
{
    /**
     * Migrer les utilisateurs existants vers la nouvelle architecture
     *
     * Architecture:
     * - UserType ID 1 = admin (superadmin, admin, agent)
     * - UserType ID 2 = customer (particulier, business_individual, business_enterprise)
     *
     * Mapping des anciens rôles vers les nouveaux:
     * - "Super Admin" → "superadmin" (user_type_id = 1)
     * - "Admin" → "admin" (user_type_id = 1)
     * - "Agent" → "agent" (user_type_id = 1)
     * - "Business Enterprise" → "business_enterprise" (user_type_id = 2)
     * - "Business Individual" → "business_individual" (user_type_id = 2)
     * - "Business" → "business_enterprise" (user_type_id = 2)
     * - "Particulier" → "particulier" (user_type_id = 2)
     * - "Client" → "particulier" (user_type_id = 2)
     */
    public function run(): void
    {
        echo "🔄 Migration des utilisateurs existants...\n\n";

        // Récupérer tous les utilisateurs
        $users = User::with('roles')->get();

        if ($users->isEmpty()) {
            echo "ℹ️  Aucun utilisateur à migrer.\n";
            return;
        }

        echo "📊 " . $users->count() . " utilisateurs trouvés.\n\n";

        $adminTypeId = 1;
        $customerTypeId = 2;

        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            echo "👤 Utilisateur #{$user->id} - {$user->first_name} {$user->last_name} ({$user->email})\n";

            // Récupérer les anciens rôles
            $oldRoles = $user->roles;

            if ($oldRoles->isEmpty()) {
                echo "   ⚠️  Pas de rôle trouvé - attribution du rôle 'particulier' par défaut\n";

                // Assigner le type et le rôle par défaut
                $user->user_type_id = $customerTypeId;
                $user->save();

                $particulierRole = Role::where('name', 'particulier')->first();
                if ($particulierRole) {
                    \DB::table('user_roles')->insert([
                        'user_id' => $user->id,
                        'role_id' => $particulierRole->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    echo "   ✅ Assigné: particulier (user_type_id = {$customerTypeId})\n\n";
                    $migratedCount++;
                }
                continue;
            }

            // Traiter chaque ancien rôle
            foreach ($oldRoles as $oldRole) {
                echo "   📌 Ancien rôle: {$oldRole->name}\n";

                $newRoleName = null;
                $newUserTypeId = null;

                // Mapping des rôles
                switch ($oldRole->name) {
                    case 'Super Admin':
                    case 'superadmin':
                        $newRoleName = 'superadmin';
                        $newUserTypeId = $adminTypeId;
                        break;

                    case 'Admin':
                    case 'admin':
                        $newRoleName = 'admin';
                        $newUserTypeId = $adminTypeId;
                        break;

                    case 'Agent':
                    case 'agent':
                    case 'Agent Back Office':
                        $newRoleName = 'agent';
                        $newUserTypeId = $adminTypeId;
                        break;

                    case 'Business Enterprise':
                    case 'business_enterprise':
                    case 'Business':
                        $newRoleName = 'business_enterprise';
                        $newUserTypeId = $customerTypeId;
                        break;

                    case 'Business Individual':
                    case 'business_individual':
                        $newRoleName = 'business_individual';
                        $newUserTypeId = $customerTypeId;
                        break;

                    case 'Particulier':
                    case 'particulier':
                    case 'Client':
                    default:
                        $newRoleName = 'particulier';
                        $newUserTypeId = $customerTypeId;
                        break;
                }

                // Mettre à jour le user_type_id
                if ($user->user_type_id !== $newUserTypeId) {
                    $user->user_type_id = $newUserTypeId;
                    $user->save();
                    echo "   🔄 user_type_id mis à jour: {$newUserTypeId}\n";
                }

                // Trouver le nouveau rôle
                $newRole = Role::where('name', $newRoleName)
                    ->where('user_type_id', $newUserTypeId)
                    ->first();

                if ($newRole) {
                    // Vérifier si l'association existe déjà
                    $exists = \DB::table('user_roles')
                        ->where('user_id', $user->id)
                        ->where('role_id', $newRole->id)
                        ->exists();

                    if (!$exists) {
                        \DB::table('user_roles')->insert([
                            'user_id' => $user->id,
                            'role_id' => $newRole->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        echo "   ✅ Nouveau rôle assigné: {$newRoleName} (ID: {$newRole->id})\n";
                    } else {
                        echo "   ℹ️  Rôle déjà assigné: {$newRoleName}\n";
                    }
                } else {
                    echo "   ❌ Erreur: Nouveau rôle '{$newRoleName}' non trouvé!\n";
                    $skippedCount++;
                }
            }

            echo "\n";
            $migratedCount++;
        }

        echo "\n✅ Migration terminée!\n";
        echo "   - {$migratedCount} utilisateurs migrés\n";
        if ($skippedCount > 0) {
            echo "   - {$skippedCount} erreurs détectées\n";
        }
    }
}
