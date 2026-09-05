<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enterprise;
use Str;
use Auth;
use Modules\JobPost\Entities\JobPackage;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;
use App\AdminCommission;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Illuminate\Support\Facades\Log;
use App\Mail\BasicMail;
use App\Mail\OrderMail;
use Illuminate\Support\Facades\Mail;
class EnterpriseController extends Controller
{
     private const CANCEL_ROUTE = 'frontend.order.payment.cancel.static';
    public function create()
    {
        $enterprise = Enterprise::where('user_id', auth()->id())->first();
        return view('frontend.user.buyer.enterprise.form', compact('enterprise'));
    }

    public function store(Request $request)
    {
       
        $enterprise = Enterprise::where('user_id', auth()->id())->first();

        $request->validate([
            'name' => 'required',
            'enterprise_email' => 'required|email|unique:enterprises,enterprise_email' . ($enterprise ? ',' . $enterprise->id : ''),
            'phone_number' => 'required|numeric',
            'office_address' => 'required',
            'representative_name' => 'required',
            'representative_position' => 'required',
            'representative_email' => 'required|email|unique:enterprises,representative_email' . ($enterprise ? ',' . $enterprise->id : ''),
            'representative_phone' => 'required|numeric',
        ]);

        if ($enterprise) {
            // Update existing enterprise
             $request->merge(['status' => 0]);
            $enterprise->update($request->all());
            toastr_success(__('Enterprise Updated Successfully!'));
        } else {
            // Create a new enterprise
            // variable for all payment gateway
                $global_currency = get_static_option('site_global_currency');
                $usd_conversion_rate =  get_static_option('site_' . strtolower($global_currency) . '_to_usd_exchange_rate');
                $inr_exchange_rate = getenv('INR_EXCHANGE_RATE');
                $ngn_exchange_rate = getenv('NGN_EXCHANGE_RATE');
                $zar_exchange_rate = getenv('ZAR_EXCHANGE_RATE');
                $brl_exchange_rate = getenv('BRL_EXCHANGE_RATE');
                $idr_exchange_rate = getenv('IDR_EXCHANGE_RATE');
                $myr_exchange_rate = getenv('MYR_EXCHANGE_RATE');
           $amount =   get_static_option('Company_request_amount');
              if(moduleExists('Wallet')){
                            if ($request->selected_payment_gateway === 'wallet') {
                              
                                $buyer_id = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : NULL;
                                $wallet_balance = Wallet::where('buyer_id',$buyer_id)->first();
                                if(!empty($wallet_balance)){
                                    if($wallet_balance->balance >= $amount){
                                        Wallet::where('buyer_id',$buyer_id)->update([
                                            'balance' => $wallet_balance->balance-$amount,
                                        ]);
                Enterprise::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'description' => $request->description,
                'business_type' => $request->business_type,
                'industry' => $request->industry,
                'enterprise_email' => $request->enterprise_email,
                'phone_number' => $request->phone_number,
                'website' => $request->website,
                'office_address' => $request->office_address,
                'representative_name' => $request->representative_name,
                'representative_position' => $request->representative_position,
                'representative_email' => $request->representative_email,
                'representative_phone' => $request->representative_phone,
            ]);
                                        //wallet transaction                
                                       WalletHistory::create([
                                        'buyer_id' => Auth::guard('web')->user()->id,
                                        'amount' => $amount,
                                        'payment_gateway' => 'Pay Fee for Enterprise Request',
                                        'payment_status' => 'complete',
                                        'status' => 1,
                            
                                    ]);
                                    
                                try {
           
                $messages = get_static_option('buyer-client-registers-enterprise-request_message') ?? '';
                $messages = str_replace(["@clientname"],[$request->name],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-client-registers-enterprise-request_subject') ??  __('Welcome to Huduma Portal Enterprise'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $buyer_info->id;
              
                notifySeller(
                    $seller_id,
                    "Karibu Huduma Portal Enterprise! Sasa unaweza kutangaza kazi na kusimamia huduma zako. / Welcome to Huduma Portal Enterprise! You can now post jobs and manage your service needs.", //p
                    "Karibu Huduma Portal Enterprise! Sasa unaweza kutangaza kazi na kusimamia huduma zako.", //sms
                    [
                        'type' => 'gernalnotifications',
                        'id' => uniqid('notif_'),
                        'Karibu Huduma Portal Enterprise! Sasa unaweza kutangaza kazi na kusimamia huduma zako. / Welcome to Huduma Portal Enterprise! You can now post jobs and manage your service needs.' //p
                    ]
                );
                      
                    } catch (\Exception $e) {
                      
                        return redirect()->back()->with(FlashMsg::item_new($e->getMessage()));
                    }     
                                
                                    }else{
                                      
                                             // BuyerJob::where('id', $last_order_id)->delete();
                                        $shortage_balance =  $amount-$wallet_balance->balance;
                                        toastr_warning('Your wallet has '.float_amount_with_currency_symbol($shortage_balance).' shortage to order this service. Please Credit your wallet first and try again.');
                                        return back();
                                    }
                                }
                            }
                        }
                        
                        if($request->selected_payment_gateway === 'flutterwave'){
                
                                try{
                                    $flutterwave_public_key = getenv("FLW_PUBLIC_KEY");
                                    $flutterwave_secret_key = getenv("FLW_SECRET_KEY");
                                    $flutterwave_secret_hash = getenv("FLW_SECRET_HASH");
                
                                    $flutterwave = XgPaymentGateway::flutterwave();
                                    $flutterwave->setPublicKey($flutterwave_public_key);
                                    $flutterwave->setSecretKey($flutterwave_secret_key);
                                    $flutterwave->setCurrency($global_currency);
                                    $flutterwave->setEnv(true); //env must set as boolean, string will not work
                                    $flutterwave->setExchangeRate($usd_conversion_rate); // if NGN not set as currency
                                    $title ="Company Request";
                                    $description="Pay For the Company Request";
                                    $redirect_url = $flutterwave->charge_customer([
                                        'amount' => $amount,
                                        'title' => $title,
                                        'description' => $description,
                                        'ipn_url' => route('buyer.CompanyRequest.payment.flutterwave.ipn'),
                                      
                                        'track' => \Str::random(36),
                                        'order_id' => Str::random(10), // Temporary order tracking
                                        'cancel_url' => route(self::CANCEL_ROUTE),
                                        'success_url' => route('buyer.all.jobs'),
                                        'email' => Auth::user()->email,
                                        'name' => Auth::user()->name,
                                        'payment_type' => 'order',
                                    ]);
                      
                                   session()->put('CRequest_payment_details', [
                                        // Enterprise data
                                        'user_id' => auth()->id(),
                                        'enterprise_name' => $request->name,
                                        'enterprise_description' => $request->description,
                                        'business_type' => $request->business_type,
                                        'industry' => $request->industry,
                                        'enterprise_email' => $request->enterprise_email,
                                        'phone_number' => $request->phone_number,
                                        'website' => $request->website,
                                        'office_address' => $request->office_address,
                                        'representative_name' => $request->representative_name,
                                        'representative_position' => $request->representative_position,
                                        'representative_email' => $request->representative_email,
                                        'representative_phone' => $request->representative_phone,
                                    
                                       
                                    ]);
                                                                       
                                   
                                    
                                    return $redirect_url;
                                }
                                catch(\Exception $e){
                                
                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                }
                
                            }
        
            toastr_success(__('Enterprise Request Created Successfully!'));
        }

        return redirect()->back();
    }
}

?>