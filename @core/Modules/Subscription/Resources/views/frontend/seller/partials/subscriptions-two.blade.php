@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Subscription')}}
@endsection
@section('content')
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <x-msg.error/>
                <x-msg.success/>
                @if($subscription)
                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard__wallet">
                        <div class="dashboard__wallet__flex">
                            <div class="dashboard__wallet__balance">
                                <div class="dashboard__wallet__balance__contents">
                                    <h5 class="dashboard__wallet__balance__price mt-2">  {{ __('Current Subscription Details') }}  </h5>
                                    <p class="text-info">{{ __('Note: Pending connect will be added to available connect only the payment status is completed.') }}, {{ get_static_option('set_number_of_connect') }}  {{ __('Connect will reduce for each order from available connects') }}</p>

                                </div>
                            </div>

                            <div class="dashboard__wallet__btn">
                                @if(moduleExists('Wallet'))
                                    <div class="col-lg-12">
                                        <div class="dashboard-settings margin-top-40">
                                            <form action="{{ route('seller.subscription.renew') }}" method="post" id="renew_current_subscription_using_wallter_balance_form">
                                                @csrf
                                                <input type="hidden" value="{{ optional($subscription->subscription)->id }}" name="subscription_id">
                                                <button type="submit" id="renew_current_subscription_using_wallter_balance" class="btn btn-warning">{{ __('Renew Current Subscription') }} <br> {{ __('Before Expired') }}</button>
                                                <span  class="btn btn-success">
                                             @php $balance =  \Modules\Wallet\Entities\Wallet::select('balance')->where('buyer_id',Auth::guard('web')->user()->id)->first() @endphp
                                                    {{ __('Wallet Balance') }}<br>
                                             {{ float_amount_with_currency_symbol($balance->balance ?? 0) }}
                                         </span>
                                            </form>
                                            <p class="text-info mt-2">{{ __('Note: You can renew your current subscription from here using your wallet balance. Simply click on the above button and rest of the process will done automatically.') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                  </div>
                @endif


                <!--seller subscription basic info start-->
                @if($subscription)
                <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
                    <h4 class="dashboard_table__title">{{ __('Wallet history') }}</h4>
                    <div class="dashboard_table__main custom--table mt-4">
                        <table>
                            <thead>
                            <tr>
                                <th> {{ __('Subscription Details') }} </th>
                                <th> {{ __('Connect Details') }} </th>
                                <th> {{ __('Payment Gateway') }} </th>
                                <th> {{ __('Payment Status') }} </th>
                                <th> {{ __('Expire Date') }} </th>
                            </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td>
                                        <div>
                                            <span> {{ __('Title:') }}   <strong class="text-secondary"> {{ optional($subscription->subscription)->title }} </strong> </span> <br>
                                            <span> {{ __('Type:') }}  <strong class="text-secondary">  {{ optional($subscription->subscription)->type }}  </strong></span> <br>
                                            <span> {{ __('Connect:') }}   <strong class="text-secondary"> {{ optional($subscription->subscription)->connect }} </strong> </span> <br>
                                            <span> {{ __('Service:') }}  <strong class="text-secondary">  {{ optional($subscription->subscription)->service }}  </strong></span> <br>
                                            <span> {{ __('Job:') }}  <strong class="text-secondary">  {{ optional($subscription->subscription)->job }}  </strong></span> <br>
                                            <span> {{ __('Price:') }} <strong class="text-secondary">  {{ float_amount_with_currency_symbol(optional($subscription->subscription)->price) }} </strong> </span>
                                        </div>
                                    </td>


                                    <td>
                                        <div>
                                             <span class="mt-2">
                                                 @if($subscription->type == 'lifetime')
                                                     {{ __('Connect: ') }}  {{ __('No Limit') }} <br>
                                                 @else
                                                     {{ __('Available Connect: ') }}  {{$subscription->connect}} <br>
                                                     @if($subscription->payment_status == 'pending' || $subscription->payment_status == '')
                                                         {{ __('Pending Connect: ') }}
                                                         {{$subscription->initial_connect}} <br>
                                                     @endif
                                                 @endif
                                             </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dashboard_table__main__paymentGateway">
                                            {{ ucfirst($subscription->payment_gateway) }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dashboard_table__main__priority">
                                            <span class="service-review">
                                            <a href="javascript:void(0)" class="priorityBtn @if($subscription->payment_status=='complete') @else pending @endif completed">
                                                {{ ucfirst($subscription->payment_status=='complete' ? 'complete' : 'pending') }}</a>
                                                 <br>
                                                    @if($subscription->payment_status == 'pending' || $subscription->payment_status == '')
                                                    @if( $subscription->payment_gateway != 'manual_payment')
                                                        <form action="{{route('seller.subscription.buy')}}" method="post" enctype="multipart/form-data">
                                                            @csrf
                                                            <input type="hidden" name="subscription_id" value="{{$subscription->id}}" >
                                                            <input type="hidden" name="price" value="{{$subscription->initial_price}}" >
                                                            <input type="hidden" name="type" value="{{$subscription->type}}" >
                                                            <input type="hidden" name="seller_payment_later" value="later" >
                                                            <input type="hidden" name="selected_payment_gateway" value="{{$subscription->payment_gateway}}">
                                                             <button  type="submit" class="dashboard_table__title__btn btn-bg-1 radius-5" style="border: none">  {{__('Pay Now')}}</button>
                                                        </form>
                                                    @endif
                                                @endif
                                             </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dashboard_table__main__date">
                                            @if($subscription->type == 'lifetime')
                                                <span class="service-review">{{ __('Expire Date:') }}  {{ __('No Limit') }}</span>
                                            @else
                                                <span class="service-review"> {{ __('Expire Date:') }}  {{date('d-m-Y', strtotime($subscription->expire_date))}}  </span>
                                            @endif
                                        </div>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--seller subscription basic info end-->
                @else
                    <div class="chat_wrapper__details__inner__chat__contents">
                         <h2 class="chat_wrapper__details__inner__chat__contents__para">{{ __('No Subscription Found') }}</h2>
                    </div>
                @endif

                <!--seller subscription history  start-->
                @if($subscription_history)
                <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white mt-4">
                    <h5 class="dashboards-title">{{ __('Subscription History') }}</h5>
                    <p class="text-info mt-2">{{ __('Your earlier subscription history list.') }}</p>
                    <div class="dashboard_table__main custom--table mt-4">
                        <table>
                            <thead>
                            <tr>
                                <th> {{ __('#No') }} </th>
                                <th> {{ __('Subscription Details') }} </th>
                                <th> {{ __('Payment Details') }} </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($subscription_history as $key=>$data)
                                <tr>
                                    <td>
                                        <div class="dashboard_table__main__paymentId">{{ $key+1 }} </div>
                                    </td>
                                    <td>
                                        <div class="">
                                            {{ __('Title:') }} {{optional($data->subscription)->title}} <br>
                                            {{ __('Price:') }}
                                            @if($data->price == 0)
                                                {{float_amount_with_currency_symbol($data->initial_price)}} <br>
                                            @else
                                                {{float_amount_with_currency_symbol($data->price)}} <br>
                                            @endif
                                            {{ __('Type:') }} {{ucfirst($data->type)}} <br>
                                            @if($data->type != 'lifetime')
                                                {{ __('Connect:') }}
                                                @if($data->connect == 0)
                                                    {{$data->initial_connect}} <br>
                                                @else
                                                    {{$data->connect}} <br>
                                                @endif
                                                {{ __('Expire Date:') }} {{date('d-m-Y', strtotime($data->expire_date))}}<br>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dashboard_table__main__priority">
                                            {{ __('Payment Gateway:') }} {{ ucfirst($data->payment_gateway) }} <br>
                                            {{ __('Payment Status:') }}

                                            <a href="javascript:void(0)" class="priorityBtn @if($data->payment_status == 'complete') completed @else pending @endif">
                                            {{ ucfirst($data->payment_status=='complete' ? 'complete' : 'pending') }}
                                            </a>

                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="blog-pagination margin-top-55">
                        <div class="custom-pagination mt-4 mt-lg-5">
                            {!! $subscription_history->links() !!}
                        </div>
                    </div>
                </div>
                @endif
                <!--seller subscription history  end-->

            </div>
        </div>
 @endsection
 @section('scripts')
<script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
<x-payment-gateway-two-js/>
<script>
    (function($){
        "use strict";
        $(document).ready(function(){

            // date range
            $('.flatpickr_input').flatpickr({
                altFormat: "invisible",
                altInput: false,
                mode: "range",
            });

            $(document).on('click','#renew_current_subscription_using_wallter_balance',function(e){
                e.preventDefault();
                Swal.fire({
                    title:"{{__('Are you sure to renew subscription?')}}",
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: "{{__('Yes')}}",
                    denyButtonText: "{{__('No')}}",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#renew_current_subscription_using_wallter_balance_form').trigger('submit');
                    }
                });
            });
        });
    })(jQuery);
</script>
@endsection