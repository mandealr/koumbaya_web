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
        // Process lottery draws every day at 14:00 (2 PM)
        $schedule->command('lottery:draw')
            ->dailyAt('14:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-draws.log'));

        // Also run at 20:00 (8 PM) for lotteries ending in the evening
        $schedule->command('lottery:draw')
            ->dailyAt('20:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-draws.log'));

        // Process automatic refunds daily at 3 AM
        $schedule->command('refunds:process')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/refunds.log'));

        // Expire old orders every 15 minutes
        $schedule->command('orders:expire-old')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/expired-orders.log'));

        // Envoyer rappels tombola 24h avant tirage - tous les jours à 8h
        $schedule->command('lottery:send-reminders 24h')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-reminders.log'));

        // Envoyer rappels tombola 1h avant tirage - toutes les heures
        $schedule->command('lottery:send-reminders 1h')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/lottery-reminders.log'));
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