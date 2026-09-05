<?php

namespace Modules\JobPost\Http\Controllers\Backend;

use App\Helpers\FlashMsg;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\JobPost\Entities\BuyerJob;
use Modules\JobPost\Entities\JobRequest;
use Modules\JobPost\Entities\JobRequestConversation;
use Modules\JobPost\Entities\JobPackage;
use App\User;
use App\Mail\BasicMail;
use Illuminate\Support\Facades\Mail;
class JobsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:job-list|job-status|job-delete',['only' => ['jobs']]);
        $this->middleware('permission:job-status',['only' => ['change_status']]);
        $this->middleware('permission:job-delete',['only' => ['delete']]);
    }

    public function jobs()
    {
        $current_date = date('Y-m-d h:i:s');
        $all_jobs = BuyerJob::orderByDesc('id')->get();
        return view('jobpost::backend.jobs',compact('all_jobs'));
    }
    
    
     public function jobsPackages()
    {
        
      
         $packages = JobPackage::all();
        
        return view('jobpost::backend.job-package',compact('packages'));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'numeric|min:0'
        ]);

        foreach ($request->prices as $id => $price) {
            JobPackage::where('id', $id)->update(['price' => $price]);
        }

        return redirect()->route('admin.job.packages')->with('success', 'Prices updated successfully.');
    }

    public function change_status($id)
    {
       
        $job = BuyerJob::find($id);
        $job->status === 1 ? $status = 0 : $status = 1;
        
      
         try {
             
             if( $job->status == 0){
                $buyer_info = User::find($job->buyer_id);
                $messages = get_static_option('buyer-Job-approved-admin_message') ?? '';
                $messages = str_replace(["@name","@clientname"],[$buyer_info->username,$buyer_info->username],$messages);
               
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => get_static_option('buyer-Job-approved-admin_subject') ??  __('Your job post is now live on Huduma Portal'),
                    'message' => $messages ?? '',
                ]));
                 
                $buyer_id = $buyer_info->id;
              
                notifySeller(
                    $buyer_id,
                    "Tangazo lako la kazi limepitishwa na lipo hewani! Wafanyakazi wanaweza kuanza kutuma maombi./ Your job post has been approved and is live! Freelancers can now apply.", //p
                    "Tangazo lako la kazi limepitishwa na lipo hewani! Wafanyakazi wanaweza kuanza kutuma maombi.", //sms
                    [
                        'type' => 'gernalnotifications',
                      // 'service_id' => $service->id,
                        'id' => uniqid('notif_'),
                        'Tangazo lako la kazi limepitishwa na lipo hewani! Wafanyakazi wanaweza kuanza kutuma maombi./ Your job post has been approved and is live! Freelancers can now apply.' //p
                    ]
                );  
             }
            } catch (\Exception $e) {
                      dd($e->getMessage());
                        return redirect()->back()->with(FlashMsg::item_new($e->getMessage()));
                    }        
     
        BuyerJob::where('id',$id)->update(['status'=>$status]);
        return redirect()->back()->with(FlashMsg::item_new('Status Changed Success'));
    }

    public function delete($id){
        BuyerJob::find($id)->delete();
        return redirect()->back()->with(FlashMsg::item_new('Job Deleted Success'));
    }

    public function all_request($id)
    {
        $all_request = JobRequest::with('job')->where('job_post_id',$id)->orderByDesc('id')->get();
        return view('jobpost::backend.all-request',compact('all_request'));
    }

    public function conversation_details($id)
    {
        $request_details = JobRequest::with('job')
            ->where('id',$id)
            ->first();
        $all_messages = JobRequestConversation::where(['job_request_id'=>$id])->get();
        $q = $request->q ?? '';
        return view('jobpost::backend.view-conversation', compact('request_details','all_messages','q'));
    }

    public function jobSettings()
    {
        return view('jobpost::backend.job-settings');
    }
    public function jobCreateSettingsUpdate(Request $request)
    {
        update_static_option('job_create_settings',$request->job_create_settings);
        update_static_option('job_overview_title',$request->job_overview_title);
        update_static_option('job_starting_at_price_title',$request->job_starting_at_price_title);
        return redirect()->back()->with(FlashMsg::item_new('Update Success'));

    }

}
