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
            $day_list = json_decode(get_static_option('package_expire_notify_mail_days')) ?? [];
            rsort($day_list);
            foreach ($day_list as $day){


                $startDate = Carbon::today();
                $notification_date = \Carbon\Carbon::parse(optional($user->subscribedSeller)->expire_date)->subDay($day);
                $compareDays = $notification_date->lt($startDate);

                if ($compareDays){

                    $days_reaming = $startDate->diffInDays(\Carbon\Carbon::parse(optional($user->subscribedSeller)->expire_date));

                    try {
                        $message_body = __('Your Subscription will expire very soon. Only'). $days_reaming. __('Days Left. Please subscribe to a plan before expiration');
                        Mail::to($user->email)->send(new BasicMail([
                            'subject' => __('Subscription Expire Reminder'),
                            'message' => $message_body
                        ]));
                    } catch (\Exception $e) {
                       //
                    }
                    break;
                
                }
            }
        }
        return 0;
    }
}
