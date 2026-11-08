<?php

namespace App\Services;

use App\SpinWheelSpin;
use Illuminate\Support\Str;

/**
 * All Spin-&-Win rules live here so the frontend, the spin endpoint and the
 * admin page share one source of truth. The outcome is decided SERVER-SIDE
 * (never trusted from the browser), which is what makes "the first try always
 * lands on Try Again, the second try can win" reliable and un-cheatable.
 */
class SpinWheelService
{
    /**
     * How long an unpaid gateway order may hold a reward code before the code
     * is given back. Long enough that a buyer who is slow at the payment page
     * is never affected, short enough that an abandoned checkout frees the
     * prize the same session.
     */
    const ABANDONED_ORDER_HOURS = 2;

    /** What a code was spent on. Buyers book services; sellers buy subscriptions. */
    const CONTEXT_ORDER        = 'service_order';
    const CONTEXT_SUBSCRIPTION = 'subscription';

    /**
     * Shipped defaults, used until an admin saves their own config.
     *
     * Nothing here exceeds 10%, because the discount is funded out of the
     * admin commission and 10% is the default commission rate. A segment
     * worth more than the commission would be trimmed at checkout, so the
     * wheel would advertise a prize it cannot pay.
     */
    const DEFAULT_SEGMENTS = [
        ['label' => 'Try Again', 'type' => 'nothing',  'value' => 0,  'weight' => 28, 'color' => '#5a6488', 'emoji' => '🔁'],
        ['label' => '2% OFF',    'type' => 'discount', 'value' => 2,  'weight' => 24, 'color' => '#39d0c9', 'emoji' => '🏷️'],
        ['label' => '4% OFF',    'type' => 'discount', 'value' => 4,  'weight' => 18, 'color' => '#4c8dff', 'emoji' => '🔖'],
        ['label' => '5% OFF',    'type' => 'discount', 'value' => 5,  'weight' => 12, 'color' => '#a66bff', 'emoji' => '💎'],
        ['label' => 'Try Again', 'type' => 'nothing',  'value' => 0,  'weight' => 20, 'color' => '#6b7390', 'emoji' => '😅'],
        ['label' => '6% OFF',    'type' => 'discount', 'value' => 6,  'weight' => 8,  'color' => '#ff5d7a', 'emoji' => '🔥'],
        ['label' => '8% OFF',    'type' => 'discount', 'value' => 8,  'weight' => 4,  'color' => '#ffcf4d', 'emoji' => '🏆'],
        ['label' => '10% OFF',   'type' => 'discount', 'value' => 10, 'weight' => 2,  'color' => '#ff8a54', 'emoji' => '🎉'],
    ];

    public function settings(): array
    {
        return [
            'enabled'          => (int) get_static_option('spin_wheel_enabled') === 1,
            'max_tries'        => max(1, (int) (get_static_option('spin_wheel_max_tries') ?: 2)),
            'force_first_lose' => get_static_option('spin_wheel_force_first_lose') === '' ? true : ((int) get_static_option('spin_wheel_force_first_lose') === 1),
            'expiry_days'      => max(0, (int) (get_static_option('spin_wheel_code_expiry_days') ?? 0)),
            'title'            => get_static_option('spin_wheel_title') ?: 'Spin & Win',
            'subtitle'         => get_static_option('spin_wheel_subtitle') ?: 'Give the wheel a spin for a chance to win a discount on your next order.',
        ];
    }

    /** Normalised segment list (always has the keys the wheel needs). */
    public function segments(): array
    {
        $raw = json_decode((string) get_static_option('spin_wheel_segments'), true);
        if (! is_array($raw) || count($raw) < 2) {
            $raw = self::DEFAULT_SEGMENTS;
        }

        $out = [];
        foreach ($raw as $s) {
            $type  = $s['type'] ?? 'nothing';
            $value = (float) ($s['value'] ?? 0);
            $out[] = [
                'label'  => (string) ($s['label'] ?? 'Reward'),
                'type'   => $type,
                'value'  => $value,
                'weight' => max(0, (float) ($s['weight'] ?? 1)),
                'color'  => $s['color'] ?? '#5a6488',
                'emoji'  => $s['emoji'] ?? ($type === 'discount' ? '🏷️' : '🔁'),
                'is_win' => $type !== 'nothing' && $value > 0,
            ];
        }
        return $out;
    }

    /** Reasons a user can earn extra spins. */
    const BONUS_REFERRAL_SIGNUP  = 'referral_signup';   // someone you invited joined
    const BONUS_REFERRAL_WELCOME = 'referral_welcome';  // you joined via an invite

    public function triesUsed(int $userId): int
    {
        return SpinWheelSpin::where('user_id', $userId)->count();
    }

    /** Extra spins this user has earned on top of the standard allowance. */
    public function bonusSpins(int $userId): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('spin_wheel_bonus_spins')
            ->where('user_id', $userId)
            ->sum('spins');
    }

    /** The user's whole allowance: the standard tries plus anything earned. */
    public function totalTries(int $userId): int
    {
        return $this->settings()['max_tries'] + $this->bonusSpins($userId);
    }

    public function triesLeft(int $userId): int
    {
        return max(0, $this->totalTries($userId) - $this->triesUsed($userId));
    }

    /**
     * Award extra spins. Idempotent: the unique key on
     * (user_id, reason, source_user_id) means the same referral cannot pay out
     * twice however many times the signup hook runs. Returns true if this call
     * is what granted them.
     */
    public function grantBonus(int $userId, string $reason, int $sourceUserId = 0, int $spins = 1): bool
    {
        if ($userId <= 0 || $spins < 1) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::table('spin_wheel_bonus_spins')->insertOrIgnore([
            'user_id'        => $userId,
            'reason'         => $reason,
            'source_user_id' => $sourceUserId,
            'spins'          => $spins,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]) === 1;
    }

    /** Referral settings, read once so callers do not repeat the lookups. */
    public function referralSettings(): array
    {
        return [
            'enabled'  => (int) get_static_option('spin_wheel_referral_enabled') === 1,
            'referrer' => max(0, (int) get_static_option('spin_wheel_referral_spins_referrer')),
            'invitee'  => max(0, (int) get_static_option('spin_wheel_referral_spins_invitee')),
        ];
    }

    /**
     * Both sides of a referral get a spin: the person who invited, and the
     * person who joined. Never throws — a signup must not fail because a
     * bonus spin could not be written.
     */
    public function grantReferralSpins(int $referrerId, int $newUserId): void
    {
        try {
            $cfg = $this->referralSettings();
            if (! $cfg['enabled'] || $referrerId <= 0 || $newUserId <= 0 || $referrerId === $newUserId) {
                return;
            }

            if ($cfg['referrer'] > 0) {
                $this->grantBonus($referrerId, self::BONUS_REFERRAL_SIGNUP, $newUserId, $cfg['referrer']);
            }
            if ($cfg['invitee'] > 0) {
                $this->grantBonus($newUserId, self::BONUS_REFERRAL_WELCOME, $referrerId, $cfg['invitee']);
            }
        } catch (\Throwable $e) {
            \Log::warning('[Spin & Win] referral spins failed: ' . $e->getMessage(), [
                'referrer_id' => $referrerId, 'new_user_id' => $newUserId,
            ]);
        }
    }

    /**
     * Run one spin for a user. Returns a structured result; never throws for
     * the ordinary "no tries left / disabled" cases.
     */
    public function spin(int $userId): array
    {
        $settings = $this->settings();
        $segments = $this->segments();

        if (! $settings['enabled']) {
            return ['ok' => false, 'reason' => 'disabled', 'tries_left' => 0];
        }

        // The allowance includes spins earned by inviting people, not just the
        // configured maximum.
        $allowance = $settings['max_tries'] + $this->bonusSpins($userId);

        $used = $this->triesUsed($userId);
        if ($used >= $allowance) {
            return ['ok' => false, 'reason' => 'no_tries', 'tries_left' => 0];
        }

        $tryNumber = $used + 1;

        // First try is forced to a losing ("Try Again") segment when enabled.
        $forceLose = $settings['force_first_lose'] && $tryNumber === 1;
        $index     = $forceLose ? $this->pickLosingIndex($segments) : $this->pickWeightedIndex($segments);
        $segment   = $segments[$index];
        $isWin     = (bool) $segment['is_win'];

        $code      = null;
        $expiresAt = null;
        if ($isWin && $segment['type'] === 'discount') {
            $code = 'SPIN-' . strtoupper(Str::random(6));

            // A prize with no deadline is a prize nobody hurries to use, and
            // it leaves the discount owed indefinitely. 0 days means no expiry.
            if ($settings['expiry_days'] > 0) {
                $expiresAt = now()->addDays($settings['expiry_days']);
            }
        }

        SpinWheelSpin::create([
            'user_id'       => $userId,
            'try_number'    => $tryNumber,
            'segment_label' => $segment['label'],
            'result_type'   => $segment['type'],
            'result_value'  => $segment['value'],
            'is_win'        => $isWin,
            'code'          => $code,
            'expires_at'    => $expiresAt,
        ]);

        return [
            'ok'         => true,
            'index'      => $index,
            'label'      => $segment['label'],
            'emoji'      => $segment['emoji'],
            'is_win'     => $isWin,
            'type'       => $segment['type'],
            'value'      => $segment['value'],
            'code'       => $code,
            'expires_at' => $expiresAt ? $expiresAt->toDateString() : null,
            'expiry_days'=> $expiresAt ? $settings['expiry_days'] : 0,
            'tries_left' => max(0, $allowance - $tryNumber),
        ];
    }

    /**
     * Resolve a code typed at checkout into the winning spin that owns it.
     * Returns null unless the code is a real, unspent win belonging to THIS
     * user — a code from somebody else's account is treated as unknown.
     */
    public function findRedeemableCode(?string $code, ?int $userId): ?SpinWheelSpin
    {
        $code = trim((string) $code);
        if ($code === '' || empty($userId)) {
            return null;
        }

        $spin = SpinWheelSpin::where('code', $code)->where('user_id', $userId)->first();
        if (! $spin) {
            return null;
        }

        // A code spent on an order that never actually happened is handed back,
        // so a buyer who closes the tab at the payment gateway does not lose
        // their prize. Checked here rather than on a schedule: it costs one
        // query, only for codes that are already marked used.
        if ($spin->used_at && $this->releaseIfOrderDead($spin)) {
            $spin->refresh();
        }

        return $spin->isRedeemable() ? $spin : null;
    }

    /**
     * Free a code whose order is dead. "Dead" means cancelled, or a gateway
     * checkout that was never completed and has sat untouched past the grace
     * period.
     *
     * Deliberately narrow: a cash-on-delivery or manual-payment order carries
     * payment_status 'pending' and is a real booking awaiting payment, so its
     * code stays spent. Only the empty status createOrder() writes for a
     * gateway order — plus the explicit cancel states — can expire.
     */
    protected function releaseIfOrderDead(SpinWheelSpin $spin): bool
    {
        if ($spin->used_context === self::CONTEXT_SUBSCRIPTION) {
            return $this->releaseIfSubscriptionDead($spin);
        }

        if (empty($spin->used_order_id)) {
            return false;
        }

        $order = \App\Order::select('id', 'payment_status', 'status', 'created_at')
            ->find($spin->used_order_id);

        // The order is gone, so nothing is holding the code any more.
        if (! $order) {
            $this->release($spin->used_order_id);
            return true;
        }

        $status = strtolower(trim((string) $order->payment_status));

        // 'cancalled' is the spelling the payment cancel handler writes.
        $cancelled = in_array($status, ['cancalled', 'cancelled', 'failed'], true)
            || (int) $order->status === 4;

        if ($cancelled) {
            $this->release($order->id);
            return true;
        }

        // Anything other than "never paid through a gateway" keeps the code.
        if ($status !== '') {
            return false;
        }

        if (! $order->created_at || $order->created_at->gt(now()->subHours(self::ABANDONED_ORDER_HOURS))) {
            return false;
        }

        $this->release($order->id);
        return true;
    }

    /**
     * A subscription is claimed before its purchase row exists, so there is no
     * id to check against. Look instead for the subscription history the code
     * was recorded on: if the seller got as far as a real purchase the code
     * stays spent, and if nothing was ever recorded it is handed back.
     */
    protected function releaseIfSubscriptionDead(SpinWheelSpin $spin): bool
    {
        // Still inside the grace period: the seller may be at the gateway.
        if (! $spin->used_at || $spin->used_at->gt(now()->subHours(self::ABANDONED_ORDER_HOURS))) {
            return false;
        }

        $alive = \Illuminate\Support\Facades\DB::table('subscription_histories')
            ->where('coupon_code', $spin->code)
            ->whereNotIn(
                \Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(payment_status,'')))"),
                ['', 'cancalled', 'cancelled', 'failed']
            )
            ->exists();

        if ($alive) {
            return false;
        }

        $this->releaseSpin($spin);
        return true;
    }

    /**
     * Claim a code for an order. The UPDATE is conditional on the code still
     * being unspent, so two orders placed at the same moment cannot both
     * redeem it: exactly one gets a row back, the other gets false.
     */
    public function redeem(SpinWheelSpin $spin, $orderId, string $context = self::CONTEXT_ORDER): bool
    {
        return SpinWheelSpin::where('id', $spin->id)
            ->whereNull('used_at')
            ->update([
                'used_at'       => now(),
                'used_order_id' => $orderId,
                'used_context'  => $context,
            ]) === 1;
    }

    /** Give a code back when the thing it paid for never happened. */
    public function release($orderId, string $context = self::CONTEXT_ORDER): void
    {
        SpinWheelSpin::where('used_order_id', $orderId)
            ->where('used_context', $context)
            ->update(['used_at' => null, 'used_order_id' => null]);
    }

    /** Give back a specific spin, used where there is no id to key on. */
    public function releaseSpin(SpinWheelSpin $spin): void
    {
        SpinWheelSpin::where('id', $spin->id)
            ->update(['used_at' => null, 'used_order_id' => null]);
    }

    /** Discount this spin takes off a given total (percentage based). */
    public function discountFor(SpinWheelSpin $spin, float $total): float
    {
        $amount = ($total * (float) $spin->result_value) / 100;

        // Never hand back more than the order is worth.
        return round(min($amount, $total), 2);
    }

    /**
     * The highest discount a segment may offer, as a percentage.
     *
     * Spin rewards are funded by the platform, not the seller: the discount is
     * taken out of the admin commission so the seller is paid exactly what
     * they would have been without it. That only works while the discount is
     * no larger than the commission, so the commission rate IS the ceiling.
     *
     * Returns null when the commission is a flat fee rather than a percentage,
     * because there is then no meaningful percentage ceiling — the per-order
     * cap in capDiscountToCommission() still applies.
     */
    public function maxDiscountPercent(): ?float
    {
        $commission = \App\AdminCommission::first();
        if (! $commission) {
            return null;
        }

        $isPercentage = $commission->system_type === 'subscription'
            || $commission->commission_charge_type === 'percentage';

        return $isPercentage ? max(0, (float) $commission->commission_charge) : null;
    }

    /**
     * Trim a discount so it never exceeds what the platform earns on the order.
     * The worst case is the platform making nothing on that sale; it can never
     * end up paying the seller out of pocket.
     */
    public function capDiscountToCommission(float $discount, float $commissionAmount): float
    {
        return round(max(0, min($discount, $commissionAmount)), 2);
    }

    /** Weighted random index across all segments. */
    protected function pickWeightedIndex(array $segments): int
    {
        $total = array_sum(array_map(fn ($s) => $s['weight'], $segments));
        if ($total <= 0) {
            return array_rand($segments);
        }
        $r = mt_rand(1, (int) round($total * 1000)) / 1000;
        foreach ($segments as $i => $s) {
            $r -= $s['weight'];
            if ($r <= 0) {
                return $i;
            }
        }
        return count($segments) - 1;
    }

    /** Weighted random index among the losing ("Try Again") segments only. */
    protected function pickLosingIndex(array $segments): int
    {
        $losing = array_values(array_filter(array_keys($segments), fn ($i) => ! $segments[$i]['is_win']));
        if (empty($losing)) {
            // No losing segment configured — fall back to a normal weighted pick.
            return $this->pickWeightedIndex($segments);
        }
        $subset = array_map(fn ($i) => $segments[$i], $losing);
        $pick   = $this->pickWeightedIndex($subset);
        return $losing[$pick];
    }
}
