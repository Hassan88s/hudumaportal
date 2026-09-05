<?php

namespace App\Http\Controllers;
use App\AdminNotification;
use Illuminate\Http\Request;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;
use Illuminate\Support\Facades\Mail;
use App\Helpers\FlashMsg;
use App\Mail\OrderMail;
use App\Order;
use App\User;
use Str;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;
use App\Mail\BasicMail;
use Illuminate\Support\Facades\Http;
use App\Notifications\OrderNotification;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;
use Auth;

use App\StaticOption;
class ServicePaymentController extends Controller
{
    protected function cancel_page()
    {
        return redirect()->route('frontend.order.payment.cancel.static');
    }

    public function paypal_ipn(Request $request)
    {
        $paypal_mode = getenv('PAYPAL_MODE');
        $client_id = $paypal_mode === 'sandbox' ? getenv('PAYPAL_SANDBOX_CLIENT_ID') : getenv('PAYPAL_LIVE_CLIENT_ID');
        $client_secret = $paypal_mode === 'sandbox' ? getenv('PAYPAL_SANDBOX_CLIENT_SECRET') : getenv('PAYPAL_LIVE_CLIENT_SECRET');
        $app_id = $paypal_mode === 'sandbox' ? getenv('PAYPAL_SANDBOX_APP_ID') : getenv('PAYPAL_LIVE_APP_ID');
        $paypal = XgPaymentGateway::paypal();
        $paypal->setClientId($client_id);
        $paypal->setClientSecret($client_secret);
        $paypal->setEnv($paypal_mode === 'sandbox');
        $paypal->setAppId($app_id);
        $payment_data = $paypal->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function razorpay_ipn(Request $request)
    {
        $razorpay_api_key = getenv('RAZORPAY_API_KEY');
        $razorpay_api_secret = getenv('RAZORPAY_API_SECRET');

        $razorpay = XgPaymentGateway::razorpay();
        $razorpay->setApiKey($razorpay_api_key);
        $razorpay->setApiSecret($razorpay_api_secret);

        $payment_data = $razorpay->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function paytm_ipn(Request $request)
    {
        $paytm_merchant_id = getenv('PAYTM_MERCHANT_ID');
        $paytm_merchant_key = getenv('PAYTM_MERCHANT_KEY');
        $paytm_merchant_website = getenv('PAYTM_MERCHANT_WEBSITE') ?? 'WEBSTAGING';
        $paytm_channel = getenv('PAYTM_CHANNEL') ?? 'WEB';
        $paytm_industry_type = getenv('PAYTM_INDUSTRY_TYPE') ?? 'Retail';
        $paytm_env = getenv('PAYTM_ENVIRONMENT');

        $paytm = XgPaymentGateway::paytm();
        $paytm->setMerchantId($paytm_merchant_id);
        $paytm->setMerchantKey($paytm_merchant_key);
        $paytm->setMerchantWebsite($paytm_merchant_website);
        $paytm->setChannel($paytm_channel);
        $paytm->setIndustryType($paytm_industry_type);
        $paytm->setEnv($paytm_env === 'local'); //env must set as boolean, string will not work

        $payment_data = $paytm->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function mollie_ipn()
    {
        $mollie_key = getenv('MOLLIE_KEY');
        $mollie = XgPaymentGateway::mollie();
        $mollie->setApiKey($mollie_key);
        $mollie->setEnv(true); //env must set as boolean, string will not work
        $payment_data = $mollie->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return self::cancel_page();
    }

    public function stripe_ipn(Request $request){

        $stripe_public_key = getenv('STRIPE_PUBLIC_KEY');
        $stripe_secret_key = getenv('STRIPE_SECRET_KEY');
        $stripe = XgPaymentGateway::stripe();
        $stripe->setSecretKey($stripe_secret_key);
        $stripe->setPublicKey($stripe_public_key);
        $stripe->setEnv(true); //env must set as boolean, string will not work

        $payment_data = $stripe->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function flutterwave_ipn(Request $request)
    {
        $flutterwave_public_key = getenv("FLW_PUBLIC_KEY");
        $flutterwave_secret_key = getenv("FLW_SECRET_KEY");
        $flutterwave_secret_hash = getenv("FLW_SECRET_HASH");

        $flutterwave = XgPaymentGateway::flutterwave();
        $flutterwave->setPublicKey($flutterwave_public_key);
        $flutterwave->setSecretKey($flutterwave_secret_key);
        $flutterwave->setEnv(true); //env must set as boolean, string will not work

        $payment_data = $flutterwave->ipn_response();
 
        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $this->send_order_notification($payment_data['order_id']);
             $order_id = $payment_data['order_id'];
                         // SMS Notifications
                  $buyer_id = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : NULL;
                  $get_buyer = User::where('id',$buyer_id)->first();
                  $order_details = Order::find($order_id);
                  $seller = User::where('id',$order_details->seller_id)->first();
                        
                        
                        $order_id = $order_details->id;
                            $website_link = "https://hudumaportal.co.tz/";
                    $message_for_buyer = "You have successfully placed the order. Your order ID is $order_id. Visit our website at $website_link for more details. " . 
                     "Umefanikiwa kuweka oda. Kitambulisho chako cha oda ni $order_id. Tembelea tovuti yetu $website_link kwa maelezo zaidi.";
             
             
               // $this->sendSMS($get_buyer->phone, strip_tags($message_for_buyer));
              
                
                   $message_for_seller_admin = "A new order has been placed. The order ID is $order_id. Please check your $website_link dashboard for more details. " . 
                            "Oda mpya imewekwa. Kitambulisho cha oda ni $order_id. Tafadhali angalia dashibodi yako $website_link kwa maelezo zaidi.";
                //$this->sendSMS($seller->phone, strip_tags($message_for_seller_admin));
         $user = Auth::guard('web')->user();

             if (!empty($user->referred_by)) {
        $referrer_id = $user->referred_by;

       
        $firstOrder= Order::where('buyer_id', $user->id)->count();

        if ($firstOrder == 1) { 
          
            $referrer_wallet = Wallet::firstOrCreate(
                ['buyer_id' => $referrer_id],
                ['balance' => 0, 'status' => 1]
            );

          
            $first_order_points = StaticOption::where(['option_name' => 'first_purchase_points'])->first();
            $points_to_add = $first_order_points->option_value ?? 5; // Default 5 points if not set

         
            $referrer_wallet->increment('balance', $points_to_add);

         
            WalletHistory::create([
                'buyer_id' => $referrer_id,
                'amount' => $points_to_add,
                'payment_gateway' => 'First Order Creation Bonus',
                'payment_status' => 'complete',
                'status' => 1,
                'Action' => 'Referral Bonus (First Service Created)',
            ]);
        }
    }
          
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }


    public function paystack_ipn(Request $request)
    {
        $paystack_public_key = getenv('PAYSTACK_PUBLIC_KEY');
        $paystack_secret_key = getenv('PAYSTACK_SECRET_KEY');
        $paystack_merchant_email = getenv('MERCHANT_EMAIL');

        $paystack = XgPaymentGateway::paystack();
        $paystack->setPublicKey($paystack_public_key);
        $paystack->setSecretKey($paystack_secret_key);
        $paystack->setMerchantEmail($paystack_merchant_email);

        $payment_data = $paystack->ipn_response();
        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
          	if($payment_data['type'] === 'deposit'){
                $order_id = $payment_data['order_id'];
                $history_id = session()->get('history_id');
                $this->update_wallet_database($order_id, $payment_data['transaction_id'],$history_id);
                $this->send_wallet_mail($order_id);
                $new_order_id = wrapped_id($order_id);
                toastr_success('Your wallet successfully credited');
                return redirect()->route('buyer.wallet.history');
            }
          	if($payment_data['type'] === 'order'){
              $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
              $this->send_order_mail($payment_data['order_id']);
              $order_id = $payment_data['order_id'];
              $random_order_id_1 = Str::random(30);
              $random_order_id_2 = Str::random(30);
              $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
              return redirect()->route('frontend.order.payment.success',$new_order_id);
            }
            
        }
        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }
      
        return $this->cancel_page();
    }

  public function send_wallet_mail($last_deposit_id)
    {
        if(empty($last_deposit_id)){
            return redirect()->route('homepage');
        }
        //Send order email to buyer
        try {
            $message_body = __('Hello a buyer just deposit to his wallet.').'</br>'.'<span class="verify-code">'.__('Deposit ID:').$last_deposit_id.'</span>';
            Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                'subject' => __('Deposit Confirmation'),
                'message' => $message_body
            ]));

        } catch (\Exception $e) {
            //return redirect()->back()->with(FlashMsg::error($e->getMessage()));
        }
    }

    private function update_wallet_database($last_deposit_id, $transaction_id, $history_id)
    {
        $deposit_details = WalletHistory::find($last_deposit_id);
        WalletHistory::where('id', $last_deposit_id)->update([
            'payment_status' => 'complete',
            'transaction_id' => $transaction_id,
            'status' => 1,
        ]);

        $get_balance_from_wallet = Wallet::where('buyer_id',$deposit_details->buyer_id)->first();
        Wallet::where('buyer_id', $deposit_details->buyer_id)
            ->update([
                'balance' => $get_balance_from_wallet->balance + $deposit_details->amount,
            ]);
    }
  
    public function midtrans_ipn()
    {
        $midtrans_env =  getenv('MIDTRANS_ENVAIRONTMENT') === 'true';
        $midtrans_server_key = getenv('MIDTRANS_SERVER_KEY');
        $midtrans_client_key = getenv('MIDTRANS_CLIENT_KEY');
        $midtrans = XgPaymentGateway::midtrans();
        $midtrans->setClientKey($midtrans_client_key);
        $midtrans->setServerKey($midtrans_server_key);
        $midtrans->setEnv($midtrans_env); //true mean sandbox mode , false means live mode

        $payment_data = $midtrans->ipn_response();
        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function payfast_ipn()
    {

        $payfast_merchant_id = getenv('PF_MERCHANT_ID');
        $payfast_merchant_key = getenv('PF_MERCHANT_KEY');
        $payfast_passphrase = getenv('PAYFAST_PASSPHRASE');
        $payfast_env = getenv('PAYFAST_PASSPHRASE') === 'true';
        $payfast = XgPaymentGateway::payfast();
        $payfast->setMerchantId($payfast_merchant_id);
        $payfast->setMerchantKey($payfast_merchant_key);
        $payfast->setPassphrase($payfast_passphrase);
        $payfast->setEnv($payfast_env); //env must set as boolean, string will not work

        $payment_data = $payfast->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function cashfree_ipn(Request $request)
    {
        $cashfree_env = getenv('CASHFREE_TEST_MODE') === 'true';
        $cashfree_app_id = getenv('CASHFREE_APP_ID');
        $cashfree_secret_key = getenv('CASHFREE_SECRET_KEY');

        $cashfree = XgPaymentGateway::cashfree();
        $cashfree->setAppId($cashfree_app_id);
        $cashfree->setSecretKey($cashfree_secret_key);

        $payment_data = $cashfree->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();

    }

    public function instamojo_ipn(Request $request)
    {
        $instamojo_client_id = getenv('INSTAMOJO_CLIENT_ID');
        $instamojo_client_secret = getenv('INSTAMOJO_CLIENT_SECRET');
        $instamojo_env = getenv('INSTAMOJO_TEST_MODE') === 'true';

        $instamojo = XgPaymentGateway::instamojo();
        $instamojo->setClientId($instamojo_client_id);
        $instamojo->setSecretKey($instamojo_client_secret);
        $instamojo->setEnv($instamojo_env); //true mean sandbox mode , false means live mode //env must set as boolean, string will not work
        $payment_data = $instamojo->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }


    public function squareup_ipn(Request $request)
    {
        $squareup_env =  !empty(get_static_option('squareup_test_mode'));
        $squareup_location_id = get_static_option('cinetpay_site_id');
        $squareup_access_token = get_static_option('squareup_access_token');
        $squareup_application_id = get_static_option('squareup_application_id');

        $squareup = XgPaymentGateway::squareup();
        $squareup->setLocationId($squareup_location_id);
        $squareup->setAccessToken($squareup_access_token);
        $squareup->setApplicationId($squareup_application_id);
        $squareup->setEnv($squareup_env);

        $payment_data = $squareup->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }
        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function cinetpay_ipn(Request $request)
    {
        $cinetpay_env =  !empty(get_static_option('cinetpay_test_mode'));
        $cinetpay_site_id = get_static_option('cinetpay_site_id');
        $cinetpay_app_key = get_static_option('cinetpay_app_key');

        $cinetpay = XgPaymentGateway::cinetpay();
        $cinetpay->setAppKey($cinetpay_app_key);
        $cinetpay->setSiteId($cinetpay_site_id);
        $cinetpay->setEnv($cinetpay_env);

        $payment_data = $cinetpay->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }


    public function paytabs_ipn(Request $request)
    {
        $paytabs_env =  !empty(get_static_option('paytabs_test_mode'));
        $paytabs_region = get_static_option('paytabs_region');
        $paytabs_profile_id = get_static_option('paytabs_profile_id');
        $paytabs_server_key = get_static_option('paytabs_server_key');

        $paytabs = XgPaymentGateway::paytabs();
        $paytabs->setProfileId($paytabs_profile_id);
        $paytabs->setRegion($paytabs_region);
        $paytabs->setServerKey($paytabs_server_key);
        $paytabs->setEnv($paytabs_env);

        $payment_data = $paytabs->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }
    public function billplz_ipn(Request $request)
    {
        $billplz_env =  !empty(get_static_option('billplz_test_mode'));
        $billplz_key =  get_static_option('billplz_key');
        $billplz_xsignature =  get_static_option('billplz_xsignature');
        $billplz_collection_name =  get_static_option('billplz_collection_name');

        $billplz = XgPaymentGateway::billplz();
        $billplz->setKey($billplz_key);
        $billplz->setVersion('v4');
        $billplz->setXsignature($billplz_xsignature);
        $billplz->setCollectionName($billplz_collection_name);
        $billplz->setEnv($billplz_env);

        $payment_data = $billplz->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }

        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function zitopay_ipn(Request $request)
    {
        $zitopay_env =  !empty(get_static_option('zitopay_test_mode'));
        $zitopay_username =  get_static_option('zitopay_username');

        $zitopay = XgPaymentGateway::zitopay();
        $zitopay->setUsername($zitopay_username);
        $zitopay->setEnv($zitopay_env);

        $payment_data = $zitopay->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }
        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function kineticpay_ipn(Request $request)
    {
        $kineticpay_env =  !empty(get_static_option('kineticpay_test_mode'));
        $kineticpay_username =  get_static_option('kineticpay_username');

        $kineticpay = XgPaymentGateway::kineticpay();
        $kineticpay->setMerchantKey($kineticpay_username);
        $kineticpay->setBank(request()->kineticpay_bank);
        $kineticpay->setEnv($kineticpay_env);

        $payment_data = $kineticpay->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }
        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function marcadopago_ipn(Request $request)
    {
        $mercadopago_client_id = getenv('MERCADO_PAGO_CLIENT_ID');
        $mercadopago_client_secret = getenv('MERCADO_PAGO_CLIENT_SECRET');
        $mercadopago_env =  getenv('MERCADO_PAGO_TEST_MOD') === 'true';

        $marcadopago = XgPaymentGateway::marcadopago();
        $marcadopago->setClientId($mercadopago_client_id);
        $marcadopago->setClientSecret($mercadopago_client_secret);
        $marcadopago->setEnv($mercadopago_env); ////true mean sandbox mode , false means live mode
        $payment_data = $marcadopago->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $this->update_database($payment_data['order_id'], $payment_data['transaction_id']);
            $this->send_order_mail($payment_data['order_id']);
            $order_id = $payment_data['order_id'];
            $random_order_id_1 = Str::random(30);
            $random_order_id_2 = Str::random(30);
            $new_order_id = $random_order_id_1.$order_id.$random_order_id_2;
            return redirect()->route('frontend.order.payment.success',$new_order_id);
        }
        if(!empty(session()->get('order_id'))){
            $this->update_status_cancel(session()->get('order_id'));
        }

        return $this->cancel_page();
    }

    public function send_order_mail($order_id)
    {
        if(empty($order_id)){
            return redirect()->route('homepage');    
        }
        
        $order_details = Order::find($order_id);
        $seller_email = User::select('name','email')->where('id',$order_details->seller_id)->first();
        //Send order email to buyer
        try {
            $mail_subject = get_static_option('new_order_email_subject') ?? __('New Order #');
             $mail_subject_for_seller = 'Your Service Has Been Booked!';
            $message_for_buyer = get_static_option('new_order_buyer_message') ?? __('You have successfully placed an order #');
            $message_for_seller_admin = get_static_option('new_order_admin_seller_message') ?? __('You have a new order #');
               $message_for_seller_admin = str_replace(["@name","@service_id","@id"],[$seller_email->name,$order_details->service_id,$order_id],$message_for_seller_admin);
               $message_for_buyer = str_replace(["@id"],[$order_id],$message_for_buyer);   
            Mail::to($order_details->email)->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details,$message_for_buyer));
            Mail::to($seller_email)->send(new OrderMail(strip_tags($mail_subject_for_seller).$order_details->id,$order_details,$message_for_seller_admin));
            Mail::to(get_static_option('site_global_email'))->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details,$message_for_seller_admin));
        } catch (\Exception $e) {
            return redirect()->back()->with(FlashMsg::error($e->getMessage()));
        }

    }
    
    public function send_order_notification($order_id)
    {
        if(empty($order_id)){
            return redirect()->route('homepage');    
        }
        
        $order_details = Order::find($order_id);
        // $seller_email = User::select('email')->where('id',$order_details->seller_id)->first();
        //Send order email to buyer
        $seller = User::where('id',$order_details->seller_id)->first();
        $buyer_id =  NULL;
        $order_message = __('You have a new order');

        // admin notification add
        AdminNotification::create(['order_id' => $order_id]);

        // seller buyer notification
        $seller->notify(new OrderNotification($order_id,$order_details->service_id, $seller->id, $buyer_id,$order_message));

    }

    private function update_status_cancel($id){
        if(!empty($id)){
            return Order::find($id)->update(['status' => 4]);
        }
    }

    private function update_database($order_id, $transaction_id)
    {
        Order::where('id', $order_id)->update([
            'payment_status' => 'complete',
            'status' => 0,
            'transaction_id' => $transaction_id,
        ]);
        
        $pusher_auth = get_static_option('pusher_app_push_notification_auth_token'); //"A4EEE003A0AEB2B95F78FAD12EA11D8E1C281448DD8D9B33B47F6E5EC47CEDEA";
        $pusher_auth_url = get_static_option('pusher_app_push_notification_auth_url'); //'https://fcaf9caf-509c-4611-a225-2e508593d6af.pushnotifications.pusher.com/publish_api/v1/instances/fcaf9caf-509c-4611-a225-2e508593d6af/publishes';
        
           $orderInfo = Order::find( $order_id);
        if(!is_null($orderInfo) && !is_null($pusher_auth) && !is_null($pusher_auth_url)){
            $response = Http::withToken($pusher_auth)->acceptJson()->post(
                $pusher_auth_url
            ,[
                "interests" => ["debug-seller".$orderInfo->seller_id, 'message'],
                "fcm" =>[
                    "notification" => [
                        "title" => "You have received a new order id #".$orderInfo->id,
                        "body" => ""
                    ]
                ]
            ]);
        }
    }
    
     public function sendSMS($receiverNumber, $message)
{
     $receiverNumber = preg_replace('/[^0-9+]/', '', $receiverNumber);

    try {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");

        $countrySupportsAlphaID = true; // Replace with actual check

        $senderID = $countrySupportsAlphaID ? 'Hudumapotal' : $twilio_number;

        $client = new Client($account_sid, $auth_token);
        $client->messages->create($receiverNumber, [
            'from' => $senderID,
            'body' => $message
        ]);

        Log::info("SMS Sent Successfully to $receiverNumber");

    } catch (RestException $e) {
      
        Log::warning("Twilio SMS Error: " . $e->getMessage());

    } catch (\Exception $e) {
        // Handle general errors
        Log::error("SMS Error: " . $e->getMessage());
    }
}      
}