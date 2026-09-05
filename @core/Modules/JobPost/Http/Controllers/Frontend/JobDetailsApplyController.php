<?php

namespace Modules\JobPost\Http\Controllers\Frontend;

use App\AdminCommission;
use App\AdminNotification;
use App\Category;
use App\JobPost;
use App\JobRequestTicket;
use App\Mail\BasicMail;
use App\Notifications\JobApplyNotification;
use App\SellerVerify;
use App\Service;
use App\Subcategory;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\JobPost\Entities\BuyerJob;
use Modules\JobPost\Entities\JobRequest;
use Modules\JobPost\Entities\SellerViewJob;
use App\Helpers\FlashMsg;
use Twilio\Rest\Client;
class JobDetailsApplyController extends Controller
{
    
    public function job_details($slug=null){



        $current_date = date('Y-m-d h:i:s');
        $job_details = BuyerJob::with(['job_request','buyer'])->where('slug',$slug)->firstOrFail();
        $same_buyer_jobs = BuyerJob::where('buyer_id',$job_details->buyer_id)
            ->where('is_job_on', 1)
            ->where('dead_line', '>=' ,$current_date)
            ->take(6)->get()
            ->except($job_details->id);
     
        $similar_jobs = BuyerJob::where(['is_job_on'=> 1 ,['dead_line', '>=' ,$current_date], 'category_id'=> $job_details->category_id ])->take(6)->inRandomOrder()->get()->except($job_details->id);

        $job_view = BuyerJob::select('view')->where('id', $job_details->id)->first();
        $view_count = $job_view->view + 1;
        BuyerJob::where('id', $job_details->id)->update([
            'view' => $view_count,
        ]);

        $seller = Auth::guard('web')->user();
        if($seller && $seller->user_type == 0) {
            $seller_job_view_count = SellerViewJob::where('seller_id', $seller->id)->where('job_post_id', $job_details->id)->count();
            if ($seller_job_view_count < 1){
                SellerViewJob::create([
                    'job_post_id' => $job_details->id,
                    'seller_id' => $seller->id,
                ]);
            }
        }

        $is_job_hired = JobRequest::where('job_post_id',$job_details->id)->where('is_hired',1)->count();

        // Admin Notification read
        if(Auth::guard('admin')->check()){
           AdminNotification::where('job_post_id', $job_details->id)->update(['status' => 1]);
        }

        return view('jobpost::frontend.jobs.job-details',compact('job_details','same_buyer_jobs','similar_jobs','is_job_hired'));
    }

    //job apply
    public function job_apply(Request $request){

        if(Auth::guard('web')->check() && Auth::guard('web')->user()->user_type === 1){
            toastr_warning(__('For create an offer you must register as a seller'));
            return back();
        }

        if($request->isMethod('post')){
            if(Auth::guard('web')->check()){

                //todo: check subscription step:1 commission type check step:2 subscription check step:3 subscription
                // type example(monthly, yearly, liveTime) Step:4 seller total job request count
                //commission type check
                $commission = AdminCommission::first();
                if($commission->system_type == 'subscription'){
                    if(subscriptionModuleExistsAndEnable('Subscription')){
                        $seller_subscription = \Modules\Subscription\Entities\SellerSubscription::where('seller_id', Auth::guard('web')->user()->id)->first();
                       
                        if(is_null($seller_subscription)){
                            toastr_warning(__('you have to subscribe a package in order to apply job post.'));
                            return back();
                        }
                        if($seller_subscription->status == '0'){
                            toastr_warning(__('Your Subscribe Package is deactivated Due to Some Issues.'));
                            return back();
                        }

                        // Seller Service count
                        $seller_job_request_count = JobRequest::where('seller_id', Auth::guard('web')->user()->id)->count();
                       
                        if ($seller_subscription->type === 'monthly') {
                        // check seller jobs & expire date
                        if ($seller_subscription->initial_job <= $seller_job_request_count) {
                            toastr_error(__('Your Subscription is expired For Apply New Job'));
                            return redirect()->back();
                        } elseif ($seller_subscription->expire_date <= Carbon::now()) {
                            toastr_error(__('Your Subscription is expired'));
                            return redirect()->back();
                        }
                    } elseif ($seller_subscription->type === 'yearly') {
                        // check seller jobs & expire date
                        if ($seller_subscription->initial_job <= $seller_job_request_count) {
                            toastr_error(__('Your Subscription is expired For Apply New Job'));
                            return redirect()->back();
                        } elseif ($seller_subscription->expire_date <= Carbon::now()) {
                            toastr_error(__('Your Subscription is expired'));
                            return redirect()->back();
                        }
                    }


                        
                        // old code
                        
                        // if ($seller_subscription->type === 'monthly'){
                        //     // check seller connect,service,expire date
                        //     if ($seller_subscription->connect == 0){
                        //         toastr_error(__('Your Subscription is expired'));
                        //         return redirect()->back();
                        //     }elseif ($seller_subscription->initial_job <= $seller_job_request_count){
                        //         toastr_error(__('Your Subscription is expired For Apply New Job'));
                        //         return redirect()->back();
                        //     }elseif ($seller_subscription->expire_date <= Carbon::now()){
                        //         toastr_error(__('Your Subscription is expired'));
                        //         return redirect()->back();
                        //     }
                        // }elseif ($seller_subscription->type === 'yearly'){
                        //     // check seller connect,service,expire date
                        //     if ($seller_subscription->connect == 0){
                        //         toastr_error(__('Your Subscription is expired'));
                        //         return redirect()->back();
                        //     }elseif ($seller_subscription->initial_job <= $seller_job_request_count){
                        //         toastr_error(__('Your Subscription is expired'));
                        //         return redirect()->back();
                        //     }elseif ($seller_subscription->expire_date <= Carbon::now()){
                        //         toastr_error(__('Your Subscription is expired'));
                        //         return redirect()->back();
                        //     }
                        // }
                    }
                }

                if($request->expected_salary == '' || $request->cover_letter == ''){
                    toastr_warning(__('Please enter your budget and description'));
                    return back();
                }
                if($request->expected_salary < 1){
                    toastr_warning(__('Your budget can not be lesss than 1'));
                    return back();
                }
                if($request->expected_salary > $request->job_price){
                    toastr_warning(__('Your budget must less than the original price'));
                    return back();
                }
                $request->validate([
                    'cover_letter'=>'required',
                ]);
                $seller_request_count = JobRequest::select('seller_id')
                    ->where('seller_id',Auth::guard('web')->user()->id)
                    ->where('job_post_id',$request->job_post_id)
                    ->count();
                if($seller_request_count >=1){
                    toastr_warning(__('You have already applied for this job.'));
                    return redirect()->back();
                }
                JobRequest::create([
                    'seller_id'=> Auth::guard('web')->user()->id,
                    'buyer_id'=> $request->buyer_id,
                    'job_post_id'=> $request->job_post_id,
                    'expected_salary'=> $request->expected_salary,
                    'cover_letter'=> $request->cover_letter,
                ]);

                try {
                    $seller_name = Auth::guard('web')->user()->name;
                    $seller_username = Auth::guard('web')->user()->username;
                    $message = $message = __('Hello,') . "\n\n" . 
           __('A new application has been created for your job.') . "\n\n" . 
           __('Job post ID:') . ' ' . $request->job_post_id . "\n\n" . 
           "--\n\n" . 
           "Habari,\n\n" . 
           "Ombi jipya limeundwa kwa kazi yako.\n\n" . 
           "Kitambulisho cha Chapisho la Kazi: " . $request->job_post_id;

                    $message = str_replace(["@job_post_id"],[$request->job_post_id],$message);
                    $message = str_replace(["@seller_name"],["<a href='" . route('about.seller.profile', $seller_username) . "'>$seller_name</a>"], $message);
                    // Mail::to($request->buyer_email)->send(new BasicMail([
                    //     'subject' => get_static_option('job_apply_subject') ?? __('New Application Created'),
                    //     'message' => $message
                    // ]));
                  
                     $seller_username = $seller_username; 
                    // $job_title = $request->title; 
                    // $website_link = "https://hudumaportal.co.tz/";
                    
                    // $message_for_buyer = "$seller_username has applied for your job titled: $job_title. Visit our website at $website_link for more details. " . 
                    //                      "$seller_username ameomba kazi yako yenye kichwa: $job_title. Tembelea tovuti yetu $website_link kwa maelezo zaidi.";

                    
                     $get_buyer = User::where('email',$request->buyer_email)->first();
                    // sendSMS($get_buyer->phone, strip_tags($message_for_buyer));
                    
                    
                    
                    ///email and notifcations
                $seller_info = User::find(Auth::guard('web')->user()->id);
                $buyer_info = $get_buyer;
                $messages = get_static_option('applyjob_message') ?? '';
                $messages = str_replace(["@name","@clientname"],[$seller_info->username,$buyer_info->username],$messages);
               
                Mail::to($seller_info->email)->send(new BasicMail([
                    'subject' => get_static_option('applyjob_subject') ??  __('Your Job Application Has Been Submitted'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $seller_info->id;
              
                notifySeller(
                    $seller_id,
                    "Ombi lako la kazi limetumwa! / Your job application has been submitted!", //p
                    "Ombi lako la kazi limetumwa!Tutakutaarifu mteja atakapokagua ombi lako", //sms
                    [
                        'type' => 'gernalnotifications',
                      // 'service_id' => $service->id,
                        'id' => uniqid('notif_'),
                        'details' => "Tutakutaarifu mteja atakapokagua ombi lako. / We’ll notify you once the client reviews your application." //p
                    ]
                );   
                
                // for buyer
                 $messages = get_static_option('buyer-apply-job-by-freelancer_message') ?? '';
                $messages = str_replace(["@name","@clientname","@jobid"],[$seller_info->username,$buyer_info->username,$request->job_post_id],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-apply-job-by-freelancer_subject') ??  __('You have received a new application for your job'),
                    'message' => $messages ?? '',
                ]));
                 
                $seller_id = $buyer_info->id;
              
                notifySeller(
                    $seller_id,
                    "Maombi mapya ya kazi kutoka kwa $seller_info->username kwa kazi yako (Job ID: $request->job_post_id). Kagua na jibu. / New job application from $seller_info->username for your job (Job ID: $request->job_post_id). Review and respond.", //p
                    "Maombi mapya ya kazi kutoka kwa $seller_info->username kwa kazi yako (Job ID: $request->job_post_id). Kagua na jibu.", //sms
                    [
                        'type' => 'gernalnotifications',
                        'id' => uniqid('notif_'),
                        'Maombi mapya ya kazi kutoka kwa '.$seller_info->username.' kwa kazi yako (Job ID: '.$request->job_post_id.'). Kagua na jibu. / New job application from '.$seller_info->username.' for your job (Job ID: '.$request->job_post_id.'). Review and respond.' //p
                    ]
                );     
                    
                } catch (\Exception $e) {
      
                    return redirect()->back()->with(FlashMsg::item_new($e->getMessage()));
                }
                if(subscriptionModuleExistsAndEnable('Subscription')){
                    \Modules\Subscription\Entities\SellerSubscription::where('seller_id', Auth::guard('web')->user()->id)->update([
                        'connect' => DB::raw(sprintf("connect - %s",(int)strip_tags(get_static_option('set_number_of_connect')))),
                    ]);
                }
                
                 //todo send push notification
                $pusher_auth = get_static_option('pusher_app_push_notification_auth_token');
                $pusher_instance_id = get_static_option('pusher_app_push_notification_instanceId');

                $buyer_job = BuyerJob::find($request->job_post_id);

                //todo send push notification
                $pusher_auth_url = 'https://'.$pusher_instance_id.'.pushnotifications.pusher.com/publish_api/v1/instances/'.$pusher_instance_id.'/publishes';

                try{
                    $response = Http::withToken($pusher_auth)->acceptJson()->post(
                        $pusher_auth_url
                    ,[
                        "interests" => ["debug-buyer".$request->buyer_id, 'message'],
                        "fcm" =>[
                            "notification" => [
                                "title" => __("You have received a job request")." ". Auth::guard('web')->user()->name,
                                "body" =>  __("Job Title:")." ".$buyer_job->title
                            ],
                            "data" => [
                                "sender-id" => Auth::guard("web")->user()->id,
                                "type" => "message"
                            ]
                        ]
                    ]);
                }catch(\Exception $e){
                    //
                }
                
                toastr_success(__('You have successfully applied for this job.'));
                return redirect()->back();
            }
            toastr_error(__('You must login to apply for a job.'));
            return back();
        }
    }

    //category wise services
    public function category_jobs($slug = null)
    {
        $category = Category::select('id','name')->where('slug',$slug)->firstOrFail();
        $sub_category = Subcategory::select('id','name')->where('slug',$slug)->first();
        $all_jobs = collect([]);
        if(!is_null($category)){
            $all_jobs = BuyerJob::where(['category_id' => $category->id, 'status' => 1, 'is_job_on' => 1])
                ->paginate(9);
        }

        if(!is_null($sub_category)){
            $all_jobs = BuyerJob::where(['subcategory_id' => $sub_category->id, 'status' => 1, 'is_job_on' => 1])
                ->paginate(9);
        }
        return view('jobpost::frontend.jobs.category-jobs', compact(
            'all_jobs',
            'category',
            'sub_category'
        ));
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
