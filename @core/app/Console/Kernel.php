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
        Commands\ChampionsSeason::class,
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

        // Huduma Champions (monthly gamification) — times in EAT.
        //   hourly  : pending HP → confirmed after the refund/fraud window
        //   day 1   : month-end quality + loyalty bonuses for last season
        //   day 4   : finalize rankings → provisional Top Five (admin approves, announced day 5)
        $schedule->command('champions:season confirm')->hourly()->withoutOverlapping(10)->onOneServer();
        $schedule->command('champions:season sync')->everyTenMinutes()->withoutOverlapping(15)->onOneServer();
        $schedule->command('champions:season weekly')->weeklyOn(1, '00:30')->timezone('Africa/Dar_es_Salaam')->onOneServer();
        $schedule->command('champions:season nudges')->dailyAt('18:00')->timezone('Africa/Dar_es_Salaam')->onOneServer();
        $schedule->command('champions:season bonuses')->monthlyOn(1, '01:00')->timezone('Africa/Dar_es_Salaam')->onOneServer();
        $schedule->command('champions:season finalize')->monthlyOn(4, '02:00')->timezone('Africa/Dar_es_Salaam')->onOneServer();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
