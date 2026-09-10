<?php

namespace App\Services;

use App\Referral;
use App\StaticOption;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Rafiki Rewards — fraud detection.
 *
 * Runs a set of pluggable checks against a new referral and returns
 * a list of flags. Each flag has:
 *   - type      short slug (self_referral, phone_prefix, email_pattern, etc.)
 *   - severity  low | medium | high
 *   - message   human-readable explanation for admin
 *   - data      any relevant IDs/values for follow-up investigation
 *
 * Flags never auto-reject a referral — they set its status to "flagged"
 * so admin sees it in the pending review queue. This is intentional:
 * fraud detection is signal, not a verdict.
 */
class FraudDetector
{
    /**
     * Run every check and return the collected flags array.
     *
     * @param  Referral  $referral  freshly created referral to inspect
     * @return array                array of flag objects (empty = clean)
     */
    public function checkReferral(Referral $referral): array
    {
        $flags = [];

        $referrer = User::find($referral->referrer_id);
        $referred = User::find($referral->referred_user_id);

        if (!$referrer || !$referred) {
            return [];
        }

        foreach ([
            $this->checkSelfMatch($referrer, $referred),
            $this->checkPhonePrefix($referrer, $referred),
            $this->checkEmailPattern($referrer, $referred),
            $this->checkIpFrequency($referral),
            $this->checkEmailDomainConcentration($referrer, $referred),
            $this->checkReferrerVelocity($referrer),
        ] as $flag) {
            if ($flag !== null) $flags[] = $flag;
        }

        return $flags;
    }

    /**
     * Convenience — should this referral be auto-flagged for admin review?
     * True if any high-severity flag is present AND the auto-flag setting is on.
     */
    public function shouldAutoFlag(array $flags): bool
    {
        if ((int) $this->opt('referral_fraud_auto_flag', 1) !== 1) return false;

        foreach ($flags as $f) {
            if (($f['severity'] ?? '') === 'high') return true;
        }
        return false;
    }

    /* -----------------------------------------------------------------
     |  Individual checks
     | ----------------------------------------------------------------- */

    /**
     * Referrer and referred user share phone or email exactly.
     * (Self-referral via a different user account.)
     */
    protected function checkSelfMatch(User $referrer, User $referred): ?array
    {
        if ($referrer->phone && $referred->phone && $referrer->phone === $referred->phone) {
            return $this->flag('self_referral', 'high',
                'Referrer and referred user share the same phone number.',
                ['phone' => $referrer->phone]);
        }

        if ($referrer->email && $referred->email && $referrer->email === $referred->email) {
            return $this->flag('self_referral', 'high',
                'Referrer and referred user share the same email.',
                ['email' => $referrer->email]);
        }

        return null;
    }

    /**
     * Referrer and referred user share a suspiciously long phone prefix
     * (default 9 digits = same country + operator + subscriber pattern).
     * Family/friends legitimately match on 5-6 digits, but 9+ is suspicious.
     */
    protected function checkPhonePrefix(User $referrer, User $referred): ?array
    {
        $len = (int) $this->opt('referral_fraud_phone_prefix_len', 9);
        if ($len <= 0) return null;

        $a = $this->digits($referrer->phone);
        $b = $this->digits($referred->phone);
        if (strlen($a) < $len + 1 || strlen($b) < $len + 1) return null;
        if (substr($a, 0, $len) !== substr($b, 0, $len)) return null;

        return $this->flag('phone_prefix', 'medium',
            "Phone numbers share the same {$len}-digit prefix. Possible duplicate account.",
            ['referrer_phone' => $referrer->phone, 'referred_phone' => $referred->phone, 'prefix_len' => $len]);
    }

    /**
     * Same email base + numeric suffix (foo1@x.com, foo2@x.com, ...).
     * Classic sockpuppet pattern.
     */
    protected function checkEmailPattern(User $referrer, User $referred): ?array
    {
        if (!$referrer->email || !$referred->email) return null;

        [$aLocal, $aDomain] = array_pad(explode('@', $referrer->email, 2), 2, null);
        [$bLocal, $bDomain] = array_pad(explode('@', $referred->email, 2), 2, null);
        if (!$aDomain || !$bDomain) return null;
        if (strtolower($aDomain) !== strtolower($bDomain)) return null;

        // Strip trailing digits and compare bases
        $aBase = preg_replace('/\d+$/', '', strtolower($aLocal ?? ''));
        $bBase = preg_replace('/\d+$/', '', strtolower($bLocal ?? ''));
        if ($aBase === '' || $bBase === '') return null;
        if ($aBase !== $bBase) return null;

        return $this->flag('email_pattern', 'high',
            'Referrer and referred user share the same email base and domain (sockpuppet pattern).',
            ['referrer_email' => $referrer->email, 'referred_email' => $referred->email]);
    }

    /**
     * 3+ signups (by default) from the same IP within the last 24 hours.
     */
    protected function checkIpFrequency(Referral $referral): ?array
    {
        if (!$referral->ip_address) return null;

        $limit = (int) $this->opt('referral_fraud_ip_daily_limit', 3);
        if ($limit <= 0) return null;

        $count = Referral::where('ip_address', $referral->ip_address)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($count < $limit) return null;

        return $this->flag('ip_frequency', 'medium',
            "Same IP address used for {$count} referrals in the last 24 hours.",
            ['ip' => $referral->ip_address, 'count_24h' => $count]);
    }

    /**
     * More than N referrals from the SAME email domain by the SAME referrer.
     * Corporate emails (@companyname.com) can legitimately have many — the
     * limit is configurable.
     */
    protected function checkEmailDomainConcentration(User $referrer, User $referred): ?array
    {
        $limit = (int) $this->opt('referral_fraud_email_domain_limit', 5);
        if ($limit <= 0 || !$referred->email) return null;

        [, $domain] = array_pad(explode('@', $referred->email, 2), 2, null);
        if (!$domain) return null;

        $count = Referral::where('referrer_id', $referrer->id)
            ->whereHas('referredUser', function ($q) use ($domain) {
                $q->where('email', 'like', "%@{$domain}");
            })
            ->count();

        if ($count < $limit) return null;

        return $this->flag('email_domain_concentration', 'low',
            "Referrer has {$count} referrals from the domain @{$domain}. Verify these are real people.",
            ['domain' => $domain, 'count' => $count]);
    }

    /**
     * Referrer created too many referrals in a short window.
     */
    protected function checkReferrerVelocity(User $referrer): ?array
    {
        $limit = (int) $this->opt('referral_fraud_referrer_daily_limit', 10);
        if ($limit <= 0) return null;

        $count = Referral::where('referrer_id', $referrer->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($count < $limit) return null;

        return $this->flag('referrer_velocity', 'medium',
            "Referrer created {$count} referrals in the last 24 hours.",
            ['count_24h' => $count]);
    }

    /* -----------------------------------------------------------------
     |  Helpers
     | ----------------------------------------------------------------- */

    protected function flag(string $type, string $severity, string $message, array $data = []): array
    {
        return [
            'type'       => $type,
            'severity'   => $severity,
            'message'    => $message,
            'data'       => $data,
            'detected_at'=> now()->toIso8601String(),
        ];
    }

    protected function digits(?string $s): string
    {
        return preg_replace('/\D+/', '', (string) $s);
    }

    protected function opt(string $name, $default)
    {
        $row = StaticOption::where('option_name', $name)->first();
        return $row ? $row->option_value : $default;
    }
}
