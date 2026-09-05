<?php

namespace App\Http\Controllers\Frontend;

use App\Accountdeactive;
use App\AdminCommission;
use App\AdminNotification;
use App\ChildCategory;
use App\EditServiceHistory;
use App\ExtraService;
use App\Helpers\ServiceCalculationHelper;
use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Mail\OrderMail;
use App\Notifications\TicketNotification;
use App\OnlineServiceFaq;
use App\OrderCompleteDecline;
use App\Report;
use App\ReportChatMessage;
use App\Tax;
use FontLib\Table\Type\post;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Serviceadditional;
use App\Serviceinclude;
use App\Servicebenifit;
use App\Subcategory;
use App\Category;
use App\Country;
use App\Service;
use App\ServiceCity;
use App\ServiceArea;
use App\User;
use App\Day;
use App\Order;
use App\OrderAdditional;
use App\OrderInclude;
use App\Review;
use App\Schedule;
use App\ServiceCoupon;
use App\SupportTicket;
use App\SupportTicketMessage;
use App\ToDoList;
use App\Events\SupportMessage;
use App\Helpers\FlashMsg;
use App\PayoutRequest;
use App\AmountSettings;
use App\SellerVerify;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Mail;
use Modules\JobPost\Entities\BuyerJob;
use Modules\JobPost\Entities\JobRequest;
use Str;
use DB;
use Modules\LiveChat\Entities\LiveChatMessage;
use App\CustomOffer;
use App\Events\MessageSent;
use App\Notifications\OrderNotification;
use Modules\Wallet\Entities\Wallet;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;
use DateTime;
// use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;
class BuyerCustomController extends Controller
{
     private const CANCEL_ROUTE = 'frontend.order.payment.cancel.static';
    public function __construct()
    {
        $this->middleware('inactiveuser');
    }


    public function index(){
        
        //  return view('frontend.user.seller.custom.custom');
        
        
        $cities = ServiceCity::where('status',1)->get();
            $areas = ServiceArea::where('status',1)->get();
            $countries = Country::where('status',1)->get();
            
            $buyers = LiveChatMessage::select('buyer_id')
            ->with('buyerList')
            ->distinct('buyer_id')
            ->where('buyer_id','!=',NULL)
            ->where('seller_id', Auth::guard('web')->user()->id)
            ->get();
         
         $offer= CustomOffer::where('buyer_id', Auth::guard('web')->user()->id)
             ->orderBy('id','DESC')->paginate(6);

        return view('frontend.user.buyer.custom.custom', compact('countries', 'areas' ,'cities','buyers','offer'));
    }
    ///
    
    public function acceptOffer($id){
        // var_dump($id);
        
        
    }
    
    public function add_custom_offer(Request $request){
      
$Seller_id=Auth::guard('web')->user()->id;
               

    $created_job =  CustomOffer::create([
                'buyer_id'=>$request->buyer_id,
                'title'=>$request->title,
                'price'=>$request->Price,
                'end_date'=>$request->Days,
                'seller_id'=>$Seller_id,
                'description'=>$request->Description,

            ]);


            toastr_success('Custom Offer Send Successfully');
            return redirect()->route('seller.Custom');
        
    }


public function Withdrwal_custom_offer($id){
    
    CustomOffer::where('seller_id',Auth::guard('web')->user()->id)->where('id', $id)->update([
            'status' =>"2",
        ]);

        toastr_success('Custom Request Offer Widhraw Successfully');
        return back();
}
// offer decline late request
   public function Decline_custom_lateoffer($id=null,$offerid=null)
    {
        //  
      
        // deduct amount if cancel order
      $order_Detials =  Order::where('id',$id)->first();
      $user_id=  $order_Detials->buyer_id;
      
        Order::where('id',$id)->update(['payment_status'=>'pending','status'=>5]);
        CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $order_Detials->Custom_offer_id)->update([
            'status' =>"6",
        ]);
                 $seller = User::where('id',$order_Detials->seller_id)->first();
               $time_limit = $order_Detials->offer_time_end;
          try {
                            $mail_subject = __('Decline Order Request #') . ' / ' . __('Agizo Limekataliwa #');

                            $message_for_buyer = __('You have requested to cancel the order.') . ' / ' . __('Umeomba kughairi agizo.');
                            
                            
                            $message_for_seller_admin = __('Request for canceling order is rejected by admin') . 
                                                        ' / ' . __('Mnunuzi ameomba kughairi agizo kutokana na ucheleweshaji wa utoaji.');


                            Mail::to($order_Detials->email)->send(new OrderMail(strip_tags($mail_subject).$order_Detials->id,$order_Detials, $message_for_buyer));
                          //  Mail::to($seller->email)->send(new OrderMail(strip_tags($mail_subject).$order_Detials->id,$order_Detials,$message_for_seller_admin ));
                            Mail::to(get_static_option('site_global_email'))->send(new OrderMail(strip_tags($mail_subject).$order_Detials->id,$order_Detials, $message_for_seller_admin));
                $seller_info = User::find($order_Detials->seller_id);
                $buyer_info = User::find($order_Detials->buyer_id);
                $messages = get_static_option('Deliverytimeextensiondecline_message') ?? '';
                $messages = str_replace(["@name","@clientname","@newdate","@orderid"],[$seller_info->username,$buyer_info->username,$time_limit,$id],$messages);
                            Mail::to($seller_info->email)->send(new BasicMail([
                    'subject' => get_static_option('Deliverytimeextensiondecline_subject') ??  __('Delivery Time Extension Request Declined'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $order_Detials->seller_id;
              
                notifySeller(
                    $seller_id,
                    "Ombi lako la kuongezewa muda limekataliwa. / Your extension request was declined.", // p
                    "Ombi lako la kuongezewa muda limekataliwa.Endelea kufanya kazi kwa tarehe ya awali ya kukamilisha " . $time_limit . ". ", // sms
                    [
                        'type' => 'gernalnotifications',
                        // 'service_id' => $service->id,
                        'id' => uniqid('notif_'),
                        'details' => "Endelea kufanya kazi kwa tarehe ya awali ya kukamilisha " . $time_limit . " / Please continue working toward the original deadline: " . $time_limit . "."// p
                    ]
                );
                
                // for buyer
                
                 $messages = get_static_option('buyer-delivery-time-extension-declined-by-freelancer_message') ?? '';
                $messages = str_replace(["@name","@clientname","@orderid"],[$seller_info->username,$buyer_info->username,$id],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-delivery-time-extension-declined-by-freelancer_subject') ??  __('You approved the service completion'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $buyer_info->id;
              
                notifySeller(
                    $seller_id,
                    "Umekataa ombi la kuongeza muda wa kukamilisha (Order ID: $id). Mtoa huduma amepewa taarifa. / You declined the delivery time extension request (Order ID: $id). The freelancer has been notified.", //p
                    "Umekataa ombi la kuongeza muda wa kukamilisha (Order ID: $id). Mtoa huduma amepewa taarifa.", //sms
                    [
                        'type' => 'gernalnotifications',
                        'id' => uniqid('notif_'),
                        'Umekataa ombi la kuongeza muda wa kukamilisha (Order ID: '.$id.'). Mtoa huduma amepewa taarifa. You declined the delivery time extension request (Order ID: '.$id.'). The freelancer has been notified.' //p
                    ]
                );
                            
                        } catch (\Exception $e) {
                       
                            \Toastr::error($e->getMessage());
                                    }
                    toastr_success(__('order cancelled Request successfully Submitted.'));
                    return redirect()->back();
                }
    
    
        //decline offeer not approved yet
        
               public function Decline_pending_lateoffer($id=null,$offerid=null)
                    {
                        //  
                        
                        // deduct amount if cancel order
                      $order_Detials=  Order::where('id',$id)->first();
                     
                      
                        // var_dump($order_Detials->buyer_id);die;
                        // $order_Detials->total;
                      $user_id=  $order_Detials->buyer_id;
                         if (!empty($user_id)){
                            $user_wallet = Wallet::where('buyer_id',$user_id)->first();
                            if(empty($user_wallet)){
                                Wallet::create([
                                    'buyer_id' => $user_id,
                                    'balance' => 0,
                                    'status' => 0,
                                ]);
                            }
                             $user_wallet = Wallet::where('buyer_id',$user_id)->first();
                        }
                        
                
                        // create wallet history
                        $deposit = WalletHistory::create([
                            'buyer_id' => $user_id,
                            'amount' =>$order_Detials->total,
                            'payment_gateway' => 'Decline order Amount Return',
                            'payment_status' => 'complete',
                            'status' => 1,
                            'Action'=>'Decline order Amount Return',
                
                        ]);
                
                        $last_deposit_id = $deposit->id;
                        $user_wallet->balance += $order_Detials->total;
                         $user_wallet->save();
                        ////
                        Order::where('id',$id)->update(['payment_status'=>'return','status'=>4]);
                        CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $order_Detials->Custom_offer_id)->update([
                            'status' =>"6",
                        ]);
                         $seller = User::where('id',$order_Detials->seller_id)->first();
                          try {
                                            // $mail_subject = 'Decline Order #';
                                            // $message_for_buyer = 'You  cancelled an order ';
                                            // $message_for_seller_admin = 'Buyer Cancel order Due to Late delivery' ;
                                            $mail_subject = __('Decline Order #') . ' / ' . __('Agizo Limekataliwa #');
                
                $message_for_buyer = __('You cancelled an order.') . ' / ' . __('Umeghairi agizo.');
                
                $message_for_seller_admin = __('Buyer cancelled order due to late delivery.') . 
                                            ' / ' . __('Mnunuzi amekatisha agizo kutokana na ucheleweshaji wa utoaji.');
                
                                            Mail::to($order_Detials->email)->send(new OrderMail(strip_tags($mail_subject).$order_Detials->id,$order_Detials, $message_for_buyer));
                                            Mail::to($seller->email)->send(new OrderMail(strip_tags($mail_subject).$order_Detials->id,$order_Detials,$message_for_seller_admin ));
                                            Mail::to(get_static_option('site_global_email'))->send(new OrderMail(strip_tags($mail_subject).$order_Detials->id,$order_Detials, $message_for_seller_admin));
                                        } catch (\Exception $e) {
                                            \Toastr::error($e->getMessage());
                                        }
                        toastr_success(__('order successfully cancelled.'));
                        return redirect()->back();
                    }


        
        
        public function Decline_custom_offer($id){
      
       
        CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $id)->update([
                'status' =>"5",
            ]);
    
         try {
                                                   $details = CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $id)->first();      
                                                     ///email and notifcations
                                                    $seller_info = User::find($details->seller_id);
                                                   
                                                    $buyer_info = User::find(Auth::guard('web')->user()->id);
                                                    $messages = get_static_option('customerofferdecline_message') ?? '';
                                                    $messages = str_replace(["@name","@clientname"],[$seller_info->username,$buyer_info->username],$messages);
                                                   
                                                    Mail::to($seller_info->email)->send(new BasicMail([
                                                        'subject' =>  get_static_option('customerofferdecline_subject') ?? __('Your Custom Offer Was Declined'),
                                                        'message' => $messages ?? '',
                                                    ]));
                                                     
                                                    $seller_id = $details->seller_id;
                                                  
                                                    notifySeller(
                                                        $seller_id,
                                                        "Ofa yako maalum imekataliwa. / Your custom offer was declined.", //p
                                                        "Ofa yako maalum imekataliwa.Unaweza kuibadilisha au kutuma mpya.", //sms
                                                        [
                                                            'type' => 'gernalnotifications',
                                                          // 'service_id' => $service->id,
                                                            'id' => uniqid('notif_'),
                                                            'details' => "Ofa yako maalum imekataliwa. / You can revise or send a new proposal." //p
                                                        ]
                                                    ); 
                                                            
                                                    // for buyer
                                                    
                 $messages = get_static_option('buyer-custom-offer-decline_message') ?? '';
                $messages = str_replace(["@name","@clientname"],[$seller_info->name,$buyer_info->name],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-custom-offer-decline_subject') ??  __('Your service booking request has been sent'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $buyer_info->id;
              
                notifySeller(
                    $seller_id,
                    "Umekataa ofa maalum ya Huduma kutoka kwa $seller_info->username. / You declined the custom service offer  from $seller_info->username.", //p
                    "Umekataa ofa maalum ya Huduma kutoka kwa $seller_info->username.", //sms
                    [
                        'type' => 'gernalnotifications',
                        'id' => uniqid('notif_'),
                        'Umekataa ofa maalum ya Huduma kutoka kwa ' . $seller_info->username . '. / You declined the custom service offer from ' . $seller_info->username . '.' // p
                    ]
                );     
                                                            
                                                        } catch (\Exception $e) {
                                                       
                                                            \Toastr::error($e->getMessage());
                                                        }    
            toastr_success('Custom Request Offer Widhraw Successfully');
            return back();
    }

                                 public function Accept_custom_offer(Request $request){
                                   
                                  
                                   
                                     $request_details = CustomOffer::findOrFail($request->hiddenValue);
                                        if($request->selected_payment_gateway === 'manual_payment') {
                                            $request->validate([
                                                'manual_payment_image' => 'required|mimes:jpg,jpeg,png,pdf,webp'
                                            ]);
                                        }
                                        ///setting time
                                       
                                          $custom_id=$request->hiddenValue;
                                  
                                
                                  $dys=  CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $custom_id)->first();
                                   
                                        // date_default_timezone_set("Asia/Karachi");
                                              $days=$dys->end_date;
                                              $makesting= "+ $days days";
                                            //   var_dump($makesting);die;
                                              $time_limit = new DateTime($makesting);
                                  
                                        //(if Subscription else admin commission calculate)
                                        $admin_commmission = AdminCommission::first();
                                
                                        if($admin_commmission->system_type == 'subscription'){
                                            if(subscriptionModuleExistsAndEnable('Subscription')){
                                              $commission_amount = ($request_details->price*$admin_commmission->commission_charge)/100;
                                                // \Modules\Subscription\Entities\SellerSubscription::where('id', $request->seller_id)->update([
                                                //     'connect' => DB::raw(sprintf("connect - %s",(int)strip_tags(get_static_option('set_number_of_connect')))),
                                                // ]);
                                            }
                                        }else{
                                            if($admin_commmission->commission_charge_type=='percentage'){
                                                $commission_amount = ($request_details->price*$admin_commmission->commission_charge)/100;
                                            }else{
                                                $commission_amount = $admin_commmission->commission_charge;
                                            }
                                        }
                                
                                        if($request->selected_payment_gateway=='cash_on_delivery' || $request->selected_payment_gateway == 'manual_payment'){
                                            $payment_status='pending';
                                        }else{
                                            $payment_status='';
                                        }
                                
                                        //tax amount calculate
                                        $tax_amount =0;
                                        if(optional($request_details->price)->country_id != 0){
                                            $country_tax =  Tax::select('id','tax')->where('country_id',optional($request_details->price)->country_id)->first();
                                            $country_tax = $country_tax->tax ?? 0;
                                            $tax_amount = ($request_details->price * $country_tax) / 100;
                                        }
                                        $total = $request_details->price + $tax_amount;
                                
                                        //buyer info get
                                        $user = Auth::guard('web')->user();
                                        $is_check = Auth::guard('web')->check();
                                        $is_job_online = optional($request_details->job)->is_job_online;
                                
                                        $buyer_id =  $is_check ? $user->id : NULL;
                                        $name = $is_check ? $user->name : NULL;
                                        $email = $is_check ? $user->email : NULL;
                                        $phone = $is_check ? $user->phone : NULL;
                                        $post_code = $is_check ? $user->post_code : NULL;
                                        $address = $is_check ? $user->address : NULL;
                                        $city = $is_check ? $user->service_city : NULL;
                                        $area = $is_check ? $user->service_area : NULL;
                                        $country = $is_check ? $user->country_id : NULL;
                                
                                        $order_details = Order::create([
                                            'service_id' => '0',
                                            'seller_id' => $request_details->seller_id,
                                            'buyer_id' => $buyer_id,
                                            'name' => $name,
                                            'email' => $email,
                                            'phone' => $phone,
                                            'post_code' => $post_code ?? 0000,
                                            'address' => $address ?? " ",
                                            'city' => $city,
                                            'area' => $area,
                                            'country' => $country,
                                            'date' => 'No Date Created',
                                            'schedule' => 'No Schedule Created',
                                            'package_fee' => 0,
                                            'extra_service' => 0,
                                            'sub_total' => $total,
                                            'tax' => $tax_amount,
                                            'total' => $total,
                                            'commission_type' => $admin_commmission->commission_charge_type,
                                            'commission_charge' => $admin_commmission->commission_charge,
                                            'commission_amount' => $commission_amount,
                                            'status' => 0,
                                            'order_note' => NULL,
                                            'payment_gateway' => $request->selected_payment_gateway,
                                            'payment_status' => $payment_status,
                                            'order_from_job' => 'yes',
                                            'job_post_id' => '0',
                                            'is_order_online' => '1',
                                            'Custom_offer_id'=>$request_details->id,
                                            'offer_time_end'=>$time_limit
                                        ]);
                                            $last_order_id = $order_details->id;
                                ///change status
                                
                                
                                
                                        // invoice generate
                                        $invoiceNumber = 'INV'.$last_order_id;
                                        Order::where('id', $last_order_id)->update(['invoice' => $invoiceNumber]);
                                        ///
                                        
                                
                                        // variable for all payment gateway
                                        $global_currency = get_static_option('site_global_currency');
                                
                                        $usd_conversion_rate =  get_static_option('site_' . strtolower($global_currency) . '_to_usd_exchange_rate');
                                        $inr_exchange_rate = getenv('INR_EXCHANGE_RATE');
                                        $ngn_exchange_rate = getenv('NGN_EXCHANGE_RATE');
                                        $zar_exchange_rate = getenv('ZAR_EXCHANGE_RATE');
                                        $brl_exchange_rate = getenv('BRL_EXCHANGE_RATE');
                                        $idr_exchange_rate = getenv('IDR_EXCHANGE_RATE');
                                        $myr_exchange_rate = getenv('MYR_EXCHANGE_RATE');
                                
                                
                                        //todo: check payment gateway is wallet or not
                                        if(moduleExists('Wallet')){
                                            if ($request->selected_payment_gateway === 'wallet') {
                                                $order_details = Order::find($last_order_id);
                                                $random_order_id_1 = Str::random(30);
                                                $random_order_id_2 = Str::random(30);
                                                $new_order_id = $random_order_id_1.$last_order_id.$random_order_id_2;
                                                $buyer_id = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : NULL;
                                                $wallet_balance = Wallet::where('buyer_id',$buyer_id)->first();
                                
                                                if(!empty($wallet_balance)){
                                                    if($wallet_balance->balance >= $order_details->total){
                                                        
                                        $job_post_title = optional($request_details->job)->title;
                                        $title = Str::limit($job_post_title,20);
                                        $description = sprintf(__('Order id #%1$d Email: %2$s, Name: %3$s'),$last_order_id,$email,$name);
                                
                                        //Send order notification to seller
                                        $seller = User::where('id',$request_details->seller_id)->first();
                                        $buyer_id = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : NULL;
                                        $order_message = __('Your Custom Offer Has Been Accepted!');
                                        $seller->notify(new OrderNotification($last_order_id,$request_details->job_post_id, $request_details->seller_id, $buyer_id,$order_message));
                                                        //Send order email to buyer for cash on delivery
                                                        try {
                                                            $mail_subject = get_static_option('new_order_email_subject') ?? __('New Order #');
                                                            $message_for_buyer = get_static_option('new_order_buyer_message') ?? __('You have successfully placed an order #');
                                                            $message_for_seller_admin = get_static_option('new_order_admin_seller_message') ?? __('You have a new order #');
                                                           
                                                            
                                                            ///email and notifcations
                                                    $seller_info = User::find($request_details->seller_id);
                                                    $buyer_info = User::find($buyer_id);;
                                                    $messages = get_static_option('customerofferaccepted_message') ?? '';
                                                    $messages = str_replace(["@name","@clientname"],[$seller_info->username,$buyer_info->username],$messages);
                                                   
                                                    Mail::to($seller_info->email)->send(new BasicMail([
                                                        'subject' =>  get_static_option('customerofferaccepted_subject') ?? __('Your Custom Offer Has Been Accepted!'),
                                                        'message' => $messages ?? '',
                                                    ]));
                                                     
                                                    $seller_id = $request_details->seller_id;
                                                  
                                                    notifySeller(
                                                        $seller_id,
                                                        "Ofa yako maalum imekubaliwa! / Your custom offer was accepted!", //p
                                                        "Ofa yako maalum imekubaliwa!Anza kazi sasa kupitia dashibodi yako", //sms
                                                        [
                                                            'type' => 'gernalnotifications',
                                                          // 'service_id' => $service->id,
                                                            'id' => uniqid('notif_'),
                                                            'details' => "Anza kazi sasa kupitia dashibodi yako. / Get started now from your dashboard" //p
                                                        ]
                                                    ); 
                                                    
                                                    
                                                    // For buyer
                                                     $messages = get_static_option('buyer-custom-offer-accept_message') ?? '';
                                                    $messages = str_replace(["@name","@clientname"],[$seller_info->username,$buyer_info->username],$messages);
                                                   
                                                    Mail::to($buyer_info->email)->send(new BasicMail([
                                                        'subject' => get_static_option('buyer-custom-offer-accept_subject') ??  __('You have accepted the custom service offer'),
                                                        'message' => $messages ?? '',
                                                    ]));
                                                     
                                                    $seller_id = $buyer_info->id;
                                                  
                                                    notifySeller(
                                                        $seller_id,
                                                        "Ulikubali ofa maalum ya huduma (Order ID: $last_order_id) kutoka kwa $seller_info->username Kazi itaanza. / You accepted the custom service offer (Order ID: $last_order_id) from $seller_info->username Work will proceed.", //p
                                                        "Ulikubali ofa maalum ya huduma (Order ID: $last_order_id) kutoka kwa $seller_info->username Kazi itaanza.", //sms
                                                        [
                                                            'type' => 'gernalnotifications',
                                                            'id' => uniqid('notif_'),
                                                            'Ulikubali ofa maalum ya huduma (Order ID: '.$last_order_id.') kutoka kwa $seller_info->username Kazi itaanza. / You accepted the custom service offer (Order ID: '.$last_order_id.') from '.$seller_info->username.' Work will proceed.' //p
                                                        ]
                                                    );         
                                                            
                                                            
                                                        } catch (\Exception $e) {
                                                         
                                                            \Toastr::error($e->getMessage());
                                                        }
                                                        Order::where('id', $last_order_id)->update([
                                                            'payment_status' => 'complete',
                                                            'payment_gateway' => 'wallet',
                                                            'status'=>1
                                                        ]);
                                                        $custom_id=$request->hiddenValue;
                                                        CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $custom_id)->update([
                                                            'status' =>"1",'cjob_timelimit'=>$time_limit
                                                        ]);
                                
                                                        JobRequest::where('job_post_id', $order_details->job_post_id)->where('buyer_id', Auth::guard('web')->id())
                                                            ->update([
                                                                'is_hired' => 1,
                                                            ]);
                                
                                                        Wallet::where('buyer_id',$buyer_id)->update([
                                                            'balance' => $wallet_balance->balance-$order_details->total,
                                                        ]);
                                                         //wallet transaction                
                                          WalletHistory::create([
                                            'buyer_id' => Auth::guard('web')->user()->id,
                                            'amount' => $order_details->total,
                                            'payment_gateway' => 'Pay For Custom offer',
                                            'payment_status' => 'complete',
                                            'status' => 1,
                                
                                        ]);
                                                    }else{
                                                      
                                                           Order::where('id', $last_order_id)->delete();
                                                        $shortage_balance =  $order_details->total-$wallet_balance->balance;
                                                        toastr_warning('Your wallet has '.float_amount_with_currency_symbol($shortage_balance).' shortage to order this service. Please Credit your wallet first and try again.');
                                                        return back();
                                                    }
                                                }
                                                toastr_success('Your Order Created Successfully');
                                                return back();
                                            }
                                        }
                                
                                
                                        if ($request->selected_payment_gateway === 'cash_on_delivery') {
                                            $order_details = Order::find($last_order_id);
                                            $random_order_id_1 = Str::random(30);
                                            $random_order_id_2 = Str::random(30);
                                            $new_order_id = $random_order_id_1.$last_order_id.$random_order_id_2;
                                
                                            //Send order email to buyer for cash on delivery
                                            try {
                                                $mail_subject = get_static_option('new_order_email_subject') ?? __('New Order #');
                                                $message_for_buyer = get_static_option('new_order_buyer_message') ?? __('You have successfully placed an order #');
                                                $message_for_seller_admin = get_static_option('new_order_admin_seller_message') ?? __('You have a new order #');
                                                Mail::to($order_details->email)->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details, $message_for_buyer));
                                                Mail::to($seller->email)->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details,$message_for_seller_admin ));
                                                Mail::to(get_static_option('site_global_email'))->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details, $message_for_seller_admin));
                                            } catch (\Exception $e) {
                                                \Toastr::error($e->getMessage());
                                            }
                                            return redirect()->route('frontend.order.payment.success',$new_order_id);
                                        }
                                
                                
                                        // ============ ClickPesa (hosted checkout) ============
                                        if ($request->selected_payment_gateway === 'clickpesa') {
                                            try {
                                                $service = new \App\Services\ClickPesaService();
                                                $orderRef = \App\Services\ClickPesaService::makeOrderReference('custom', $last_order_id);
                                                $result = $service->createCheckoutLink([
                                                    'orderReference' => $orderRef,
                                                    'amount'         => $total,
                                                    'currency'       => env('CLICKPESA_CURRENCY', 'TZS'),
                                                    'description'    => $description ?? ('Custom offer #'.$last_order_id),
                                                    'customerName'   => $name,
                                                    'customerEmail'  => $email,
                                                    'callbackUrl'    => route('clickpesa.return'),
                                                ]);
                                                if (!$result['ok']) {
                                                    return back()->with(['msg' => 'ClickPesa: '.($result['error'] ?? 'unknown error'), 'type' => 'danger']);
                                                }
                                                session()->put('order_id', $last_order_id);
                                                session()->put('clickpesa_order_ref', $orderRef);
                                                return redirect()->away($result['url']);
                                            } catch (\Exception $e) {
                                                return back()->with(['msg' => $e->getMessage(), 'type' => 'danger']);
                                            }
                                        }

                                        if($request->selected_payment_gateway === 'manual_payment') {
                                            $order_details = Order::find($last_order_id);
                                            if($request->hasFile('manual_payment_image')){
                                                $manual_payment_image = $request->manual_payment_image;
                                                $img_ext = $manual_payment_image->extension();
                                
                                                $manual_payment_image_name = 'manual_attachment_'.time().'.'.$img_ext;
                                                if(in_array($img_ext,['jpg','jpeg','png','pdf'])){
                                                    $manual_image_path = 'assets/uploads/manual-payment/';
                                                    $manual_payment_image->move($manual_image_path,$manual_payment_image_name);
                                
                                                    Order::where('id',$last_order_id)->update([
                                                        'manual_payment_image'=>$manual_payment_image_name
                                                    ]);
                                                    CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $custom_id)->update([
                                            'status' =>"1",'cjob_timelimit'=>$time_limit
                                        ]);
                                                   
                                                }else{
                                                    return back()->with(['msg' => __('image type not supported'),'type' => 'danger']);
                                                }
                                            }
                                
                                            try {
                                                $mail_subject = get_static_option('new_order_email_subject') ?? __('New Order #');
                                                $message_for_buyer = get_static_option('new_order_buyer_message') ?? __('You have successfully placed an order #');
                                                $message_for_seller_admin = get_static_option('new_order_admin_seller_message') ?? __('You have a new order #');
                                                Mail::to($order_details->email)->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details, $message_for_buyer));
                                                Mail::to($seller->email)->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details,$message_for_seller_admin ));
                                                Mail::to(get_static_option('site_global_email'))->send(new OrderMail(strip_tags($mail_subject).$order_details->id,$order_details, $message_for_seller_admin));
                                            } catch (\Exception $e) {
                                                \Toastr::error($e->getMessage());
                                            }
                                            toastr_success('Your Order Created Successfully');
                                            return redirect()->route('buyer.job.orders');
                                
                                        }else{
                                            if ($request->selected_payment_gateway === 'paypal') {
                                
                                                try{
                                                    $paypal_mode = getenv('PAYPAL_MODE');
                                                    $client_id = $paypal_mode === 'sandbox' ? getenv('PAYPAL_SANDBOX_CLIENT_ID') : getenv('PAYPAL_LIVE_CLIENT_ID');
                                                    $client_secret = $paypal_mode === 'sandbox' ? getenv('PAYPAL_SANDBOX_CLIENT_SECRET') : getenv('PAYPAL_LIVE_CLIENT_SECRET');
                                                    $app_id = $paypal_mode === 'sandbox' ? getenv('PAYPAL_SANDBOX_APP_ID') : getenv('PAYPAL_LIVE_APP_ID');
                                
                                                    $paypal = XgPaymentGateway::paypal();
                                
                                                    $paypal->setClientId($client_id); // provide sandbox id if payment env set to true, otherwise provide live credentials
                                                    $paypal->setClientSecret($client_secret); // provide sandbox id if payment env set to true, otherwise provide live credentials
                                                    $paypal->setAppId($app_id); // provide sandbox id if payment env set to true, otherwise provide live credentials
                                                    $paypal->setCurrency($global_currency);
                                                    $paypal->setEnv($paypal_mode === 'sandbox'); //env must set as boolean, string will not work
                                                    $paypal->setExchangeRate($usd_conversion_rate); // if INR not set as currency
                                
                                                    $redirect_url = $paypal->charge_customer([
                                                        'amount' => $total, // amount you want to charge from customer
                                                        'title' => $title, // payment title
                                                        'description' => $description, // payment description
                                                        'ipn_url' => route('buyer.paypal.ipn.jobs'), //you will get payment response in this route
                                                        'order_id' => $last_order_id, // your order number
                                                        'track' => \Str::random(36), // a random number to keep track of your payment
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id), //payment gateway will redirect here if the payment is failed
                                                        'success_url' => route('buyer.orders'), // payment gateway will redirect here after success
                                                        'email' => $email, // user email
                                                        'name' => $name, // user name
                                                        'payment_type' => 'order', // which kind of payment your are receving from customer
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'paytm'){
                                                try{
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
                                                    $paytm->setCurrency($global_currency);
                                                    $paytm->setEnv($paytm_env === 'local'); // this must be type of boolean , string will not work
                                                    $paytm->setExchangeRate($inr_exchange_rate); // if INR not set as currency
                                
                                                    $redirect_url = $paytm->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.paytm.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                            }
                                            elseif($request->selected_payment_gateway === 'mollie'){
                                                try{
                                                    $mollie_key = getenv('MOLLIE_KEY');
                                                    $mollie = XgPaymentGateway::mollie();
                                                    $mollie->setApiKey($mollie_key);
                                                    $mollie->setCurrency($global_currency);
                                                    $mollie->setEnv(true); //env must set as boolean, string will not work
                                                    $mollie->setExchangeRate($usd_conversion_rate); // if INR not set as currency
                                
                                
                                                    $redirect_url = $mollie->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.mollie.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'stripe'){
                                                try{
                                                    $stripe_public_key = getenv('STRIPE_PUBLIC_KEY');
                                                    $stripe_secret_key = getenv('STRIPE_SECRET_KEY');
                                                    $stripe = XgPaymentGateway::stripe();
                                                    $stripe->setSecretKey($stripe_secret_key);
                                                    $stripe->setPublicKey($stripe_public_key);
                                                    $stripe->setCurrency($global_currency);
                                                    $stripe->setEnv(true); //env must set as boolean, string will not work
                                                    $stripe->setExchangeRate($usd_conversion_rate); // if INR not set as currency
                                
                                                    $redirect_url = $stripe->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.stripe.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                                }
                                                catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                            }
                                            elseif($request->selected_payment_gateway === 'razorpay'){
                                
                                                try{
                                                    $razorpay_api_key = getenv('RAZORPAY_API_KEY');
                                                    $razorpay_api_secret = getenv('RAZORPAY_API_SECRET');
                                                    $razorpay = XgPaymentGateway::razorpay();
                                                    $razorpay->setApiKey($razorpay_api_key);
                                                    $razorpay->setApiSecret($razorpay_api_secret);
                                                    $razorpay->setCurrency($global_currency);
                                                    $razorpay->setEnv(true); //env must set as boolean, string will not work
                                                    $razorpay->setExchangeRate($inr_exchange_rate); // if INR not set as currency
                                
                                                    $redirect_url = $razorpay->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.razorpay.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'flutterwave'){
                                                
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
                                                    $title ="custom offer";
                                                    $description="Pay For the Custom Order";
                                                    $redirect_url = $flutterwave->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.flutterwave.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                        //             CustomOffer::where('buyer_id',Auth::guard('web')->user()->id)->where('id', $custom_id)->update([
                                        //     'status' =>"1",'cjob_timelimit'=>$time_limit
                                        // ]);
                                                    session()->put('order_id',$last_order_id);
                                                    session()->put('custom_id', $custom_id);
                                                    session()->put('time_limit',$time_limit);
                                                    
                                                    return $redirect_url;
                                                }
                                                catch(\Exception $e){
                                                   
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'paystack'){
                                                try{
                                                    $paystack_public_key = getenv('PAYSTACK_PUBLIC_KEY');
                                                    $paystack_secret_key = getenv('PAYSTACK_SECRET_KEY');
                                                    $paystack_merchant_email = getenv('MERCHANT_EMAIL');
                                
                                                    $paystack = XgPaymentGateway::paystack();
                                                    $paystack->setPublicKey($paystack_public_key);
                                                    $paystack->setSecretKey($paystack_secret_key);
                                                    $paystack->setMerchantEmail($paystack_merchant_email);
                                                    $paystack->setCurrency($global_currency);
                                                    $paystack->setEnv(true); //env must set as boolean, string will not work
                                                    $paystack->setExchangeRate($ngn_exchange_rate); // if NGN not set as currency
                                
                                                    $redirect_url = $paystack->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.paystack.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' =>  $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                } catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'payfast'){
                                                try{
                                                    $random_order_id_1 = Str::random(30);
                                                    $random_order_id_2 = Str::random(30);
                                                    $payfast_merchant_id = getenv('PF_MERCHANT_ID');
                                                    $payfast_merchant_key = getenv('PF_MERCHANT_KEY');
                                                    $payfast_passphrase = getenv('PAYFAST_PASSPHRASE');
                                                    $payfast_env = getenv('PF_MERCHANT_ENV') === 'true';
                                
                                                    $payfast = XgPaymentGateway::payfast();
                                                    $payfast->setMerchantId($payfast_merchant_id);
                                                    $payfast->setMerchantKey($payfast_merchant_key);
                                                    $payfast->setPassphrase($payfast_passphrase);
                                                    $payfast->setCurrency($global_currency);
                                                    $payfast->setEnv($payfast_env); //env must set as boolean, string will not work
                                                    $payfast->setExchangeRate($zar_exchange_rate); // if ZAR not set as currency
                                
                                                    $redirect_url = $payfast->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.payfast.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' =>  $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                                } catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'cashfree'){
                                
                                                try{
                                                    $cashfree_env = getenv('CASHFREE_TEST_MODE') === 'true';
                                                    $cashfree_app_id = getenv('CASHFREE_APP_ID');
                                                    $cashfree_secret_key = getenv('CASHFREE_SECRET_KEY');
                                
                                                    $cashfree = XgPaymentGateway::cashfree();
                                                    $cashfree->setAppId($cashfree_app_id);
                                                    $cashfree->setSecretKey($cashfree_secret_key);
                                                    $cashfree->setCurrency($global_currency);
                                                    $cashfree->setEnv($cashfree_env); //true means sandbox, false means live , //env must set as boolean, string will not work
                                                    $cashfree->setExchangeRate($inr_exchange_rate); // if INR not set as currency
                                
                                                    $redirect_url = $cashfree->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.cashfree.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' =>  $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'instamojo'){
                                
                                                try{
                                                    $instamojo_client_id = getenv('INSTAMOJO_CLIENT_ID');
                                                    $instamojo_client_secret = getenv('INSTAMOJO_CLIENT_SECRET');
                                                    $instamojo_env = getenv('INSTAMOJO_TEST_MODE') === 'true';
                                
                                                    $instamojo = XgPaymentGateway::instamojo();
                                                    $instamojo->setClientId($instamojo_client_id);
                                                    $instamojo->setSecretKey($instamojo_client_secret);
                                                    $instamojo->setCurrency($global_currency);
                                                    $instamojo->setEnv($instamojo_env); //true mean sandbox mode , false means live mode //env must set as boolean, string will not work
                                                    $instamojo->setExchangeRate($inr_exchange_rate); // if INR not set as currency
                                
                                                    $redirect_url = $instamojo->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.instamojo.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => 'asdfasdfsdf',
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'marcadopago'){
                                                try{
                                                    $mercadopago_client_id = getenv('MERCADO_PAGO_CLIENT_ID');
                                                    $mercadopago_client_secret = getenv('MERCADO_PAGO_CLIENT_SECRET');
                                                    $mercadopago_env =  getenv('MERCADO_PAGO_TEST_MOD') === 'true';
                                
                                                    $marcadopago = XgPaymentGateway::marcadopago();
                                                    $marcadopago->setClientId($mercadopago_client_id);
                                                    $marcadopago->setClientSecret($mercadopago_client_secret);
                                                    $marcadopago->setCurrency($global_currency);
                                                    $marcadopago->setExchangeRate($brl_exchange_rate); // if BRL not set as currency, you must have to provide exchange rate for it
                                                    $marcadopago->setEnv($mercadopago_env); ////true mean sandbox mode , false means live mode
                                                    ///
                                                    $redirect_url = $marcadopago->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.marcadopago.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'midtrans'){
                                
                                                try{
                                                    $midtrans_env =  getenv('MIDTRANS_ENVAIRONTMENT') === 'true';
                                                    $midtrans_server_key = getenv('MIDTRANS_SERVER_KEY');
                                                    $midtrans_client_key = getenv('MIDTRANS_CLIENT_KEY');
                                
                                                    $midtrans = XgPaymentGateway::midtrans();
                                                    $midtrans->setClientKey($midtrans_client_key);
                                                    $midtrans->setServerKey($midtrans_server_key);
                                                    $midtrans->setCurrency($global_currency);
                                                    $midtrans->setEnv($midtrans_env); //true mean sandbox mode , false means live mode
                                                    $midtrans->setExchangeRate($idr_exchange_rate); // if IDR not set as currency
                                
                                                    $redirect_url = $midtrans->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.midtrans.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'squareup'){
                                
                                                try{
                                                    $squareup_env =  !empty(get_static_option('squareup_test_mode'));
                                                    $squareup_location_id = get_static_option('squareup_location_id');
                                                    $squareup_access_token = get_static_option('squareup_access_token');
                                                    $squareup_application_id = get_static_option('squareup_application_id');
                                
                                                    $squareup = XgPaymentGateway::squareup();
                                                    $squareup->setLocationId($squareup_location_id);
                                                    $squareup->setAccessToken($squareup_access_token);
                                                    $squareup->setApplicationId($squareup_application_id);
                                                    $squareup->setCurrency($global_currency);
                                                    $squareup->setEnv($squareup_env);
                                                    $squareup->setExchangeRate($usd_conversion_rate); // if USD not set as currency
                                
                                
                                                    $redirect_url = $squareup->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.squareup.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                
                                            }
                                            elseif($request->selected_payment_gateway === 'cinetpay'){
                                                try{
                                                    $cinetpay_env =  !empty(get_static_option('cinetpay_test_mode'));
                                                    $cinetpay_site_id = get_static_option('cinetpay_site_id');
                                                    $cinetpay_app_key = get_static_option('cinetpay_app_key');
                                
                                                    $cinetpay = XgPaymentGateway::cinetpay();
                                                    $cinetpay->setAppKey($cinetpay_app_key);
                                                    $cinetpay->setSiteId($cinetpay_site_id);
                                                    $cinetpay->setCurrency($global_currency);
                                                    $cinetpay->setEnv($cinetpay_env);
                                                    $cinetpay->setExchangeRate($usd_conversion_rate); // if ['XOF', 'XAF', 'CDF', 'GNF', 'USD'] not set as currency
                                
                                                    $redirect_url = $cinetpay->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.cinetpay.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                            }
                                            elseif($request->selected_payment_gateway === 'paytabs'){
                                                try{
                                                    $paytabs_env =  !empty(get_static_option('paytabs_test_mode'));
                                                    $paytabs_region = get_static_option('paytabs_region');
                                                    $paytabs_profile_id = get_static_option('paytabs_profile_id');
                                                    $paytabs_server_key = get_static_option('paytabs_server_key');
                                
                                                    $paytabs = XgPaymentGateway::paytabs();
                                                    $paytabs->setProfileId($paytabs_profile_id);
                                                    $paytabs->setRegion($paytabs_region);
                                                    $paytabs->setServerKey($paytabs_server_key);
                                                    $paytabs->setCurrency($global_currency);
                                                    $paytabs->setEnv($paytabs_env);
                                                    $paytabs->setExchangeRate($usd_conversion_rate); // if ['AED','EGP','SAR','OMR','JOD','USD'] not set as currency
                                
                                                    $redirect_url = $paytabs->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.paytabs.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                            }
                                            elseif($request->selected_payment_gateway === 'billplz'){
                                                try{
                                
                                                    $billplz_env =  !empty(get_static_option('billplz_test_mode'));
                                                    $billplz_key =  get_static_option('billplz_key');
                                                    $billplz_xsignature =  get_static_option('billplz_xsignature');
                                                    $billplz_collection_name =  get_static_option('billplz_collection_name');
                                
                                                    $billplz = XgPaymentGateway::billplz();
                                                    $billplz->setKey($billplz_key);
                                                    $billplz->setVersion('v4');
                                                    $billplz->setXsignature($billplz_xsignature);
                                                    $billplz->setCollectionName($billplz_collection_name);
                                                    $billplz->setCurrency($global_currency);
                                                    $billplz->setEnv($billplz_env);
                                                    $billplz->setExchangeRate($myr_exchange_rate); // if ['MYR'] not set as currency
                                                    $random_order_id_1 = Str::random(30);
                                                    $random_order_id_2 = Str::random(30);
                                                    $new_order_id = $random_order_id_1.$last_order_id.$random_order_id_2;
                                
                                                    $redirect_url = $billplz->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.billplz.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                            }
                                            elseif($request->selected_payment_gateway === 'zitopay'){
                                                try{
                                
                                                    $zitopay_env =  !empty(get_static_option('zitopay_test_mode'));
                                                    $zitopay_username =  get_static_option('zitopay_username');
                                
                                                    $zitopay = XgPaymentGateway::zitopay();
                                                    $zitopay->setUsername($zitopay_username);
                                                    $zitopay->setCurrency($global_currency);
                                                    $zitopay->setEnv($zitopay_env);
                                                    $zitopay->setExchangeRate($usd_conversion_rate);
                                
                                                    $random_order_id_1 = Str::random(30);
                                                    $random_order_id_2 = Str::random(30);
                                                    $new_order_id = $random_order_id_1.$last_order_id.$random_order_id_2;
                                
                                                    $redirect_url = $zitopay->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.zitopay.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                            }elseif ($request->selected_payment_gateway === 'kineticpay'){
                                                try{
                                
                                                    $kineticpay_env =  !empty(get_static_option('kineticpay_test_mode'));
                                                    $kineticpay_username =  get_static_option('kineticpay_username');
                                
                                                    $kineticpay = XgPaymentGateway::kineticpay();
                                                    $kineticpay->setMerchantKey($kineticpay_username);
                                                    $kineticpay->setBank(request()->kineticpay_bank);
                                                    $kineticpay->setCurrency($global_currency);
                                                    $kineticpay->setEnv($kineticpay_env);
                                                    $kineticpay->setExchangeRate($usd_conversion_rate);
                                
                                                    $random_order_id_1 = Str::random(30);
                                                    $random_order_id_2 = Str::random(30);
                                                    $new_order_id = $random_order_id_1.$last_order_id.$random_order_id_2;
                                                    $redirect_url = $kineticpay->charge_customer([
                                                        'amount' => $total,
                                                        'title' => $title,
                                                        'description' => $description,
                                                        'ipn_url' => route('buyer.kineticpay.ipn.jobs'),
                                                        'order_id' => $last_order_id,
                                                        'track' => \Str::random(36),
                                                        'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                        'success_url' => route('buyer.orders'),
                                                        'email' => $email,
                                                        'name' => $name,
                                                        'payment_type' => 'order',
                                                    ]);
                                                    session()->put('order_id',$last_order_id);
                                                    return $redirect_url;
                                
                                                }catch(\Exception $e){
                                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                                }
                                            }else{
                                                //todo check qixer meta data for new payment gateway
                                                $module_meta =  new ModuleMetaData();
                                                    $list = $module_meta->getAllPaymentGatewayList();
                                                    if (in_array($request->selected_payment_gateway,$list)){
                                                        //todo call the module payment gateway customerCharge function
                                                        $random_order_id_1 = Str::random(30);
                                                        $random_order_id_2 = Str::random(30);
                                                        $new_order_id = $random_order_id_1.$last_order_id.$random_order_id_2;
                                
                                                        $customerChargeMethod =  $module_meta->getChargeCustomerMethodNameByPaymentGatewayName($request->selected_payment_gateway);
                                                        try {
                                                            $returned_val = $customerChargeMethod([
                                                               'amount' => $total,
                                                                'title' => $title,
                                                                'description' => $description,
                                                                'ipn_url' => null,
                                                                'order_id' => $last_order_id,
                                                                'track' => \Str::random(36),
                                                                'cancel_url' => route(self::CANCEL_ROUTE,$last_order_id),
                                                                'success_url' => route('buyer.orders'),
                                                                'email' => $email,
                                                                'name' => $name,
                                                                'payment_type' => 'job',
                                                            ]);
                                                            
                                                            if(is_array($returned_val) && isset($returned_val['route'])){
                                        					   $return_url = !empty($returned_val['route']) ? $returned_val['route'] : route('homepage');
                                        						return redirect()->away($return_url); 
                                        					}
                                					
                                                        }catch (\Exception $e){
                                                            toastr_error( $e->getMessage());
                                                            return back();
                                                        }
                                                    }
                                            }
                                        }
                                    }
                                }


