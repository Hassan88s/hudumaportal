<?php


namespace Modules\Subscription\PageBuilder\Addons;

use App\PageBuilder\Fields\ColorPicker;
use App\PageBuilder\Fields\Slider;
use App\PageBuilder\Fields\Text;
use App\PageBuilder\Traits\LanguageFallbackForPageBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Subscription\Entities\Subscription;

class PricePlan extends \App\PageBuilder\PageBuilderBase
{
    use LanguageFallbackForPageBuilder;

    public function preview_image()
    {
        return 'price_plan/price_plan_one.jpg';
    }

    public function admin_render()
    {
        $output = $this->admin_form_before();
        $output .= $this->admin_form_start();
        $output .= $this->default_fields();
        $widget_saved_values = $this->get_settings();

        $output .= Text::get([
            'name' => 'title',
            'label' => __('Title'),
            'value' => $widget_saved_values['title'] ?? null,
        ]);
        $output .= ColorPicker::get([
            'name' => 'title_text_color',
            'label' => __('Title Text Color'),
            'value' => $widget_saved_values['title_text_color'] ?? null,
            'info' => __('select color you want to show in frontend'),
        ]);
        $output .= Text::get([
            'name' => 'subtitle',
            'label' => __('Subtitle'),
            'value' => $widget_saved_values['subtitle'] ?? null,
        ]);

        $output .= Slider::get([
            'name' => 'padding_top',
            'label' => __('Padding Top'),
            'value' => $widget_saved_values['padding_top'] ?? 260,
            'max' => 500,
        ]);
        $output .= Slider::get([
            'name' => 'padding_bottom',
            'label' => __('Padding Bottom'),
            'value' => $widget_saved_values['padding_bottom'] ?? 190,
            'max' => 500,
        ]);

        $output .= $this->admin_form_submit_button();
        $output .= $this->admin_form_end();
        $output .= $this->admin_form_after();

        return $output;
    }


    public function frontend_render(): string
    {
        if (!moduleExists('Subscription') || !Route::has('seller.subscription.buy')) {
            return '';
        }

        $settings = $this->get_settings();
        $title = __($settings['title']);
        $title_text_color = $settings['title_text_color'];
        $explode = explode(" ", $title);
        $title_start = __(current($explode));
        $title_end = __(end($explode));
        $subtitle_raw = $settings['subtitle'] ?? '';

        $subtitle_key = preg_replace('/\s+/', ' ', trim($subtitle_raw)); // collapse multiple spaces
        $subtitle = __($subtitle_key);
        $padding_top = $settings['padding_top'];
        $padding_bottom = $settings['padding_bottom'];
        $subscription_text = __('You must pay first to buy a subscription');
        $close_text = __('Close');
         $buy_now_text  = __('Subscribe');
         $free_plan_text = __('Use Free Plan');

        $apply = __('Apply');
        $number_of_connect = get_static_option('set_number_of_connect',2);

        $connect_text = sprintf(__('Connect to get order from buyer, each order will deduct %d connect from seller account.'),$number_of_connect);
        $route = route('seller.subscription.buy');
        $csrf_token = csrf_token();

        // payment gateway
        $payment_gateway = \App\Helpers\PaymentGatewayRenderHelper::renderPaymentGatewayForForm(false, 'old');



         $wallet_gateway = '';
        if (moduleExists('Wallet')) {
            $wallet_gateway = \App\Helpers\PaymentGatewayRenderHelper::renderWalletForm();
        }

        $login_user_type='';
        if(Auth::guard('web')->check()){
            $login_user_type = Auth::guard('web')->user()->user_type == 0 ? 'seller' : '';
        }

        $abc = get_static_option('site_manual_payment_name');
        $abcd = get_static_option('site_manual_payment_description');
        $receipt = __('Receipt');

        $form = <<<FORM
    <div class="form-group">
        <div class="label mt-3 mb-2">$abc  $receipt</div>
        <input type="file" name="manual_payment_image" class="form-control" style="line-height: 1.15">
    </div>
    <div class="manual_description">
       $abcd
    </div>
FORM;


        // price plan Coupon code
        $coupon_placeholder = __('Enter Coupon Code');
        if(!empty(get_static_option('manual_payment_gateway'))){
            $form;
        }
        $price_plan_markup= '';
        $subscriptions = Subscription::where('status',1)->get();

        foreach($subscriptions as $subscription) {
            $s_id = $subscription->id;
            $s_title = __($subscription->title);
            $type = $subscription->type;

            // translate
            $subscription_type_text = '';
            if ($type == 'monthly'){
                $subscription_type_text = __('Monthly');
            }elseif($type == 'yearly'){
                $subscription_type_text = __('Yearly');
            }elseif($type == 'lifetime'){
                $subscription_type_text = __('Lifetime');
            }

            $service = $subscription->service;
            $job = $subscription->job;
            $pro_job = $subscription->type == 'lifetime' ? __('UNLIMITED') : $subscription->promoted_services;
            $price = float_amount_with_currency_symbol($subscription->price);
            $connect = $type == 'lifetime' ? __('No limit') : $subscription->connect;
            $price_without_currency_symbol = $subscription->price;
            $image = render_image_markup_by_attachment_id($subscription->image);
            
           $projects_allowed = $subscription->projects_allowed;
           $cashback_percentage = $subscription->cashback_percentage;
           $sms_notifications =  $subscription->sms_notifications;
           $Website_enabled =    $subscription->Website_enabled;
           $personal_enabled = $subscription->personal_enabled;
           $partialpayment_enabled =   $subscription->partialpayment_enabled;
             $aidescription_enabled =   $subscription->aidescription_enabled;
            
            
            
            $month_text = $type; //ucfirst(substr($type,0,2));
            $typeText = $type == 'lifetime' ? __('package user will charge only once') : __('billing cycle, system will deduct this amount from seller account, if seller has balance, otherwise will send an invoice mail to pay the bill');
            $buy_now_markup='';
            $monthradio='';
            if ($type == 'lifetime'){
                $connect_text = __('this package will get unlimited number of connect, mean no need to purchase subscription again.');
            }else{
                $connect_text = sprintf(__('Connect to get order from buyer, each order will deduct %d connect from seller account.'),$number_of_connect);
            }

            // The first plan (id=1) is the free tier — show "Use Free Plan".
            // All other paid plans show "Subscribe".
            $btn_label = ($s_id == '1') ? $free_plan_text : $buy_now_text;

            if($login_user_type == 'seller'){
                if ($s_id == '1' || (float) $price_without_currency_symbol == 0.0) {
                    // FREE plan → submit directly, skip the payment modal
                    $buy_now_markup .= <<<FREEPLANFORM
                    <div class="btn-wrapper">
                        <form action="{$route}" method="post" class="free-plan-form" onsubmit="return confirm('{$free_plan_text}?');">
                            <input type="hidden" name="_token" value="{$csrf_token}">
                            <input type="hidden" name="subscription_id" value="{$s_id}">
                            <input type="hidden" name="type" value="{$type}">
                            <input type="hidden" name="price" value="0">
                            <input type="hidden" name="connect" value="{$connect}">
                            <input type="hidden" name="service" value="{$service}">
                            <input type="hidden" name="job" value="{$job}">
                            <input type="hidden" name="projob" value="{$pro_job}">
                            <input type="hidden" name="month" value="1">
                            <input type="hidden" name="projects_allowed" value="{$projects_allowed}">
                            <input type="hidden" name="cashback_percentage" value="{$cashback_percentage}">
                            <input type="hidden" name="sms_notifications" value="{$sms_notifications}">
                            <input type="hidden" name="Website_enabled" value="{$Website_enabled}">
                            <input type="hidden" name="personal_enabled" value="{$personal_enabled}">
                            <input type="hidden" name="partialpayment_enabled" value="{$partialpayment_enabled}">
                            <input type="hidden" name="aidescription_enabled" value="{$aidescription_enabled}">
                            <button type="submit" id="drp_{$s_id}" class="cmn-btn btn-outline-1">{$btn_label}</button>
                        </form>
                    </div>
                    FREEPLANFORM;
                } else {
                    // Paid plan → opens payment modal
                    $buy_now_markup.=<<<BUYNOWMARKUP
                    <div class="btn-wrapper">
                        <a href="#"
                        class="cmn-btn btn-outline-1 get_subscription_id"
                        id="drp_{$s_id}"
                        data-bs-toggle="modal"
                        data-bs-target="#buySubscriptionModal"
                        data-id="{$s_id}"
                        data-type="{$type}"
                        data-price="{$price_without_currency_symbol}"
                        data-connect="{$connect}"
                         data-service="{$service}"
                        data-job="{$job}"
                        data-projob="{$pro_job}"
                        data-projects_allowed="{$projects_allowed}"
                        data-cashback_percentage="{$cashback_percentage}"
                        data-sms_notifications="{$sms_notifications}"
                        data-website_enabled = "{$Website_enabled}"
                        data-personal_enabled="{$personal_enabled}"
                        data-partialpayment_enabled="{$partialpayment_enabled}"
                        data-aidescription_enabled="{$aidescription_enabled}"
                        data-month="1"
                            >{$btn_label}</a>
                    </div>
                    BUYNOWMARKUP;
                }
            }else{
                $buy_now_markup.=<<<BUYNOWMARKUP
                <div class="btn-wrapper">
                    <span href="#"
                    id="drp_{$s_id}"
                    class="cmn-btn btn-outline-1 get_subscription_id"
                        style="cursor:no-drop; opacity:0.4">{$btn_label}</span>
                  </div>
                BUYNOWMARKUP;
            }
            
            if($s_id != '1'){
            $monthradio.=<<<MONTHRADIO
            <div  id="subscriptions">
            <div class="form-check form-check-inline ">
  <input class="form-check-input" type="radio" name="inlineRadioOptions"  id="inlineRadio_{$s_id}_1" value="{$s_id}"  data-price="{$price_without_currency_symbol}" data-month="1" onclick="checkRadio(this)">
  <label class="form-check-label" for="inlineRadio_{$s_id}_1">1 Month</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio_{$s_id}_3" value="{$s_id}"  data-price="{$price_without_currency_symbol}" data-month="3" onclick="checkRadio(this)">
  <label class="form-check-label" for="inlineRadio_{$s_id}_3">3 Month</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio_{$s_id}_6" value="{$s_id}"  data-price="{$price_without_currency_symbol}" data-month="6"  onclick="checkRadio(this)">
  <label class="form-check-label" for="inlineRadio_{$s_id}_6">6 Month </label>
</div>
</div>
MONTHRADIO;
}
           $isUnlimited = ($subscription->type == 'lifetime' || $subscription->service == 10000);

            $service   = $isUnlimited ? __('UNLIMITED') : $subscription->service;
            $job       = $isUnlimited ? __('UNLIMITED') : $subscription->job;
            
            $portfolio = $subscription->type == 'lifetime' ? __('UNLIMITED') : $subscription->projects_allowed ?? '';
            
            $cashback = $subscription->type == 'lifetime' ? __('UNLIMITED') : $subscription->cashback_percentage ?? '';
            
            $service_text = sprintf(__('Post up to <strong>%s</strong> ads for your services.'),$service );
            
            $job_text = sprintf(__('Apply for up to <strong>%s</strong> jobs per month..'), $job);
            
            $jobpro_job_text = sprintf(__('Get  <strong>%s</strong> promoted ad for increased visibility.'),$pro_job);
            
               $portfolio_text = sprintf(__('Post up to  <strong>%s</strong> projects to your portfolio to showcase your skills.'),$portfolio );
               
              $cashback_percentage = sprintf( __('Get  <strong>%s%%</strong> cashback on admin fees to your HudumaPortal wallet.'),$cashback);

               $check_sms_on_off  =  $subscription->sms_notifications ?? '';
               
                // Build an array of texts
                        $texts = [];
                        
                        // Promotional placement texts
                        if ($s_id == '3') {
                            $texts[] = __("Basic inclusion in weekly promotional emails.");
                        } elseif ($s_id == '5') {
                            $texts[] = __("Featured placement in promotional emails.");
                        } elseif ($s_id == '6') {
                            $texts[] = __("Top-tier placement in promotional emails and social media posts.");
                            $texts[] = __("Receive biweekly insights reports with advanced analytics.");
                        } elseif ($s_id == '7') {
                            $texts[] = __("Top-tier placement in promotional emails and social media posts.");
                            $texts[] = __("Receive a VIP insights report with exclusive market data, trends, and personalized strategies.");
                            $texts[] = __("Get a dedicated personal manager for personalized support.");
                        }
                        
                        // SMS notification
                        if (($subscription->sms_notifications ?? '') === 'yes') {
                            $texts[] = __("Receive sms notifications for all relevant job matches.");
                        }
                        
                        // Website
                        if (($subscription->Website_enabled ?? '') === 'yes') {
                            $texts[] = __("Add Website and social media links on your profile.");
                        }
                        
                        // Personal info
                        if (($subscription->personal_enabled ?? '') === 'yes') {
                            $texts[] = __("Add your personal contact information on your profile (i.e phone number and email).");
                        }
                        
                        // Partial payment
                        if (($subscription->partialpayment_enabled ?? '') === 'yes') {
                            $texts[] = __("Apply for a partial payment before the service is done");
                        }
                        // AI
                        if (($subscription->aidescription_enabled ?? '') === 'yes') {
                            $texts[] = __("You can use AI-generated content to enhance your experience.");
                        }
                        
                        // Convert to <li> only when not empty
                        $extraLis = '';
                        foreach ($texts as $t) {
                            if (!empty($t)) {
                                $extraLis .= "<li>{$t}</li>";
                            }
                        }

  

 
            $price_plan_markup.= <<<PRICEPLAN
               <div class="col-lg-4 col-md-6 mt-5">
                    <div class="pricing-table-10">
                        <div class="icon-area">
                            $image
                            <h3 class="title">{$s_title}</h3>
                        </div>
                        <div class="price-body">
                            <ul>
                               
                                                          
                                <li> {$service_text}</li>                               
                                <li>{$job_text}</li>            
                               <li> $jobpro_job_text</li>
                               
                                <li>{$portfolio_text}</li>
                                 <li>{$cashback_percentage}</li>
                                  
                                 {$extraLis}
                                 <li></li>
                            </ul>
                        </div>
                                              
                        {$monthradio}
                        <div class="price-footer">
                          
                            <div class="price" id="new_{$s_id}">
                           
                                <span class="dollar" ></span>{$price}<span class="month">/{$subscription_type_text}</span>
                            </div>
                           
                           {$buy_now_markup}
                        </div>
                    </div>
                </div>
            PRICEPLAN;
        }


// payment option modal new and old
 $payment_option_one_markup='';
    $payment_option_one_markup .= <<<PAYMENTOPTIONONE
         {$wallet_gateway}
            <div class="confirm-payment payment-border">
                <div class="single-checkbox">
                    <div class="checkbox-inlines">
                        <label class="checkbox-label" for="check2">
                            {$payment_gateway}
                        </label>
                    </div>
                </div>
            </div> 
PAYMENTOPTIONONE;



        return <<<HTML

    <!-- ============ PRICE PLAN REDESIGN (scoped) ============ -->
    <style>
    .About-area {
        --pp-primary: #ff6b2c;
        --pp-primary-dark: #e55621;
        --pp-primary-soft: #fff2eb;
        --pp-primary-tint: #ffe9dd;
        --pp-bg: #f7f8fb;
        --pp-surface: #ffffff;
        --pp-border: #ececf3;
        --pp-text: #1f2433;
        --pp-muted: #6b7280;
        --pp-muted-2: #8b8fa3;
        --pp-success: #1aae6f;
        --pp-shadow: 0 8px 30px rgba(20,24,50,.07);
        --pp-shadow-hover: 0 14px 40px rgba(255,107,44,.18);
        background: var(--pp-bg);
        position: relative;
    }
    .About-area .section-title .title {
        font-weight: 800;
        color: var(--pp-text);
        font-size: 40px;
        line-height: 1.2;
        margin-bottom: 14px;
    }
    .About-area .section-title .section-para {
        color: var(--pp-muted);
        font-size: 15px;
        line-height: 1.6;
        max-width: 620px;
        display: inline-block;
    }

    /* ===== Pricing Card ===== */
    .About-area .pricing-table-10 {
        background: var(--pp-surface);
        border: 1px solid var(--pp-border);
        border-radius: 20px;
        padding: 32px 26px 26px;
        box-shadow: var(--pp-shadow);
        transition: all .3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
    }
    .About-area .pricing-table-10::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pp-primary), var(--pp-primary-dark));
        opacity: 0;
        transition: opacity .3s ease;
    }
    .About-area .pricing-table-10:hover {
        transform: translateY(-6px);
        box-shadow: var(--pp-shadow-hover);
        border-color: var(--pp-primary-tint);
    }
    .About-area .pricing-table-10:hover::before { opacity: 1; }

    /* Middle card: keep the solid orange button only, no border highlight */

    /* Icon / Title area */
    .About-area .pricing-table-10 .icon-area {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--pp-border);
        margin-bottom: 22px;
    }
    .About-area .pricing-table-10 .icon-area img {
        width: 62px;
        height: 62px;
        object-fit: contain;
        margin: 0 auto 14px;
        display: block;
        padding: 12px;
        background: var(--pp-primary-soft);
        border-radius: 16px;
    }
    .About-area .pricing-table-10 .icon-area .title {
        font-size: 20px;
        font-weight: 700;
        color: var(--pp-text);
        margin: 0;
        letter-spacing: .2px;
    }

    /* Feature list */
    .About-area .pricing-table-10 .price-body { flex: 1 1 auto; }
    .About-area .pricing-table-10 .price-body ul {
        list-style: none;
        padding: 0;
        margin: 0 0 20px;
    }
    .About-area .pricing-table-10 .price-body ul li {
        position: relative;
        padding: 9px 0 9px 30px;
        font-size: 14px;
        line-height: 1.55;
        color: #4b5063;
        border-bottom: 1px dashed #f0f1f7;
    }
    .About-area .pricing-table-10 .price-body ul li:last-child { border-bottom: none; padding-bottom: 4px; }
    .About-area .pricing-table-10 .price-body ul li:empty { display: none; }
    .About-area .pricing-table-10 .price-body ul li::before {
        content: '';
        position: absolute;
        left: 0; top: 12px;
        width: 20px; height: 20px;
        border-radius: 50%;
        background: var(--pp-primary-soft);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .About-area .pricing-table-10 .price-body ul li::after {
        content: '';
        position: absolute;
        left: 6px; top: 17px;
        width: 8px; height: 4px;
        border-left: 2px solid var(--pp-primary);
        border-bottom: 2px solid var(--pp-primary);
        transform: rotate(-45deg);
    }
    .About-area .pricing-table-10 .price-body ul li strong { color: var(--pp-text); font-weight: 700; }

    /* Radio group */
    .About-area #subscriptions {
        display: flex;
        justify-content: center;
        gap: 6px;
        background: var(--pp-primary-soft);
        border: 1px solid var(--pp-primary-tint);
        border-radius: 10px;
        padding: 6px;
        margin: 0 0 18px;
    }
    .About-area .form-check-inline { margin: 0 !important; flex: 1; }
    .About-area .form-check-inline .form-check-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .About-area .form-check-inline .form-check-label {
        display: block;
        text-align: center;
        padding: 7px 4px;
        border-radius: 7px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--pp-primary-dark);
        cursor: pointer;
        transition: all .2s ease;
        margin: 0;
    }
    .About-area .form-check-inline .form-check-input:checked + .form-check-label {
        background: linear-gradient(135deg, var(--pp-primary), var(--pp-primary-dark));
        color: #fff;
        box-shadow: 0 3px 8px rgba(255,107,44,.28);
    }

    /* Price footer */
    .About-area .pricing-table-10 .price-footer {
        text-align: center;
        padding-top: 6px;
        border-top: 1px solid var(--pp-border);
        margin-top: auto;
    }
    .About-area .pricing-table-10 .price {
        font-size: 34px;
        font-weight: 800;
        color: var(--pp-text);
        margin: 18px 0 8px;
        display: flex;
        justify-content: center;
        align-items: baseline;
        gap: 4px;
        line-height: 1;
    }
    .About-area .pricing-table-10 .price .dollar { display: none; }
    .About-area .pricing-table-10 .price .month {
        font-size: 13px;
        font-weight: 500;
        color: var(--pp-muted);
        text-transform: capitalize;
    }

    /* Buy button */
    .About-area .pricing-table-10 .btn-wrapper { margin-top: 14px; }
    .About-area .pricing-table-10 .cmn-btn.btn-outline-1 {
        display: block;
        width: 100%;
        padding: 12px 20px;
        border-radius: 12px;
        background: var(--pp-primary-soft) !important;
        color: var(--pp-primary-dark) !important;
        border: 2px solid var(--pp-primary-tint) !important;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: .3px;
        transition: all .25s ease;
        text-align: center;
        text-decoration: none;
    }
    .About-area .pricing-table-10 .cmn-btn.btn-outline-1:hover {
        background: linear-gradient(135deg, var(--pp-primary), var(--pp-primary-dark)) !important;
        color: #fff !important;
        border-color: var(--pp-primary-dark) !important;
        box-shadow: 0 8px 20px rgba(255,107,44,.32);
        transform: translateY(-2px);
    }
    /* Middle card uses the same button style as others */

    /* ===== Modal polish ===== */
    #buySubscriptionModal .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(20,24,50,.25);
        overflow: hidden;
    }
    #buySubscriptionModal .modal-header {
        background: linear-gradient(135deg, var(--pp-primary, #ff6b2c), var(--pp-primary-dark, #e55621));
        color: #fff;
        border: none;
        padding: 16px 22px;
    }
    #buySubscriptionModal .modal-header .modal-title { color: #fff !important; font-weight: 700; }
    #buySubscriptionModal .modal-header .close,
    #buySubscriptionModal .modal-header .btn-close { color: #fff; opacity: .9; text-shadow: none; }
    #buySubscriptionModal .modal-body { padding: 22px; background: #fafbfd; }
    #buySubscriptionModal .subscription-coupon-btn-group {
        display: flex;
        gap: 8px;
        align-items: stretch;
        margin-top: 8px;
    }
    #buySubscriptionModal .subscription-coupon-btn-group input {
        flex: 1;
        border-radius: 10px !important;
        border: 1px solid #dfe1ec !important;
        padding: 10px 14px !important;
        margin: 0 !important;
    }
    #buySubscriptionModal .subscription-coupon-btn-group .coupon_apply_btn {
        margin: 0 !important;
        border-radius: 10px !important;
        background: #1aae6f !important;
        border-color: #1aae6f !important;
        padding: 10px 18px;
        font-weight: 600;
    }
    #buySubscriptionModal .modal-footer {
        background: #fff;
        border-top: 1px solid var(--pp-border, #ececf3);
        padding: 14px 22px;
    }
    #buySubscriptionModal .modal-footer .btn { border-radius: 10px; padding: 9px 18px; font-weight: 600; }
    #buySubscriptionModal .modal-footer .btn-primary,
    #buySubscriptionModal .modal-footer .order_create_from_jobs {
        background: linear-gradient(135deg, var(--pp-primary, #ff6b2c), var(--pp-primary-dark, #e55621)) !important;
        border: none !important;
        box-shadow: 0 6px 14px rgba(255,107,44,.3);
    }
    #buySubscriptionModal .modal-footer .btn-secondary {
        background: #eef0f6 !important;
        color: #4b5063 !important;
        border: 1px solid var(--pp-border, #ececf3) !important;
    }
    #buySubscriptionModal .display_error_msg { color: #d9344b; font-size: 13px; font-weight: 500; }
    #buySubscriptionModal .display_coupon_amount { color: #1aae6f; font-size: 13px; font-weight: 600; }

    /* ===== FIX: remove theme's grey background on price footer / inner blocks ===== */
    .About-area .pricing-table-10,
    .About-area .pricing-table-10 .price-footer,
    .About-area .pricing-table-10 .price-body,
    .About-area .pricing-table-10 .icon-area,
    .About-area .pricing-table-10 .price,
    .About-area .pricing-table-10 .btn-wrapper,
    .About-area .pricing-table-10 #subscriptions,
    .About-area .pricing-table-10 > div,
    .About-area .pricing-table-10 > div > div {
        background: transparent !important;
    }
    .About-area .pricing-table-10 { background: var(--pp-surface) !important; }
    .About-area .pricing-table-10 #subscriptions {
        background: var(--pp-primary-soft) !important;
    }

    /* ===== Month selector polish (visible + centered) ===== */
    .About-area #subscriptions {
        max-width: 100%;
    }
    .About-area .form-check-inline .form-check-label {
        letter-spacing: .2px;
        padding: 8px 6px;
        border: 1px solid transparent;
    }
    .About-area .form-check-inline .form-check-input:not(:checked) + .form-check-label:hover {
        background: #fff;
        border-color: var(--pp-primary-tint);
    }

    /* ===== Modal close (×) — nice round button ===== */
    #buySubscriptionModal .modal-header .close,
    #buySubscriptionModal .modal-header .btn-close {
        width: 32px;
        height: 32px;
        min-width: 32px;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 50%;
        background: rgba(255,255,255,.22) !important;
        border: 1px solid rgba(255,255,255,.35) !important;
        color: #fff !important;
        opacity: 1 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        line-height: 1;
        font-weight: 400;
        cursor: pointer;
        transition: all .18s ease;
        text-shadow: none !important;
        box-shadow: none;
    }
    #buySubscriptionModal .modal-header .close:hover,
    #buySubscriptionModal .modal-header .btn-close:hover {
        background: rgba(255,255,255,.35) !important;
        transform: rotate(90deg);
    }
    #buySubscriptionModal .modal-header .close span {
        display: block;
        font-size: 22px;
        line-height: 1;
        color: #fff;
    }

    /* ===== Coupon Apply button (orange to match theme) ===== */
    #buySubscriptionModal .subscription-coupon-btn-group .coupon_apply_btn {
        background: linear-gradient(135deg, var(--pp-primary, #ff6b2c), var(--pp-primary-dark, #e55621)) !important;
        border: none !important;
        color: #fff !important;
        padding: 10px 22px !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        letter-spacing: .3px;
        box-shadow: 0 4px 12px rgba(255,107,44,.28);
        transition: all .18s ease;
    }
    #buySubscriptionModal .subscription-coupon-btn-group .coupon_apply_btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(255,107,44,.36);
    }
    #buySubscriptionModal .subscription-coupon-btn-group input:focus {
        outline: none;
        border-color: var(--pp-primary, #ff6b2c) !important;
        box-shadow: 0 0 0 3px rgba(255,107,44,.15) !important;
    }

    /* ===== Responsive ===== */
    @media (max-width: 991px) {
        .About-area .section-title .title { font-size: 32px; }
        .About-area .col-lg-4:nth-child(3n+2) .pricing-table-10 { transform: none; }
    }
    @media (max-width: 767px) {
        .About-area .section-title .title { font-size: 26px; }
        .About-area .pricing-table-10 { padding: 26px 20px 22px; }
        .About-area .pricing-table-10 .price { font-size: 28px; }
        .About-area .col-lg-4:nth-child(3n+2) .pricing-table-10 { transform: none; }
    }
    </style>
    <!-- ============ /PRICE PLAN REDESIGN ============ -->

     <!-- About area Starts -->
     <section class="About-area" data-padding-top="{$padding_top}" data-padding-bottom="{$padding_bottom}">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="section-title desktop-center margin-bottom-55">
                        <h2 class="title"> {$title_start} <span style="color:{$title_text_color}"> {$title_end} </span> </h2>
                        <span class="section-para">{$subtitle}</span>
                    </div>
                </div>
            </div>
            <div class="row">
                  {$price_plan_markup}
            </div>
        </div>
    </section>
    <!-- About area ends -->
    
        <!-- Add Modal -->
    <div class="modal fade" id="buySubscriptionModal" tabindex="-1" role="dialog" aria-labelledby="couponModal" aria-hidden="true">
        <form id="msform" class="ms-order-form" action="{$route}" method="post"  enctype="multipart/form-data">
            <input type="hidden" name="_token" value="{$csrf_token}">
            <input type="hidden" name="subscription_id" class="subscription_id" value="">
            <input type="hidden" name="type" class="type" value="">
            <input type="hidden" name="price" class="price" value="">
            <input type="hidden" name="connect" class="connect" value="">            
             <input type="hidden" name="service" class="service" value="">
            <input type="hidden" name="job" class="job" value="">  
            <input type="hidden" name="projob" class="projob" value="">
            <input type="hidden" name="month" class="month" value="">
            
             <input type="hidden" name="projects_allowed" class="projects_allowed" value="">
              <input type="hidden" name="cashback_percentage" class="cashback_percentage" value="">
               <input type="hidden" name="sms_notifications" class="sms_notifications" value="">
                <input type="hidden" name="Website_enabled" class="Website_enabled" value="">
                 <input type="hidden" name="personal_enabled" class="personal_enabled" value="">
                  <input type="hidden" name="partialpayment_enabled" class="partialpayment_enabled" value="">
                  
                  <input type="hidden" name="aidescription_enabled" class="aidescription_enabled" value="">
                  
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-warning" id="couponModal">{$subscription_text}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">


                        <div class="confirm-bottom-content">
                                 {$payment_option_one_markup}
                            
                            <div class="col-lg-12">
                                <div class="order cart-total">
                                    <div class="form-group">
                                        <input type="hidden" value="" id="subscription_price">
                                        <p class="display_error_msg"></p>
                                        <p class="display_coupon_amount"></p>
                                       <div class="subscription-coupon-btn-group">
                                            <input type="text" name="apply_coupon_code" id="apply_coupon_code" class="form-control mt-2" style="line-height: 1.15" placeholder="{$coupon_placeholder}">
                                            <button type="button" class="btn btn-success coupon_apply_btn mx-4">{$apply}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>                        

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{$close_text}</button>
                        <button type="submit" class="btn btn-primary order_create_from_jobs">{$buy_now_text}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
HTML;

    }

    public function addon_title()
    {
        return __('Price Plan');
    }
}


