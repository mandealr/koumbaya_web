<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthenticationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Configure l'authentification 2FA et les documents d'identité
     * pour les utilisateurs de test (optionnel)
     */
    public function run(): void
    {
        // Les tables 2FA et documents d'identité ne sont pas encore créées
        // Ce seeder sera fonctionnel une fois les migrations appropriées créées
        echo "⚠️  Seeder d'authentification désactivé - tables non disponibles.\n";
        echo "ℹ️  Tables nécessaires : two_factor_settings, identity_documents, business_profiles\n";
        echo "✅ Seeder d'authentification terminé (pas d'action).\n";
    }

    /**
     * Configure 2FA pour les customers (obligatoire selon spécification)
     */
    private function setupTwoFactorAuth()
    {
        $customerEmails = [
            'business@koumbaya.ga',
            'business2@koumbaya.ga', 
            'particulier1@koumbaya.ga',
            'particulier2@koumbaya.ga'
        ];

        foreach ($customerEmails as $email) {
            $user = DB::table('users')->where('email', $email)->first();
            
            if ($user) {
                // Simuler 2FA activé
                DB::table('two_factor_settings')->insert([
                    'user_id' => $user->id,
                    'method' => 'sms',
                    'secret' => encrypt('DEMO2FA' . $user->id), // Demo secret
                    'backup_codes' => json_encode([
                        '12345678', '87654321', '11111111', '22222222', '33333333'
                    ]),
                    'enabled' => true,
                    'verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "📱 2FA configuré pour " . count($customerEmails) . " customers.\n";
    }

    /**
     * Configure les documents d'identité (obligatoire pour customers)
     */
    private function setupIdentityDocuments()
    {
        $customerData = [
            'business@koumbaya.ga' => [
                'type' => 'national_id',
                'number' => 'GA123456789',
                'status' => 'verified'
            ],
            'business2@koumbaya.ga' => [
                'type' => 'passport',
                'number' => 'GA987654321', 
                'status' => 'verified'
            ],
            'particulier1@koumbaya.ga' => [
                'type' => 'national_id',
                'number' => 'GA555666777',
                'status' => 'verified'
            ],
            'particulier2@koumbaya.ga' => [
                'type' => 'drivers_license',
                'number' => 'GA444333222',
                'status' => 'pending' // Exemple de vérification en cours
            ]
        ];

        foreach ($customerData as $email => $docData) {
            $user = DB::table('users')->where('email', $email)->first();
            
            if ($user) {
                DB::table('identity_documents')->insert([
                    'user_id' => $user->id,
                    'document_type' => $docData['type'],
                    'document_number' => $docData['number'],
                    'verification_status' => $docData['status'],
                    'document_path' => '/storage/documents/demo/' . $docData['number'] . '.pdf',
                    'submitted_at' => now(),
                    'verified_at' => $docData['status'] === 'verified' ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "📄 Documents d'identité configurés pour " . count($customerData) . " customers.\n";
    }
}