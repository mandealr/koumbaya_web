<?php

namespace App\Console\Commands;

use App\Mail\LotteryReminderEmail;
use App\Models\Lottery;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLotteryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lottery:send-reminders {type=24h : Type de rappel (24h ou 1h)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des rappels email pour les tirages de tombola approchants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        
        if (!in_array($type, ['24h', '1h'])) {
            $this->error('Type de rappel invalide. Utilisez: 24h ou 1h');
            return 1;
        }

        $now = now();
        $targetTime = $type === '24h' 
            ? $now->copy()->addHours(24)
            : $now->copy()->addHour();

        // Trouver les tombolas qui se terminent dans la plage de temps spécifiée
        $lotteries = Lottery::where('status', 'active')
            ->where('end_date', '>=', $targetTime->copy()->subMinutes(5))
            ->where('end_date', '<=', $targetTime->copy()->addMinutes(5))
            ->with(['product'])
            ->get();

        if ($lotteries->isEmpty()) {
            $this->info("Aucune tombola trouvée pour les rappels {$type}.");
            return 0;
        }

        $totalSent = 0;

        foreach ($lotteries as $lottery) {
            $this->info("Traitement de la tombola: {$lottery->lottery_number}");
            
            // Obtenir tous les participants de cette tombola
            $participants = User::whereHas('lotteryTickets', function ($query) use ($lottery) {
                $query->where('lottery_id', $lottery->id)
                      ->where('status', 'paid');
            })->get();

            foreach ($participants as $participant) {
                try {
                    Mail::to($participant->email)->send(
                        new LotteryReminderEmail($participant, $lottery, $type)
                    );
                    $totalSent++;
                } catch (\Exception $e) {
                    $this->error("Erreur envoi email pour {$participant->email}: {$e->getMessage()}");
                }
            }

            $this->info("- {$participants->count()} participants notifiés");
        }

        $this->info("Rappels {$type} envoyés: {$totalSent} emails pour {$lotteries->count()} tombola(s)");
        return 0;
    }
}
