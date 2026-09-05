<style>
    /* ===== Mobile Action Bar Fix ===== */
@media (max-width: 767px) {

    .action-bar {
        gap: 8px;
    }

    .action-bar .btn {
        flex: 1 1 calc(50% - 6px);
        text-align: center;
        font-size: 13px;
        padding: 10px 8px;
    }

    /* Status badge goes full width */
    .action-bar .badge {
        flex: 1 1 100%;
        text-align: center;
        margin-top: 6px;
        font-size: 13px;
        padding: 8px 0;
    }

    /* Push badge below buttons */
    .action-bar .ms-auto {
        margin-left: 0 !important;
    }
}

</style>
<div class="card shadow-sm border-0 mb-4 ">
    <div class="card-body">

{{-- ================================================== --}}
{{-- TOP ACTION BAR (ONE BOX, ONE LINE, MINIMAL SPACE) --}}
{{-- ================================================== --}}
<div class="mb-4 d-flex flex-wrap align-items-center gap-2 justify-content-start action-bar">
    <div class="p-2 border rounded bg-light d-inline-flex align-items-center flex-wrap gap-1">

        {{-- ================= PRIMARY ACTIONS (Approve / Decline) ================= --}}
        @if($order_details->status == 0)
            <a href="{{ route('seller.approve.services', $order_details->id) }}"
               class="btn btn-success btn-sm">
                <i class="fa-solid fa-check me-1"></i>{{ __('Approve') }}
            </a>

            <a href="{{ route('seller.cancel.services', $order_details->id) }}"
               class="btn btn-outline-danger btn-sm">
                <i class="fa-solid fa-xmark me-1"></i>{{ __('Decline') }}
            </a>

            <span class="vr mx-1"></span>
        @endif


        {{-- ================= DELIVERY TIMER (Label + Running Time) ================= --}}
        @if($order_details->offer_time_end)
            <span class="badge bg-info-subtle text-info fw-semibold">
                <i class="fa-solid fa-hourglass-half me-1"></i>{{ __('Order Time Remaining:') }}
            </span>

            <span id="race{{ $order_details->id }}" class="fw-bold text-primary"></span>

            @php
                $start_datetime = new DateTime($order_details->offer_time_end);
                $current_date = new DateTime("now");
            @endphp

            {{-- When time finished (same logic as your code) --}}
            @if($current_date > $start_datetime && $order_details->status != 4)

                @if($order_details->time_extension_request != '1')
                    <span class="badge bg-danger-subtle text-danger fw-semibold">
                        {{ __('Delivery Time Finished') }}
                    </span>

                    <a href="#0"
                       class="btn btn-primary btn-sm time_extension"
                       data-bs-toggle="modal"
                       data-bs-target="#timeextension"
                       data-id="{{ $order_details->id }}"
                       data-status="{{ $order_details->status }}">
                        <i class="fa-solid fa-clock me-1"></i>{{ __('Time Extension') }}
                    </a>
                @elseif($order_details->time_extension_request == '2')   
                 <span class="badge bg-warning-subtle text-danger fw-semibold">
                        {{ __(' Time extension Request  decline') }}
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning fw-semibold">
                        {{ __('Waiting for time extension approval') }}
                    </span>
                @endif

            @endif

            <span class="vr mx-1"></span>
        @endif


        {{-- ================= ORDER PROGRESS / PARTIAL PAYMENT / COMPLETE REQUEST ================= --}}
        @if($order_details->status != 4 && $order_details->offer_time_end)

            @php
                $review_count = \App\Review::where('order_id',$order_details->id)
                    ->where('type',1)
                    ->where('seller_id',Auth::guard('web')->user()->id)
                    ->get();
            @endphp

            @if(in_array($order_details->order_complete_request,[0,1]) && $order_details->payment_status != 'pending')

                @if($order_details->order_complete_request == 0)

                    @php
                        $partialPaymentDetails = \App\PartialPayment::where('order_id',$order_details->id)->first();
                    @endphp

                    {{-- Partial Payment (same logic) --}}
                    @if($order_details->partialPayment == 0)
                        <a href="#0"
                           class="btn btn-outline-success btn-sm parital_payment_modal"
                           data-bs-toggle="modal"
                           data-bs-target="#paritalpaymentModal"
                           data-id="{{ $order_details->id }}"
                           data-status="{{ $order_details->status }}">
                            <i class="fa-solid fa-coins me-1"></i>{{ __('Partial Payment') }}
                        </a>

                    @elseif($order_details->partialPayment == 2)
                        <span class="badge bg-success fw-semibold">
                            {{ $partialPaymentDetails->amount }} TsH – {{ __('Partial Payment Complete') }}
                        </span>

                    @elseif($order_details->partialPayment == 3)
                        <span class="badge bg-danger fw-semibold">
                            {{ __('Partial Payment Request Declined') }}
                        </span>

                    @else
                        <span class="badge bg-warning text-dark fw-semibold">
                            {{ __('Partial Payment Request Pending') }}
                        </span>
                    @endif

                    {{-- Complete Request (same logic) --}}
                    <a href="#0"
                       class="btn btn-outline-primary btn-sm edit_status_modal"
                       data-bs-toggle="modal"
                       data-bs-target="#editStatusModal"
                       data-id="{{ $order_details->id }}"
                       data-status="{{ $order_details->status }}">
                        <i class="fa-solid fa-flag-checkered me-1"></i>{{ __('Complete Request') }}
                    </a>

                @else
                    <span class="badge bg-warning text-dark fw-semibold">
                        {{ __('Request Pending') }}
                    </span>
                    
                @endif

            @endif

            {{-- Completed (same logic) --}}
            @if($order_details->order_complete_request == 2)
                <span class="badge bg-success fw-semibold">
                    <i class="fa-solid fa-check-circle me-1"></i>{{ __('Completed') }}
                </span>
            @endif

            {{-- Declined & Retry (same logic) --}}
            @if($order_details->order_complete_request == 3)
                <a href="#0"
                   class="btn btn-outline-primary btn-sm edit_status_modal"
                   data-bs-toggle="modal"
                   data-bs-target="#editStatusModal"
                   data-id="{{ $order_details->id }}"
                   data-status="{{ $order_details->status }}">
                    {{ __('Create Complete Request Again') }}
                </a>

                @if(optional($order_details->completedeclinehistory)->count() >= 1)
                    <a href="{{ route('seller.order.request.decline.history',$order_details->id) }}"
                       class="btn btn-warning btn-sm">
                        {{ __('View History') }}
                    </a>
                @endif
            @endif

            <span class="vr mx-1"></span>
        @endif


        {{-- ================= CLIENT DESCRIPTION DATA (DO NOT REMOVE) ================= --}}
        @php $fullText = ''; @endphp

        @if($order_details->job_post_id)
            @php
                $value = \Modules\JobPost\Entities\BuyerJob::find($order_details->job_post_id);
                $fullText = strip_tags($value->description ?? '');
            @endphp
        @elseif($order_details->Custom_offer_id)
            @php
                $value = \App\CustomOffer::find($order_details->Custom_offer_id);
                $fullText = strip_tags($value->description ?? '');
            @endphp
        @elseif($order_details->service_id)
            @php
                $fullText = strip_tags($order_details->order_note ?? '');
            @endphp
        @endif


        {{-- ================= EXTRA SERVICES (ONLY WHEN ACTIVE) ================= --}}
        @if($order_details->status == 1 && empty($order_details->job_post_id))
            <a href="#0"
               class="btn btn-secondary btn-sm extra_submit_request_btn"
               data-bs-toggle="modal"
               data-bs-target="#extraServiceRequest"
               data-id="{{ $order_details->id }}">
                <i class="fa-solid fa-plus me-1"></i>{{ __('Extra Services') }}
            </a>
        @endif

        {{-- ================= READ DESCRIPTION ================= --}}
        @if(strlen($fullText) > 5)
            <button type="button"
                    class="btn btn-outline-primary btn-sm ms-1"
                    data-bs-toggle="modal"
                    data-bs-target="#readMoreModal"
                    onclick="setModalContent(`{!! addslashes($fullText) !!}`)">
                {{ __('Read full description') }}
            </button>
        @endif

    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3">{{ __('Client Details') }}</h5>

    <div class="row g-2">
        <div class="col-md-6">
            <span class="fw-semibold text-secondary">{{ __('Name') }}:</span>
            <span class="fw-bold text-dark">{{ $buyerName }}</span>
        </div>

        @if($order_details->is_order_online != 1)
            <div class="col-md-6">
                <span class="fw-semibold text-secondary">{{ __('Address') }}:</span>
                <span class="fw-bold text-dark">{{ $order_details->address }}</span>
            </div>

            <div class="col-md-4">
                <span class="fw-semibold text-secondary">{{ __('City') }}:</span>
                <span class="fw-bold text-dark">
                    {{ optional($order_details->service_city)->service_city }}
                </span>
            </div>

            <div class="col-md-4">
                <span class="fw-semibold text-secondary">{{ __('Area') }}:</span>
                <span class="fw-bold text-dark">
                    {{ optional($order_details->service_area)->service_area }}
                </span>
            </div>

            <div class="col-md-4">
                <span class="fw-semibold text-secondary">{{ __('Post Code') }}:</span>
                <span class="fw-bold text-dark">{{ $order_details->post_code }}</span>
            </div>

            <div class="col-md-6">
                <span class="fw-semibold text-secondary">{{ __('Country') }}:</span>
                <span class="fw-bold text-dark">
                    {{ optional($order_details->service_country)->country }}
                </span>
            </div>
        @endif
    </div>
</div>
@if($order_details->is_order_online != 1)
<div class="mb-4">
    <h5 class="fw-bold mb-3">{{ __('Date & Schedule') }}</h5>

    <div class="d-flex flex-wrap gap-5 align-items-start">

        <!-- Booking Date & Time -->
        <div>
            <span class="d-block fw-semibold text-secondary mb-1">
                {{ __('Order Booking Date & Time') }}
            </span>

            <span class="fw-bold text-dark">
                <i class="fa-regular fa-calendar me-1"></i>
                @if($order_details->date === 'No Date Created')
                    {{ __('No Date Created') }}
                @else
                    {{ \Carbon\Carbon::parse($order_details->date)->format('d M Y') }}
                @endif
                <span class="mx-1">•</span>
                <i class="fa-regular fa-clock me-1"></i>
                {{ __($order_details->schedule) }}
            </span>
        </div>

        <!-- Order Type -->
        <div>
            <span class="d-block fw-semibold text-secondary mb-1">
                {{ __('Order Type') }}
            </span>

            @if($order_details->is_order_online == 1)
                <span class="badge bg-success fw-semibold px-3 py-2">
                    <i class="fa-solid fa-globe me-1"></i>
                    {{ __('Online') }}
                </span>
            @else
                <span class="badge bg-secondary fw-semibold px-3 py-2">
                    <i class="fa-solid fa-location-dot me-1"></i>
                    {{ __('Offline') }}
                </span>
            @endif
        </div>

    </div>
</div>
@endif

<div class="mb-4">
    <h5 class="fw-bold mb-3">{{ __('Amount Details') }}</h5>

    <div class="row g-2">
        <div class="col-md-4">
            <span class="fw-semibold text-secondary">{{ __('Package Fee') }}:</span>
            <span class="fw-bold">{{ float_amount_with_currency_symbol($order_details->package_fee) }}</span>
        </div>

        <div class="col-md-4">
            <span class="fw-semibold text-secondary">{{ __('Extra Service') }}:</span>
            <span class="fw-bold">{{ float_amount_with_currency_symbol($order_details->extra_service) }}</span>
        </div>

        <div class="col-md-4">
            <span class="fw-semibold text-secondary">{{ __('Sub Total') }}:</span>
            <span class="fw-bold">{{ float_amount_with_currency_symbol($order_details->sub_total) }}</span>
        </div>

        <div class="col-md-4">
            <span class="fw-semibold text-secondary">{{ __('Tax') }}:</span>
            <span class="fw-bold">{{ float_amount_with_currency_symbol($order_details->tax) }}</span>
        </div>
        
        <div class="col-md-4">
            <span class="fw-semibold text-secondary">{{ __('Admin Charge') }}:</span>
            <span class="fw-bold">{{ float_amount_with_currency_symbol($order_details->commission_amount) }}</span>
        </div>

        @if(!empty($order_details->coupon_amount))
        <div class="col-md-4">
            <span class="fw-semibold text-secondary">{{ __('Coupon') }}:</span>
            <span class="fw-bold text-danger">
                -{{ float_amount_with_currency_symbol($order_details->coupon_amount) }}
            </span>
        </div>
        @endif

        <div class="col-md-4">
            <span class="fw-semibold text-secondary">{{ __('Total') }}:</span>
            <span class="fw-bold fs-5 text-dark">
                {{ float_amount_with_currency_symbol($order_details->total) }}
            </span>
        </div>
    </div>
</div>
<div class="mb-4">
    <h5 class="fw-bold mb-3">{{ __('Payment Details') }}</h5>

    <div class="d-flex flex-wrap gap-4">
        <div>
            <span class="fw-semibold text-secondary d-block">{{ __('Gateway') }}</span>
            <span class="fw-bold">
                {{ __(ucwords(str_replace('_',' ',$order_details->payment_gateway))) }}
            </span>
        </div>

        <div>
            <span class="fw-semibold text-secondary d-block">{{ __('Status') }}</span>
            @if($order_details->payment_status === 'complete')
                <span class="badge bg-success fw-semibold px-3 py-2">{{ __('Complete') }}</span>
            @elseif($order_details->payment_status === 'return')
                <span class="badge bg-danger fw-semibold px-3 py-2">{{ __('Refunded') }}</span>
            @else
                <span class="badge bg-warning text-dark fw-semibold px-3 py-2">{{ __('Pending') }}</span>
            @endif
        </div>
        
        @if(!empty($partialdetials))
    <div>
        <span class="fw-semibold text-secondary d-block">{{ __('Partial Payment') }}</span>

        <div class="fw-bold">
            {{-- amount --}}
            <span>{{ number_format($partialdetials->amount, 2) }} TSh</span>

            {{-- percentage (optional) --}}
            @if(!empty($partialdetials->percentage))
                <span class="text-secondary">({{ rtrim(rtrim(number_format($partialdetials->percentage, 2), '0'), '.') }}%)</span>
            @endif
        </div>

        {{-- status (optional) --}}
        @if(!empty($partialdetials->status))
            <div class="mt-1">
                @if($partialdetials->status === 'complete')
                    <span class="badge bg-success fw-semibold px-3 py-2">{{ __('Partial Complete') }}</span>
                @elseif($partialdetials->status === 'pending')
                    <span class="badge bg-warning text-dark fw-semibold px-3 py-2">{{ __('Partial Pending') }}</span>
                @else
                    <span class="badge bg-secondary fw-semibold px-3 py-2">{{ __(ucwords(str_replace('_',' ',$partialdetials->status))) }}</span>
                @endif
            </div>
        @endif
    </div>
@endif
    </div>
</div>
<div class="mb-2">
    <h5 class="fw-bold mb-3">{{ __('Order Details') }}</h5>

    <div class="d-flex flex-wrap gap-4">
        <div>
            <span class="fw-semibold text-secondary d-block">{{ __('Order ID') }}</span>
            <span class="fw-bold">#{{ $order_details->id }}</span>
        </div>

        <div>
            <span class="fw-semibold text-secondary d-block">{{ __('Order Status') }}</span>
        
            @php
                $statusMap = [
                    0 => ['Pending', 'warning'],
                    1 => ['In Progress', 'info'],
                    2 => ['Completed', 'success'],
                    3 => ['Delivered', 'primary'],
                    4 => ['Cancel', 'danger'],
                    5 => ['Request For Cancel', 'danger'],
                ];
        
                $status = $order_details->status;
            @endphp
        
            <span class="badge bg-{{ $statusMap[$status][1] ?? 'secondary' }} fw-semibold px-3 py-2">
                {{ __($statusMap[$status][0] ?? 'Unknown') }}
            </span>
        </div>

    </div>
</div>


<hr>



    </div>
</div>
{{-- Remaining service modal and js code --}} 
@include('frontend.user.seller.order.partials.remaing-service-orders-actions-js-code')

