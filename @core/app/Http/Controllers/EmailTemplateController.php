<?php

namespace App\Http\Controllers;

use App\Helpers\FlashMsg;
use App\Traits\EmailTemplateHelperTrait;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
//    use EmailTemplateHelperTrait;
    const BASE_PATH = 'backend.pages.email-template.';
    const BASE_PATH_TWO = 'backend.pages.email-template.admin.';
    const BASE_PATH_JOB = 'backend.pages.email-template.jobs.';
    const BASE_PATH_SUBSCRIPTION = 'backend.pages.email-template.subscription.';

    public function __construct()
    {
        $this->middleware('permission:service-list|service-status|service-delete|service-view', ['only' => ['all']]);
        $this->middleware('permission:service-status', ['only' => ['change_status']]);
        $this->middleware('permission:service-delete', ['only' => ['delete_service', 'bulk_action']]);
        $this->middleware('permission:service-view', ['only' => ['viewServiceDetails']]);
        $this->middleware('permission:service-book-setting', ['only' => ['service_book_settings']]);
        $this->middleware('permission:service-detail-setting', ['only' => ['service_details_settings']]);
    }

    public function all()
    {
       
        return view(self::BASE_PATH.'all');
    }
    public function dynamicEmailTemplate(Request $request, $slug)
    {
       
        $templates = [
            // SLUG => [subject_field, message_field, title]
            'buyer-user-register' => [
                'subject_field' => 'buyer-user-register_subject',
                'message_field' => 'buyer-user-register_message',
                'title' => 'buyer user register Email Template',
                'notes' => 'For buyer when  register Email Template',
            ],
            'buyer-Job-posting' => [
                'subject_field' => 'buyer-Job-posting_subject',
                'message_field' => 'buyer-Job-posting_message',
                'title' => 'Job Posting Email Template',
                'notes' => 'When Buyer Upload Job',
            ],
            'buyer-Job-approved-admin' => [
                'subject_field' => 'buyer-Job-approved-admin_subject',
                'message_field' => 'buyer-Job-approved-admin_message',
                'title' => 'Buyer email For job Approval Email Template',
                'notes' => 'notify buyer when admin approved their job',
            ],
            'buyer-New-Message' => [
                'subject_field' => 'buyer-New-Message_subject',
                'message_field' => 'buyer-New-Message_message',
                'title' => 'Buyer New Message Email Template',
                'notes' => 'Used to notify buyer about new messsage.',
            ],
            'buyer-Service-booking' => [
                'subject_field' => 'buyer-Service-booking_subject',
                'message_field' => 'abuyer-Service-booking_message',
                'title' => 'Buyer Service booking Email Template',
                'notes' => 'Used to notify buyer about service booking .',
            ],
           
            'buyer-Service-booking-approved' => [
                'subject_field' => 'buyer-Service-booking-approved_subject',
                'message_field' => 'buyer-Service-booking-approved_message',
                'title' => 'Buyer Service approved Email Template',
                'notes' => 'Used to notify buyer about service booking approved .',
            ],
            'buyer-custom-offer-recieved' => [
                'subject_field' => 'buyer-custom-offer-recieved_subject',
                'message_field' => 'buyer-custom-offer-recieved_message',
                'title' => 'Buyer custom offer recieved  Email Template',
                'notes' => 'Used to notify buyer When freelancer send custom offer .',
            ],
            'buyer-custom-offer-accept' => [
                'subject_field' => 'buyer-custom-offer-accept_subject',
                'message_field' => 'buyer-custom-offer-accept_message',
                'title' => 'Buyer custom offer accept  Email Template',
                'notes' => 'Used to notify buyer When buyer accpet custom offer .',
            ],
            'buyer-custom-offer-decline' => [
                'subject_field' => 'buyer-custom-offer-decline_subject',
                'message_field' => 'buyer-custom-offer-decline_message',
                'title' => 'Buyer custom offer decline  Email Template',
                'notes' => 'Used to notify buyer When buyer decline custom offer ',
            ],
            'buyer-apply-job-by-freelancer' => [
                'subject_field' => 'buyer-apply-job-by-freelancer_subject',
                'message_field' => 'buyer-apply-job-by-freelancer_message',
                'title' => 'Buyer job apply  Email Template',
                'notes' => 'Used to notify buyer when any freelancer applied for job.',
            ],
            
             'buyer-job-hire-to-freelancer' => [
                'subject_field' => 'buyer-job-hire-to-freelancer_subject',
                'message_field' => 'buyer-job-hire-to-freelancer_message',
                'title' => 'Buyer hire freelancer for jon  Email Template',
                'notes' => 'Used to notify buyer when hire freelancer for job.',
            ],
            'buyer-service-completion-request-from-freelancer' => [
                'subject_field' => 'buyer-service-completion-request-from-freelancer_subject',
                'message_field' => 'buyer-service-completion-request-from-freelancer_message',
                'title' => 'Buyer Service completion request Email Template',
                'notes' => 'Used to notify buyer when they complete the service complete request',
            ],
            'buyer-request-modification-for-freelancer' => [
                'subject_field' => 'buyer-request-modification-for-freelancer_subject',
                'message_field' => 'buyer-request-modification-for-freelancer_message',
                'title' => 'Buyer Request Modification Email Template',
                'notes' => 'Used to notify buyer when buyer ask for request modification of the order',
            ],
            'buyer-approves-service-completion' => [
                'subject_field' => 'buyer-approves-service-completion_subject',
                'message_field' => 'buyer-approves-service-completion_message',
                'title' => 'Buyer Approves Service Completion  Email Template',
                'notes' => 'Used to notify buyer when they marked the order complete',
            ],
            'buyer-delivery-time-extension-by-freelancer' => [
                'subject_field' => 'buyer-delivery-time-extension-by-freelancer_subject',
                'message_field' => 'buyer-delivery-time-extension-by-freelancer_message',
                'title' => 'Buyer Delivery Time Extension  Email Template',
                'notes' => 'Used to notify buyer when freelancer send delivery time extension',
            ],
             // /
              'buyer-delivery-time-extension-approved-by-freelancer' => [
                'subject_field' => 'buyer-delivery-time-extension-approved-by-freelancer_subject',
                'message_field' => 'buyer-delivery-time-extension-approved-by-freelancer_message',
                'title' => 'Buyer Delivery Time Extension Approved  Email Template',
                'notes' => 'Used to notify buyer when delivery time extension approved',
            ],
             'buyer-delivery-time-extension-declined-by-freelancer' => [
                'subject_field' => 'buyer-delivery-time-extension-declined-by-freelancer_subject',
                'message_field' => 'buyer-delivery-time-extension-declined-by-freelancer_message',
                'title' => 'Buyer Delivery Time Extension Decline  Email Template',
                'notes' => 'Used to notify buyer when delivery time extension Decline',
            ],
             'buyer-additional-service-extension-request' => [
                'subject_field' => 'buyer-additional-service-extension-request_subject',
                'message_field' => 'buyer-additional-service-extension-request_message',
                'title' => 'Buyer Additional Service Extension request Email Template',
                'notes' => 'Used to notify buyer when freelancer send additional service extension request',
            ],
             'buyer-additional-service-extension-request-approved' => [
                'subject_field' => 'buyer-additional-service-extension-request-approved_subject',
                'message_field' => 'buyer-additional-service-extension-request-approved_message',
                'title' => 'Buyer Additional Service Extension request approved Email Template',
                'notes' => 'Used to notify buyer when freelancer approved additional service extension request',
            ],
             'buyer-additional-service-extension-request-decline' => [
                'subject_field' => 'buyer-additional-service-extension-request-decline_subject',
                'message_field' => 'buyer-additional-service-extension-request-decline_message',
                'title' => 'Buyer Additional Service Extension request decline Email Template',
                'notes' => 'Used to notify buyer when freelancer decline additional service extension request',
            ],
            //
             'buyer-promotes-job-request' => [
                'subject_field' => 'buyer-promotes-job-request_subject',
                'message_field' => 'buyer-promotes-job-request_message',
                'title' => 'Buyer Promotes Job Email Template',
                'notes' => 'Used to notify buyer when buyer  promotes job ',
            ],
             'buyer-client-registers-enterprise-request' => [
                'subject_field' => 'buyer-client-registers-enterprise-request_subject',
                'message_field' => 'buyer-client-registers-enterprise-request_message',
                'title' => 'client registers as  enterprise Email Template',
                'notes' => 'Used to notify buyer when client register as enterprise',
            ],
             'buyer-partial-payment-extension-request' => [
                'subject_field' => 'buyer-partial-payment-extension-request_subject',
                'message_field' => 'buyer-partial-payment-extension-request_message',
                'title' => 'Buyer Partial payment Extension request Email Template',
                'notes' => 'Used to notify buyer when freelancer request for parital parital',
            ],
             'buyer-partial-payment-extension-request-approved' => [
                'subject_field' => 'buyer-partial-payment-extension-request-approved_subject',
                'message_field' => 'buyer-partial-payment-extension-request-approved_message',
                'title' => 'Buyer Partial payment Extension request approved Email Template',
                'notes' => 'Used to notify buyer when they approved additional parital parital request',
            ],
             'buyer-partial-payment-extension-request-decline' => [
                'subject_field' => 'buyer-partial-payment-extension-request-decline_subject',
                'message_field' => 'buyer-partial-payment-extension-request-decline_message',
                'title' => 'Buyer Partial payment Extension request decline  Email Template',
                'notes' => 'Used to notify buyer when they decline additional parital parital request',
            ],
            // Add the rest of your 30 templates here...
        ];
    
        if (!isset($templates[$slug])) {
            abort(404);
        }
    
        $config = $templates[$slug];
    
        if ($request->isMethod('post')) {
            $request->validate([
                $config['subject_field'] => 'required|min:5|max:100',
                $config['message_field'] => 'required|min:10|max:2000',
            ]);
    
            update_static_option($config['subject_field'], $request->input($config['subject_field']));
            update_static_option($config['message_field'], $request->input($config['message_field']));
    
            return back()->with(FlashMsg::item_new(__('Update Success')));
        }
    
        return view('backend.pages.email-template.dynamic', [
            'subject_field' => $config['subject_field'],
            'message_field' => $config['message_field'],
            'title' => $config['title'],
            'notes' => $config['notes'],
        ]);
    }

    public function user_register_template(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'user_register_subject'=>'required|min:5|max:100',
                'user_register_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'user_register_subject',
                'user_register_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'user-register-template');
    }
    
    
    
     public function user_jobsnewsletter_template(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'user_jobsnewsletter_subject'=>'required|min:5|max:100',
                'user_jobsnewsletter_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'user_jobsnewsletter_subject',
                'user_jobsnewsletter_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'user-jobsnewsletter-template');
    }

    public function user_email_verify_template(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'user_email_verify_subject'=>'required|min:5|max:100',
                'user_email_verify_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'user_email_verify_subject',
                'user_email_verify_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'user-email-verify-template');
    }
    
    
    
    public function new_service_approve(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'service_approve_subject'=>'required|min:5|max:100',
                'service_approve_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'service_approve_subject',
                'service_approve_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'new-service-approve-template');
    }
    
    //sami working
    
     public function servicecompletiontemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'servicecompletion_subject'=>'required|min:5|max:200',
                'servicecompletion_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'servicecompletion_subject',
                'servicecompletion_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'service-completion-template');
    }
    
     public function requestmodificationtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'RequestModification_subject'=>'required|min:5|max:200',
                'RequestModification_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'RequestModification_subject',
                'RequestModification_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'service-request-modification-template');
    }
    
     public function approvesservicecompletiontemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'approvesservicecompletion_subject'=>'required|min:5|max:100',
                'approvesservicecompletion_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'approvesservicecompletion_subject',
                'approvesservicecompletion_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'approves-service-completion');
    }
    
     public function deliverytimeextensionrequesttemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'deliverytimeextension_subject'=>'required|min:5|max:100',
                'deliverytimeextension_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'deliverytimeextension_subject',
                'deliverytimeextension_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'service-delivery-timeextension-request-template');
    }
    // 4
     public function deliverytimeextensionapprovedtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'Deliverytimeextensionapproved_subject'=>'required|min:5|max:100',
                'Deliverytimeextensionapproved_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'Deliverytimeextensionapproved_subject',
                'Deliverytimeextensionapproved_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'delivery-timeextension-approved-template');
    }
    
     public function deliverytimeextensionDeclinedtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'Deliverytimeextensiondecline_subject'=>'required|min:5|max:200',
                'Deliverytimeextensiondecline_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'Deliverytimeextensiondecline_subject',
                'Deliverytimeextensiondecline_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'delivery-timeextension-decline-template');
    }
     public function additionalservicerequesttemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'additionalservicerequest_subject'=>'required|min:5|max:100',
                'additionalservicerequest_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'additionalservicerequest_subject',
                'additionalservicerequest_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'additional-service-request-template');
    }
     public function additionalservicerequestapprovedtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'additionalservicerequestapproved_subject'=>'required|min:5|max:100',
                'additionalservicerequestapproved_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'additionalservicerequestapproved_subject',
                'additionalservicerequestapproved_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'additional-service-request-approved-template');
    }
     public function additionalservicerequestdeclinedtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'additionalservicerequestdeclined_subject'=>'required|min:5|max:100',
                'additionalservicerequestdeclined_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'additionalservicerequestdeclined_subject',
                'additionalservicerequestdeclined_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'additional-service-request-decline-template');
    }
    // 
    
    // 1 to 6
     public function promoteservicestemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'promotesservicetemplate_subject'=>'required|min:5|max:200',
                'promotesservicetemplate_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'promotesservicetemplate_subject',
                'promotesservicetemplate_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'promote-services-template');
    }
    
     public function partialpaymentrequesttemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'partialpaymentrequest_subject'=>'required|min:5|max:100',
                'partialpaymentrequest_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'partialpaymentrequest_subject',
                'partialpaymentrequest_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'partial-payment-request-template');
    }
    
     public function partialpaymentrequestapprovedtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'partialpaymentapprovedrequest_subject'=>'required|min:5|max:100',
                'partialpaymentapprovedrequest_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'partialpaymentapprovedrequest_subject',
                'partialpaymentapprovedrequest_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'partial-payment-approved-request-template');
    }
    
     public function partialpaymentrequestdeclinedtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'partialpaymentdeclinerequest_subject'=>'required|min:5|max:100',
                'partialpaymentdeclinerequest_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'partialpaymentdeclinerequest_subject',
                'partialpaymentdeclinerequest_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'partial-payment-approved-declined-template');
    }
    
     public function payoutrequest(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'Payoutrequest_subject'=>'required|min:5|max:100',
                'Payoutrequest_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'Payoutrequest_subject',
                'Payoutrequest_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'payout-request-template');
    }
    
     public function paymentsenttemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'Paymentsent_subject'=>'required|min:5|max:100',
                'Paymentsent_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'Paymentsent_subject',
                'Paymentsent_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'payment-sent-template');
    }
    
    
    // 
    
    
    
    public function seller_service_pending_approve(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'new_seller_service_pendingapprove_subject'=>'required|min:5|max:100',
                'new_seller_service_pendingapprove_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'new_seller_service_pendingapprove_subject',
                'new_seller_service_pendingapprove_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'seller-service-pending-approve-template');
    }

        public function new_message(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'new_message_subject'=>'required|min:5|max:200',
                'new_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'new_message_subject',
                'new_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'new-message');
    }
      public function BookingApprovedByFreelancer(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'booking_approved_subject'=>'required|min:5|max:200',
                'booking_approved_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'booking_approved_subject',
                'booking_approved_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'booking-approved-by-freelancer');
    }
       public function customofferSent(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'customeroffersent_subject'=>'required|min:5|max:200',
                'customeroffersent_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'customeroffersent_subject',
                'customeroffersent_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'custom-offer-sent');
    }
       public function customofferAccepted(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'customerofferaccepted_subject'=>'required|min:5|max:200',
                'customerofferaccepted_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'customerofferaccepted_subject',
                'customerofferaccepted_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'custom-offer-accepted');
    }
       public function customofferDeclined(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'customerofferdecline_subject'=>'required|min:5|max:200',
                'customerofferdecline_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'customerofferdecline_subject',
                'customerofferdecline_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'custom-offer-declined');
    }
    
    
      public function applyjobtemmplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'applyjob_subject'=>'required|min:5|max:200',
                'applyjob_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'applyjob_subject',
                'applyjob_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'apply-for-job');
    }
     public function jobhiringtemplate(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'jobhiring_subject'=>'required|min:5|max:200',
                'jobhiring_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'jobhiring_subject',
                'jobhiring_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH.'hiring-job');
    }

    
    public function seller_report(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'seller_report_subject'=>'required|min:5|max:100',
                'seller_report_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'seller_report_subject',
                'seller_report_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'seller-report-template');
    }

    public function seller_payout_request(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'seller_payout_subject'=>'required|min:5|max:100',
                'seller_payout_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'seller_payout_subject',
                'seller_payout_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'seller-payout-request-template');
    }

    public function seller_order_ticket(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'seller_order_ticket_subject'=>'required|min:5|max:100',
                'seller_order_ticket_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'seller_order_ticket_subject',
                'seller_order_ticket_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'seller-order-ticket-template');
    }

    public function seller_verification(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'seller_verification_subject'=>'required|min:5|max:100',
                'seller_verification_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'seller_verification_subject',
                'seller_verification_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'seller-verification-template');
    }

    public function seller_extra_service(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'seller_extra_service_subject'=>'required|min:5|max:100',
                'seller_extra_service_message'=>'required|min:10|max:2000',
                'seller_to_buyer_extra_service_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'seller_extra_service_subject',
                'seller_extra_service_message',
                'seller_to_buyer_extra_service_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'seller-extra-service-template');
    }

    public function buyer_decline(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'buyer_order_decline_subject'=>'required|min:5|max:100',
                'buyer_order_decline_message'=>'required|min:10|max:2000',
                'buyer_to_admin_extra_service_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'buyer_order_decline_subject',
                'buyer_order_decline_message',
                'buyer_to_admin_extra_service_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'buyer-order-complete-decline-template');
    }

    public function buyer_report(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'buyer_report_subject'=>'required|min:5|max:100',
                'buyer_report_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'buyer_report_subject',
                'buyer_report_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'buyer-report-template');
    }

    public function buyer_order_ticket(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'buyer_order_ticket_subject'=>'required|min:5|max:100',
                'buyer_order_ticket_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'buyer_order_ticket_subject',
                'buyer_order_ticket_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'buyer-order-ticket-template');
    }

    public function buyer_extra_service_accept(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'buyer_extra_service_subject'=>'required|min:5|max:100',
                'buyer_extra_service_message'=>'required|min:10|max:2000',
                'buyer_to_seller_extra_service_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'buyer_extra_service_subject',
                'buyer_extra_service_message',
                'buyer_to_seller_extra_service_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));
        }
        return view(self::BASE_PATH.'buyer-extra-service-accept-template');
    }


    //admin email template
    public function change_payment_status(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'admin_change_payment_status_subject'=>'required|min:5|max:100',
                'admin_change_payment_status_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'admin_change_payment_status_subject',
                'admin_change_payment_status_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'change-payment-status-template');
    }

    public function withdraw_amount_send(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'admin_withdraw_amount_send_subject'=>'required|min:5|max:100',
                'admin_withdraw_amount_send_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'admin_change_payment_status_subject',
                'admin_withdraw_amount_send_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'withdraw-amount-send-template');
    }

    public function service_approve(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'admin_service_approve_subject'=>'required|min:5|max:100',
                'admin_service_approve_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'admin_service_approve_subject',
                'admin_service_approve_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'service-approve-template');
    }

    public function service_assign_to_seller(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'admin_service_assign_subject'=>'required|min:5|max:100',
                'admin_service_assign_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'admin_service_assign_subject',
                'admin_service_assign_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'admin-service-assign-to-seller-template');
    }

    public function seller_verification_from_admin(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'admin_seller_verification_subject'=>'required|min:5|max:100',
                'admin_seller_verification_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'admin_seller_verification_subject',
                'admin_seller_verification_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'seller-verification-template');
    }

    public function user_verification_code(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'admin_user_verification_code_subject'=>'required|min:5|max:100',
                'admin_user_verification_code_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'admin_user_verification_code_subject',
                'admin_user_verification_code_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'verification-code-template');
    }

    public function user_new_password(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'admin_user_new_password_subject'=>'required|min:5|max:100',
                'admin_user_new_password_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'admin_user_new_password_subject',
                'admin_user_new_password_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'new-password-template');
    }

    public function order_ad_sell_buyer(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'new_order_email_subject'=>'required|min:5|max:100',
                'new_order_buyer_message'=>'required|min:10|max:2000',
                'new_order_admin_seller_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'new_order_email_subject',
                'new_order_buyer_message',
                'new_order_admin_seller_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_TWO.'new-order-template');
    }

    public function job_apply(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'job_apply_subject'=>'required|min:5|max:100',
                'job_apply_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'job_apply_subject',
                'job_apply_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_JOB.'job-apply-template');
    }


    public function job_create(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'job_create_subject'=>'required|min:5|max:100',
                'job_create_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'job_create_subject',
                'job_create_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_JOB.'job-create-template');
    }

    public function buy_subscription(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'buy_subscription_email_subject'=>'required|min:5|max:100',
                'buy_subscription_seller_message'=>'required|min:10|max:2000',
                'buy_subscription_admin_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'buy_subscription_email_subject',
                'buy_subscription_seller_message',
                'buy_subscription_admin_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_SUBSCRIPTION.'buy-subscription-template');
    }

    public function renew_subscription(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'renew_subscription_email_subject'=>'required|min:5|max:100',
                'renew_subscription_seller_message'=>'required|min:10|max:2000',
                'renew_subscription_admin_message'=>'required|min:10|max:2000',
            ]);
            $fields = [
                'renew_subscription_email_subject',
                'renew_subscription_seller_message',
                'renew_subscription_admin_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_SUBSCRIPTION.'renew-subscription-template');
    }

    public function subscription_payment_status(Request $request)
    {
        if($request->isMethod('post')){
            $request->validate([
                'payment_subscription_email_subject'=>'required|min:5|max:100',
                'payment_subscription_seller_message'=>'required|min:5|max:2000',
            ]);
            $fields = [
                'payment_subscription_email_subject',
                'payment_subscription_seller_message',
            ];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            return redirect()->back()->with(FlashMsg::item_new(__('Update Success')));

        }
        return view(self::BASE_PATH_SUBSCRIPTION.'payment-status-template');
    }


}
