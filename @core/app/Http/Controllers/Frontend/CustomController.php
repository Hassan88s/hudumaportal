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
use DateTime;
class CustomController extends Controller
{
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
         
         $offer= CustomOffer::where('seller_id', Auth::guard('web')->user()->id)
             ->orderBy('id','DESC')->paginate(6);

        return view('frontend.user.seller.custom.custom', compact('countries', 'areas' ,'cities','buyers','offer'));
    }
    
    public function add_custom_offer(Request $request){
       
                $request->validate([
                   
                    'buyer_id' => 'required',
                    'title' => 'required',
                     'Description' => 'required',
                    'Days' => 'required|numeric',
                    'Price' => 'required|numeric',
                 
                ]);
              
              $Seller_id=Auth::guard('web')->user()->id;
            
               $buyer_id=$request->buyer_id;
              $buyerDetials= User::where('id',$buyer_id)->first();
             

              $created_job =  CustomOffer::create([
                'buyer_id'=>$request->buyer_id,
                'title'=>$request->title,
                'price'=>$request->Price,
                'end_date'=>$request->Days,
                'seller_id'=>$Seller_id,
                'description'=>$request->Description,
                'cjob_timelimit'=>'NULL',

            ]);

            // admin notification add
            AdminNotification::create(['job_post_id' => $created_job->id]);

            try {
                $Seller_name=Auth::guard('web')->user()->name;
             
                
    //             $message_body_buyer = __('Hello, ') . $Seller_name . __(' sent you a custom offer.') . '</br>' . 
    // '<span class="verify-code">' . __('Job Title: ') . $request->title . '</span></br>' . 
    // '<span class="verify-code">' . __('Job Price: ') . $request->Price . '</span></br>' . 
    // '<span class="verify-code">' . __('Delivery Days: ') . $request->Days . '</span></br><br>' . 
    // '-----------------------</br><br>' . 
    // 'Habari, ' . $Seller_name . ' amekutumia ofa maalum.</br>' . 
    // '<span class="verify-code">Kichwa cha Kazi: ' . $request->title . '</span></br>' . 
    // '<span class="verify-code">Bei ya Kazi: ' . $request->Price . '</span></br>' . 
    // '<span class="verify-code">Siku za Uwasilishaji: ' . $request->Days . '</span>';

                    
    //                     Mail::to($buyerDetials->email)->send(new BasicMail([
    //                         'subject' => __('New Custom Order Request'),
    //                         'message' => $message_body_buyer
    //                     ]));
                       
                       
                       
                $message = 'New Custom Order Request';
                $message = str_replace(["@job_post_id"],[$created_job->id],$message);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => __('New Custom Order Request'),
                    'message' => $message
                ]));
                
                 ///email and notifcations
                $seller_info = User::find($Seller_id);
                $buyer_info = $buyerDetials;
                $messages = get_static_option('customeroffersent_message') ?? '';
                $messages = str_replace(["@name","@clientname"],[$seller_info->username,$buyer_info->username],$messages);
               
                Mail::to($seller_info->email)->send(new BasicMail([
                    'subject' =>  get_static_option('customeroffersent_subject') ?? __('Your Custom Offer Has Been Sent'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $Seller_id;
              
                notifySeller(
                    $seller_id,
                    "Ofa maalum imetumwa kwa mafanikio! / Custom offer sent successfully", //p
                    "Ofa maalum imetumwa kwa mafanikio!Tutakutaarifu mteja atakapojibu.", //sms
                    [
                        'type' => 'gernalnotifications',
                    
                        'id' => uniqid('notif_'),
                        'details' => "Tutakutaarifu mteja atakapojibu. / We’ll notify you when the client responds." //p
                    ]
                );    
                
                // For buyer
                 $messages = get_static_option('buyer-custom-offer-recieved_message') ?? '';
                $messages = str_replace(["@name","@clientname"],[$seller_info->username,$buyer_info->username],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-custom-offer-recieved_subject') ??  __('You have received a custom service offer'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $buyer_info->id;
              
                notifySeller(
                    $seller_id,
                    "Umepokeaa ofa ya huduma maalum kutoka kwa $seller_info->username Kagua na jibu. / You have a new custom service offer  from $seller_info->username Review and respond.", //p
                    "Umepokeaa ofa ya huduma maalum kutoka kwa $seller_info->username Kagua na jibu.", //sms
                    [
                        'type' => 'gernalnotifications',
                        'id' => uniqid('notif_'),
                        'Umepokeaa ofa ya huduma maalum kutoka kwa '.$seller_info->username.' Kagua na jibu. / You have a new custom service offer  from '.$seller_info->username.' Review and respond..' //p
                    ]
                );     
                
                
            } catch (\Exception $e) {
              
                FlashMsg::item_new($e->getMessage());
            }

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


}
