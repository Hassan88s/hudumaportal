<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Diagnose the NextSMS integration.
 *
 *   php artisan sms:diagnose                  -> config + account balance only
 *   php artisan sms:diagnose 0712345678       -> also sends ONE real test SMS
 *
 * Needed because sendSMS() used to log every API response as "SMS sent", so a
 * rejected message (no balance, bad key, bad number) was indistinguishable
 * from a delivered one.
 */
class SmsDiagnose extends Command
{
    protected $signature = 'sms:diagnose {phone? : Send one real test SMS to this number}';
    protected $description = 'Check NextSMS credentials and balance, and optionally send a test SMS';

    public function handle()
    {
        $key    = config('services.nextsms.key') ?: env('NEXTSMS_AUTH_KEY');
        $sender = config('services.nextsms.sender') ?: env('NEXTSMS_SENDER');

        $this->line('--- NextSMS configuration ---');
        $this->line('Auth key   : ' . (empty($key) ? 'NOT SET  <-- SMS cannot be sent' : 'set (' . strlen($key) . ' chars)'));
        $this->line('Sender ID  : ' . ($sender ?: 'NOT SET') . ' (' . strlen((string) $sender) . ' chars; max 11 for alphanumeric)');
        $this->line('Config cached: ' . (file_exists(base_path('bootstrap/cache/config.php')) ? 'YES' : 'no'));

        if (empty($key)) {
            $this->error('No auth key available — stopping. If the config cache is built, run: php artisan config:clear');
            return 1;
        }

        // ---- Account balance -------------------------------------------------
        $this->line('');
        $this->line('--- Account balance ---');
        [$body, $code, $err] = $this->get('https://messaging-service.co.tz/api/sms/v1/balance', $key);

        if ($err) {
            $this->error('Could not reach NextSMS: ' . $err);
        } else {
            $this->line('HTTP ' . $code);
            $this->line($body ?: '(empty response)');
            if ($code === 401 || $code === 403) {
                $this->error('Credentials rejected by NextSMS (check NEXTSMS_AUTH_KEY).');
            }
        }

        // ---- Optional live test ---------------------------------------------
        $phone = $this->argument('phone');
        if ($phone) {
            $normalised = normalize_tz_phone($phone);
            $this->line('');
            $this->line('--- Test send ---');
            $this->line('Input number     : ' . $phone);
            $this->line('Normalised to    : ' . $normalised);

            $ok = sendSMS($phone, 'Huduma Portal test message. If you received this, SMS delivery is working.');
            $this->line('sendSMS() result : ' . ($ok ? 'ACCEPTED by gateway' : 'FAILED (see storage/logs for the reason)'));
            $this->line('Note: "accepted" only means the gateway queued it. If it never arrives, the sender ID may not be approved or the balance may be exhausted.');
        } else {
            $this->line('');
            $this->line('Tip: pass a phone number to send one real test SMS, e.g. php artisan sms:diagnose 0712345678');
        }

        return 0;
    }

    private function get(string $url, string $key): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $key,
                'Accept: application/json',
            ],
        ]);
        $body = curl_exec($curl);
        $err  = curl_error($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [$body, $code, $err];
    }
}
