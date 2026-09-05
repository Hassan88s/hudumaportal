<?php

namespace Modules\Subscription\Http\Controllers\Frontend;

use App\Mail\BasicMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\Subscription\Entities\SellerSubscription;
use Auth;
use Modules\Subscription\Entities\Subscription;
use Modules\Subscription\Entities\SubscriptionHistory;
use Modules\Wallet\Entities\Wallet;


class SellerSubsController extends Controller
{
    public function subscriptions()
    {
        $seller_id = Auth::guard('web')->user()->id;
        $subscription = SellerSubscription::where('seller_id', $seller_id)->first();
        $subscription_history = SubscriptionHistory::where('seller_id', $seller_id)->where('payment_status','complete')->latest()->paginate(10);
        return view('subscription::frontend.seller.subscriptions', compact('subscription', 'subscription_history'));
    }

    public function sub_renew(Request $request)
    {
       
        if ($request->subscription_id == '1'){
                //todo check it price is zero, check it already has subscription for this month in zero price
                $check_already_has_free_subscription_in_this_month =  SubscriptionHistory::where([
                    'seller_id' => auth('web')->id(),
                    'subscription_id' => $request->subscription_id,
                    'payment_status' => 'complete',
                ])->limit(1)->latest()->first();//->whereMonth('expire_date',Carbon::today())->exists();

                if(!empty($check_already_has_free_subscription_in_this_month)){
                    $expire_date = Carbon::parse($check_already_has_free_subscription_in_this_month->expire_date)->subDays(2);
                    if (Carbon::today()->lessThan($expire_date)){
                        toastr_error(__('you have already avail free plan in this month, you cannot avail free plan more than once in a month.'));
                        return redirect()->back();
                    }
                }

            }
        if (moduleExists("Wallet")){

            if ($request->subscription_id) {
                $seller_id = Auth::guard('web')->user()->id;
                $seller_email = Auth::guard('web')->user()->email;
                $seller_name = Auth::guard('web')->user()->name;
                $subscription_details = Subscription::where('id', $request->subscription_id)->first();
                //subscription has or not check
                if(is_null($subscription_details)){
                    toastr_warning(__('subscription not found'));
                    return back();
                }
                
                $seller_subscription = SellerSubscription::where('subscription_id', $request->subscription_id)->where('seller_id', $seller_id)->first();
                $wallet_balance = Wallet::select('balance')->where('buyer_id', $seller_id)->first();
                //check wallet has or not
                if(is_null($wallet_balance)){
                    toastr_warning(__('wallet not enabled. make your initial diposit to enable your wallet'));
                    return back();
                }


                if ($wallet_balance->balance >= $subscription_details->price) {
                    if ($subscription_details->type == 'monthly') {
                        $expire_date = Carbon::now()->addDays(30);
                        $connect = $subscription_details->connect;
                    } elseif ($subscription_details->type == 'yearly') {
                        $expire_date = Carbon::now()->addDays(365);
                        $connect = $subscription_details->connect;
                    } elseif ($subscription_details->type == 'lifetime') {
                        $expire_date = Carbon::now()->addDays(3650);
                        $connect = 1000000;
                    }


                    SellerSubscription::where('subscription_id', $subscription_details->id)->update([
                        'payment_status' => 'complete',
                        'payment_gateway' => 'wallet',
                        'expire_date' => $expire_date,
                        'connect' => ($seller_subscription->connect + $connect),
                        'price' => $subscription_details->price,
                        'status' => 1,
                    ]);

                    Wallet::where('buyer_id', $seller_id)->update([
                        'balance' => $wallet_balance->balance - $subscription_details->price,
                    ]);

                    //Send order email to admin and seller
                    try {
                        $connect = $subscription_details->type =='lifetime' ? __("No Limit") : $connect;
                        $message = get_static_option('renew_subscription_seller_message') ?? '';
                        $message = str_replace(["@type","@price","@connect"],[$subscription_details->type,float_amount_with_currency_symbol($subscription_details->price),$connect],$message);
                        Mail::to($seller_email)->send(new BasicMail([
                            'subject' =>get_static_option('renew_subscription_email_subject') ?? __('Renew Subscription'),
                            'message' => $message
                        ]));


                        $message = get_static_option('buy_subscription_admin_message') ?? '';
                        $message = str_replace(["@type","@price","@connect","@seller_name","@seller_email"],[$subscription_details->type,float_amount_with_currency_symbol($subscription_details->price),$connect,$seller_name,$seller_email],$message);
                        Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                            'subject' =>get_static_option('renew_subscription_email_subject') ?? __('Renew Subscription'),
                            'message' => $message
                        ]));

                    } catch (\Exception $e) {
                        \Toastr::error($e->getMessage());
                    }

                    toastr_success(__('Your subscription renewed successfully'));
                } else {
                    toastr_warning(__('Your wallet balance is not sufficient to renew this subscription'));
                }
                return back();
            }
       }
       toastr_warning(__('Your wallet not activated yet, to make automatically renew subscription using wallet balance'));
       return back();

    }
}
