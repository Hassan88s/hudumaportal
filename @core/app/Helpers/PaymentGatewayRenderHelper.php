<?php

namespace App\Helpers;

use Xgenious\Paymentgateway\Facades\XgPaymentGateway;
use Modules\Wallet\Entities\Wallet;

class PaymentGatewayRenderHelper
{
    /**
     * Human-readable name shown as a small label under each gateway logo.
     * Falls back to a title-cased version of the key for anything not in the map.
     */
    public static function gatewayDisplayName(string $gateway): string
    {
        $map = [
            'paypal'          => 'PayPal',
            'manual_payment'  => 'Manual',
            'mollie'          => 'Mollie',
            'paytm'           => 'Paytm',
            'stripe'          => 'Stripe',
            'razorpay'        => 'Razorpay',
            'flutterwave'     => 'Flutterwave',
            'paystack'        => 'Paystack',
            'marcadopago'     => 'MercadoPago',
            'instamojo'       => 'Instamojo',
            'cashfree'        => 'Cashfree',
            'payfast'         => 'PayFast',
            'midtrans'        => 'Midtrans',
            'squareup'        => 'Square',
            'cinetpay'        => 'CinetPay',
            'paytabs'         => 'PayTabs',
            'billplz'         => 'Billplz',
            'zitopay'         => 'Zitopay',
            'kineticpay'      => 'KineticPay',
            'clickpesa'       => 'ClickPesa',
            'cash_on_delivery'=> 'Cash on Delivery',
        ];
        return $map[$gateway] ?? ucwords(str_replace('_', ' ', $gateway));
    }

    /**
     * Small CSS block that styles the label under each gateway logo.
     * Output once per render to avoid duplication across style1 / style2.
     */
    protected static function gatewayLabelStyle(): string
    {
        static $printed = false;
        if ($printed) return '';
        $printed = true;
        return '<style>
            /* ================================================================
               PAYMENT GATEWAY CARDS — professional card layout
               ================================================================ */
            .payment-gateway-wrapper {
                margin: 8px 0 4px;
            }
            .payment-gateway-wrapper .paymentGateway_add,
            .paymentGateway_add.custom_radio {
                display: grid !important;
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)) !important;
                gap: 14px !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                flex-wrap: unset !important;
                flex-direction: unset !important;
            }
            .paymentGateway_add.custom_radio > .paymentGateway_add__item {
                width: auto !important;
                max-width: unset !important;
                flex: unset !important;
            }

            /* ===== Style TWO (new markup) — the card itself ===== */
            .paymentGateway_add__item.custom_radio__single {
                position: relative;
                display: flex !important;
                flex-direction: column;
                align-items: stretch;
                padding: 18px 14px 14px !important;
                border: 1.5px solid #e6e8ef !important;
                border-radius: 14px !important;
                background: #ffffff;
                transition: all .22s cubic-bezier(.4, 0, .2, 1);
                min-height: 118px;
                cursor: pointer;
                overflow: hidden;
            }
            .paymentGateway_add__item.custom_radio__single::before {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: 14px;
                background: linear-gradient(135deg, rgba(255,107,44,0) 0%, rgba(255,107,44,0) 100%);
                transition: background .22s ease;
                pointer-events: none;
            }
            .paymentGateway_add__item.custom_radio__single:hover {
                border-color: #ffb98d !important;
                box-shadow: 0 6px 18px rgba(255,107,44,.10);
                transform: translateY(-2px);
            }
            .paymentGateway_add__item.custom_radio__single.active {
                border-color: #ff6b2c !important;
                background: #fff8f3;
                box-shadow: 0 8px 22px rgba(255,107,44,.18);
            }
            .paymentGateway_add__item.custom_radio__single.active::before {
                background: linear-gradient(135deg, rgba(255,107,44,.06) 0%, rgba(255,107,44,0) 60%);
            }

            /* Logo area — take the top half, centered */
            .paymentGateway_add__item__img {
                display: flex !important;
                align-items: center;
                justify-content: center;
                width: 100% !important;
                min-height: 46px;
                margin: 4px 0 12px !important;
                padding: 0 !important;
                cursor: pointer;
            }
            .paymentGateway_add__item__img img {
                max-width: 78px !important;
                max-height: 38px !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain;
                display: block;
                margin: 0 auto;
            }

            /* Name label — clean caption below */
            .paymentGateway_add__item__name {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #1f2433;
                text-align: center;
                letter-spacing: .15px;
                line-height: 1.3;
                margin: 0;
                padding: 10px 4px 2px;
                border-top: 1px solid #f0f1f7;
                width: 100%;
                font-family: inherit;
                text-transform: capitalize;
            }
            .paymentGateway_add__item.custom_radio__single.active .paymentGateway_add__item__name {
                color: #e55621;
                font-weight: 700;
            }

            /* Radio indicator — small orange dot in corner */
            .paymentGateway_add__item__radio {
                position: absolute;
                top: 10px;
                right: 10px;
                margin: 0 !important;
                z-index: 2;
            }
            .paymentGateway_add__item__radio input[type=radio] {
                width: 15px;
                height: 15px;
                accent-color: #ff6b2c;
                cursor: pointer;
                margin: 0;
            }

            /* ===== Style ONE (legacy <ul><li>) parity ===== */
            .payment_getway_image ul {
                display: grid !important;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 16px !important;
                list-style: none;
                padding: 0 !important;
                margin: 0 !important;
            }
            .payment_getway_image li {
                position: relative;
                padding: 18px 14px 14px;
                border: 1.5px solid #e6e8ef;
                border-radius: 14px;
                background: #fff;
                min-height: 118px;
                cursor: pointer;
                transition: all .22s cubic-bezier(.4, 0, .2, 1);
                overflow: hidden;
            }
            .payment_getway_image li:hover {
                border-color: #ffb98d;
                box-shadow: 0 6px 18px rgba(255,107,44,.10);
                transform: translateY(-2px);
            }
            .payment_getway_image li.selected,
            .payment_getway_image li.active {
                border-color: #ff6b2c;
                background: #fff8f3;
                box-shadow: 0 8px 22px rgba(255,107,44,.18);
            }
            .payment_getway_image li .img-select {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                gap: 0;
                height: 100%;
            }
            .payment_getway_image li .img-select img {
                max-width: 78px;
                max-height: 38px;
                width: auto;
                height: auto;
                object-fit: contain;
                margin: 6px 0 12px;
                display: block;
            }
            .payment_getway_image li .gateway-label-below {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #1f2433;
                text-align: center;
                padding: 10px 4px 2px;
                border-top: 1px solid #f0f1f7;
                width: 100%;
                line-height: 1.3;
                letter-spacing: .15px;
                text-transform: capitalize;
                margin-top: auto;
            }
            .payment_getway_image li.selected .gateway-label-below,
            .payment_getway_image li.active .gateway-label-below {
                color: #e55621;
                font-weight: 700;
            }

            /* ===== Extra fields (manual receipt, kineticpay bank select) spacing ===== */
            .payment_gateway_extra_field_information_wrap {
                margin-top: 18px;
            }

            /* ===== Responsive ===== */
            @media (max-width: 575px) {
                .payment-gateway-wrapper .paymentGateway_add,
                .payment_getway_image ul {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 12px !important;
                }
                .paymentGateway_add__item.custom_radio__single,
                .payment_getway_image li {
                    min-height: 108px;
                    padding: 14px 10px 12px !important;
                }
                .paymentGateway_add__item__img img,
                .payment_getway_image li .img-select img {
                    max-width: 60px;
                    max-height: 32px;
                }
                .paymentGateway_add__item__name,
                .payment_getway_image li .gateway-label-below {
                    font-size: 12px;
                    padding-top: 8px;
                }
            }
        </style>';
    }

    public static function listOfPaymentGateways(){
        $payment_gateway_list = ['paypal','manual_payment','mollie','paytm','stripe','razorpay','flutterwave','paystack','marcadopago','instamojo','cashfree','payfast','midtrans','squareup','cinetpay','paytabs','billplz','zitopay', 'kineticpay', 'clickpesa'];
        //todo append payment gateway name from modules
        $modules_payment_gateway = (new ModuleMetaData())->getAllPaymentGatewayList();
        return !empty($modules_payment_gateway) ? array_merge($payment_gateway_list,$modules_payment_gateway) : $payment_gateway_list;
    }

    public static function renderCurrentBalanceForm(){
        $output = '<div class="current-balance-wrapper">';
        $output .= '<input type="checkbox" name="selected_payment_gateway" id="current_balance_gateway" class="mr-2 current_balance_selected_gateway">';
        $output .= '<label for="current_balance_gateway">'.__('Deposit From Current Balance').'</label>';
        $output .= '</div>';
        return $output;
    }

    public static function renderWalletForm(){
        
        if(!\Auth::guard('web')->check()) {
            return '';
        }
        $auth_user_id = \Auth::guard('web')->user()->id;
        $wallet_lists = Wallet::where('buyer_id', $auth_user_id)->where('status', 1)->latest()->first();
        if (!empty($wallet_lists)){
            $output = '<div class="wallet-payment-gateway-wrapper">';
            $output .= '<input type="checkbox" name="selected_payment_gateway" id="wallet_selected_payment_gateway" class="mr-2 wallet_selected_payment_gateway">';
            $output .= '<label for="wallet_selected_payment_gateway">'.__('Order From Wallet').'</label>';
            $output .= '</div>';
        }else{
            $output = '';
        }
        return $output;
    }

    public static function renderPaymentGatewayForForm($cash_on_delivery_show = true, $type = null){
        if($type == 'old'){
            return (new self())->styleOnePaymentGatewayForm($cash_on_delivery_show);
        }else{
            return  (new self())->styleTwoPaymentGatewayForm($cash_on_delivery_show);
        }
    }


    private function styleOnePaymentGatewayForm($cash_on_delivery_show)
    {
        $output = self::gatewayLabelStyle();
        $output .= '<div class="payment-gateway-wrapper payment_getway_image">';

        $output .= '<input type="hidden" name="selected_payment_gateway" id="order_from_user_wallet" value="' . get_static_option('site_default_payment_gateway') . '">';

        $all_gateway = self::listOfPaymentGateways();

        $kineticpay_enable = 0;

        $output .= '<ul>';
        $cash_on_delivery = (bool)get_static_option('cash_on_delivery_gateway');
        if ($cash_on_delivery && $cash_on_delivery_show) {
            $output .= '<li data-gateway="cash_on_delivery" ><div class="img-select">';
            $output .= render_image_markup_by_attachment_id(get_static_option('cash_on_delivery_preview_logo'));
            $output .= '</div></li>';
        }

        foreach ($all_gateway as $gateway) {
            if (!empty(get_static_option($gateway . '_gateway'))) :
                $class = (get_static_option('site_default_payment_gateway') == $gateway) ? 'class="selected active"' : '';
                // kinetic pay
                if($gateway === 'kineticpay'){
                    $kineticpay_enable = 1;
                }
                $gateway_label = self::gatewayDisplayName($gateway);
                $output .= '<li data-gateway="' . $gateway . '" ' . $class . '><div class="img-select">';
                $output .= render_image_markup_by_attachment_id(get_static_option($gateway . '_preview_logo'));
                $output .= '<span class="gateway-label-below">'.$gateway_label.'</span>';
                $output .= '</div></li>';
            endif;
        }
        $output .= '</ul>';
        $output .= '</div>';
        //extra field data for payment gateway
        $output .= '<div class="payment_gateway_extra_field_information_wrap">';
        if(!empty(get_static_option('manual_payment_gateway'))){
            $output .= '<div class="manual_payment_gateway_extra_field"><div class="form-group"> <div class="label mt-3 mb-2">'.get_static_option('site_manual_payment_name').__('Receipt').'</div> <input type="file" name="manual_payment_image" class="form-control" style="line-height: 1.15"></div><div class="manual_description">'. get_static_option('site_manual_payment_description') .'</div></div>';
        }

        //kinetic pay
        if($kineticpay_enable == 1){
            $output .= ' <div class="kinetic_payment_show_hide mt-4"> <div class="form-group kinetic_payment_field">
                            <div class="label">'.__('Choose Payment Method').'</div>
                            <select name="kineticpay_bank" id="kineticpay_bank" class="select " data-allow_clear="true" data-placeholder="Select Bank">
                                <option value="" selected="selected">Select Bank</option>
                                <option value="ABMB0212">Alliance Bank Malaysia Berhad</option>
                                <option value="ABB0233">Affin Bank Berhad</option>
                                <option value="AMBB0209">AmBank (M) Berhad</option>
                                <option value="BCBB0235">CIMB Bank Berhad</option>
                                <option value="BIMB0340">Bank Islam Malaysia Berhad</option>
                                <option value="BKRM0602">Bank Kerjasama Rakyat Malaysia Berhad</option>
                                <option value="BMMB0341">Bank Muamalat (Malaysia) Berhad</option>
                                <option value="BSN0601">Bank Simpanan Nasional Berhad</option>
                                <option value="CIT0219">Citibank Berhad</option>
                                <option value="HLB0224">Hong Leong Bank Berhad</option>
                                <option value="HSBC0223">HSBC Bank Malaysia Berhad</option>
                                <option value="KFH0346">Kuwait Finance House</option>
                                <option value="MB2U0227">Maybank2u / Malayan Banking Berhad</option>
                                <option value="MBB0228">Maybank2E / Malayan Banking Berhad E</option>
                                <option value="OCBC0229">OCBC Bank (Malaysia) Berhad</option>
                                <option value="PBB0233">Public Bank Berhad</option>
                                <option value="RHB0218">RHB Bank Berhad</option>
                                <option value="SCB0216">Standard Chartered Bank (Malaysia) Berhad</option>
                                <option value="UOB0226">United Overseas Bank (Malaysia) Berhad</option>
                            </select>
                        </div> </div>';
        }


        //todo write code for all module extra info markup
        $output .= (new ModuleMetaData())->renderAllPaymentGatewayExtraInforBlade();
        $output .= '</div>';
        return $output;
    }

    public static function styleTwoPaymentGatewayForm($cash_on_delivery_show){
        $output = self::gatewayLabelStyle();
        $output .= '<div class="payment-gateway-wrapper payment_getway_image">';
        $output .= '<input type="hidden" name="selected_payment_gateway" id="order_from_user_wallet" value="' . get_static_option('site_default_payment_gateway') . '">';
        $all_gateway = self::listOfPaymentGateways();

        $output .= '<div class="paymentGateway_add custom_radio">';
        $cash_on_delivery = (bool)get_static_option('cash_on_delivery_gateway');
        if ($cash_on_delivery && $cash_on_delivery_show) {
            $output .= '<div  class="paymentGateway_add__item custom_radio__single radius-10"  data-gateway="cash_on_delivery"><label for="cash_on_delivery_gateway" class="paymentGateway_add__item__img">';

            $output .= render_image_markup_by_attachment_id(get_static_option('cash_on_delivery_preview_logo'));
            $output .= '</label>
                            <div class="paymentGateway_add__item__radio"><input class="custom_radio__single__input" type="radio" id="cash_on_delivery_gateway" name="paymentRadio"></div>
                            </div>';
        }

        $kineticpay_enable = 0;
        foreach ($all_gateway as $gateway) {
            if (!empty(get_static_option($gateway . '_gateway'))) :
                $class = (get_static_option('site_default_payment_gateway') == $gateway) ? 'active' : '';
                $checked = (get_static_option('site_default_payment_gateway') == $gateway) ? 'checked' : '';
                // kinetic pay
                if($gateway === 'kineticpay'){
                    $kineticpay_enable = 1;
                }
                // new markup
                $gateway_label = self::gatewayDisplayName($gateway);
                $output .= '<div  class="paymentGateway_add__item custom_radio__single radius-10 '.$class.'" data-gateway="' . $gateway . '"><label for="'.$gateway.'" class="paymentGateway_add__item__img">';
                $output .= render_image_markup_by_attachment_id(get_static_option($gateway . '_preview_logo'));
                $output .= '</label>
                            <span class="paymentGateway_add__item__name">'.$gateway_label.'</span>
                            <div class="paymentGateway_add__item__radio"><input class="custom_radio__single__input" type="radio" id="'.$gateway.' " name="paymentRadio" '.$checked.'>
                            </div>
                            </div>';
            endif;
        }
        $output .= '</div>';
        $output .= '</div>';
        //extra field data for payment gateway
        $output .= '<div class="payment_gateway_extra_field_information_wrap">';
        if(!empty(get_static_option('manual_payment_gateway'))){
            $output .= '<div class="manual_payment_gateway_extra_field"><div class="form-group"> <div class="label mt-3 mb-2">'.get_static_option('site_manual_payment_name').__('Receipt').'</div> <input type="file" name="manual_payment_image" class="form-control" style="line-height: 3.15"></div><div class="manual_description">'. get_static_option('site_manual_payment_description') .'</div></div>';
        }

        //kinetic pay
        if($kineticpay_enable == 1){
            $output .= ' <div class="kinetic_payment_show_hide mt-4"> <div class="form-group kinetic_payment_field">
                            <div class="label">'.__('Choose Payment Method').'</div>
                            <select name="kineticpay_bank" id="kineticpay_bank" class="select " data-allow_clear="true" data-placeholder="Select Bank">
                                <option value="" selected="selected">Select Bank</option>
                                <option value="ABMB0212">Alliance Bank Malaysia Berhad</option>
                                <option value="ABB0233">Affin Bank Berhad</option>
                                <option value="AMBB0209">AmBank (M) Berhad</option>
                                <option value="BCBB0235">CIMB Bank Berhad</option>
                                <option value="BIMB0340">Bank Islam Malaysia Berhad</option>
                                <option value="BKRM0602">Bank Kerjasama Rakyat Malaysia Berhad</option>
                                <option value="BMMB0341">Bank Muamalat (Malaysia) Berhad</option>
                                <option value="BSN0601">Bank Simpanan Nasional Berhad</option>
                                <option value="CIT0219">Citibank Berhad</option>
                                <option value="HLB0224">Hong Leong Bank Berhad</option>
                                <option value="HSBC0223">HSBC Bank Malaysia Berhad</option>
                                <option value="KFH0346">Kuwait Finance House</option>
                                <option value="MB2U0227">Maybank2u / Malayan Banking Berhad</option>
                                <option value="MBB0228">Maybank2E / Malayan Banking Berhad E</option>
                                <option value="OCBC0229">OCBC Bank (Malaysia) Berhad</option>
                                <option value="PBB0233">Public Bank Berhad</option>
                                <option value="RHB0218">RHB Bank Berhad</option>
                                <option value="SCB0216">Standard Chartered Bank (Malaysia) Berhad</option>
                                <option value="UOB0226">United Overseas Bank (Malaysia) Berhad</option>
                            </select>
                        </div> </div>';
        }

        //todo write code for all module extra info markup
        $output .= (new ModuleMetaData())->renderAllPaymentGatewayExtraInforBlade();
        $output .= '</div>';
        return $output;
    }


}