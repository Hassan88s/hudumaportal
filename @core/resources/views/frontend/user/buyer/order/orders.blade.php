
@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Orders') }}
@endsection
@section('style')
    <style>
        .table-td-padding {
            border-collapse: separate;
            border-spacing: 10px 20px;
        }
        .add-to-calendar .icon-google {
            display: block!important;
        }
        .add-to-calendar-checkbox~a:before{
            display:none!important;
        }
    </style>
    <link rel="stylesheet" href="{{asset('assets/common/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/frontend/css/font-awesome.min.css')}}">
@endsection

@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php  $default_lang = get_default_language(); @endphp
    @include('frontend.user.buyer.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">

                <!-- search section start-->
                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard__wallet">
                        <form action="@if(request()->path() == 'buyer/job-orders') {{ route('buyer.job.orders') }} @else {{ route('buyer.orders') }} @endif" method="GET">
                            <div class="dashboard__headerGlobal__flex">
                                <div class="dashboard__headerGlobal__content">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <h4 class="dashboard_table__title">{{ __('Search Order Module') }}</h4> <i class="las la-angle-down search_by_all"></i>
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
                                 @if(request()->get('order_id'))  show
                                 @elseif(request()->get('order_date')) show
                                 @elseif(request()->get('payment_status')) show
                                 @elseif((request()->get('order_status'))) show
                                 @elseif(request()->get('total')) show
                                 @elseif(request()->get('service_title')) show
                                 @elseif(request()->get('seller_name')) show
                                 @elseif(request()->get('job_title')) show
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
                                                                    <label for="order_id" class="info-title"> {{__('Order ID')}} </label>
                                                                    <input class="form--control" name="order_id" value="{{ request()->get('order_id') }}" type="text" placeholder="{{ __('Order ID') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="order_status" class="info-title"> {{__('Order Status')}} </label>
                                                                    <select name="order_status">
                                                                        <option value="">{{__('Select Order Status')}}</option>
                                                                        <option value="pending" @if(request()->get('order_status') == 'pending') selected @endif>{{ __('Pending') }}</option>
                                                                        <option value="1" @if(request()->get('order_status') == 1) selected @endif>{{ __('Active') }}</option>
                                                                        <option value="2" @if(request()->get('order_status') == 2) selected @endif>{{  __('completed') }}</option>
                                                                        <option value="3" @if(request()->get('order_status') == 3) selected @endif>{{  __('Delivered') }}</option>
                                                                        <option value="4" @if(request()->get('order_status') == 4) selected @endif>{{ __('Cancel') }}</option>
                                                                    </select>

                                                                </div>
                                                            </div>
                                                            <div class="col-lg-3 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="order_date" class="info-title"> {{__('Created Date Range')}} </label>
                                                                    <input class="form--control flatpickr_input"  name="order_date" type="text" placeholder="{{ __('Created Date Range') }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row g-4 mt-2">
                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    @if(request()->path() == 'buyer/job-orders')
                                                                        <input type="hidden" value="job_order" name="job_order_request">
                                                                        <label for="job_title" class="info-title"> {{__('Job Title')}} </label>
                                                                        <input class="form--control" name="job_title" value="{{ request()->get('job_title') }}" type="text" placeholder="{{ __('Job Title') }}">
                                                                    @else
                                                                        <label for="service_title" class="info-title"> {{__('Service Title')}} </label>
                                                                        <input class="form--control" name="service_title" value="{{ request()->get('service_title') }}" type="text" placeholder="{{ __('Service Title') }}">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="seller_name" class="info-title"> {{__('Freelancer  Name')}} </label>
                                                                    <input class="form--control" name="seller_name" value="{{ request()->get('seller_name') }}" type="text" placeholder="{{ __('Seller Name') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-3 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="payment_status" class="info-title"> {{__('Payment Status')}} </label>
                                                                    <select name="payment_status">
                                                                        <option value="">{{__('Select Payment Status')}}</option>
                                                                        <option value="complete" @if(request()->get('payment_status') == 'complete') selected @endif>{{ __('Complete') }}</option>
                                                                        <option value="pending" @if(request()->get('payment_status') == 'pending') selected @endif>{{ __('Pending') }}</option>
                                                                    </select>
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

                <!-- order table section start-->
                <div class="dashboard_table__wrapper dashboard_border  padding-20 radius-10 bg-white">
                    @if(request()->path() == 'buyer/job-orders')
                        <h4 class="dashboards-title mb-3">{{ __('All Job Orders') }}</h4>
                    @else
                        <h4 class="dashboards-title mb-3">{{ __('All Service Orders') }}</h4>
                    @endif
                    <x-msg.success/>
                    <x-msg.error/>

                    @if($orders->count() >= 1)
                        <div class="dashboard_table__main custom--table mt-4">
                            <table>
                                <thead>
                                <tr>
                                    
                              
                                    <th>{{ __('Order item') }}</th>

                                </tr>
                                </thead>
                                <tbody>

                                @foreach ($orders as $order)
                                   @if($order->payment_status !='cancalled' )
                                   @if($order->payment_status !=''  )
                                    <tr>
                                        <td>
                                            <div class="dashboard_table__main__order">
                                                <div class="dashboard_table__main__order__flex">
                                                    <div class="dashboard_table__main__order__thumb">
                                                         <a class="load_only_page_this_tab"
                                               href="{{ route('buyer.order.details', $order->id) }}"
                                               style="display:inline-block;">
                                                        @if(request()->path() == 'buyer/job-orders')
                                                            @if(!empty(render_image_markup_by_attachment_id(optional($order->job)->image, '', 'thumb')))
                                                                {!! render_image_markup_by_attachment_id(optional($order->job)->image, '', 'thumb') !!}
                                                            @else
                                                                <img src="{{ asset('assets/frontend/img/no-image-one.jpg') }}" alt="No Image" style="height: 80px">
                                                            @endif
                                                        @else
                                                            @if(!empty(render_image_markup_by_attachment_id(optional($order->service)->image, '', 'thumb')))
                                                                {!! render_image_markup_by_attachment_id(optional($order->service)->image, '', 'thumb') !!}
                                                            @else
                                                                <img src="{{ asset('assets/frontend/img/no-image-one.jpg') }}" alt="No Image" style="height: 80px">
                                                            @endif
                                                        @endif
                                                                 </a>
                                                    </div>
                                                    <div class="dashboard_table__main__order__contents">
                                                        @if(request()->path() == 'buyer/job-orders')
                                                            <h5 class="dashboard_table__main__order__contents__title"> @if($order->order_from_job == 'yes') {{ Str::limit(optional($order->job)->title,60) }} @endif </h5>
                                                        @else
                                                            <h5 class="dashboard_table__main__order__contents__title">{{ optional($order->service)->title }}</h5>
                                                        @endif
                                                        <span class="dashboard_table__main__order__contents__subtitle mt-2">
                                                    <a href="javascript:void(0)" class="dashboard_table__main__order__contents__id"> <strong class="text-dark">{{ __('Order ID:') }}</strong> {{ $order->id }}</a> ,
                                                    <a href="javascript:void(0)" class="dashboard_table__main__order__contents__author"> <strong class="text-dark">{{ __('Freelancer Name:') }}</strong>{{ optional($order->seller)->username }} </a>
                                                </span>
                                                        <span><strong>{{ __('Booking Date:') }}</strong> {{ Carbon\Carbon::parse( strtotime($order->created_at))->format('d/m/y') }}</span>
                                                        <span><strong>{{ __('Booking Date:') }}</strong>  {{ Carbon\Carbon::parse( strtotime($order->created_at))->format('d/m/y') }}</span>
                                                         {{-- Order Status Badge --}}
                                                    @php
                                                        $statusMap = [
                                                            0 => ['Pending', 'warning'],
                                                            1 => ['In Progress', 'info'],
                                                            2 => ['Completed', 'success'],
                                                            3 => ['Delivered', 'primary'],
                                                            4 => ['Cancelled', 'danger'],
                                                            5 => ['Request For Cancel', 'secondary'],
                                                        ];
                                                    @endphp
                                    
                                                    <span class="d-block mt-1">
                                                        <span class="badge bg-{{ $statusMap[$order->status][1] ?? 'secondary' }} fw-semibold px-2 py-1">
                                                            {{ __($statusMap[$order->status][0] ?? 'Unknown') }}
                                                        </span>
                                                    </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        
                                    
                                        <!-- Order status end -->
                                        <td>

                                            <div class="dashboard_recentOrder__item__icon">
                                    <span class="dashboard_recentOrder__item__icon__single" data-bs-toggle="dropdown">
                                      {{--  <i class="fa-solid fa-ellipsis-vertical"></i>--}}
                                       <button class="btn btn-primary">{{ __('Actions') }}</button>
                                        <ul class="dropdown-menu">
                                            <!--review section start -->
                                            @if ($order->status == 2)
                                                <li><a class="dropdown-item review_add_modal"
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#reviewModal"
                                                       data-seller_id="{{ $order->seller_id }}"
                                                       data-service_id="{{ $order->service_id }}"
                                                       data-order_id="{{  $order->id }}"
                                                    ><i class="las la-star text-success"></i> {{ __('Review') }} </a>
                                            </li>
                                            @endif
                                            
                                               @if ($order->location_request == 1)
                                            <li>
                                                   <a href="javascript:void(0);" class="dropdown-item " onclick="getLocation({{ $order->id }})">
                                                    <i class="las la-map-marker"></i> {{ __('Share location with freelancer') }}
                                                </a>

                                                   
                                           </li>
                                             @endif
                                             @if ($order->location_request == 2)
                                            <li>
                                               <a  class="dropdown-item new_tab_open_page" href="{{route('location.show',$order->id)}}">
                                                   <i class="las la-map-marker "></i> {{ __('View location') }} </a>
                                                   
                                           </li>
                                                     @endif
                                            
                                            
                                            <!--review section end -->

                                            <li><a class="dropdown-item load_only_page_this_tab" href="{{ route('buyer.order.details', $order->id) }}"><i class="fa-regular fa-eye text-success"></i>{{ __('View Details') }}</a></li>
                                           @if($order->is_order_online != 1)
                                                @if($order->buyer_id != NULL)
                                                    <li> <a class="dropdown-item load_only_page_this_tab" href="{{ route('buyer.support.ticket.new', $order->id) }}"><i class="las la-ticket-alt text-success"></i> {{ __('New Ticket') }} </a> </li>
                                                @endif
                                            @else
                                                @if(!empty($order->online_order_ticket->id))
                                                    <li><a class="dropdown-item load_only_page_this_tab" href="{{ route('buyer.support.ticket.view', optional($order->online_order_ticket)->id ?? 0) }}">
                                                        <i class="las la-eye-slash text-success"></i> {{ __('View Ticket') }}</a>
                                                </li>
                                                @endif
                                            @endif

                                           <li>
                                               <a  class="dropdown-item new_tab_open_page" href="{{ route('buyer.order.invoice.details',$order->id) }}">
                                                   <i class="las la-print text-danger"></i> {{ __('Print Pdf') }} </a>
                                           </li>
                                           
                                           
                                           

                                            <!-- report section Start -->
                                            @if($order->status != 2)
                                                <li><a class="dropdown-item report_add_modal"
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#reportModal"
                                                       data-seller_id="{{ $order->seller_id }}"
                                                       data-service_id="{{ $order->service_id }}"
                                                       data-order_id="{{  $order->id }}"
                                                    ><i class="lar la-file text-danger"></i> {{ __('Report') }} </a>
                                            </li>
                                            @endif
                                            <!-- report section end -->
                                            @php
                                                if (request()->path() == 'buyer/job-orders'){
                                                    $service_title =  optional($order->job)->title;
                                                }else{
                                                     $service_title =  optional($order->service)->title;
                                                }
                                                $details = __('Order Successfully Created');
                                                $address =  optional($order->buyer)->address;
                                            @endphp
                                             <li>
                                                 <a class="dropdown-item new_tab_open_page" href="{{ get_google_calender($service_title,$order->date,$details, $address) }}" target="_blank">
                                                     <i class="las la-calendar text-danger"></i> {{ __('Add To Google Calendar') }} </a>
                                             </li>
                                        </ul>
                                    </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                     @endif
                                @endforeach

                                </tbody>
                            </table>
                        </div>

                        <div class="blog-pagination margin-top-55">
                            <div class="custom-pagination mt-4 mt-lg-5">
                                {!! $orders->links() !!}
                            </div>
                        </div>

                    @else
                        <div class="chat_wrapper__details__inner__chat__contents mt-3">
                            <p class="no_data_found_for_buyer_seller_panel">
                                {{ __('No Orders found')}}
                            </p>
                        </div>
                    @endif
                </div>
                <!-- order table section end-->
            </div>
        </div>
    </div>

    <!--Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Review') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="custom-form">
                        <form action="{{ route('service.review.from.dashboard') }}" method="post">
                            @csrf
                            <input type="hidden" id="rating" name="rating" class="form-control form-control-sm">
                            <input type="hidden" id="seller_id" name="seller_id" class="form-control form-control-sm">
                            <input type="hidden" id="service_id" name="service_id" class="form-control form-control-sm">
                            <input type="hidden" id="order_id" name="order_id" class="form-control form-control-sm">
                            <div class="row g-4">
                                <div class="col-12">

                                    <div class="single-commetns" style="font-size: 1.1rem;">
                                        <label class="comment-label label_title"> {{ __('Ratings*') }} </label>
                                        <div id="review"></div>
                                    </div>

                                    <div class="single-input">
                                        <label for="ticketTitle" class="label_title">{{ __('Comments') }}</label>
                                        <textarea id="message" name="message" cols="20" rows="4"  class="form--control radius-10 textarea-input" placeholder="{{ __('Post Comments') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('Send Review') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Report Us') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="custom-form">
                        <form action="{{ route('buyer.order.report') }}" method="post">
                            @csrf
                            <input type="hidden" id="seller_id" name="seller_id" class="form-control form-control-sm">
                            <input type="hidden" id="service_id" name="service_id" class="form-control form-control-sm">
                            <input type="hidden" id="order_id" name="order_id" class="form-control form-control-sm">

                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="single-input">
                                        <label for="ticketTitle" class="label_title">{{ __('Report Here') }}</label>
                                        <textarea name="report" cols="30" rows="4"  class="form--control radius-10 textarea-input" placeholder="{{ __('Report Here') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Cancel')  }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('Send Report') }}</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Modal -->
    <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('Request For Modification') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="custom-form">
                        <form action="{{ route('buyer.order.complete.request.decline') }}" method="post">
                            @csrf
                            <input type="hidden" id="seller_id" name="seller_id" class="form-control form-control-sm">
                            <input type="hidden" id="service_id" name="service_id" class="form-control form-control-sm">
                            <input type="hidden" id="order_id" name="order_id" class="form-control form-control-sm">

                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="single-input">
                                        <label for="ticketTitle" class="label_title">{{ __('Modification') }}</label>
                                        <p class="text-info">{{ __('please descibe the  Modification in a short details.') }}</p>
                                        <textarea name="decline_reason" cols="30" rows="4"  class="form--control radius-10 form--message textarea-input" placeholder="{{ __('Enter Modification Details') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Cancel')  }}</button>
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('assets/backend/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/rating.js') }}"></script>
     <script>
    function getLocation(orderId) {
        if (navigator.geolocation) {
           
            navigator.geolocation.getCurrentPosition(function(position) {
                let lat = position.coords.latitude;
                let lng = position.coords.longitude;

                // Send to server
                fetch("{{ route('location.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        latitude: lat,
                        longitude: lng
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert("Location shared successfully!");
                     window.location.reload(); 
                })
                .catch(error => {
                    alert("Error sharing location.");
                    console.error(error);
                });
            }, function(error) {
                alert("Location access denied or unavailable.");
            });
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }
</script>
           
    <script>
        (function($){
            "use strict";
            $(document).ready(function(){

                // open new  tab
                $('.new_tab_open_page').click(function (e) {
                    e.preventDefault();
                    var url = $(this).attr('href');
                    window.open(url, '_blank');
                });

                // load_only_page_this_tab
                $('.load_only_page_this_tab').click(function () {
                        window.location = $(this).attr('href');
                });


                // Order approve after send review
                var openReviewModal = "{{\Illuminate\Support\Facades\Session::get('open_review_modal')}}";
                var CompleteOrderId = "{{\Illuminate\Support\Facades\Session::get('CompleteOrderId')}}";
                var seller_id = "{{\Illuminate\Support\Facades\Session::get('seller_id')}}";
                var service_id = "{{\Illuminate\Support\Facades\Session::get('service_id')}}";
                if(openReviewModal === 'yes'){
                    $('.review_add_modal[data-order_id="'+CompleteOrderId+'"]').trigger("click");
                    // $('.review_add_modal[data-order_id="'+CompleteOrderId+'"]').dispatchEvent(new MouseEvent("click"))
                    let myModal = new bootstrap.Modal(document.getElementById('reviewModal'), {
                        keyboard: false
                    });
                    $('#reviewModal input[name="seller_id"]').val(seller_id);
                    $('#reviewModal input[name="service_id"]').val(service_id);
                    $('#reviewModal input[name="order_id"]').val(CompleteOrderId);
                    myModal.show();
                }


                // date range
                $('.flatpickr_input').flatpickr({
                    altFormat: "invisible",
                    altInput: false,
                    mode: "range",
                });


                $(document).on('click','.swal_status_change',function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: '{{__("Are you sure to change status complete? Once you done you can not revert this !!")}}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{__('Yes, complete it!')}}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).next().find('.swal_form_submit_btn').trigger('click');
                        }
                    });
                });
                //order cancel status
                $(document).on('click','.swal_status_change_order_cancel',function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: '{{__("Are you sure to cancel the order")}}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{__('Yes, cancel it!')}}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).next().find('.swal_form_submit_btn_cancel_order').trigger('click');
                        }
                    });
                });

                // buyer to seller report
                $(document).on('click', '.report_add_modal', function () {
                    let el = $(this);
                    let seller_id = el.data('seller_id');
                    let service_id = el.data('service_id');
                    let order_id = el.data('order_id');
                    let form = $('#reportModal');
                    form.find('#seller_id').val(seller_id);
                    form.find('#service_id').val(service_id);
                    form.find('#order_id').val(order_id);
                });

                // review
                $(document).on('click', '.review_add_modal', function () {
                    let el = $(this);
                    let seller_id = el.data('seller_id');
                    let service_id = el.data('service_id');
                    let order_id = el.data('order_id');
                    let form = $('#reviewModal');
                    form.find('#seller_id').val(seller_id);
                    form.find('#service_id').val(service_id);
                    form.find('#order_id').val(order_id);
                });

                $("#review").rating({
                    "value": 5,
                    "click": function (e) {
                        $("#rating").val(e.stars);
                    }
                });

                //decline request
                $(document).on('click', '.decline_add_modal', function () {
                    let el = $(this);
                    let seller_id = el.data('seller_id');
                    let service_id = el.data('service_id');
                    let order_id = el.data('order_id');
                    let form = $('#declineModal');
                    form.find('#seller_id').val(seller_id);
                    form.find('#service_id').val(service_id);
                    form.find('#order_id').val(order_id);
                });


            });
        })(jQuery);
    </script>


@endsection


