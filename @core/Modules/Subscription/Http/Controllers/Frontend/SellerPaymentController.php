<?php

namespace Modules\Subscription\Http\Controllers\Frontend;

use App\Helpers\FlashMsg;
use App\Mail\BasicMail;
use App\Mail\OrderMail;
use App\Service;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\Subscription\Entities\SellerSubscription;
use Modules\Subscription\Entities\SubscriptionHistory;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;
use Str;
use Modules\Subscription\Entities\TemproryData;
use Auth;
use Illuminate\Support\Facades\DB;

class SellerPaymentController extends Controller
{
    protected function cancel_page()
    {
        return redirect()->route('seller.subscription.payment.cancel.static');
    }

    public function paypal_ipn_for_subs(Request $request)
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function paytm_ipn_for_subs(Request $request)
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function mollie_ipn_for_subs(Request $request)
    {
        $mollie_key = getenv('MOLLIE_KEY');
        $mollie = XgPaymentGateway::mollie();
        $mollie->setApiKey($mollie_key);
        $mollie->setEnv(true); //env must set as boolean, string will not work
        $payment_data = $mollie->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function stripe_ipn_for_subs(Request $request){

        $stripe_public_key = getenv('STRIPE_PUBLIC_KEY');
        $stripe_secret_key = getenv('STRIPE_SECRET_KEY');
        $stripe = XgPaymentGateway::stripe();
        $stripe->setSecretKey($stripe_secret_key);
        $stripe->setPublicKey($stripe_public_key);
        $stripe->setEnv(true); //env must set as boolean, string will not work

        $payment_data = $stripe->ipn_response();


        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function razorpay_ipn_for_subs(Request $request)
    {
        $razorpay_api_key = getenv('RAZORPAY_API_KEY');
        $razorpay_api_secret = getenv('RAZORPAY_API_SECRET');

        $razorpay = XgPaymentGateway::razorpay();
        $razorpay->setApiKey($razorpay_api_key);
        $razorpay->setApiSecret($razorpay_api_secret);

        $payment_data = $razorpay->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function flutterwave_ipn_for_subs(Request $request)
    {
      // Get the subscription data from session
    $subscription_data = $request->session()->get('subscription_data');
   
        $flutterwave_public_key = getenv("FLW_PUBLIC_KEY");
        $flutterwave_secret_key = getenv("FLW_SECRET_KEY");
        $flutterwave_secret_hash = getenv("FLW_SECRET_HASH");

        $flutterwave = XgPaymentGateway::flutterwave();
        $flutterwave->setPublicKey($flutterwave_public_key);
        $flutterwave->setSecretKey($flutterwave_secret_key);
        $flutterwave->setEnv(true); //env must set as boolean, string will not work

        $payment_data = $flutterwave->ipn_response();


        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            
            // $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $transaction_id = $payment_data['transaction_id'];
            // $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            
            // Now update the database with subscription data
          DB::transaction(function () use ($subscription_data, $transaction_id, &$last_subscription_id) {
            // Create SubscriptionHistory record
            $create_history = SubscriptionHistory::create([
                'subscription_id' => $subscription_data['subscription_id'],
                'seller_id' => $subscription_data['seller_id'],
                'type' => $subscription_data['type'],
                'service' => $subscription_data['service'],
                'job' => $subscription_data['job'],
                'connect' => $subscription_data['connect'],
                'coupon_code' => $subscription_data['coupon_code'],
                'coupon_type' => $subscription_data['coupon_type'],
                'coupon_amount' => $subscription_data['coupon_amount'],
                'price' => $subscription_data['price'],
                'expire_date' => $subscription_data['expire_date'],
                'payment_gateway' => $subscription_data['payment_gateway'],
                'payment_status' => 'complete', // Update status to successful
                'transaction_id' => $transaction_id, // Save the transaction ID
            ]);
                  // Get the order ID (last inserted ID)
            $order_id_history = $create_history->id;

                
            // Update or create SellerSubscription
            $seller_subscription = SellerSubscription::where('seller_id', $subscription_data['seller_id'])->first();

            // if ($seller_subscription) {
            //     $total = $seller_subscription->total;
            //   $seller_id_from_seller_subscription_table = SellerSubscription::where('seller_id', $subscription_data['seller_id'])->update([
            //         'subscription_id' => $subscription_data['subscription_id'],
            //         'type' => $subscription_data['type'],
            //         'initial_service' => $subscription_data['service'],
            //         'initial_job' => $subscription_data['job'],
            //         'initial_price' => $subscription_data['price'],
            //         'total' => $total + $subscription_data['price'],
            //         'initial_connect' => $subscription_data['connect'],
            //         'expire_date' => $subscription_data['expire_date'],
            //         'payment_gateway' => $subscription_data['payment_gateway'],
            //         'payment_status' => 'complete',
            //     ]);
            //      // Fetch the updated record to get its ID
            //     $updated_subscription = SellerSubscription::where('seller_id', $subscription_data['seller_id'])->first();
            //     $last_subscription_id = $updated_subscription->id;
            //     // dd($last_subscription_id);
            // } else {
            //   $create_subscription =  SellerSubscription::create([
            //         'subscription_id' => $subscription_data['subscription_id'],
            //         'type' => $subscription_data['type'],
            //         'initial_service' => $subscription_data['service'],
            //         'initial_job' => $subscription_data['job'],
            //         'price' => 0,
            //         'initial_price' => $subscription_data['price'],
            //         'total' => $subscription_data['price'],
            //         'initial_connect' => $subscription_data['connect'],
            //         'expire_date' => $subscription_data['expire_date'],
            //         'seller_id' => $subscription_data['seller_id'],
            //         'status' => 1, // Active status
            //         'payment_gateway' => $subscription_data['payment_gateway'],
            //         'payment_status' => 'complete',
            //     ]);
            //       // Get the newly created subscription ID
            //     $last_subscription_id = $create_subscription->id;
            // }
            
            if ($seller_subscription) {
                // Delete existing subscription
                SellerSubscription::where('seller_id', $subscription_data['seller_id'])->delete();
            }
            
            // Always insert new subscription
            $create_subscription = SellerSubscription::create([
                'subscription_id'   => $subscription_data['subscription_id'],
                'type'              => $subscription_data['type'],
                'initial_service'   => $subscription_data['service'],
                'initial_job'       => $subscription_data['job'],
                'Intial_promoted_services'=> $subscription_data['Intial_promoted_services'],
                'price'             => 0,
                'initial_price'     => $subscription_data['price'],
                'total'             => $subscription_data['price'],
                'initial_connect'   => $subscription_data['connect'],
                'expire_date'       => $subscription_data['expire_date'],
                'seller_id'         => $subscription_data['seller_id'],
                'status'            => 1, // Active status
                'payment_gateway'   => $subscription_data['payment_gateway'],
                'payment_status'    => 'complete',
                
                'initialprojects_allowed' => $subscription_data['initialprojects_allowed'],
                'initialcashback_percentage' => $subscription_data['initialcashback_percentage'],
                'sms_notifications' =>  $subscription_data['sms_notifications'],
                'Website_enabled' => $subscription_data['Website_enabled'],
                'personal_enabled' => $subscription_data['personal_enabled'],
                 'partialpayment_enabled' => $subscription_data['partialpayment_enabled'],
                 'aidescription_enabled' => $subscription_data['aidescription_enabled'],
                
            ]);
             $last_subscription_id = $create_subscription->id;
            
        });
        //update 
          $subscription_details = SellerSubscription::find($last_subscription_id);
           if($subscription_details){
                        SellerSubscription::where('id', $last_subscription_id)->update([
                            'payment_status' => 'complete',
                            'connect' => ($subscription_details->initial_connect + $subscription_details->connect),
                            'price' => $subscription_details->initial_price,
                            'initial_service' => $subscription_details->initial_service,
                            'initial_job' => $subscription_details->initial_job,
                            'status' => 1,
                           // 'expire_date' =>  $expire_date,
                        ]);
           }
            $this->send_subscription_mail($last_subscription_id);
            $new_order_id = wrapped_id($last_subscription_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        
        return $this->cancel_page();
    }

    public function paystack_ipn_for_subs(Request $request)
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function cashfree_ipn_for_subs(Request $request)
    {
        $cashfree_env = getenv('CASHFREE_TEST_MODE') === 'true';
        $cashfree_app_id = getenv('CASHFREE_APP_ID');
        $cashfree_secret_key = getenv('CASHFREE_SECRET_KEY');

        $cashfree = XgPaymentGateway::cashfree();
        $cashfree->setAppId($cashfree_app_id);
        $cashfree->setSecretKey($cashfree_secret_key);

        $payment_data = $cashfree->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function instamojo_ipn_for_success(Request $request)
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function marcadopago_ipn_for_subs(Request $request)
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }
    public function payfast_ipn_for_subs(Request $request)
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function midtrans_ipn_for_subs()
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }
    public function squareup_ipn_for_subs()
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function cinetpay_ipn_for_subs()
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }
    public function paytabs_ipn_for_subs()
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function billplz_ipn_for_subs()
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }
    public function zitopay_ipn_for_subs()
    {
        $zitopay_env =  !empty(get_static_option('zitopay_test_mode'));
        $zitopay_username =  get_static_option('zitopay_username');

        $zitopay = XgPaymentGateway::zitopay();
        $zitopay->setUsername($zitopay_username);
        $zitopay->setEnv($zitopay_env);

        $payment_data = $zitopay->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_subscription_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            return redirect()->route('seller.subscription.payment.success',$new_order_id);
        }
        return $this->cancel_page();
    }

    public function send_subscription_mail($order_id)
    {
        if(empty($order_id)){
            return redirect()->route('homepage');
        }

        $subscription_details = SellerSubscription::find($order_id);
        $user_name = User::select('name','username')->where('id',$subscription_details->seller_id)->first();
        $user_email = User::select('email')->where('id',$subscription_details->seller_id)->first();
        $expire_date  =    $subscription_details->expire_date;

        try {
            $message = get_static_option('buy_subscription_seller_message') ?? '';
            $message = str_replace(["@type","@price","@connect","@expiredate","@seller_name","@seller_email"],[$subscription_details->type,float_amount_with_currency_symbol($subscription_details->price),$subscription_details->connect,$expire_date,$user_name->username,$user_email],$message);
            Mail::to($user_email)->send(new BasicMail([
                'subject' =>get_static_option('buy_subscription_email_subject') ?? __('New Subscription'),
                'message' => $message
            ]));

            $message = get_static_option('buy_subscription_admin_message') ?? '';
            $message = str_replace(["@type","@price","@connect","@seller_name","@seller_email"],[$subscription_details->type,float_amount_with_currency_symbol($subscription_details->price),$subscription_details->connect,$user_name->username,$user_email],$message);
            Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                'subject' =>get_static_option('buy_subscription_email_subject') ?? __('New Subscription'),
                'message' => $message
            ]));

        } catch (\Exception $e) {
            \Toastr::error($e->getMessage());
        }
    }

    private function update_database($subscription_id, $transaction_id, $history_id)
    {
        $subscription_details = SellerSubscription::find($subscription_id);
        TemproryData::where('seller_id',Auth::guard('web')->user()->id)->delete();
        if($subscription_details){
            SellerSubscription::where('id', $subscription_id)->update([
                'payment_status' => 'complete',
                'transaction_id' => $transaction_id,
                'connect' => ($subscription_details->initial_connect + $subscription_details->connect),
                'price' => $subscription_details->initial_price,
                'initial_service' => $subscription_details->initial_service,
                'initial_job' => $subscription_details->initial_job,
                'status' => 1,
            ]);

            SubscriptionHistory::where('id', $history_id)->update([
                'payment_status' => 'complete',
            ]);
        }

    }
}
