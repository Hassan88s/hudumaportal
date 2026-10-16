<?php

namespace App\Console\Commands;

use App\Helpers\FlashMsg;
use App\Mail\BasicMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class SubscriptionExpireReminder extends Command
{
    protected $signature = 'package:subscription_expire';
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        
        $all_user = \App\User::with('subscribedSeller')
            ->select('id','name','email')
            ->where('user_type',0)
            ->whereHas('subscribedSeller')
            ->get();
        foreach ($all_user as $user){
            $expireRaw = optional($user->subscribedSeller)->expire_date;
            if (empty($expireRaw)) {
                continue;
            }

            // Signed days from today to expiry: positive = days left, negative = already expired.
            $expire        = \Carbon\Carbon::parse($expireRaw)->startOfDay();
            $daysRemaining = \Carbon\Carbon::today()->diffInDays($expire, false);

            // Once the plan has expired there are no "days left" to count — skip,
            // otherwise the old code counted the days SINCE expiry and the number
            // kept going up.
            if ($daysRemaining < 0) {
                continue;
            }

            $day_list = json_decode(get_static_option('package_expire_notify_mail_days')) ?? [];
            $day_list = array_map('intval', (array) $day_list);

            // Remind on the configured days-before-expiry (e.g. 7, 5, 3, 1).
            if (in_array($daysRemaining, $day_list, true)) {
                $daysText = $daysRemaining === 0
                    ? __('today')
                    : ($daysRemaining === 1 ? __('1 day') : $daysRemaining . ' ' . __('days'));

                try {
                    $message_body = __('Your subscription will expire in :days. Please renew before it expires to keep your account active.', ['days' => $daysText]);
                    Mail::to($user->email)->send(new BasicMail([
                        'subject' => __('Subscription Expiry Reminder'),
                        'message' => $message_body,
                    ]));
                } catch (\Exception $e) {
                    //
                }
            }
        }
        return 0;
    }
}
