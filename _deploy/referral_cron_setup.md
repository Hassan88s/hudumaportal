# Rafiki Rewards — Scheduled Task Setup

The Protection Window automation runs as a Laravel scheduled command.
It needs a cron entry on the server so Laravel's scheduler fires every minute
and dispatches the hourly job when its time comes.

---

## 1. Add the cron job (one-time cPanel setup)

**cPanel → Cron Jobs → Add New Cron Job**

**Common Settings:** Once Per Minute (* * * * *)

**Command (adjust paths for your server):**

```
* * * * * cd /home/hudumaportalco/dev.hudumaportal.co.tz/@core && /opt/cpanel/ea-php81/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

For **production** (`hudumaportal.co.tz`), use:

```
* * * * * cd /home/hudumaportalco/public_html/@core && /opt/cpanel/ea-php81/root/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

That single line lets Laravel's scheduler decide what runs when. Right now it runs:

- `package:subscription_expire` daily (existing behaviour, untouched)
- `referrals:promote-approved` hourly (**new** — this is what protects the referral system)

## 2. Verify it works

**SSH into the server and run these:**

```bash
# See what jobs Laravel scheduler knows about
cd /home/hudumaportalco/dev.hudumaportal.co.tz/@core
/opt/cpanel/ea-php81/root/usr/bin/php artisan schedule:list

# Preview what would change without touching the DB
/opt/cpanel/ea-php81/root/usr/bin/php artisan referrals:promote-approved --dry-run

# Actually run it once manually (safe — nothing without expired protection is touched)
/opt/cpanel/ea-php81/root/usr/bin/php artisan referrals:promote-approved
```

Expected output (dry-run, no rewards ready yet):
```
[2026-09-10 20:00:00] referrals:promote-approved (dry-run)
  · found 0 rewards ready to promote (0 TZS total)
```

## 3. What the automation does

Every hour:
1. Finds `referral_rewards` where `status = 'pending'` AND `protection_ends_at <= now()`
2. Updates them to `status = 'approved'` + sets `approved_at = now()`
3. Bumps the parent `referrals.status` to `'approved'` if no rewards remain pending

**Log line on each run** (in `storage/logs/laravel.log`):
```
[Rafiki Rewards] promoted 3 rewards to approved (2,500 TZS), lifted 1 referrals to approved
```

## 4. Refund reversal — how to hook it in later

There's a new method on `ReferralService`:

```php
app(\App\Services\ReferralService::class)
    ->reverseRewardsForBuyerRefund($buyerId, 'Order #123 refunded');
```

Call it from wherever a buyer's order gets refunded (e.g. Stripe webhook, admin refund
button, or the "Order cancelled" flow). It only touches rewards still in the protection
window — already-paid money is not clawed back. Idempotent, safe to call blind.

Not wired into any refund path yet — flag the exact controller/method and I'll add
the one-line call. Doing this properly is a separate follow-up.
