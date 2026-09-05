@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Wallet') }}
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>

    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <x-msg.error/>
                <x-msg.success/>

                <!-- search section start-->
                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard__wallet">
                        <form action="{{ route('seller.wallet.history') }}" method="GET">
                            <div class="dashboard__headerGlobal__flex">
                                <div class="dashboard__headerGlobal__content">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <h4 class="dashboard_table__title">{{ __('Search Wallet Module') }}</h4> <i class="las la-angle-down search_by_all"></i>
                                    </button>
                                </div>
                                <div class="dashboard__headerGlobal__btn">
                                    <div class="btn-wrapper">
                                        <button href="#" class="dashboard_table__title__btn btn-bg-1 radius-5" type="submit">
                                            <i class="fa-solid fa-magnifying-glass"></i> {{ __('Search') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div id="collapseOne" class="accordion-collapse collapse
                                @if(request()->get('payment_id'))  show
                                @elseif(request()->get('status')) show
                                @elseif(request()->get('wallet_date')) show
                                @endif
                             " aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="single-settings">
                                                    <div class="single-dashboard-input">
                                                        <div class="row g-4 mt-3">
                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="payment_id" class="info-title"> {{__('Payment ID')}} </label>
                                                                    <input class="form--control" name="payment_id" value="{{ request()->get('payment_id') }}" type="text" placeholder="{{ __('Payment ID') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="status" class="info-title"> {{__('Payment Status')}} </label>
                                                                    <select name="status">
                                                                        <option value="">{{__('Select Status')}}</option>
                                                                        <option value="pending" @if(request()->get('status') == 'pending') selected @endif>{{ __('Pending') }}</option>
                                                                        <option value="complete" @if(request()->get('status') == 'complete') selected @endif>{{  __('Complete') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="wallet_date" class="info-title"> {{__('Created Date Range')}} </label>
                                                                    <input class="form--control flatpickr_input" value="{{ request()->get('wallet_date') }}" name="wallet_date" type="text" placeholder="{{ __('Created Date Range') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--search section end-->

                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard__wallet">
                        <div class="dashboard__wallet__flex">
                            <div class="dashboard__wallet__balance">
                                <div class="dashboard__wallet__balance__icon"><i class="fa-solid fa-dollar"></i></div>
                                <div class="dashboard__wallet__balance__contents">
                                    <p class="dashboard__wallet__balance__title">{{ __('Wallet balance') }}</p>
                                    <h4 class="dashboard__wallet__balance__price mt-2">
                                        @if(empty($balance->balance))
                                            {{ float_amount_with_currency_symbol(0.00) }}
                                        @else
                                            {{ float_amount_with_currency_symbol($balance->balance) }}
                                        @endif
                                    </h4>
                                </div>
                            </div>
                            <div class="dashboard__wallet__btn">
                                <div class="btn-wrapper" data-bs-toggle="modal" data-bs-target="#depositeWallet">
                                    <a href="javascript:void(0)" class="dashboard_table__title__btn btn-bg-1 radius-5">
                                        <i class="fa-solid fa-download"></i> {{ __('Deposit to Wallet') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
                    <h4 class="dashboard_table__title">{{ __('Wallet history') }}</h4>
                    <div class="dashboard_table__main custom--table mt-4">
                        <table>
                            <thead>
                            <tr>
                                <th>{{ __('Payment. ID') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Payment. gateway') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Reference Image') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($wallet_histories as $history)
                            <tr>
                                <td>
                                    <div class="dashboard_table__main__paymentId">#{{ $history->id }} </div>
                                </td>
                                <td>
                                    <div class="dashboard_table__main__amount">
                                        <span class="price">{{ float_amount_with_currency_symbol($history->amount) }} </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="dashboard_table__main__paymentGateway">{{ $history->payment_gateway }}</div>
                                </td>
                                <td>
                                    <div class="dashboard_table__main__priority">
                                        @if($history->payment_status == 'pending')
                                            <a href="javascript:void(0)" class="priorityBtn pending">{{ $history->payment_status }}</a>
                                        @else
                                            <a href="javascript:void(0)" class="priorityBtn completed">{{ $history->payment_status }}</a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="dashboard_table__main__date">{{ $history->created_at->diffForHumans() }} </div>
                                </td>
                                <td>
                                    <div class="dashboard_table__main__reference">
                                        @if(empty($history->manual_payment_image))
                                         <img src="{{ asset('assets/uploads/no-image.png') }}" alt="payment-image">
                                        @else
                                            <img style="width:100px;" src="{{ asset('assets/uploads/manual-payment/'.$history->manual_payment_image) }}" alt="payment-image">
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="blog-pagination margin-top-55">
                        <div class="custom-pagination mt-4 mt-lg-5">
                            {!! $wallet_histories->links() !!}
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Add Balance Modal -->
        <div class="modal fade" id="depositeWallet" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-warning" id="couponModal">{{ __('You can deposit to your wallet from the available payment gateway.') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="custom-form">
                            <form action="{{ route('seller.wallet.deposit') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="single-input">
                                            <label for="depositeWallet" class="label_title">{{ __('Deposit amount') }}</label>
                                            <input type="number" class="form--control radius-10" name="amount" placeholder="{{ __('Enter Deposit Amount') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="confirm-bottom-content">
                                            <div class="confirm-payment payment-border mt-3">
                                                <div class="single-checkbox">
                                                    <div class="checkbox-inlines">
                                                        <label class="checkbox-label" for="check2">
                                                            {!! \App\Helpers\PaymentGatewayRenderHelper::renderPaymentGatewayForForm(false) !!}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                    <button type="submit" class="btn btn-primary">{{ __('Add Balance') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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

            $(document).on('click', '.edit_status_modal', function(e) {
                e.preventDefault();
                alert();
                let order_id = $(this).data('id');
                let status = $(this).data('status');

                $('#order_id').val(order_id);
                $('#status').val(status);
                $('.nice-select').niceSelect('update');
            });

            $('#kineticpay_bank').select2({
                dropdownParent: $('#depositeWallet')
            });

        });
    })(jQuery);
</script>
@endsection