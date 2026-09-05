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
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">

        {{-- ================================================== --}}
        {{-- BUYER TOP ACTION BAR (MATCH SELLER LOOK) --}}
        {{-- ================================================== --}}
        <div class="mb-4 d-flex flex-wrap align-items-center gap-2 justify-content-start action-bar">
            <div class="p-2 border rounded bg-light d-inline-flex align-items-center flex-wrap gap-1">

                {{-- ================= OFFER TIME END / LATE OFFER ACTIONS ================= --}}
                @if($order_details->offer_time_end != NULL)

                    {{-- (Optional) label + live timer span (kept for compatibility) --}}
                    <span class="badge bg-info-subtle text-info fw-semibold">
                        <i class="fa-solid fa-hourglass-half me-1"></i>{{ __('Order Time:') }}
                    </span>
                    <span id="race{{ $order_details->id }}" class="fw-bold text-primary"></span>

                    @php
                        $start_datetime = new DateTime($order_details->offer_time_end);
                        $current_date   = new DateTime("now");
                    @endphp

                    @if($order_details->status != 5)

                        @if($current_date > $start_datetime)
                            @if ($order_details->status != 4)

                                {{-- Accept time extension --}}
                                @if($order_details->time_extension_request == '1')
                                    <a href="{{ route('buyer.accept.timeextesion',$order_details->id) }}"
                                       class="btn btn-success btn-sm">
                                        {{ __('Accept') }} {{ $order_details->time_extension_days }} {{ __('Days time extension') }}
                                    </a>
                                @endif

                                {{-- Decline late offer --}}
                                <a href="{{ route('buyer.cancel.lateoffer',$order_details->id) }}"
                                   class="btn btn-outline-warning btn-sm">
                                    {{ __('Decline') }}
                                </a>

                            @endif
                        @endif

                    @else
                        {{-- Request For cancel --}}
                        <div class="dashboard_table__main__priority">
                            <a href="javascript:void(0)" class="priorityBtn cancel">
                                {{ __('Request For cancel') }}
                            </a>
                        </div>
                    @endif

                    <span class="vr mx-1"></span>

                @else
                    {{-- ================= NO offer_time_end: after 4 hours decline ================= --}}
                    @php
                        $createdAt = \Carbon\Carbon::parse($order_details->created_at);
                        $now = \Carbon\Carbon::now();
                    @endphp

                    @if ($order_details->status == '0' && $createdAt->diffInHours($now) >= 4)
                        <a href="{{ route('buyer.cancel.pending.lateoffer', $order_details->id) }}"
                           class="btn btn-outline-warning btn-sm">
                            {{ __('Decline') }}
                        </a>
                        <span class="vr mx-1"></span>
                    @endif
                @endif
                {{-- ================= END OFFER TIME END ================= --}}


                {{-- ================= PARTIAL PAYMENT ================= --}}
                @if( $order_details->partialPayment == 1
                    && $order_details->order_complete_request != 2
                    && $order_details->status != 4)

                    @php
                        $partialPaymentDetails = \App\PartialPayment::where('order_id', $order_details->id)->first();
                    @endphp

                    <button type="button"
                            class="btn btn-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#partialPaymentModal">
                        {{ __('Freelancer Requesting') }}
                        {{ $partialPaymentDetails->amount ?? '' }} TsH
                        {{ __('Partial Amount') }}
                    </button>

                    <a href="{{ route('buyer.cancel.paritalamount',$order_details->id) }}"
                       class="btn btn-outline-warning btn-sm">
                        {{ __('Decline') }}
                    </a>

                    <span class="vr mx-1"></span>

                    {{-- Modal (single order page so static id is OK) --}}
                    <div class="modal fade" id="partialPaymentModal" tabindex="-1" aria-labelledby="partialPaymentModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title" id="partialPaymentModalLabel">{{ __('Confirm Partial Payment') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    {{ __('This is a partial payment request of') }}
                                    <strong>{{ $partialPaymentDetails->amount ?? '' }} TsH</strong>.
                                    <br><br>
                                    <span class="text-danger">
                                        <strong>{{ __('Note:') }}</strong> {{ __('Partial amount is non-refundable.') }}
                                    </span>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                    <a href="{{ route('buyer.accept.paritalamount', $order_details->id) }}" class="btn btn-success btn-sm">
                                        {{ __('Accept & Pay') }}
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>

                @endif
                {{-- ================= END PARTIAL PAYMENT ================= --}}


                {{-- ================= ORDER COMPLETE REQUEST ================= --}}
                @if ($order_details->order_complete_request == 0)
                    <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                        {{ __('No Request Created') }}
                    </span>
                @endif

                @if ($order_details->order_complete_request == 1)
                    <span class="badge bg-info-subtle text-info fw-semibold">
                        {{ __('Complete Request') }}
                    </span>

                    <span>
                        <x-order-complete-request-approve
                            :url="route('buyer.order.complete.request.approve',$order_details->id)"
                        />
                    </span>

                    <span class="btn btn-outline-warning btn-sm">
                        <a href="#"
                           data-bs-toggle="modal"
                           data-bs-target="#declineModal"
                           data-seller_id="{{ $order_details->seller_id }}"
                           data-service_id="{{ $order_details->service_id }}"
                           data-order_id="{{ $order_details->id }}"
                           class="decline_add_modal">
                            {{ __('Request Modifictions') }}
                        </a>
                    </span>
                @endif

                @if ($order_details->order_complete_request == 2)
                    @php
                        $review_count = \App\Review::where('order_id',$order_details->id)
                            ->where('type', 1)
                            ->where('buyer_id',Auth::guard('web')->user()->id)
                            ->get();
                    @endphp

                   

                    {{-- Review section --}}
                    @if($review_count->count() == 0)
                        @if ($order_details->status == 2)
                            <a class="btn btn-outline-success btn-sm review_add_modal"
                               href="#"
                               data-bs-toggle="modal"
                               data-bs-target="#reviewModal"
                               data-seller_id="{{ $order_details->seller_id }}"
                               data-service_id="{{ $order_details->service_id }}"
                               data-order_id="{{ $order_details->id }}">
                                <i class="las la-star me-1 text-success"></i>{{ __('Review') }}
                            </a>
                        @endif
                    @else
                        <span class="badge bg-warning-subtle text-warning fw-semibold">
                            {{ __('Reviewed') }}
                        </span>
                    @endif
                @endif

                @if ($order_details->order_complete_request == 3)
                    @if(optional($order_details->completedeclinehistory)->count() >= 1)
                        <span class="badge bg-danger-subtle text-danger fw-semibold">
                            {{ __('Requested Modifications') }}
                        </span>

                        <span class="btn btn-outline-warning btn-sm">
                            <a href="{{ route('buyer.order.request.decline.history',$order_details->id) }}">
                                {{ __('View History') }}
                            </a>
                        </span>
                    @endif
                @endif
                {{-- ================= END ORDER COMPLETE REQUEST ================= --}}

                <span class="vr mx-1"></span>

                {{-- ================= ORDER STATUS ================= --}}
                @if ($order_details->status == 0)
                    <div class="dashboard_table__main__priority">
                        <a href="javascript:void(0)" class="priorityBtn pending">{{ __('Pending') }}</a>
                    </div>
                @endif

                @if ($order_details->status == 1)
                    <div class="dashboard_table__main__priority">
                        <a href="javascript:void(0)" class="priorityBtn active">{{ __('In Progress') }}</a>
                    </div>
                @endif

                @if ($order_details->status == 2)
                    <div class="dashboard_table__main__priority">
                        <a href="javascript:void(0)" class="priorityBtn completed">{{ __('Completed') }}</a>
                    </div>
                @endif

                @if ($order_details->status == 3)
                    <div class="dashboard_table__main__priority">
                        <a href="javascript:void(0)" class="priorityBtn delivered">{{ __('Delivered') }}</a>
                    </div>
                @endif

                @if ($order_details->status == 4)
                    <div class="dashboard_table__main__priority">
                        <a href="javascript:void(0)" class="priorityBtn cancel">{{ __('Cancel') }}</a>
                    </div>
                @endif
                {{-- ================= END ORDER STATUS ================= --}}

            </div>
        </div>

        {{-- Your remaining order details sections continue below... --}}

    </div>
</div>



<div class="mb-4">
    <h5 class="fw-bold mb-3">{{ __('Freelancer Details') }}</h5>

    <div class="row g-2">
        <div class="col-md-6">
            <span class="fw-semibold text-secondary">{{ __('Name') }}:</span>
            <span class="fw-bold text-dark">{{ optional($order_details->seller)->username }}</span>
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
@include('frontend.user.buyer.order.partials.remaing-service-orders-actions-js-code')

