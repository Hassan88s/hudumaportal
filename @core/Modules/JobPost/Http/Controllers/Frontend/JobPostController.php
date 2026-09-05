<?php

namespace Modules\JobPost\Http\Controllers\Frontend;

use App\AdminNotification;
use App\Category;
use App\ChildCategory;
use App\Country;
use App\Helpers\FlashMsg;
use App\Mail\BasicMail;
use App\ServiceCity;
use App\Subcategory;
use App\User;
use App\ServiceArea;
use Auth;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\JobPost\Entities\BuyerJob;
use Modules\JobPost\Entities\JobRequest;
use Str;
use Modules\JobPost\Entities\JobPackage;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;
use App\AdminCommission;
use Xgenious\Paymentgateway\Facades\XgPaymentGateway;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Illuminate\Support\Facades\Log;
use App\JobAlertSubscription;
use App\Jobs\SendJobAlertSMS;
class JobPostController extends Controller
{
     private const CANCEL_ROUTE = 'frontend.order.payment.cancel.static';
    public function all_jobs(Request $request)
    {

        if(!empty($request->job_id || $request->job_date || $request->job_status || $request->job_type || $request->job_title || $request->job_budget )){

            $job_query = BuyerJob::where('buyer_id', Auth::guard('web')->user()->id);

            // search by ID
            if (!empty($request->job_id)){
                $job_query->where('id', $request->job_id);
            }
            // search by order create date
            if (!empty($request->job_date)){
                $start_date = \Str::of($request->job_date)->before('to');
                $end_date = \Str::of($request->job_date)->after('to');
                $job_query->whereBetween('created_at', [$start_date,$end_date]);
            }
            // search by  status
            if (!empty($request->job_status)){
                if ($request->job_status == 'active'){
                    $job_query->where('status', 1);
                }else{
                    $job_query->where('status', 0);
                }
            }

            // search by job type
            if (!empty($request->job_type)){
                if ($request->job_type == 'online'){
                    $job_query->where('is_job_online', 1);
                }else{
                    $job_query->where('is_job_online', 0);
                }
            }

            // search by job_budget
            if (!empty($request->job_budget)){
                $job_query->where('price', 'LIKE', "%{$request->job_budget}%");
            }

            // search by job title
            if (!empty($request->job_title)){
                 $job_query->where('title',  'LIKE', "%{$request->job_title}%");
            }

            $jobs = $job_query->orderByDesc('id')->paginate(6);

        }else{
            $jobs = BuyerJob::where('buyer_id', Auth::guard('web')->user()->id)->orderByDesc('id')->paginate(6);
        }

         return view('jobpost::frontend.buyer.all-jobs',compact('jobs'));
    }

    // //get sub category while change category
    // public function sub_category(Request $request)
    // {
    //     $sub_categories = Subcategory::where('category_id', $request->category_id)->where('status', 1)->get();
    //     return response()->json([
    //         'status' => 'success',
    //         'sub_categories' => $sub_categories,
    //     ]);
    // }

    public function sub_category(Request $request)
{
    $sub_categories = Subcategory::where('category_id', $request->category_id)
        ->where('status', 1)
        ->get()
        ->map(function ($sub) {
            return [
                'id' => $sub->id,
                'name' => __($sub->name), // translate here
            ];
        });

    return response()->json([
        'status' => 'success',
        'sub_categories' => $sub_categories,
    ]);
}
    //get child category while change sub category
    public function child_category(Request $request)
    {
        $child_categories = ChildCategory::where('sub_category_id', $request->sub_cat_id)->where('status', 1)->get();
        return response()->json([
            'status' => 'success',
            'child_category' => $child_categories,
        ]);
    }

    //get city while change country
    public function city(Request $request)
    {
        $cities = ServiceCity::where('country_id', $request->country_id)->where('status', 1)->get();
        return response()->json([
            'status' => 'success',
            'cities' => $cities,
        ]);
    }

    //add new job post
    public function add_job(Request $request)
    {

       
            
             if($request->isMethod('post')){
         
         if ($request->package_id != 1) {

    if (empty($request->selected_payment_gateway)) {
        toastr_warning('Please Select a Payment Method.');
        return back();
    }
}

            if($request->is_job_online == 1){
                $request->validate([
                    'category' => 'required',
                    'subcategory' => 'required',
                    'title' => 'required|max:191',
                    'description' => 'required|min:50',
                    'price' => 'required|numeric',
                    'dead_line' => 'required',
                    'image' => 'required',
                    'Days'=>'required',
                ]);
                $country_id = 0;
                $city_id = 0;
                $area_id = 0; 
            }else{
                $request->validate([
                    'category' => 'required',
                    'subcategory' => 'required',
                    'country_id' => 'required',
                    'city_id' => 'required',
                     'area_id' => 'required',
                    'title' => 'required|max:191',
                    'description' => 'required|min:50',
                    'price' => 'required|numeric',
                    'dead_line' => 'required',
                    'Days'=>'required',
                ]);
                $country_id = $request->country_id;
                $city_id = $request->city_id;
                 $area_id =  $request->area_id;
            }

                if(get_static_option('job_create_settings') == 'active'){
                    $job_status = 1;
                }else{
                    $job_status = 0;
                }
                
                if ($request->package_id == 1) {
                 $job_status = (get_static_option('job_create_settings') == 'active') ? 1 : 0;

                // Directly create the job since it's free
                // need to uncomment
                $created_job = BuyerJob::create([
                    'category_id'=>$request->category,
                'subcategory_id'=>$request->subcategory,
                'child_category_id'=>$request->child_category,
                'buyer_id'=>Auth::guard('web')->user()->id,
                'country_id'=>$country_id,
                'city_id'=>$city_id,
                'area_id' => $area_id,
                'title'=>$request->title,
                'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
                'description'=>$request->description,
                'image'=>$request->image,
                'is_job_online'=>$request->is_job_online,
                'price'=>$request->price,
                'dead_line'=>$request->dead_line,
                'status'=>$job_status,
                'Days'=>$request->Days,
                'promoteddays'=>now()->addMonth(),
                'package_id'=>$request->package_id,
                    'promoted' => 0,
                    'is_paid' => 1, 
                    'no_of_hiring'=>$request->no_of_hiring,
                ]);
            
                
            }

       
                $global_currency = get_static_option('site_global_currency');
        
                $usd_conversion_rate =  get_static_option('site_' . strtolower($global_currency) . '_to_usd_exchange_rate');
                $inr_exchange_rate = getenv('INR_EXCHANGE_RATE');
                $ngn_exchange_rate = getenv('NGN_EXCHANGE_RATE');
                $zar_exchange_rate = getenv('ZAR_EXCHANGE_RATE');
                $brl_exchange_rate = getenv('BRL_EXCHANGE_RATE');
                $idr_exchange_rate = getenv('IDR_EXCHANGE_RATE');
                $myr_exchange_rate = getenv('MYR_EXCHANGE_RATE');
                 $packages = JobPackage::where('id',$request->package_id)->first();
                 $amount = $packages->price;
    
         
                        if(moduleExists('Wallet')){
                            if ($request->selected_payment_gateway === 'wallet') {
                               
                             
                                $amount = $packages->price;
                                $buyer_id = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : NULL;
                                $wallet_balance = Wallet::where('buyer_id',$buyer_id)->first();
                                
                                if(!empty($wallet_balance)){
                                    if($wallet_balance->balance >= $amount){
                                        Wallet::where('buyer_id',$buyer_id)->update([
                                            'balance' => $wallet_balance->balance-$amount,
                                        ]);
                                        $created_job = BuyerJob::create([
                                        'category_id'=>$request->category,
                                    'subcategory_id'=>$request->subcategory,
                                    'child_category_id'=>$request->child_category,
                                    'buyer_id'=>Auth::guard('web')->user()->id,
                                    'country_id'=>$country_id,
                                    'city_id'=>$city_id,
                                     'area_id' => $area_id,
                                    'title'=>$request->title,
                                    'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
                                    'description'=>$request->description,
                                    'image'=>$request->image,
                                    'is_job_online'=>$request->is_job_online,
                                    'price'=>$request->price,
                                    'dead_line'=>$request->dead_line,
                                    'status'=>$job_status,
                                    'Days'=>$request->Days,
                                    'promoteddays'=> now()->addMonth(),
                                    'package_id'=>$request->package_id,
                                        'promoted' => 1,
                                        'is_paid' => 1, 
                                         'no_of_hiring'=>$request->no_of_hiring,
                                    ]);

                                        //wallet transaction                
                                      WalletHistory::create([
                                        'buyer_id' => Auth::guard('web')->user()->id,
                                        'amount' => $amount,
                                        'payment_gateway' => 'Pay For Promoted Job',
                                        'payment_status' => 'complete',
                                        'status' => 1,
                            
                                    ]);
                                    
              
                                    
                                                            
                                    try {
                                      
                                        $buyer_info = User::find(Auth::guard('web')->user()->id);
                                        $messages = get_static_option('buyer-promotes-job-request_message') ?? '';
                                        $messages = str_replace(["@name","@clientname"],[$buyer_info->username,$buyer_info->username],$messages);
                                       
                                        Mail::to($buyer_info->email)->send(new BasicMail([
                                            'subject' => get_static_option('buyer-promotes-job-request_subject') ??  __('Your job post promotion is active'),
                                            'message' => $messages ?? '',
                                        ]));
                                         
                                        $buyer_id = $buyer_info->id;
                                      
                                        notifySeller(
                                            $buyer_id,
                                            "Tangazo lako la kazi lililopandishwa  sasa linaonekana zaidi. / Your promoted job post  is now live and gaining visibility.", //p
                                            "Tangazo lako la kazi lililopandishwa  sasa linaonekana zaidi.", //sms
                                            [
                                                'type' => 'gernalnotifications',
                                                'id' => uniqid('notif_'),
                                                'Tangazo lako la kazi lililopandishwa  sasa linaonekana zaidi. / Your promoted job post  is now live and gaining visibility.' //p
                                            ]
                                        );    
                                        
                                    } catch (\Exception $e) {
                                       
                                        FlashMsg::item_new($e->getMessage());
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
                        
                        ///flutterwave
                          if($request->package_id != 1){
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
                                    $title ="Job offer";
                                    $description="Pay For the Job Order";
                                    $redirect_url = $flutterwave->charge_customer([
                                        'amount' => $amount,
                                        'title' => $title,
                                        'description' => $description,
                                        'ipn_url' => route('buyer.flutterwave.ipn.jobspromoted'),
                                      
                                        'track' => \Str::random(36),
                                        'order_id' => Str::random(10), // Temporary order tracking
                                        'cancel_url' => route(self::CANCEL_ROUTE),
                                        'success_url' => route('buyer.all.jobs'),
                                        'email' => Auth::user()->email,
                                        'name' => Auth::user()->name,
                                        'payment_type' => 'order',
                                    ]);
                      
                                    session()->put('job_payment_details', [
                                    'category_id' => $request->category,
                                    'subcategory_id' => $request->subcategory,
                                     'child_category_id'=>$request->child_category,
                                    'buyer_id' => Auth::id(),
                                    'title' => $request->title,
                                    'country_id'=>$country_id,
                                    'city_id'=>$city_id,
                                     'area_id' => $area_id,
                                     'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
                                    'description'=>$request->description,
                                    'image'=>$request->image,
                                    'is_job_online'=>$request->is_job_online,
                                    'price'=>$request->price,
                                    'dead_line'=>$request->dead_line,
                                    'status'=>$job_status,
                                    'Days'=>$request->Days,
                                    'promoteddays'=>now()->addMonth(),
                                    'package_id'=>$request->package_id,
                                     'no_of_hiring'=>$request->no_of_hiring,
                                ]);
                                   
                                   
                                    
                                    return $redirect_url;
                                }
                                catch(\Exception $e){
                        
                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                }
                
                            }
  
                          }
            // admin notification add
            AdminNotification::create(['job_post_id' => $created_job->id]);

            try {
                $message = get_static_option('job_create_message') ?? '';
                $message = str_replace(["@job_post_id"],[$created_job->id],$message);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => get_static_option('job_create_subject') ?? __('New Job Post Created'),
                    'message' => $message
                ]));
                
                
      
                $buyer_info = User::find(Auth::guard('web')->user()->id);
                $messages = get_static_option('buyer-Job-posting_message') ?? '';
                $messages = str_replace(["@name","@clientname"],[$buyer_info->username,$buyer_info->username],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-Job-posting_subject') ??  __('Your job post is under review'),
                    'message' => $messages ?? '',
                ]));
                 
                $buyer_id = $buyer_info->id;
              
                notifySeller(
                    $buyer_id,
                    "angazo lako la kazi linakaguliwa. Tutakutaarifu likishaidhinishwa na kuwekwa hewani! / Your job post is under review. We’ll notify you once it’s approved and live!", //p
                    "angazo lako la kazi linakaguliwa. Tutakutaarifu likishaidhinishwa na kuwekwa hewani!", //sms
                    [
                        'type' => 'gernalnotifications',
                        'id' => uniqid('notif_'),
                        'angazo lako la kazi linakaguliwa. Tutakutaarifu likishaidhinishwa na kuwekwa hewani! / Your job post is under review. We’ll notify you once it’s approved and live!' //p
                    ]
                );    
                
            } catch (\Exception $e) {
               
                FlashMsg::item_new($e->getMessage());
            }
       
                $subscribers = JobAlertSubscription::where('category_id', $request->category)
                    ->with('freelancer')
                    ->get();

                foreach ($subscribers as $sub) {
                    if (!empty($sub->freelancer->phone)) {

                             $job_url = route('job.post.details',$created_job->slug);
                           $freelancer_name = $sub->freelancer->username;
                            $messages = get_static_option('user_jobsnewsletter_message') ?? '';
                             $messages = str_replace(["@freelancername","@joburl"],[$freelancer_name, $job_url],$messages);

                          //$message = "Habari {$freelancer_name}, kazi mpya inayolingana na ujuzi wako imewekwa. Ingia Huduma Portal uombe sasa! | Hello {$freelancer_name}, a new job matching your skills has been posted. Login to Huduma Portal and apply now! {$job_url} ";
                            dispatch(new SendJobAlertSMS($sub->freelancer->phone, $messages));
                    }

                    // Email notification to subscribed sellers
                    try {
                        if (!empty($sub->freelancer) && !empty($sub->freelancer->email)) {
                            $job_url = route('job.post.details', $created_job->slug);
                            $freelancer_name = $sub->freelancer->username ?: $sub->freelancer->name;
                            $job_title = $created_job->title ?? __('New Job');

                            $email_template = get_static_option('user_jobsnewsletter_message') ?? '';
                            if (!empty($email_template)) {
                                $email_body = str_replace(
                                    ["@freelancername", "@joburl", "@jobtitle"],
                                    [$freelancer_name, $job_url, $job_title],
                                    $email_template
                                );
                            } else {
                                $email_body = '<p>' . __('Hello') . ' ' . e($freelancer_name) . ',</p>'
                                    . '<p>' . __('A new job matching your subscribed category has been posted on Huduma Portal:') . '</p>'
                                    . '<p><strong>' . e($job_title) . '</strong></p>'
                                    . '<p><a href="' . $job_url . '" style="background:#6366f1;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;display:inline-block">' . __('View Job & Apply') . '</a></p>'
                                    . '<p style="color:#666;font-size:12px;margin-top:20px">' . __('You are receiving this because you subscribed to job alerts for this category.') . '</p>';
                            }

                            Mail::to($sub->freelancer->email)->send(new BasicMail([
                                'subject' => __('New Job Posted: ') . $job_title,
                                'message' => $email_body,
                            ]));
                        }
                    } catch (\Exception $e) {
                        Log::error('Job alert email failed for freelancer_id=' . optional($sub->freelancer)->id . ': ' . $e->getMessage());
                    }

                }
            toastr_success(__('Job Post Added Success'));
            return redirect()->route('buyer.all.jobs');
        }
        $categories = Category::where('status',1)->get();
        $countries = Country::where('status',1)->whereHas('cities')->get();
         $packages = JobPackage::all();
        return view('jobpost::frontend.buyer.add-job',compact('categories','countries','packages'));
    }

    //edit job post
    public function edit_job(Request $request,$id=null)
    {
       
       $count = JobRequest::where('job_post_id',$id)->where('is_hired',1)->count(); 
        if ($count >= 1) {
        toastr_warning('Already Hired');
        return back();
    }
    
    
    
        if($request->isMethod('post')){
              $Promoted_check =    BuyerJob::where('id',$id)->first();
                    if ($request->package_id != 1) {
                       if ($Promoted_check->promoted == 0) {      
    // if (empty($request->promoteddays)) {
    //     toastr_warning('Please Enter Days.');
    //     return back();
    // }
    if (empty($request->selected_payment_gateway)) {
        toastr_warning('Please Select a Payment Method.');
        return back();
    }
}
}
           $isCompany = Auth::guard('web')->user()->is_company == 1;

                if ($request->is_job_online == 1) {
                    $rules = [
                        'category' => 'required',
                        'subcategory' => 'required',
                        'title' => 'required|max:191',
                        'description' => 'required|min:50',
                        'price' => 'required|numeric',
                        'dead_line' => 'required',
                        'Days' => 'required',
                    ];
                
                    if ($isCompany) {
                        $rules['no_of_hiring'] = 'required|integer|min:1';
                    }
                
                    $request->validate($rules);
                
                    $country_id = 0;
                    $city_id = 0;
                    $area_id = 0;
                
                } else {
                    $rules = [
                        'category' => 'required',
                        'subcategory' => 'required',
                        'country_id' => 'required',
                        'city_id' => 'required',
                        'area_id' => 'required',
                        'title' => 'required|max:191',
                        'description' => 'required|min:50',
                        'price' => 'required|numeric',
                        'dead_line' => 'required',
                        'Days' => 'required',
                    ];
                
                    if ($isCompany) {
                        $rules['no_of_hiring'] = 'required|integer|min:1';
                    }
                
                    $request->validate($rules);
                
                    $country_id = $request->country_id;
                    $city_id = $request->city_id;
                    $area_id = $request->area_id;
                }


            if(get_static_option('job_create_settings') == 'active'){
                $job_status = 1;
            }else{
                $job_status = 0;
            }

        //     BuyerJob::where('id',$id)->update([
        //         'category_id'=>$request->category,
        //         'subcategory_id'=>$request->subcategory,
        //         'child_category_id'=>$request->child_category,
        //         'buyer_id'=>Auth::guard('web')->user()->id,
        //         'country_id'=>$country_id,
        //         'city_id'=>$city_id,
        //         'title'=>$request->title,
        //         'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
        //         'description'=>$request->description,
        //         'image'=>$request->image,
        //         'is_job_online'=>$request->is_job_online,
        //         'price'=>$request->price,
        //         'dead_line'=>$request->dead_line,
        //         'status'=>$job_status,
        //         'Days'=>$request->Days,
        //     ]);
        //     toastr_success(__('Job Post Updated Success'));
        //     return redirect()->route('buyer.all.jobs');
        
        
          if ($Promoted_check->promoted == 1) {
                 $job_status = (get_static_option('job_create_settings') == 'active') ? 1 : 0;

                // Directly create the job since it's free
               BuyerJob::where('id',$id)->update([
                    'category_id'=>$request->category,
                'subcategory_id'=>$request->subcategory,
                'child_category_id'=>$request->child_category,
                'buyer_id'=>Auth::guard('web')->user()->id,
                'country_id'=>$country_id,
                'city_id'=>$city_id,
                  'area_id' => $area_id,
                'title'=>$request->title,
                'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
                'description'=>$request->description,
                'image'=>$request->image,
                'is_job_online'=>$request->is_job_online,
                'price'=>$request->price,
                'dead_line'=>$request->dead_line,
                'status'=>$job_status,
                'Days'=>$request->Days,
                 'no_of_hiring'=>$request->no_of_hiring,
                // 'promoteddays'=>$request->promoteddays,
                // 'package_id'=>$request->package_id,
                //     'promoted' => 1,
                //     'is_paid' => 1, 
                ]);
            
                toastr_success(__('Job Post Added Successfully'));
                return redirect()->route('buyer.all.jobs');
            }
        ///
                  if ($request->package_id == 1) {
                 $job_status = (get_static_option('job_create_settings') == 'active') ? 1 : 0;

                // Directly create the job since it's free
               BuyerJob::where('id',$id)->update([
                    'category_id'=>$request->category,
                'subcategory_id'=>$request->subcategory,
                'child_category_id'=>$request->child_category,
                'buyer_id'=>Auth::guard('web')->user()->id,
                'country_id'=>$country_id,
                'city_id'=>$city_id,
                  'area_id' => $area_id,
                'title'=>$request->title,
                'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
                'description'=>$request->description,
                'image'=>$request->image,
                'is_job_online'=>$request->is_job_online,
                'price'=>$request->price,
                'dead_line'=>$request->dead_line,
                'status'=>$job_status,
                'Days'=>$request->Days,
                'promoteddays'=>$request->promoteddays,
                 'no_of_hiring'=>$request->no_of_hiring,
                'package_id'=>$request->package_id,
                    'promoted' => 0,
                    'is_paid' => 1, 
                ]);
            
                toastr_success(__('Job Post Added Successfully'));
                return redirect()->route('buyer.all.jobs');
            }
            
             $global_currency = get_static_option('site_global_currency');
        
                $usd_conversion_rate =  get_static_option('site_' . strtolower($global_currency) . '_to_usd_exchange_rate');
                $inr_exchange_rate = getenv('INR_EXCHANGE_RATE');
                $ngn_exchange_rate = getenv('NGN_EXCHANGE_RATE');
                $zar_exchange_rate = getenv('ZAR_EXCHANGE_RATE');
                $brl_exchange_rate = getenv('BRL_EXCHANGE_RATE');
                $idr_exchange_rate = getenv('IDR_EXCHANGE_RATE');
                $myr_exchange_rate = getenv('MYR_EXCHANGE_RATE');
                 $packages = JobPackage::where('id',$request->package_id)->first();
                 $amount = $packages->price*$request->promoteddays;
    
                        //todo: check payment gateway is wallet or not
                        if(moduleExists('Wallet')){
                            if ($request->selected_payment_gateway === 'wallet') {
                               
                               // $lastorder = BuyerJob::where('id',$last_order_id)->first();
                               // $packages = JobPackage::where('id',$request->package_id)->first();
                                $amount = $packages->price*$request->promoteddays;
                                $buyer_id = Auth::guard('web')->check() ? Auth::guard('web')->user()->id : NULL;
                                $wallet_balance = Wallet::where('buyer_id',$buyer_id)->first();
                                
                                if(!empty($wallet_balance)){
                                    if($wallet_balance->balance >= $amount){
                                        Wallet::where('buyer_id',$buyer_id)->update([
                                            'balance' => $wallet_balance->balance-$amount,
                                        ]);
                                        $created_job =  BuyerJob::where('id',$id)->update([
                                        'category_id'=>$request->category,
                                    'subcategory_id'=>$request->subcategory,
                                    'child_category_id'=>$request->child_category,
                                    'buyer_id'=>Auth::guard('web')->user()->id,
                                    'country_id'=>$country_id,
                                    'city_id'=>$city_id,
                                      'area_id' => $area_id,
                                    'title'=>$request->title,
                                    'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
                                    'description'=>$request->description,
                                    'image'=>$request->image,
                                    'is_job_online'=>$request->is_job_online,
                                    'price'=>$request->price,
                                    'dead_line'=>$request->dead_line,
                                    'status'=>$job_status,
                                    'Days'=>$request->Days,
                                    'promoteddays'=>$request->promoteddays,
                                    'package_id'=>$request->package_id,
                                     'no_of_hiring'=>$request->no_of_hiring,
                                        'promoted' => 1,
                                        'is_paid' => 1, 
                                    ]);

                                        //wallet transaction                
                                       WalletHistory::create([
                                        'buyer_id' => Auth::guard('web')->user()->id,
                                        'amount' => $amount,
                                        'payment_gateway' => 'Pay For Promoted Job',
                                        'payment_status' => 'complete',
                                        'status' => 1,
                            
                                    ]);
                                     toastr_success(__('Job Post Added Successfully'));
                                     return redirect()->route('buyer.all.jobs');
                                    }else{
                                      
                                             // BuyerJob::where('id', $last_order_id)->delete();
                                        $shortage_balance =  $amount-$wallet_balance->balance;
                                        toastr_warning('Your wallet has '.float_amount_with_currency_symbol($shortage_balance).' shortage to order this service. Please Credit your wallet first and try again.');
                                        return back();
                                    }
                                }
                            }
                        }
                          ///flutterwave
                          if($request->package_id != 1){
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
                                    $title ="custom offer";
                                    $description="Pay For the Promoted Job";
                                    $redirect_url = $flutterwave->charge_customer([
                                        'amount' => $amount,
                                        'title' => $title,
                                        'description' => $description,
                                        'ipn_url' => route('buyer.flutterwave.ipn.jobspromoted'),
                                      
                                        'track' => \Str::random(36),
                                        'order_id' => Str::random(10), // Temporary order tracking
                                        'cancel_url' => route(self::CANCEL_ROUTE),
                                        'success_url' => route('buyer.all.jobs'),
                                        'email' => Auth::user()->email,
                                        'name' => Auth::user()->name,
                                        'payment_type' => 'order',
                                    ]);
                      
                                    session()->put('job_payment_details', [
                                         'job_id' => $id ?? null, // Store job ID if editing
                                    'category_id' => $request->category,
                                    'subcategory_id' => $request->subcategory,
                                     'child_category_id'=>$request->child_category,
                                    'buyer_id' => Auth::id(),
                                    'title' => $request->title,
                                    'country_id'=>$country_id,
                                    'city_id'=>$city_id,
                                      'area_id' => $area_id,
                                     'slug' => create_slug($request->slug, 'BuyerJob', true, 'JobPost', 'slug'),
                                    'description'=>$request->description,
                                    'image'=>$request->image,
                                    'is_job_online'=>$request->is_job_online,
                                    'price'=>$request->price,
                                    'dead_line'=>$request->dead_line,
                                    'status'=>$job_status,
                                    'Days'=>$request->Days,
                                    'promoteddays'=>$request->promoteddays,
                                    'package_id'=>$request->package_id,
                                     'no_of_hiring'=>$request->no_of_hiring,
                                ]);
                                   
                                   
                                    
                                    return $redirect_url;
                                }
                                catch(\Exception $e){
                                    dd($e->getMessage());
                                    return back()->with(['msg' => $e->getMessage(),'type' => 'danger']);
                                }
                
                            }
  
                          }
                        
                
        
         }
        
        $job = BuyerJob::with('area')->find($id);
        $areas = ServiceArea::where('status',1)->get();
        $categories = Category::where('status',1)->get();
        $countries = Country::where('status',1)->whereHas('cities')->get();
         $packages = JobPackage::all();
        return view('jobpost::frontend.buyer.edit-job',compact('categories','countries','job','packages','areas'));
    }

    //Job post on off
    public function job_on_off(Request $request)
    {
        $is_job_on = BuyerJob::select('id','is_job_on')->where('id', $request->job_post_id)->first();
        $is_job_on->is_job_on === 1 ? $is_job_on = 0 : $is_job_on = 1;
        BuyerJob::where('id', $request->job_post_id)->update(['is_job_on' => $is_job_on]);
        return response()->json([
            'status' => 'success',
        ]);
    }

    //job delete
    public function job_delete($id = null)
    {
        JobRequest::where('job_post_id',$id)->delete();
        BuyerJob::find($id)->delete();
        toastr_error(__('Job Post Delete Success'));
        return back();
    }
    
     public function generateDescription(Request $request)
                    {
                        $userPrompt = $request->input('prompt');
                    
                        $apiKey = env('OPENAI_API_KEY');
                    
                        $ch = curl_init("https://api.openai.com/v1/chat/completions");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Content-Type: application/json",
                            "Authorization: Bearer " . $apiKey
                        ]);
                    
                        $data = [
                            "model" => "gpt-4o-mini",
                            "messages" => [
                                ["role" => "user", "content" => $userPrompt],
                            ],
                            "max_tokens" => 300
                        ];
                    
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        $response = curl_exec($ch);
                        curl_close($ch);
                    
                        $result = json_decode($response, true);
                        $description = $result['choices'][0]['message']['content'] ?? 'No description generated.';
                    
                        return response()->json(['description' => $description]);
                    }
}
