@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Request Company')}}
@endsection
@section('style')
   
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php $default_lang = get_default_language(); @endphp
            <!-- Dashboard area Starts -->
    @include('frontend.user.buyer.partials.sidebar-two')
    <div class="dashboard__right">
        <!-- buyer header -->
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
               
 <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboard__headerContents__title">

                        
           
                    </div>
                </div>
                
                   <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <!--<h4 class="dashboards-title">{{ isset($enterprise) ? 'Edit Enterprise' : 'Register Enterprise' }}</h4>-->
                           <div class="container pt-5">
@if(isset($enterprise) && $enterprise->status !== null)
    <div class="mb-3">
      <strong>{{ __('Status') }}</strong>  : <span class="badge"
            style="
                padding: 5px 10px;
                border-radius: 5px;
                color: #fff;
                font-size: 14px;
                background-color: 
                    @if($enterprise->status == 0) orange
                    @elseif($enterprise->status == 1) green
                    @elseif($enterprise->status == 2) red
                    @endif;
            ">
            @if($enterprise->status == 0) {{ __('Pending') }}
            @elseif($enterprise->status == 1) {{ __('Approved') }}
            @elseif($enterprise->status == 2) {{ __('Rejected') }}
            @endif
        </span>
       
        
    </div>
     @if($enterprise->status == 2) 
        <div class="alert alert-danger  " role="alert">
        <strong>{{ __('Reason') }}:</strong> {{ $enterprise->rejection_reason }}
    </div>
            @endif
@endif


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
          <h4 class="dashboards-title">{{ __('Enterprise Registration Information') }}</h4>
    <form method="POST" action="{{route('buyer.enterprise.store') }}" class="pt-5">
        @csrf
        @if(isset($enterprise))
            @method('POST')
        @endif

        <div class="mb-3">
            <label class="form-label">{{ __('Name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $enterprise->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Description') }}</label>
            <textarea name="description" class="form-control">{{ old('description', $enterprise->description ?? '') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Business Type') }} *</label>
            <select name="business_type" class="form-control" required>
                <option value="">{{ __('Select') }}</option>
                @foreach(['Sole Proprietorship', 'Partnership', 'Limited Company', 'Corporation', 'NGO'] as $type)
                    <option value="{{ $type }}" {{ old('business_type', $enterprise->business_type ?? '') == $type ? 'selected' : '' }}>
                       {{ __($type) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Business Industry') }}  *</label>
            <select name="industry" class="form-control" required>
                <option value="">{{ __('Select') }}</option>
                @foreach([
            'IT' => 'Information Technology',
            'Marketing' => 'Marketing & Advertising',
            'Construction' => 'Construction & Real Estate',
            'Finance' => 'Financial Services',
            'Healthcare' => 'Healthcare & Pharmaceuticals',
            'agriculture' => 'Agriculture & Natural Resources',
            'manufacturing' => 'Manufacturing & Production',
            'retail' => 'Retail & Wholesale',
            'transportation' => 'Transportation & Logistics',
            'ict' => 'Information & Communication Technology (ICT)',
            'hospitality' => 'Hospitality & Tourism',
            'education' => 'Education & Training',
            'media' => 'Media & Entertainment',
            'professional' => 'Professional & Business Services',
            'energy' => 'Energy & Utilities',
            'automotive' => 'Automotive & Vehicle Services',
            'legal' => 'Legal & Compliance',
            'engineering' => 'Engineering & Technical Services',
            'telecommunications' => 'Telecommunications',
            'fashion' => 'Fashion & Apparel',
            'food' => 'Food & Beverage',
            'sports' => 'Sports & Recreation',
            'arts' => 'Arts & Culture',
            'environmental' => 'Environmental & Sustainability',
            'government' => 'Government & Public Administration'
        ] as $value => $label)
            <option value="{{ $value }}" {{ old('industry', $enterprise->industry ?? '') == $value ? 'selected' : '' }}>
                {{ __($label) }}
            </option>
        @endforeach
            </select>
        </div>
        
        
                  <h4 class="dashboards-title pt-2">{{ __('Contact Information') }}</h4>
        
        <div class="mb-3 pt-5">
            <label class="form-label">{{ __('Enterprise Email') }} *</label>
            <input type="email" name="enterprise_email" class="form-control" value="{{ old('enterprise_email', $enterprise->enterprise_email ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Phone Number') }} *</label>
            <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $enterprise->phone_number ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Website') }}</label>
            <input type="url" name="website" class="form-control" value="{{ old('website', $enterprise->website ?? '') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Office Address') }} *</label>
            <textarea name="office_address" class="form-control" required>{{ old('office_address', $enterprise->office_address ?? '') }}</textarea>
        </div>
        <h4 class="dashboards-title pt-2">{{ __('Account Details') }}</h4>
        <div class="mb-3 pt-5">
            <label class="form-label">{{ __('Full Name of Representative') }} *</label>
            <input type="text" name="representative_name" class="form-control" value="{{ old('representative_name', $enterprise->representative_name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Position in Enterprise') }} *</label>
            <input type="text" name="representative_position" class="form-control" value="{{ old('representative_position', $enterprise->representative_position ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Representative Email') }} *</label>
            <input type="email" name="representative_email" class="form-control" value="{{ old('representative_email', $enterprise->representative_email ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Representative Phone') }} *</label>
            <input type="text" name="representative_phone" class="form-control" value="{{ old('representative_phone', $enterprise->representative_phone ?? '') }}" required>
        </div>
        
        @if(!isset($enterprise))
         <div class="confirm-bottom-content">
            <p class="text-danger mb-3">
        {{ __('You need to pay a fee TSh :amount for company registration.', ['amount' => get_static_option('Company_request_amount')]) }}
        <strong>{{ __('This fee is non-refundable.') }}</strong>
    </p>
</p>
                                            @if(moduleExists('Wallet'))
                                                {!! \App\Helpers\PaymentGatewayRenderHelper::renderWalletForm() !!}
                                            @endif
                                            <div class="confirm-payment payment-border mt-3 mb-5">
                                                <div class="single-checkbox">
                                                    <div class="checkbox-inlines">
                                                        <label class="checkbox-label" for="check2">
                                                            {!! \App\Helpers\PaymentGatewayRenderHelper::renderPaymentGatewayForForm(false) !!}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
        <button type="submit" class="btn btn-primary">  {{ isset($enterprise) ? __('Update') : __('Register') }}</button>
    </form>
</div>
                        </div>
                    </div>
                </div>

     
            </div>
        </div>
   
        </div>
    </div>
    </div>
   
    <!-- Buyer Profile Edit Modal End-->
    <x-media.markup :type="'web'"/>
@endsection
@section('scripts')
    <x-media.js :type="'web'"/>
    <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
     <script src="{{asset('assets/backend/js/dropzone.js')}}"></script>

         <script>
    (function ($){

        $(document).ready(function (){

            //todo: if the wallet checkbox is checked need to show this value as current seleted payment gateway
            $(document).on('click', '.wallet_selected_payment_gateway',function(){
                let wallet_value = $(this).val();
                $('.wallet-payment-gateway-wrapper .wallet_selected_payment_gateway').addClass('selected');
                if($('.wallet_selected_payment_gateway').is(':checked')){
                    $('.payment-gateway-wrapper #order_from_user_wallet').val('wallet');

                    // if select Order From Wallet
                    $('.custom_radio__single').removeClass('active');
                    $('.custom_radio__single__input').prop('checked', false);

                    // if wallet not select
                    $('.custom_radio__single__input').on('click', function (){
                        $('#wallet_selected_payment_gateway').prop('checked', false);
                    });

                }else {
                    $('.payment-gateway-wrapper #order_from_user_wallet').val('');
                }
            });

            $(document).on('click', '.current_balance_selected_gateway',function(){
                $('.payment-gateway-wrapper li').removeClass('active');
                $('.payment-gateway-wrapper li').removeClass('selected');
                $('.current-balance-wrapper .current_balance_selected_gateway').addClass('selected');
                $('.payment-gateway-wrapper #order_from_user_wallet').val('current_balance');
            });


        //new add start
            // select payment gateway
            $(document).on('click', '.paymentGateway_add__item',function(){
                let value = $(this).data('gateway');
                $('#order_from_user_wallet').val(value);

                // manual payment image option show/hide
                if(value == 'manual_payment'){
                    $('.manual_payment_gateway_extra_field').show();
                }else {
                    $('.manual_payment_gateway_extra_field').hide();
                }
            });

            // for wallet
            $(document).on('click', '#wallet_selected_payment_gateway',function(){
                $('.confirm-payment').find('#order_from_user_wallet').val('wallet');
            });


            // select manual payment gateway
            if($('#order_from_user_wallet').val() == 'manual_payment'){
                $('.manual_payment_gateway_extra_field').show();
            }else {
                $('.manual_payment_gateway_extra_field').hide();
            }


               // kinetic select bank name option show/hide
                @if(get_static_option('site_default_payment_gateway') === 'kineticpay')
                     $('.kinetic_payment_show_hide').show();
                @else
                    $('.kinetic_payment_show_hide').hide();
                @endif
            $(document).on('click', '.paymentGateway_add__item',function(){
                let value = $(this).data('gateway');
                $('#order_from_user_wallet').val(value);
                if(value == 'kineticpay'){
                    $('.kinetic_payment_show_hide').show();
                }else {
                    $('.kinetic_payment_show_hide').hide();
                }
            });
         //new add end


            // if payment gateway name value is null and (I agree with terms and conditions) is null
            $(document).on('click', '#check3', function(){
                if($('#order_from_user_wallet').val() !== null &&  $('#check3').is(":checked")){
                    $('.all_check_for_order').removeClass('active');
                    $('.all_check_for_order').addClass('completed');
                }else{
                    $('.all_check_for_order').removeClass('completed');
                    $('.all_check_for_order').addClass('active');
                }
            });

            // if (I agree with) is not null and (Order From Wallet) is null
            $(document).on('click', '#wallet_selected_payment_gateway', function (){
                if($('#wallet_selected_payment_gateway').is(":checked") === false){
                        $('.all_check_for_order').removeClass('completed');
                        $('.all_check_for_order').addClass('active');
                }else if($('#check3').is(":checked")){
                    $('.all_check_for_order').removeClass('active');
                    $('.all_check_for_order').addClass('completed');
                }
            });


            // $(document).on('click', '.next', function (){
            //     if ($('.edit_payment_option').hasClass('completed')) {
            //         console.log('ok')
            //         $('.all_check_for_order.edit_payment_option.completed.active').removeClass('active');
            //     }
            // });


        });

    })(jQuery);

</script>
@endsection
