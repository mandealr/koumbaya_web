<?php

namespace App\Jobs;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CancelPendingOrders implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('CancelPendingOrders job started');

        // Récupérer toutes les commandes en attente ou en attente de paiement depuis plus d'une heure
        $oneHourAgo = Carbon::now()->subHour();

        $pendingOrders = Order::whereIn('status', [
                OrderStatus::PENDING->value,
                OrderStatus::AWAITING_PAYMENT->value
            ])
            ->where('created_at', '<', $oneHourAgo)
            ->get();

        $cancelledCount = 0;

        foreach ($pendingOrders as $order) {
            try {
                // Vérifier si la commande a un paiement en cours
                $hasActivePendingPayment = $order->payment &&
                    $order->payment->status === 'pending' &&
                    $order->payment->created_at > Carbon::now()->subMinutes(5);

                // Ne pas annuler si un paiement est en cours (moins de 5 minutes)
                if ($hasActivePendingPayment) {
                    Log::info('Order skipped - active payment in progress', [
                        'order_number' => $order->order_number,
                        'payment_created' => $order->payment->created_at
                    ]);
                    continue;
                }

                // Annuler la commande
                $order->update([
                    'status' => OrderStatus::CANCELLED->value,
                    'notes' => ($order->notes ? $order->notes . "\n" : '') .
                        "Annulation automatique - Commande en attente depuis plus d'une heure (annulée le " .
                        Carbon::now()->format('d/m/Y à H:i') . ")"
                ]);

                // Si un paiement existe, le marquer comme échoué
                if ($order->payment) {
                    $order->payment->update([
                        'status' => 'failed',
                        'notes' => ($order->payment->notes ? $order->payment->notes . "\n" : '') .
                            "Paiement annulé automatiquement - Commande expirée"
                    ]);
                }

                $cancelledCount++;

                Log::info('Order automatically cancelled', [
                    'order_number' => $order->order_number,
                    'created_at' => $order->created_at,
                    'age_hours' => $order->created_at->diffInHours(Carbon::now())
                ]);

            } catch (\Exception $e) {
                Log::error('Error cancelling order', [
                    'order_number' => $order->order_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('CancelPendingOrders job completed', [
            'orders_found' => $pendingOrders->count(),
            'orders_cancelled' => $cancelledCount
        ]);
    }
}
