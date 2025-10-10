<?php

namespace App\Console\Commands;

use App\Jobs\CancelPendingOrders;
use Illuminate\Console\Command;

class CancelPendingOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Annuler automatiquement les commandes en attente depuis plus d\'une heure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Démarrage de l\'annulation des commandes en attente...');

        // Dispatcher le job
        CancelPendingOrders::dispatch();

        $this->info('Job d\'annulation des commandes en attente dispatché avec succès.');

        return Command::SUCCESS;
    }
}
