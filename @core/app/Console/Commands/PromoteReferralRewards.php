<?php

namespace App\Console\Commands;

use App\Referral;
use App\ReferralReward;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Promote referral rewards past their protection window from
 * "pending" to "approved" so they become available for transfer.
 *
 * Runs daily via the scheduler (see App\Console\Kernel::schedule).
 * Also safe to invoke manually:  php artisan referrals:promote-approved
 */
class PromoteReferralRewards extends Command
{
    protected $signature   = 'referrals:promote-approved
                              {--dry-run : Report what would change without touching the DB}';

    protected $description = 'Promote pending referral rewards to approved once their 14-day protection window has passed.';

    public function handle(): int
    {
        $now    = now();
        $dryRun = (bool) $this->option('dry-run');

        $this->line("[".$now->format('Y-m-d H:i:s')."] referrals:promote-approved" . ($dryRun ? ' (dry-run)' : ''));

        // Rewards that are pending AND past their protection window.
        // Cash rewards for the referrer are the only ones with a protection window;
        // welcome credits are approved on creation so they're already skipped.
        $query = ReferralReward::query()
            ->where('status', 'pending')
            ->whereNotNull('protection_ends_at')
            ->where('protection_ends_at', '<=', $now);

        $count = (int) $query->count();
        $total = (float) $query->sum('amount');

        $this->line("  · found {$count} rewards ready to promote ({$total} TZS total)");

        if ($count === 0) return self::SUCCESS;

        if ($dryRun) {
            $this->info('  · dry-run — no changes made');
            return self::SUCCESS;
        }

        // Bulk update — atomic, fast, and deterministic.
        $updated = ReferralReward::query()
            ->where('status', 'pending')
            ->whereNotNull('protection_ends_at')
            ->where('protection_ends_at', '<=', $now)
            ->update([
                'status'      => 'approved',
                'approved_at' => $now,
                'updated_at'  => $now,
            ]);

        // Also promote the parent referral row to 'approved' if all its rewards
        // are now approved (or paid) — nothing pending, nothing rejected.
        $touchedReferralIds = ReferralReward::query()
            ->where('approved_at', $now)
            ->distinct()
            ->pluck('referral_id')
            ->filter()
            ->all();

        $promotedReferrals = 0;
        foreach ($touchedReferralIds as $referralId) {
            $referral = Referral::find($referralId);
            if (!$referral || $referral->status !== 'qualifying') continue;

            $stillPending = ReferralReward::where('referral_id', $referralId)
                ->whereIn('status', ['pending', 'qualifying'])
                ->exists();

            if (!$stillPending) {
                $referral->update(['status' => 'approved']);
                $promotedReferrals++;
            }
        }

        $msg = "  · promoted {$updated} rewards to approved ({$total} TZS), lifted {$promotedReferrals} referrals to approved";
        $this->info($msg);
        Log::info('[Rafiki Rewards] '.$msg);

        return self::SUCCESS;
    }
}
