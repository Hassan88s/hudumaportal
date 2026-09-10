<?php

namespace App\Services;

use App\User;
use App\StaticOption;
use App\Referral;
use App\ReferralReward;
use App\ReferralClick;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * ReferralService — single source of truth for the Rafiki Rewards program.
 *
 * Replaces scattered referral logic previously duplicated across:
 *   - Auth\RegisterController
 *   - ServicePaymentController
 *   - Frontend\SellerController
 *   - Frontend\ServiceListController
 *
 * Every reward creation is idempotent (via referral_rewards.idempotency_key)
 * so a controller can safely call the same method twice without double-crediting.
 */
class ReferralService
{
    /* -----------------------------------------------------------------
     |  Setting helpers
     | ----------------------------------------------------------------- */

    private function opt(string $name, $default = 0)
    {
        $row = StaticOption::where('option_name', $name)->first();
        return $row ? $row->option_value : $default;
    }

    public function enabled(): bool
    {
        return (int) $this->opt('referral_enabled', 1) === 1;
    }

    /* -----------------------------------------------------------------
     |  Code generation & lookup
     | ----------------------------------------------------------------- */

    /**
     * Generate a unique referral code. Format: HP + 6 uppercase alphanumerics.
     * Retries up to 5 times against the unique index.
     */
    public function generateUniqueCode(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $code = 'HP' . strtoupper(Str::random(6));
            if (!User::where('referral_code', $code)->exists()) {
                return $code;
            }
        }
        // Fallback: add timestamp entropy
        return 'HP' . strtoupper(Str::random(4)) . substr((string) time(), -2);
    }

    public function findReferrerByCode(?string $code): ?User
    {
        if (empty($code)) return null;
        return User::where('referral_code', $code)->first();
    }

    /* -----------------------------------------------------------------
     |  Click tracking (for /r/<code> landing)
     | ----------------------------------------------------------------- */

    public function trackClick(string $code, Request $request, ?string $channel = null): void
    {
        $referrer = $this->findReferrerByCode($code);
        ReferralClick::create([
            'code'               => $code,
            'referrer_id'        => optional($referrer)->id,
            'ip_address'         => $request->ip(),
            'user_agent'         => substr((string) $request->userAgent(), 0, 500),
            'device_fingerprint' => $request->cookie('rf_fp'),
            'channel'            => $channel,
            'created_at'         => now(),
        ]);
    }

    /* -----------------------------------------------------------------
     |  Registration attribution
     | ----------------------------------------------------------------- */

    /**
     * Called from RegisterController AFTER the user row exists.
     * Creates the referrals row and fires the Stage-1 reward.
     * Safe against self-referrals and duplicate calls.
     */
    public function attachReferralOnSignup(User $newUser, ?string $codeUsed, ?Request $request = null): ?Referral
    {
        if (!$this->enabled() || empty($newUser->referred_by)) {
            return null;
        }

        // Self-referral guard
        if ((int) $newUser->referred_by === (int) $newUser->id) {
            User::where('id', $newUser->id)->update(['referred_by' => null]);
            return null;
        }

        // Duplicate guard
        $existing = Referral::where('referred_user_id', $newUser->id)->first();
        if ($existing) return $existing;

        // Determine track (provider = seller = user_type 2, else client)
        $track = ((int) $newUser->user_type === 2) ? 'provider' : 'client';

        $referral = Referral::create([
            'referrer_id'        => $newUser->referred_by,
            'referred_user_id'   => $newUser->id,
            'code_used'          => $codeUsed,
            'track'              => $track,
            'source'             => 'link',
            'ip_address'         => $request ? $request->ip() : null,
            'device_fingerprint' => $request ? $request->cookie('rf_fp') : null,
            'user_agent'         => $request ? substr((string) $request->userAgent(), 0, 500) : null,
            'stage1_at'          => now(),
            'status'             => 'qualifying',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // Stage-1 pending reward (uses legacy sign_up_points for backward-compat if new key missing)
        $amount = $track === 'provider'
            ? $this->opt('referral_stage1_provider_amount', $this->opt('sign_up_points', 500))
            : $this->opt('sign_up_points', 100);

        $this->creditReferrerPending(
            $referral,
            'stage1_signup',
            (float) $amount,
            'Referral Bonus (Sign-up)'
        );

        // Client welcome credit (given to the NEW client, not the referrer)
        if ($track === 'client') {
            $welcome = (float) $this->opt('referral_client_welcome_credit', 0);
            if ($welcome > 0) {
                $this->creditNewUserPromo(
                    $referral,
                    $newUser->id,
                    'client_welcome_credit',
                    $welcome,
                    'Welcome credit — invited by a friend'
                );
            }
        }

        return $referral;
    }

    /* -----------------------------------------------------------------
     |  Milestone triggers (call from controllers where the event happens)
     | ----------------------------------------------------------------- */

    /**
     * Provider Stage-2: fires when a referred SELLER publishes their first service.
     * Backward-compat entry point for SellerController::store() &
     * ServiceListController::store(). Idempotent.
     */
    public function onSellerFirstService(User $seller): void
    {
        if (!$this->enabled() || empty($seller->referred_by)) return;

        $referral = Referral::where('referred_user_id', $seller->id)->first();
        if (!$referral || !empty($referral->stage2_at)) return;

        // Only fire if this really is the first service
        $count = DB::table('services')->where('seller_id', $seller->id)->count();
        if ($count !== 1) return;

        $referral->update(['stage2_at' => now(), 'track' => 'provider']);

        $cash   = (float) $this->opt('referral_stage2_provider_cash', $this->opt('first_order_points', 1000));
        $credit = (float) $this->opt('referral_stage2_provider_credit', 0);

        if ($cash > 0) {
            $this->creditReferrerPending($referral, 'stage2_seller_first_service_cash', $cash, 'Referral Bonus (Referred Provider Listed a Service)');
        }
        if ($credit > 0) {
            $this->creditNewUserPromo($referral, $seller->id, 'stage2_seller_promo_credit', $credit, 'Welcome credit — thanks for listing your first service');
        }
    }

    /**
     * Client Stage-2: fires when a referred BUYER places their first paid order.
     * Called from ServicePaymentController after payment success. Idempotent.
     */
    public function onBuyerFirstOrder(User $buyer): void
    {
        if (!$this->enabled() || empty($buyer->referred_by)) return;

        $referral = Referral::where('referred_user_id', $buyer->id)->first();
        if (!$referral || !empty($referral->stage2_at)) return;

        // Only fire on the first order (matches legacy behaviour)
        $count = DB::table('orders')->where('buyer_id', $buyer->id)->count();
        if ($count !== 1) return;

        $referral->update(['stage2_at' => now()]);

        $amount = (float) $this->opt('referral_client_first_booking', $this->opt('first_purchase_points', 750));
        $this->creditReferrerPending($referral, 'stage2_buyer_first_order', $amount, 'Referral Bonus (Referred Client First Booking)');
    }

    /**
     * Client Stage-3: fires when a referred buyer places their second booking within 60 days.
     */
    public function onBuyerSecondOrderWithin60Days(User $buyer): void
    {
        if (!$this->enabled() || empty($buyer->referred_by)) return;

        $referral = Referral::where('referred_user_id', $buyer->id)->first();
        if (!$referral || !empty($referral->stage3_at)) return;

        $count = DB::table('orders')->where('buyer_id', $buyer->id)->count();
        if ($count !== 2) return;

        // Was the referral within 60 days ago?
        if (Carbon::parse($referral->created_at)->diffInDays(now()) > 60) return;

        $referral->update(['stage3_at' => now(), 'status' => 'approved']);

        $amount = (float) $this->opt('referral_client_second_booking', 750);
        $this->creditReferrerPending($referral, 'stage3_buyer_second_order', $amount, 'Referral Bonus (Referred Client Second Booking)');
    }

    /**
     * Provider Stage-3: fires when a referred seller gets 2nd order OR buys a paid subscription.
     */
    public function onProviderStage3(User $seller, string $trigger = 'second_order'): void
    {
        if (!$this->enabled() || empty($seller->referred_by)) return;

        $referral = Referral::where('referred_user_id', $seller->id)->first();
        if (!$referral || !empty($referral->stage3_at)) return;

        $referral->update(['stage3_at' => now(), 'status' => 'approved']);

        $amount = (float) $this->opt('referral_stage3_provider_amount', 1500);
        $this->creditReferrerPending($referral, 'stage3_provider_' . $trigger, $amount, 'Referral Bonus (Provider Stage 3 — ' . $trigger . ')');
    }

    /* -----------------------------------------------------------------
     |  Reward crediting (idempotent — the heart of the ledger)
     | ----------------------------------------------------------------- */

    /**
     * Pending cash reward for the REFERRER.
     * Sits in the SEPARATE referral wallet (referral_rewards table) — NOT credited
     * to the main wallet until the referrer explicitly transfers approved earnings.
     * Lifecycle: pending -> approved (protection window ends) -> paid (transferred).
     */
    protected function creditReferrerPending(Referral $referral, string $event, float $amount, string $reason): ?ReferralReward
    {
        if ($amount <= 0) return null;

        $idem = $event . ':referral#' . $referral->id;

        // Idempotency check
        if (ReferralReward::where('idempotency_key', $idem)->exists()) {
            return null;
        }

        $protectionDays = (int) $this->opt('referral_protection_days', 14);

        return ReferralReward::create([
            'referral_id'        => $referral->id,
            'user_id'            => $referral->referrer_id,
            'event'              => $event,
            'amount'             => $amount,
            'currency'           => 'TZS',
            'type'               => 'cash',
            'status'             => 'pending',
            'reason'             => $reason,
            'protection_ends_at' => now()->addDays($protectionDays),
            'idempotency_key'    => $idem,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    /**
     * Promo credit for the NEWLY referred user (e.g. client welcome TZS 1,000).
     * Same pattern — idempotent + backed by a wallet row.
     */
    protected function creditNewUserPromo(Referral $referral, int $userId, string $event, float $amount, string $reason): ?ReferralReward
    {
        if ($amount <= 0) return null;

        $idem = $event . ':referral#' . $referral->id;

        return DB::transaction(function () use ($referral, $userId, $event, $amount, $reason, $idem) {
            if (ReferralReward::where('idempotency_key', $idem)->exists()) {
                return null;
            }

            $reward = ReferralReward::create([
                'referral_id'     => $referral->id,
                'user_id'         => $userId,
                'event'           => $event,
                'amount'          => $amount,
                'currency'        => 'TZS',
                'type'            => 'credit',
                'status'          => 'approved',
                'reason'          => $reason,
                'approved_at'     => now(),
                'idempotency_key' => $idem,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $wallet = Wallet::firstOrCreate(
                ['buyer_id' => $userId],
                ['balance'  => 0, 'status' => 1]
            );
            $wallet->increment('balance', $amount);

            $wh = WalletHistory::create([
                'buyer_id'        => $userId,
                'amount'          => $amount,
                'payment_gateway' => 'Referral Welcome Credit',
                'payment_status'  => 'complete',
                'status'          => 1,
                'Action'          => $reason,
            ]);

            $reward->update(['wallet_history_id' => $wh->id]);

            return $reward;
        });
    }

    /* -----------------------------------------------------------------
     |  Dashboard queries
     | ----------------------------------------------------------------- */

    /**
     * Referral Wallet stats (SEPARATE from the main Wallet).
     *
     *   pending    — cash rewards still inside the 14-day protection window
     *   available  — cash rewards past protection AND not yet transferred (ready to move to main wallet)
     *   mainWallet — the user's main wallet balance (for context / withdrawal target)
     *   lifetime   — total ever earned via referrals (approved + paid)
     *   thisMonth  — earned this calendar month
     *   referred   — count of people referred
     *   minWithdraw — configured minimum before transfer is allowed
     */
    public function statsForUser(int $userId): array
    {
        $referred = Referral::where('referrer_id', $userId)->count();

        $pending = (float) ReferralReward::where('user_id', $userId)
            ->where('type', 'cash')
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('protection_ends_at')
                  ->orWhere('protection_ends_at', '>', now());
            })
            ->sum('amount');

        // "available" = approved OR (pending with expired protection). Not yet transferred.
        $available = (float) ReferralReward::where('user_id', $userId)
            ->where('type', 'cash')
            ->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'pending')
                         ->where('protection_ends_at', '<=', now());
                  });
            })
            ->sum('amount');

        $mainWallet = (float) (Wallet::where('buyer_id', $userId)->value('balance') ?? 0);

        $lifetime = (float) ReferralReward::where('user_id', $userId)
            ->where('type', 'cash')
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        // "paid" rewards (already transferred to main wallet) should count in lifetime too — add them explicitly
        // (already covered by whereIn above)

        $thisMonth = (float) ReferralReward::where('user_id', $userId)
            ->where('type', 'cash')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $minWithdraw = (float) $this->opt('referral_min_withdrawal', 5000);

        return compact('referred', 'pending', 'available', 'mainWallet', 'lifetime', 'thisMonth', 'minWithdraw');
    }

    /* -----------------------------------------------------------------
     |  Reversal (refund handling within the protection window)
     | ----------------------------------------------------------------- */

    /**
     * Reverse any referral rewards tied to a buyer's order that was refunded
     * or cancelled inside the protection window. Idempotent — safe to call
     * from refund/cancel flows without checking upfront.
     *
     * Behaviour:
     *  - Finds the referral row where referred_user_id = $buyerId
     *  - If it exists and its stage2/stage3 rewards are still "pending" (i.e.
     *    inside the protection window), marks them "rejected" with a reason
     *  - If the reward was already "paid" (transferred to main wallet), we do
     *    NOT claw back — that money is out. Admin can do it manually.
     *  - The referral row itself moves to "rejected" if all its rewards are
     *    now rejected/paid and none remain pending or approved.
     *
     * @param  int         $buyerId  the buyer whose order was refunded
     * @param  string|null $reason   human-readable — stored on each reward
     * @return int                    number of rewards reversed
     */
    public function reverseRewardsForBuyerRefund(int $buyerId, ?string $reason = null): int
    {
        if (!$this->enabled()) return 0;

        $referral = Referral::where('referred_user_id', $buyerId)->first();
        if (!$referral) return 0;

        return DB::transaction(function () use ($referral, $reason) {
            // Only reverse rewards still in protection (pending) — never touch
            // approved-and-already-transferred (paid) money, and never re-reject
            // rows already rejected.
            $rows = ReferralReward::where('referral_id', $referral->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) return 0;

            $now = now();
            $note = $reason ?: __('Order refunded within protection window');

            foreach ($rows as $row) {
                $row->update([
                    'status'       => 'rejected',
                    'rejected_at'  => $now,
                    'reason'       => ($row->reason ? $row->reason . ' — ' : '') . $note,
                    'updated_at'   => $now,
                ]);
            }

            // Update the referral status if nothing salvageable is left.
            $stillLive = ReferralReward::where('referral_id', $referral->id)
                ->whereIn('status', ['pending', 'qualifying', 'approved', 'paid'])
                ->exists();
            if (!$stillLive) {
                $referral->update([
                    'status'           => 'rejected',
                    'rejection_reason' => $note,
                ]);
            }

            return $rows->count();
        });
    }

    /* -----------------------------------------------------------------
     |  Transfer to main wallet
     | ----------------------------------------------------------------- */

    /**
     * Move all currently-available referral cash into the user's main wallet.
     * Enforces the minimum-withdrawal threshold (default TZS 5,000).
     *
     * @return array{ok:bool,message:string,amount?:float}
     */
    public function transferToMainWallet(int $userId): array
    {
        $stats = $this->statsForUser($userId);
        $amount = $stats['available'];
        $min = $stats['minWithdraw'];

        if ($amount <= 0) {
            return ['ok' => false, 'message' => __('No referral earnings available to transfer.')];
        }
        if ($amount < $min) {
            return ['ok' => false, 'message' => __('Minimum transfer is :min TZS. You currently have :avail TZS available.', [
                'min'   => number_format($min, 0),
                'avail' => number_format($amount, 0),
            ])];
        }

        return DB::transaction(function () use ($userId, $amount) {
            // Grab every reward eligible for transfer (approved OR expired-pending)
            $rewards = ReferralReward::where('user_id', $userId)
                ->where('type', 'cash')
                ->where(function ($q) {
                    $q->where('status', 'approved')
                      ->orWhere(function ($q2) {
                          $q2->where('status', 'pending')
                             ->where('protection_ends_at', '<=', now());
                      });
                })
                ->lockForUpdate()
                ->get();

            if ($rewards->isEmpty()) {
                return ['ok' => false, 'message' => __('No referral earnings available to transfer.')];
            }

            $sum = (float) $rewards->sum('amount');

            // Credit main wallet
            $wallet = Wallet::firstOrCreate(
                ['buyer_id' => $userId],
                ['balance'  => 0, 'status' => 1]
            );
            $wallet->increment('balance', $sum);

            $wh = WalletHistory::create([
                'buyer_id'        => $userId,
                'amount'          => $sum,
                'payment_gateway' => 'Referral Wallet Transfer',
                'payment_status'  => 'complete',
                'status'          => 1,
                'Action'          => __('Transferred from Referral Wallet'),
            ]);

            // Mark all consumed rewards as paid + link the wallet_history row
            ReferralReward::whereIn('id', $rewards->pluck('id'))->update([
                'status'            => 'paid',
                'paid_at'           => now(),
                'wallet_history_id' => $wh->id,
                'updated_at'        => now(),
            ]);

            return [
                'ok'      => true,
                'message' => __(':amount TZS transferred to your main wallet.', ['amount' => number_format($sum, 0)]),
                'amount'  => $sum,
            ];
        });
    }
}
