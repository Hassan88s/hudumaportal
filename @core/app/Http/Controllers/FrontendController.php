<?php

namespace App\Http\Controllers;

use App\Admin;
use App\Helpers\HomePageStaticSettings;
use App\Page;
use App\Blog;
use App\Category;
use App\HeaderSlider;
use App\Mail\AdminResetEmail;
use App\Order;
use App\Review;
use App\Service;
use App\ServiceArea;
use App\StaticOption;
use App\ServiceCity;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Mail\BasicMail;
use Modules\JobPost\Entities\BuyerJob;
use App\Language;
use App\Project;
use Auth;
class FrontendController extends Controller

{
   
    public function index()
    {
       
        if(!Schema::hasColumn('users','last_seen')){
            Artisan::call('migrate', ['--force' => true ]);
        }

        $home_page_id = get_static_option('home_page');
        $page_details = Page::find($home_page_id);
        if (empty($page_details)){
            // show any notice or
        }

        return view('frontend.frontend-home')->with([
            'page_details' => $page_details
        ]);

    }



   
    
    
    public function live_stream(Request $request)
{
    $user = Auth::user();
    $role = $request->query('role', 'audience');
    $channel = $request->query('channel');
    $title = $request->query('title', '');
    $description = $request->query('description', '');

    if ($role === 'host' && !$channel) {
        $channel = 'user_' . $user->id . '_live_' . now()->format('Ymd_His');

        return redirect()->route('live', [
            'channel' => $channel,
            'role' => 'host',
            'title' => $title,
            'description' => $description,
        ]);
    }

    $stream = \App\AgoraStream::where('channel_name', $channel)->first();

    if ($role === 'host') {
        \App\AgoraStream::where('host_id', $user->id)
            ->where('is_live', 1)
            ->update(['is_live' => 0, 'ended_at' => now()]);

        if ($stream && $stream->host_id !== $user->id) {
            abort(403, 'You are not authorized to host this stream.');
        }

        if (!$stream) {
            $stream = \App\AgoraStream::create([
                'channel_name' => $channel,
                'host_id' => $user->id,
                'title' => $title,
                'description' => $description,
                'is_live' => 1,
                'started_at' => now(),
            ]);
        } else {
            $stream->update([
                'is_live' => 1,
                'title' => $title,
                'description' => $description,
                'started_at' => now(),
            ]);
        }
    }

    return view('frontend.live', [
        'channel' => $channel,
        'role' => $role,
        'title' => $title ?: ($stream->title ?? ''),
        'description' => $description ?: ($stream->description ?? ''),
        'hostName' => $stream ? optional($stream->host)->username : ($user->username ?? ''),
    ]);
}

    public function ticket(){
         return view('frontend.ticket');
    }
    public function home_page_change($id)
    {
          
        if (!in_array($id, ['01', '02', '03', '04','05'])) {
            abort(404);
        }
        $home_variant_number = get_static_option('home_page_variant');
        $all_header_slider = HeaderSlider::all();
        $latest_blog = Blog::orderBy('id','DESC')->get();
//        make a function to call all static option by home page
        $static_field_data = StaticOption::whereIn('option_name',HomePageStaticSettings::get_home_field($id))->get()->mapWithKeys(function ($item) {
            return [$item->option_name => $item->option_value];
        })->toArray();


        return view('frontend.frontend-home-demo')->with([
            'all_header_slider'=>$all_header_slider,
            'latest_blog'=>$latest_blog,
            'static_field_data' => $static_field_data,
            'home_page' => $id,
        ]);
    }

    public function dynamic_single_page($slug)
    {
    
        $page_post = Page::where('slug', $slug)->first();
        $user_details = User::where(['user_type'=> 0,'username' => $slug])->first();
        $preserved_pages = [
            'home_page',
            'service_list_page',
            'blog_page',
        ];

        $static_option = StaticOption::whereIn('option_name', $preserved_pages)->get()->mapWithKeys(function ($item) {
            return [$item->option_name => $item->option_value];
        })->toArray();

        $pages_id_slugs = Page::whereIn('id', array_values($static_option))->get()->mapWithKeys(function ($item) {
            return [$item->id => $item->slug];
        })->toArray();


        if (in_array($slug, $pages_id_slugs) && $slug === $pages_id_slugs[$static_option['home_page']]) {
            return redirect()->route('homepage');
        } elseif (in_array($slug, $pages_id_slugs) && $slug === $pages_id_slugs[$static_option['blog_page']]) {
            $all_blogs = Blog::where('status','publish')->orderBy('id','desc')->paginate(6);
            if($page_post->status === 'draft'){
                abort(404);
            }
            return view('frontend.pages.blog.blog-static', [
                'all_blogs' => $all_blogs,
                'page_post' => $page_post,
            ]);
        } elseif (in_array($slug, $pages_id_slugs) && $slug === $pages_id_slugs[$static_option['service_list_page']]) {
            $all_services = Service::with('reviews')->where(['status' => 1, 'is_service_on' => 1])->orderBy('id','desc')->get();
            if($page_post->status === 'draft'){
                abort(404);
            }

            return view('frontend.pages.services.service-static', [
                'all_services' => $all_services,
                'page_post' => $page_post,
            ]);
        }elseif(!is_null($user_details)){
            //die;
            //dd($user_details);
           return $this->_user_profile($user_details);
        }

        $page_type = 'page';
        if (!is_null($page_post)) {
            if($page_post->status === 'draft'){
                abort(404);
            }
            
       ;
      
       
          return view('frontend.pages.dynamic-single', compact('page_post','page_type'));
        }

        abort(404);
    }

    private function _user_profile($user_details)
    {
       
        $seller = $user_details;
        $seller_since = User::select('created_at')->where('id', $user_details->id)->where('user_status', 1)->first();
        $completed_order = Order::where('seller_id', $user_details->id)->where('status', 2)->count();

        $seller_rating = Review::where('seller_id', $user_details->id)->avg('rating');
        $seller_rating_percentage_value = $seller_rating * 20;

        $services = Service::select('id','seller_id','title','description','price','starting_price','slug','image','featured','service_city_id')
        ->where(['seller_id'=>$user_details->id,'status'=>1,'is_service_on'=>1])
        ->take(4)
        ->inRandomOrder()
        ->get();

        $service_rating = Review::where('seller_id', $user_details->id)->avg('rating');
        $service_reviews = Review::where('seller_id', $user_details->id)->paginate(5);
        $page_type = 'profile';
        
          $subscription = \Modules\Subscription\Entities\SellerSubscription::where('seller_id', $user_details->id)->first();

        return view('frontend.pages.seller.profile',compact(
            'seller',
            'seller_since',
            'completed_order',
            'seller_rating_percentage_value',
            'services',
            'service_rating',
            'service_reviews',
            'page_type', 
            'subscription'
        ));
    }

    public function buyerProfile($username)
    {
        $user_details = User::where(['user_type'=> 1,'username' => $username])->first();
        if (is_null($user_details)) {
            abort(404);
        }
        $buyer = $user_details;

        $buyer_since = User::select('created_at')->where('id', $user_details->id)->where('user_status', 1)->first();

        $total_job_posts = BuyerJob::where('buyer_id', $user_details->id)->where('status', 1)->count();

        $buyer_rating = Review::where('buyer_id', $user_details->id)->where('type', 0)->avg('rating');
        $buyer_rating_percentage_value = $buyer_rating * 20;

        $jobs = BuyerJob::where(['buyer_id'=>$user_details->id,'status'=>1,'is_job_on'=>1])->take(5)->inRandomOrder()->get();
        $job_rating = Review::where('buyer_id', $user_details->id)->where('type', 0)->avg('rating');
        $job_reviews = Review::where('buyer_id', $user_details->id)->where('type', 0)->paginate(5);


        return view('frontend.pages.buyer.profile',compact(
            'buyer',
            'buyer_since',
            'total_job_posts',
            'buyer_rating_percentage_value',
            'jobs',
            'job_rating',
            'job_reviews',
        ));
    }

    public function showAdminForgetPasswordForm()
    {
        return view('auth.admin.forget-password');
    }
    public function sendAdminForgetPasswordMail(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string:max:191'
        ]);
        $user_info = Admin::where('username', $request->username)->orWhere('email', $request->username)->first();
        if(is_null($user_info)){
            return redirect()->back()->with([
                    'msg' => __('your username or email does not found in our server'),
                    'type' => 'danger'
                ]);
        }
        
        $token_id = Str::random(30);
        $existing_token = DB::table('password_resets')->where('email', $user_info->email)->delete();
        DB::table('password_resets')->insert(['email' => $user_info->email, 'token' => $token_id]);
        
        
        $message = __('Hello').' '.$user_info->username."\n";
        $message .= __('Here is you password reset link, If you did not request to reset your password just ignore this mail.') . ' <a class="btn" href="' . route('admin.reset.password', ['user' => $user_info->username, 'token' => $token_id]) . '">' . __('Click Reset Password') . '</a>';
        
       try{
           
             Mail::to($user_info->email)->send(new BasicMail([
                        'subject' => __('Your Mail For Reset Password Link'),
                        'message' => $message
                    ]));
            
            return redirect()->back()->with([
                'msg' => __('Check Your Mail For Reset Password Link'),
                'type' => 'success'
            ]);
       }catch(\Exeption $e){
           //handle error
            return redirect()->back()->with([
                'msg' => $e->getMessage(),
                'type' => 'danger'
            ]);
       }
           
    
       
    }
    public function showAdminResetPasswordForm($username, $token)
    {
        return view('auth.admin.reset-password')->with([
            'username' => $username,
            'token' => $token
        ]);
    }
    public function AdminResetPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'username' => 'required',
            'password' => 'required|string|min:8|confirmed'
        ]);
        $user_info = Admin::where('username', $request->username)->first();
        $user = Admin::findOrFail($user_info->id);
        $token_iinfo = DB::table('password_resets')->where(['email' => $user_info->email, 'token' => $request->token])->first();
        if (!empty($token_iinfo)) {
            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->route('admin.login')->with(['msg' =>__( 'Password Changed Successfully'), 'type' => 'success']);
        }
        return redirect()->back()->with(['msg' => __('Somethings Going Wrong! Please Try Again or Check Your Old Password'), 'type' => 'danger']);
    }

    public function lang_change(Request $request)
    {
        session()->put('lang', $request->lang);
        return redirect()->route('homepage');
    }


    public function showUserForgetPasswordForm()
    {
        return view('frontend.user.forget-password');
    }
    public function sendUserForgetPasswordMail(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string:max:191'
        ]);
        $user_info = User::where('username', $request->username)->orWhere('email', $request->username)->first();
        if (!empty($user_info)) {
            $token_id = Str::random(30);
            $existing_token = DB::table('password_resets')->where('email', $user_info->email)->delete();
            if (empty($existing_token)) {
                DB::table('password_resets')->insert(['email' => $user_info->email, 'token' => $token_id]);
            }
            $message = __('Here is you password reset link, If you did not request to reset your password just ignore this mail.') . ' <a class="btn" href="' . route('user.reset.password', ['user' => $user_info->username, 'token' => $token_id]) . '">' . __('Click Reset Password') . '</a>';
            $data = [
                'username' => $user_info->username,
                'message' => $message
            ];
           try{
                Mail::to($user_info->email)->send(new AdminResetEmail($data));
           }catch(\Exeption $e){
               //handle error
           }

            return redirect()->back()->with([
                'msg' => __('Check Your Mail For Reset Password Link'),
                'type' => 'success'
            ]);
        }
        return redirect()->back()->with([
            'msg' => __('Your Username or Email Is Wrong!!!'),
            'type' => 'danger'
        ]);
    }
    public function order_payment_cancel($id)
    {
        
    }
    
    public function showUserResetPasswordForm($username, $token)
    {
        return view('frontend.user.reset-password')->with([
            'username' => $username,
            'token' => $token
        ]);
    }
    public function UserResetPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'username' => 'required',
            'password' => 'required|string|min:8|confirmed'
        ]);
        $user_info = User::where('username', $request->username)->first();
        $user = User::findOrFail($user_info->id);
        $token_iinfo = DB::table('password_resets')->where(['email' => $user_info->email, 'token' => $request->token])->first();
        if (!empty($token_iinfo)) {
            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->route('user.login')->with(['msg' => __('Password Changed Successfully'), 'type' => 'success']);
        }
        return redirect()->back()->with(['msg' => __('Somethings Going Wrong! Please Try Again or Check Your Old Password'), 'type' => 'danger']);
    }


    public function dark_mode_toggle(Request $request){
        if($request->mode == 'off'){
            update_static_option('site_frontend_dark_mode','on');
        }
        if($request->mode == 'on'){
            update_static_option('site_frontend_dark_mode','off');
        }

        return response()->json(['status'=>'done']);
    }


    public function home_search(Request $request)
    {

        $services = Service::query()
            ->select('title', 'slug', 'image', 'price', 'description', 'status')
            ->where('status', 1)
            ->where('is_service_on', 1)
            ->when(subscriptionModuleExistsAndEnable('Subscription'), function ($q) {
                $q->whereHas('seller_subscription');
            });

        if (!isset($request->country_id) || !isset($request->service_city_id) || !isset($request->service_area_id)) {
            $services->where('status', 1)->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->search_text . '%')
                    ->orWhere('description', 'LIKE', '%' . $request->search_text . '%')
                    ->orWhere('price', 'LIKE', '%' . $request->search_text . '%');
            });
        } else {
            $services->where('status', 1)
                ->where(function ($query) use ($request) {
                    $query->where('title', 'LIKE', '%' . $request->search_text . '%')
                        ->orWhere('description', 'LIKE', '%' . $request->search_text . '%')
                        ->orWhere('price', 'LIKE', '%' . $request->search_text . '%');
                });
        }

       
        // $services = $services->orderBy('id', 'desc')->get();
        $services = $services->orderBy('featured', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'services' => $services,
            'result' => view('frontend.partials.search-result', compact('services'))->render(),
        ]);

    }

    public function home_search_two(Request $request)
    {
         if($request->service_city){
                $services = Service::Where('service_city_id', $request->service_city)
                ->where('status',1)
                ->where('is_service_on',1)
                ->when(subscriptionModuleExistsAndEnable('Subscription'),function($q){
                    $q->whereHas('seller_subscription');
                })
                ->orderBy('id', 'desc')
                ->paginate(24);
         }else{
             toastr_error(__('Select city to search'));
             return redirect()->back();
         }
        return view('frontend.partials.clickable-search-result',compact('services'));
    }

    public function home_search_single_page(Request $request)
    {
     
        if(empty($request->home_search)){
            toastr_error(__('Enter anything to search'));
            return redirect()->back();
        }
        $request->validate([
            'home_search' => 'required|string'
        ]);
        
         if(empty($request->country_id) && empty($request->service_city_id)){
            $services = Service::Where('title', 'LIKE', '%' . $request->home_search . '%')
            ->where('status',1)
            ->where('is_service_on',1)
            ->when(subscriptionModuleExistsAndEnable('Subscription'),function($q){
                $q->whereHas('seller_subscription');
            })
            // ->orderBy('id', 'desc')
            // ->paginate(6);
            ->orderBy('featured', 'desc')
            ->get();
            
         }else{
            $services = Service::Where('title', 'LIKE', '%' . $request->home_search . '%')
            ->where('service_city_id',$request->service_city_id)
            ->where('status',1)
            ->where('is_service_on',1)
            ->when(subscriptionModuleExistsAndEnable('Subscription'),function($q){
                $q->whereHas('seller_subscription');
            })
            // ->orderBy('id', 'desc')
            // ->paginate(6);
            ->orderBy('featured', 'desc')
            ->get();
         }
         return view('frontend.partials.clickable-search-result',compact('services'));
    }
    
  public function getCity(Request $request)
    {
        $cities = ServiceCity::where('country_id', $request->country_id)->where('status', 1)->take(500)->get();
        return response()->json([
            'status' => 'success',
            'cities' => $cities,
        ]);
    }
    public function getCityAjaxSearch(Request $request)
    {

        $dQuery = ServiceCity::query();
        if(!empty($request->country_id)){
            $dQuery->where('country_id', $request->country_id);
        }
        if($request->has('q')){
            $search = $request->q;
            $dQuery->where('service_city','LIKE',"%$search%");
        }

        $data = $dQuery->where('status', 1)->take(200)->get();

        return response()->json($data);
    }

    public function getAarea(Request $request)
    {
        $areas = ServiceArea::where('service_city_id', $request->city_id)->where('status', 1)->get();
        return response()->json([
            'status' => 'success',
            'areas' => $areas,
        ]);
    }
    
    
    public function set_language(Request $request)
{
    $lang = Language::find($request->input('language_id'));

    if (!$lang) {
        return redirect()->back()->with([
            'msg' => __('Language not found.'),
            'type' => 'error'
        ]);
    }

    // Set the language for the current user session
    session()->put('lang', $lang->slug);

    return redirect()->back()->with([
        'msg' => __('Language set to') . ' ' . $lang->name,
        'type' => 'success'
    ]);
}
    public function FetchProjects(){
        if (!get_static_option('site_Challenege_Page') == 'on') {
        abort(404);
    }
         $categories = Category::where('status', 1)->get();
        return view('frontend.pages.challenges',compact('categories'));
    }
    
      public function single($slug){
         $project = Project::with('images')->where('slug', $slug)->first();
         if (is_null($project)) {
             abort(404);
         }
           return view('frontend.pages.singlechallenge',compact('project'));

    }
    
       
            
    

}
