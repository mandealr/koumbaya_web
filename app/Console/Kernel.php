<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Tirages de tombola - 3 fois par jour (heures fixes)
        $schedule->command('lottery:draw')
            ->dailyAt('12:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-draws.log'));

        $schedule->command('lottery:draw')
            ->dailyAt('16:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-draws.log'));

        $schedule->command('lottery:draw')
            ->dailyAt('20:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-draws.log'));

        // Tombolas expirées et remboursements automatiques - 1 fois par jour à 3h
        $schedule->command('lottery:process-expired')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/expired-lotteries.log'));

        // Expiration commandes anciennes - toutes les heures (au lieu de 15 min)
        $schedule->command('orders:expire-old')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/expired-orders.log'));

        // Rappels tombola 24h avant tirage - tous les jours à 8h
        $schedule->command('lottery:send-reminders 24h')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-reminders.log'));

        // Rappels tombola 1h avant tirage - toutes les 2 heures (au lieu de chaque heure)
        $schedule->command('lottery:send-reminders 1h')
            ->everyTwoHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-reminders.log'));

        // Annuler commandes en attente depuis +1h - toutes les heures (au lieu de 30 min)
        $schedule->command('orders:cancel-pending')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/cancelled-orders.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}