<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\LotteryTicket;

class ExpireOldOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-old {--dry-run : Show what would be expired without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire orders older than 1 hour that are not paid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Mode DRY RUN - Aucune modification ne sera effectuée');
        }

        // Trouver toutes les commandes expirées
        $expiredOrders = Order::expired()->with(['lottery', 'payments'])->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('✅ Aucune commande expirée trouvée');
            return 0;
        }

        $this->info("📋 {$expiredOrders->count()} commande(s) expirée(s) trouvée(s)");

        $expiredCount = 0;
        $ticketsCancelledCount = 0;

        foreach ($expiredOrders as $order) {
            $this->line("📦 Commande #{$order->order_number} (créée le {$order->created_at})");

            if (!$dryRun) {
                // Marquer la commande comme expirée
                if ($order->markAsExpired()) {
                    $expiredCount++;
                    
                    // Compter les tickets annulés pour cette commande
                    if ($order->type === Order::TYPE_LOTTERY && $order->lottery_id) {
                        $cancelledTickets = LotteryTicket::where('lottery_id', $order->lottery_id)
                            ->where('user_id', $order->user_id)
                            ->where('status', 'cancelled')
                            ->where('created_at', '>=', $order->created_at->subMinutes(5))
                            ->count();
                        
                        $ticketsCancelledCount += $cancelledTickets;
                        
                        if ($cancelledTickets > 0) {
                            $this->line("   🎫 {$cancelledTickets} ticket(s) annulé(s)");
                        }
                    }
                }
            } else {
                $this->line("   ⏰ Serait marquée comme expirée");
                
                // Compter les tickets qui seraient annulés
                if ($order->type === Order::TYPE_LOTTERY && $order->lottery_id) {
                    $ticketsToCancel = LotteryTicket::where('lottery_id', $order->lottery_id)
                        ->where('user_id', $order->user_id)
                        ->where('status', 'reserved')
                        ->where('created_at', '>=', $order->created_at->subMinutes(5))
                        ->count();
                    
                    if ($ticketsToCancel > 0) {
                        $this->line("   🎫 {$ticketsToCancel} ticket(s) seraient annulés");
                        $ticketsCancelledCount += $ticketsToCancel;
                    }
                }
            }
        }

        if (!$dryRun) {
            $this->info("✅ {$expiredCount} commande(s) marquée(s) comme expirées");
            if ($ticketsCancelledCount > 0) {
                $this->info("🎫 {$ticketsCancelledCount} ticket(s) annulé(s)");
            }
        } else {
            $this->info("ℹ️  {$expiredOrders->count()} commande(s) seraient expirées");
            if ($ticketsCancelledCount > 0) {
                $this->info("ℹ️  {$ticketsCancelledCount} ticket(s) seraient annulés");
            }
            $this->warn('Pour appliquer les modifications, exécutez sans --dry-run');
        }

        return 0;
    }
}