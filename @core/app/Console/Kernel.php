<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SubscriptionExpireReminder::class,
        Commands\PromoteReferralRewards::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('package:subscription_expire')->daily();

        // Rafiki Rewards — promote pending rewards past their 14-day protection
        // window to "approved" so users can transfer them to their main wallet.
        // Runs hourly so a reward is available within ~1 hour of the window
        // ending, not up to 24 hours later.
        $schedule->command('referrals:promote-approved')
                 ->hourly()
                 ->withoutOverlapping(10)
                 ->onOneServer();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
