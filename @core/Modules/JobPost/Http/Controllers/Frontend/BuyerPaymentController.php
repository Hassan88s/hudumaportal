<?php

namespace Modules\JobPost\Http\Controllers\Frontend;

use App\Helpers\FlashMsg;
use App\Mail\BasicMail;
use App\Mail\OrderMail;
use App\Order;
use App\User;
use App\CustomOffer;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\JobPost\Entities\JobRequest;
use Modules\JobPost\Entities\BuyerJob;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;
use App\Notifications\OrderNotification;
use Auth;
use App\JobAlertSubscription;
use Twilio\Rest\Client;
use App\Jobs\SendJobAlertSMS;
class BuyerPaymentController extends Controller
{
    protected function cancel_page()
    {
        return redirect()->route('job.order.payment.cancel.static');
    }

    public function paypal_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function paytm_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function mollie_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function stripe_ipn_for_jobs(Request $request){

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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function razorpay_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function flutterwave_ipn_for_jobs(Request $request)
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
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            if(!session()->has('custom_id')){
            $this->send_jobs_mail_for_job($order_id);
            }
                       // SMS Notifications
             $buyer_id = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : NULL;
             $get_buyer = User::where('id',$buyer_id)->first();
            $order_details = Order::find($order_id);
            $seller = User::where('id',$order_details->seller_id)->first();

             $order_id = $order_details->id;
             $this->send_order_notification($order_id);
            $new_order_id = wrapped_id($order_id);
            if(session()->has('custom_id')){
        $this->send_jobs_mail_for_custom($order_id);
        $custom_id = session()->get('custom_id');
        $time_limit = session()->get('time_limit');
        CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $custom_id)->update([
            'status' =>"1",'cjob_timelimit'=>$time_limit
        ]);
        session()->forget('custom_id');
      session()->forget('time_limit');
        }
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }
    
     public function flutterwave_ipn_for_jobspromoted(Request $request)
    {
        $flutterwave_public_key = getenv("FLW_PUBLIC_KEY");
        $flutterwave_secret_key = getenv("FLW_SECRET_KEY");
        $flutterwave_secret_hash = getenv("FLW_SECRET_HASH");

        $flutterwave = XgPaymentGateway::flutterwave();
        $flutterwave->setPublicKey($flutterwave_public_key);
        $flutterwave->setSecretKey($flutterwave_secret_key);
        $flutterwave->setEnv(true); //env must set as boolean, string will not work
            
        $payment_data = $flutterwave->ipn_response();
         $paymentData = session()->get('job_payment_details');
          if (!$paymentData) {
        return response()->json(['status' => 'failed', 'message' => 'No payment session found'], 400);
    }

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
               $order_id = $payment_data['order_id'];

                // Store the job in the database
               $job_id = $paymentData['job_id'] ?? null; // Get job ID from session

        if ($job_id) {
            // Update existing job
            $job = BuyerJob::find($job_id);
            if ($job) {
                $job->update([
                    'category_id' => $paymentData['category_id'],
                    'subcategory_id' => $paymentData['subcategory_id'],
                    'child_category_id' => $paymentData['child_category_id'],
                    'buyer_id' => $paymentData['buyer_id'],
                    'title' => $paymentData['title'],
                    'country_id' => $paymentData['country_id'],
                    'city_id' => $paymentData['city_id'],
                    'slug' => $paymentData['slug'],
                    'description' => $paymentData['description'],
                    'image' => $paymentData['image'],
                    'is_job_online' => $paymentData['is_job_online'],
                    'price' => $paymentData['price'],
                    'dead_line' => $paymentData['dead_line'],
                    'status' => $paymentData['status'],
                    'Days' => $paymentData['Days'],
                    'promoteddays' => $paymentData['promoteddays'],
                    'package_id' => $paymentData['package_id'],
                    'promoted' => 1,
                    'is_paid' => 1,
                     'no_of_hiring'=>$paymentData['no_of_hiring'],
                ]);
            }
        } else {
            // Create new job
            $created_job = BuyerJob::create([
                'category_id' => $paymentData['category_id'],
                'subcategory_id' => $paymentData['subcategory_id'],
                'child_category_id' => $paymentData['child_category_id'],
                'buyer_id' => $paymentData['buyer_id'],
                'title' => $paymentData['title'],
                'country_id' => $paymentData['country_id'],
                'city_id' => $paymentData['city_id'],
                'slug' => $paymentData['slug'],
                'description' => $paymentData['description'],
                'image' => $paymentData['image'],
                'is_job_online' => $paymentData['is_job_online'],
                'price' => $paymentData['price'],
                'dead_line' => $paymentData['dead_line'],
                'status' => $paymentData['status'],
                'Days' => $paymentData['Days'],
                'promoteddays' => $paymentData['promoteddays'],
                'package_id' => $paymentData['package_id'],
                'promoted' => 1,
                'is_paid' => 1,
            ]);
            
            $subscribers = JobAlertSubscription::where('category_id', $paymentData['category_id'])
                    ->with('freelancer')
                    ->get();
  
                foreach ($subscribers as $sub) {
                    if (!empty($sub->freelancer->phone)) {
                             $job_url = route('job.post.details', $paymentData['slug']); 
                           $freelancer_name = $sub->freelancer->username;
                          $message = "Habari {$freelancer_name}, kazi mpya inayolingana na ujuzi wako imewekwa. Ingia Huduma Portal uombe sasa! | Hello {$freelancer_name}, a new job matching your skills has been posted. Login to Huduma Portal and apply now! {$job_url} ";

                            dispatch(new SendJobAlertSMS($sub->freelancer->phone, $message));
                    }
                }
                
        }
         
                // Clear session data
                session()->forget('job_payment_details');
           
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.all.jobs');
        }
        return $this->cancel_page();
    }
    
         private function update_database_promoted_jobs_buyer($last_order_id)
    {
        $order_details = Order::find($last_order_id);
        BuyerJob::where('id', $last_order_id)->update([
             'promoted' => 1, 
             'is_paid' => 1, 
        ]);

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
      //  AdminNotification::create(['order_id' => $order_id]);

        // seller buyer notification
        $seller->notify(new OrderNotification($order_id,$order_details->service_id, $seller->id, $buyer_id,$order_message));

    }
    

    public function paystack_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function cashfree_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function instamojo_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function marcadopago_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }
    public function payfast_ipn_for_jobs(Request $request)
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function midtrans_ipn_for_jobs()
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }
    public function squareup_ipn_for_jobs()
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function cinetpay_ipn_for_jobs()
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }
    public function paytabs_ipn_for_jobs()
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function billplz_ipn_for_jobs()
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }
    public function zitopay_ipn_for_jobs()
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
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function kineticpay_ipn_for_jobs()
    {
        $kineticpay_env =  !empty(get_static_option('kineticpay_test_mode'));
        $kineticpay_username =  get_static_option('kineticpay_username');

        $kineticpay = XgPaymentGateway::kineticpay();
        $kineticpay->setMerchantKey($kineticpay_username);
        $kineticpay->setBank(request()->kineticpay_bank);
        $kineticpay->setEnv($kineticpay_env);

        $payment_data = $kineticpay->ipn_response();

        if (isset($payment_data['status']) && $payment_data['status'] === 'complete'){
            $order_id = $payment_data['order_id'];
            $history_id = session()->get('history_id');
            $this->update_database($order_id, $payment_data['transaction_id'],$history_id);
            $this->send_jobs_mail($order_id);
            $new_order_id = wrapped_id($order_id);
            toastr_success(__('Your Order Created Successfully'));
            return redirect()->route('buyer.orders');
        }
        return $this->cancel_page();
    }

    public function send_jobs_mail($order_id)
    {
        if(empty($order_id)){
            return redirect()->route('homepage');
        }
        $order_details = Order::find($order_id);
        $seller_email = User::select('email')->where('id',$order_details->seller_id)->first();
        //Send order email to buyer
        try {
            $mail_subject = get_static_option('new_order_email_subject') ?? __('New Order #');
            $message_for_buyer = get_static_option('new_order_buyer_message') ?? __('You have successfully placed an order #');
            $message_for_seller_admin = get_static_option('new_order_admin_seller_message') ?? __('You have a new order #');
            Mail::to($order_details->email)->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details, $message_for_buyer));
            Mail::to($seller_email)->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details, $message_for_seller_admin));
            Mail::to(get_static_option('site_global_email'))->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details, $message_for_seller_admin));
        } catch (\Exception $e) {
            return redirect()->back()->with(FlashMsg::error($e->getMessage()));
        }
    }
    
    
    
    
    
    
    public function send_jobs_mail_for_job($order_id)
    {
        if(empty($order_id)){
            return redirect()->route('homepage');
        }
        $order_details = Order::find($order_id);
        $seller_email = User::select('email')->where('id',$order_details->seller_id)->first();
        //Send order email to buyer
        try {
             $seller_info = User::find($order_details->seller_id);
                $buyer_info =User::find($order_details->buyer_id);;
                $messages = get_static_option('jobhiring_message') ?? '';
                $messages = str_replace(["@name","@clientname"],[$seller_info->username,$buyer_info->username],$messages);
               
                Mail::to($seller_info->email)->send(new BasicMail([
                    'subject' => get_static_option('jobhiring_subject') ??  __('Congratulations! Your Application Has Been Accepted'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $seller_info->id;
              
                notifySeller(
                    $seller_id,
                    "Ombi lako la kazi limekubaliwa!. / Your job application was accepted!", //p
                    "Ombi lako la kazi limekubaliwa!Unaweza sasa kuanza kazi kwenye mradi huu", //sms
                    [
                        'type' => 'gernalnotifications',
                      // 'service_id' => $service->id,
                        'id' => uniqid('notif_'),
                        'details' => "Unaweza sasa kuanza kazi kwenye mradi huu. / You can now start working on the project." //p
                    ]
                );
                // for buyer
                
                 $messages = get_static_option('buyer-job-hire-to-freelancer_message') ?? '';
                $messages = str_replace(["@name","@clientname","@jobid"],[$seller_info->username,$buyer_info->username,$order_details->job_post_id],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-job-hire-to-freelancer_subject') ??  __('You have successfully hired a freelancer'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $buyer_info->id;
              
                notifySeller(
                    $seller_id,
                    "You’ve hired $seller_info->name for your job (Job ID: $order_details->job_post_id). Track progress in your dashboard.", //p
                    "You’ve hired $seller_info->name for your job (Job ID: $order_details->job_post_id). Track progress in your dashboard.", //sms
                    [
                        'type' => 'gernalnotifications',
                        'id' => uniqid('notif_'),
                        'You’ve hired '.$seller_info->name.' for your job (Job ID: '.$order_details->job_post_id.'). Track progress in your dashboard.' //p
                    ]
                ); 
        } catch (\Exception $e) {
         
            return redirect()->back()->with(FlashMsg::error($e->getMessage()));
        }
    }
    
    
    
    public function send_jobs_mail_for_custom($order_id)
    {
        if(empty($order_id)){
            return redirect()->route('homepage');
        }
        $order_details = Order::find($order_id);
        $seller_email = User::select('email')->where('id',$order_details->seller_id)->first();
        //Send order email to buyer
        try {
        
               // Get user info
            $seller_info = User::find($order_details->seller_id);
            $buyer_info = User::find($order_details->buyer_id);
            
            // Notify Seller
            $messages = get_static_option('customerofferaccepted_message') ?? '';
            $messages = str_replace(["@name", "@clientname"], [$seller_info->username, $buyer_info->username], $messages);
            
            Mail::to($seller_info->email)->send(new BasicMail([
                'subject' => get_static_option('customerofferaccepted_subject') ?? __('Your Custom Offer Has Been Accepted!'),
                'message' => $messages,
            ]));
            
            notifySeller(
                $seller_info->id,
                "Your custom offer was accepted!", // push
                "Ofa yako maalum imekubaliwa! Anza kazi sasa kupitia dashibodi yako", // sms
                [
                    'type' => 'gernalnotifications',
                    'id' => uniqid('notif_'),
                    'details' => "Get started now from your dashboard" // push
                ]
            );
            
            // Notify Buyer
            $messages = get_static_option('buyer-custom-offer-accept_message') ?? '';
            $messages = str_replace(["@name", "@clientname"], [$seller_info->username, $buyer_info->username], $messages);
            
            Mail::to($buyer_info->email)->send(new BasicMail([
                'subject' => get_static_option('buyer-custom-offer-accept_subject') ?? __('You have accepted the custom service offer'),
                'message' => $messages,
            ]));
            
            notifySeller(
                $buyer_info->id,
                "You accepted the custom service offer (Order ID: $order_id) from $seller_info->username. Work will proceed.", // push
                "Ulikubali ofa maalum ya huduma (Order ID: $order_id) kutoka kwa $seller_info->username. Kazi itaanza.", // sms
                [
                    'type' => 'gernalnotifications',
                    'id' => uniqid('notif_'),
                    'details' => "You accepted the custom service offer (Order ID: $order_id) from $seller_info->username. Work will proceed." // push
                ]
            );         
            
            
            
        } catch (\Exception $e) {
        
            return redirect()->back()->with(FlashMsg::error($e->getMessage()));
        }
    }

    private function update_database($last_order_id, $transaction_id, $history_id)
    {
        $order_details = Order::find($last_order_id);
        Order::where('id', $last_order_id)->update([
            'payment_status' => 'complete',
            'transaction_id' => $transaction_id,
            'status' => 1,
        ]);

        JobRequest::where('job_post_id', $order_details->job_post_id)->where('seller_id', $order_details->seller_id)
            ->update([
            'is_hired' => 1,
        ]);
    }
    
         public function sendSMS($receiverNumber, $message)
{
       $receiverNumber = preg_replace('/[^0-9+]/', '', $receiverNumber);
    try {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");
         $countrySupportsAlphaID = true; // Replace with actual check based on the receiver's country

        $senderID = $countrySupportsAlphaID ? 'Hudumapotal' : $twilio_number;
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($receiverNumber, [
            'from' => $senderID,
            'body' => $message
        ]);
        
        info(__('SMS Sent Successfully to ') . $receiverNumber);

    } catch (Exception $e) {
  
        info("SMS Error: " . $e->getMessage());
         \Log::info(  $e->getMessage());
         \Log::error("SMS Error: " . $e->getMessage());
    }
}
}
