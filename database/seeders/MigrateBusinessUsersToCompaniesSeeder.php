<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;

class MigrateBusinessUsersToCompaniesSeeder extends Seeder
{
    /**
     * Migrer les utilisateurs Business existants vers la nouvelle architecture avec Companies
     *
     * Ce seeder :
     * 1. Trouve tous les utilisateurs avec business_name rempli
     * 2. Crée une Company pour chacun
     * 3. Lie l'utilisateur à sa company via company_id
     */
    public function run(): void
    {
        echo "🔄 Migration des utilisateurs Business vers la table Companies...\n\n";

        // Récupérer tous les utilisateurs qui ont les rôles Business
        $businessUsers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Business Enterprise', 'Business Individual', 'business_enterprise', 'business_individual']);
        })->get();

        echo "📊 " . $businessUsers->count() . " utilisateurs Business trouvés\n\n";

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($businessUsers as $user) {
            try {
                // Skip si déjà lié à une company
                if ($user->company_id) {
                    echo "⏭️  Utilisateur #{$user->id} ({$user->email}) déjà lié à une company\n";
                    $skipped++;
                    continue;
                }

                // Skip si pas de business_name
                if (empty($user->business_name)) {
                    echo "⚠️  Utilisateur #{$user->id} ({$user->email}) - pas de business_name, création avec nom par défaut\n";
                    $businessName = $user->full_name . " Company";
                } else {
                    $businessName = $user->business_name;
                }

                // Déterminer le type de company selon le rôle
                $companyType = 'enterprise'; // par défaut
                if ($user->hasRole('Business Individual') || $user->hasRole('business_individual')) {
                    $companyType = 'individual';
                }

                // Créer la company
                $company = Company::create([
                    'business_name' => $businessName,
                    'business_email' => $user->business_email,
                    'business_description' => $user->business_description,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'city' => $user->city,
                    'company_type' => $companyType,
                    'is_verified' => $user->email_verified_at ? true : false,
                    'verified_at' => $user->email_verified_at,
                    'is_active' => $user->is_active,
                ]);

                // Lier l'utilisateur à la company
                $user->company_id = $company->id;
                $user->save();

                echo "✅ Utilisateur #{$user->id} ({$user->email}) → Company #{$company->id} ({$company->business_name})\n";
                $migrated++;

            } catch (\Exception $e) {
                echo "❌ Erreur pour utilisateur #{$user->id}: " . $e->getMessage() . "\n";
                $errors++;
            }
        }

        echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✅ Migration terminée!\n";
        echo "   - {$migrated} utilisateurs migrés\n";
        echo "   - {$skipped} utilisateurs ignorés (déjà migrés)\n";
        echo "   - {$errors} erreurs\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}
