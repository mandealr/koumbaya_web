<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MerchantRatingService;
use App\Models\User;

class RecalculateMerchantRatings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratings:recalculate
                            {--merchant= : ID d\'un marchand spécifique}
                            {--snapshot : Créer également les snapshots mensuels}
                            {--dry-run : Simuler sans sauvegarder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule les scores de notation des marchands';

    /**
     * Execute the console command.
     */
    public function handle(MerchantRatingService $ratingService): int
    {
        $this->info('=== Recalcul des notations marchands ===');
        $this->newLine();

        $merchantId = $this->option('merchant');
        $createSnapshot = $this->option('snapshot');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Mode simulation (dry-run) - Aucune modification ne sera sauvegardée');
            $this->newLine();
        }

        // Recalcul d'un marchand spécifique
        if ($merchantId) {
            return $this->recalculateSingleMerchant($ratingService, (int) $merchantId, $dryRun);
        }

        // Recalcul de tous les marchands
        return $this->recalculateAllMerchants($ratingService, $createSnapshot, $dryRun);
    }

    /**
     * Recalcule le score d'un seul marchand
     */
    private function recalculateSingleMerchant(MerchantRatingService $ratingService, int $merchantId, bool $dryRun): int
    {
        $merchant = User::find($merchantId);

        if (!$merchant) {
            $this->error("Marchand avec l'ID {$merchantId} non trouvé");
            return self::FAILURE;
        }

        if (!$merchant->isMerchant()) {
            $this->error("L'utilisateur {$merchantId} n'est pas un marchand");
            return self::FAILURE;
        }

        $this->info("Recalcul du score pour : {$merchant->first_name} {$merchant->last_name} (ID: {$merchantId})");

        if ($dryRun) {
            $this->info('Mode dry-run - Calcul simulé');
            return self::SUCCESS;
        }

        try {
            $rating = $ratingService->calculateAndUpdateScore($merchant, 'manual_recalc');

            $this->newLine();
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Score global', round($rating->overall_score, 1) . '/100'],
                    ['Score activité', round($rating->activity_score, 1) . '/100'],
                    ['Score qualité', round($rating->quality_score, 1) . '/100'],
                    ['Score fiabilité', round($rating->reliability_score, 1) . '/100'],
                    ['Badge', $rating->badge_info['label']],
                    ['Étoiles', $rating->stars_display],
                    ['Note moyenne', $rating->rating_display],
                    ['Nombre d\'avis', $rating->total_reviews],
                    ['Ventes complétées', $rating->completed_sales],
                ]
            );

            $this->newLine();
            $this->info('Score recalculé avec succès !');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erreur : {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Recalcule les scores de tous les marchands
     */
    private function recalculateAllMerchants(MerchantRatingService $ratingService, bool $createSnapshot, bool $dryRun): int
    {
        $this->info('Récupération des marchands...');

        $merchants = User::where(function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->whereIn('name', ['business_individual', 'business_enterprise', 'Business Individual', 'Business Enterprise']);
            });
        })->get();

        $total = $merchants->count();
        $this->info("Nombre de marchands trouvés : {$total}");
        $this->newLine();

        if ($total === 0) {
            $this->warn('Aucun marchand trouvé');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('Mode dry-run - Les {$total} marchands seraient recalculés');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($merchants as $merchant) {
            try {
                $ratingService->calculateAndUpdateScore($merchant, 'scheduled_recalc');
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'id' => $merchant->id,
                    'name' => $merchant->first_name . ' ' . $merchant->last_name,
                    'error' => $e->getMessage(),
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Créer les snapshots si demandé
        if ($createSnapshot) {
            $this->info('Création des snapshots mensuels...');
            $snapshotCount = $ratingService->createMonthlySnapshots();
            $this->info("Snapshots créés : {$snapshotCount}");
            $this->newLine();
        }

        // Résumé
        $this->info('=== Résumé ===');
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['Succès', $success],
                ['Échecs', $failed],
                ['Total', $total],
            ]
        );

        // Afficher les erreurs s'il y en a
        if (count($errors) > 0) {
            $this->newLine();
            $this->error('Erreurs rencontrées :');
            foreach ($errors as $error) {
                $this->line("  - [{$error['id']}] {$error['name']} : {$error['error']}");
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
