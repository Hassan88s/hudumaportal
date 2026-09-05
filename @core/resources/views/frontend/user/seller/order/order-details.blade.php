@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Order Details') }}
@endsection
@section('style')
    <style>
        .line-top-contents{
            margin-top: 20px;
        }
    </style>
    @include('frontend.user.seller.order.order-details-style')
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
      <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <!-- Report section start-->
                <div class="dashboard_table__wrapper dashboard_border  padding-20 radius-10 bg-white">
                    @if(!empty($order_details))
                        <div class="row">

                            <div class="col-md-8">
                                <div class="single-flex-middle">
                                    <div class="single-flex-middle-inner">
                                        <div class="line-charts-wrapper margin-top-40">
                                        @include('frontend.user.seller.order.partials.remaing-service-orders-detail')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                 @if(!empty($order_details->completion_request_image))
                                <div class="mt-3">
                                    <h6 class="mb-2">{{ __('Completion Request Image') }}</h6>
                            
                                    <a href="{{ get_attachment_image_by_id($order_details->completion_request_image)['img_url'] ?? '#' }}"
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        {!! render_image_markup_by_attachment_id($order_details->completion_request_image,'','thumb') !!}
                                    </a>
                                </div>
                            @endif
                                @if($order_details->order_from_job != 'yes')
                                    <div class="single-flex-middle">
                                        <div class="single-flex-middle-inner">
                                            <div class="line-charts-wrapper oreder_details_rtl margin-top-40">
                                                <div class="line-top-contents">
                                                    <h4 class="earning-title">{{ __('Include Details') }}</h4>
                                                </div>
                                                <table class="table table-bordered mt-3">
                                                    <thead>
                                                    <tr>
                                                        <th>{{ __('Title') }}</th>
                                                        @if($order_details->is_order_online !=1)
                                                            <th>{{ __('Unit Price') }}</th>
                                                            <th>{{ __('Quantity') }}</th>
                                                            <th>{{ __('Total') }}</th>
                                                        @endif
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php $package_fee =0; @endphp
                                                    @foreach($order_includes as $include)
                                                        <tr>
                                                            <td>{{ $include->title }}</td>
                                                            @if($order_details->is_order_online !=1)
                                                                <td>{{ float_amount_with_currency_symbol($include->price) }}</td>
                                                                <td>{{ $include->quantity }}</td>
                                                                <td>{{ float_amount_with_currency_symbol($include->price * $include->quantity) }}</td>
                                                                @php $package_fee += $include->price * $include->quantity @endphp
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    <tr>
                                                        @if($order_details->is_order_online !=1)
                                                            <td colspan="3"><strong>{{ __('Package Fee') }}</strong>
                                                            </td>
                                                            <td>
                                                                <strong>{{ float_amount_with_currency_symbol($package_fee) }}</strong>
                                                            </td>
                                                        @else
                                                            <td colspan="3">
                                                                <strong>{{ __('Package Fee') .float_amount_with_currency_symbol($order_details->package_fee)}}</strong>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($order_additionals->count() >= 1)
                                    <div class="single-flex-middle mt-4">
                                        <div class="single-flex-middle-inner">
                                            <div class="line-charts-wrapper oreder_details_rtl margin-top-40">
                                                <div class="line-top-contents">
                                                    <h4 class="earning-title">{{ __('Additional Details') }}</h4>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered mt-3">
                                                        <thead>
                                                        <tr>
                                                            <th>{{ __('Title') }}</th>
                                                            <th>{{ __('Unit Price') }}</th>
                                                            <th>{{ __('Quantity') }}</th>
                                                            <th>{{ __('Total') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php $extra_service =0; @endphp
                                                        @foreach($order_additionals as $additional)
                                                            <tr>
                                                                <td>{{ $additional->title }}</td>
                                                                <td>{{ float_amount_with_currency_symbol($additional->price) }}</td>
                                                                <td>{{ $additional->quantity }}</td>
                                                                <td>{{ float_amount_with_currency_symbol($additional->price * $additional->quantity) }}</td>
                                                                @php $extra_service += $additional->price * $additional->quantity @endphp
                                                            </tr>
                                                        @endforeach

                                                        <tr>
                                                            <td colspan="3"><strong>{{ __('Extra Service') }}</strong></td>
                                                            <td>
                                                                <strong>{{ float_amount_with_currency_symbol($extra_service) }}</strong>
                                                            </td>
                                                        </tr>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(optional($order_details->extraSevices)->count() >= 1)
                                    <div class="single-flex-middle mt-4">
                                        <div class="single-flex-middle-inner">
                                            <div class="line-charts-wrapper oreder_details_rtl margin-top-40">
                                                <div class="line-top-contents">
                                                    <h4 class="earning-title">{{ __('Extra Service Details') }}</h4>
                                                </div>
                                                <span class="info-text d-block mb-4">{{__('This is not included in the main service order calculation')}}</span>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th>{{ __('Title') }}</th>
                                                            <th>{{ __('Unit Price') }}</th>
                                                            <th>{{ __('Quantity') }}</th>
                                                            <th>{{ __('Amount') }}</th>
                                                          
                                                            <th>{{ __('Action') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($order_details->extraSevices as $ex_service)
                                                            <tr>
                                                                <td>{{ $ex_service->title }}</td>
                                                                <td>{{ float_amount_with_currency_symbol($ex_service->price) }}</td>
                                                                <td>{{ $ex_service->quantity }}</td>
                                                                <td>{{ float_amount_with_currency_symbol($ex_service->price * $ex_service->quantity) }}</td>
                                                          
                                                                <td>
                                                                    @if($ex_service->payment_status !== 'complete' && $order_details->payment_status === 'complete')
                                                                        <a href="#" data-url="{{route('seller.order.extra.service.delete')}}" data-id="{{ $ex_service->id }}" class="btn btn-danger extra_service_delete_btn">{{__('Delete')}}</a>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($order_details->coupon_code))
                                    <div class="single-flex-middle mt-4">
                                        <div class="single-flex-middle-inner">
                                            <div class="line-charts-wrapper oreder_details_rtl margin-top-40">
                                                <div class="line-top-contents">
                                                    <h4 class="earning-title">{{ __('Coupon Details') }}</h4>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th>{{ __('Coupon Code') }}</th>
                                                            <th>{{ __('Coupon Type') }}</th>
                                                            <th>{{ __('Coupon Amount') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <td>{{ $order_details->coupon_code }}</td>
                                                            <td>{{ __($order_details->coupon_type) }}</td>
                                                            <td>
                                                                @if($order_details->coupon_amount >0)
                                                                    {{ float_amount_with_currency_symbol($order_details->coupon_amount) }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                         
                            </div>

                            <div class="col-sm-12">
                                @if(!empty($order_declines_history->count() >= 1))
                                    <div class="single-flex-middle mt-4">
                                        <div class="single-flex-middle-inner">
                                            <div class="line-charts-wrapper oreder_details_rtl margin-top-40">
                                                <div class="line-top-contents">
                                                    <h4 class="earning-title">{{ __('Order Modifications History') }}</h4>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered mt-3">
                                                        <thead>
                                                        <tr>
                                                            <th>{{ __('History ID') }}</th>
                                                            <th>{{ __('Buyer Details') }}</th>
                                                            <th>{{ __('Status') }} ({{ __('Modifications Reason') }})</th>
                                                         
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($order_declines_history as $history)
                                                            <tr>
                                                                <td>{{ $history->id }}</td>
                                                                <td>
                                                                    <strong>{{ __('Name:') }}</strong> {{ optional($history->buyer)->username }}
                                                                    <br>
                                                                    <!--<strong>{{ __('Email:') }}</strong>{{ optional($history->buyer)->email }}-->
                                                                    <!--<br>-->
                                                                    <!--<strong>{{ __('Phone:') }}</strong>{{ optional($history->buyer)->phone }}-->
                                                                    <!--<br>-->
                                                                </td>
                                                                <td>
                                                                    <strong>{{ __('Modifications:') }}</strong>{{ $history->decline_reason }}
                                                                </td>
                                                                
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endif
                 
                </div>
            </div>
        </div>


        {{--  accept extra service modal --}}
        <div class="modal fade" id="acceptExtraServiceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <form action="{{ route('buyer.order.extra.service.accept') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Accept Extra Service Request') }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="comments-flex-item">
                                <input type="hidden" name="id" class="form-control form-control-sm">
                                <input type="hidden" name="order_id" class="form-control form-control-sm">
                            </div>
                            {!! \App\Helpers\PaymentGatewayRenderHelper::renderPaymentGatewayForForm(false) !!}

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Pay Now') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @endsection
  