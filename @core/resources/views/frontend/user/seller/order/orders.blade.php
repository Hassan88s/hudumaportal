@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Orders') }}
@endsection
@section('style')
    <x-media.css/>
    <style>
    
        .table-td-padding {
            border-collapse: separate;
            border-spacing: 10px 20px;
        }
        .dash-icon.color-1{
            background: rgba(255,179,7,.1);
            color: #ffb307;
            text-align: center;
            border-radius: 5px;
            font-size: 14px;
            @if(request()->path() == 'seller/job-orders')padding: 8px;  @else padding: 6px; margin-bottom: -1px; @endif
}    </style>
<style>
    .read-more-btn {
        background: none;
        border: none;
        color: blue;
        cursor: pointer;
        padding: 0;
        font-size: 0.9em;
    }
    
</style>


    <link rel="stylesheet" href="{{asset('assets/common/css/themify-icons.css')}}">
    <link rel="stylesheet" href="{{asset('assets/frontend/css/font-awesome.min.css')}}">
@endsection

@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php $default_lang = get_default_language(); @endphp
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <!-- search section start-->
                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard__wallet">
                        <form action="@if(request()->path() == 'seller/job-orders')  {{ route('seller.job.orders') }} @else {{ route('seller.orders') }}  @endif" method="GET">
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
                                                                    <input class="form--control flatpickr_input"  name="order_date" type="text" value="{{ request()->get('order_date') }}" placeholder="{{ __('Created Date Range') }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row g-4 mt-2">
                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    @if(request()->path() == 'seller/job-orders')
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
                                                                    <label for="buyer_name" class="info-title"> {{__('Buyer Name')}} </label>
                                                                    <input class="form--control" name="buyer_name" value="{{ request()->get('buyer_name') }}" type="text" placeholder="{{ __('Buyer Name') }}">
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
                    @if(request()->path() == 'seller/job-orders')
                        <h4 class="dashboards-title mb-3">{{ __('All Job Orders') }}</h4>
                    @else
                        <h4 class="dashboards-title mb-3">{{ __('All Service Orders') }}</h4>
                    @endif
                    <!-- Order count section start -->

                    <div class="paymentGateway_add mt-3">
                        <div class="paymentGateway_add__item_seller_order custom_radio__single_seller_order radius-10">
                            <label for="Active" class="paymentGateway_add__item__img">{{ __('Pending') }} <strong class="numbers">{{ $pending_orders->count() }}</strong></label>
                        </div>
                        <div class="paymentGateway_add__item_seller_order custom_radio__single_seller_order radius-10">
                            <label for="Active" class="paymentGateway_add__item__img">{{ __('Active') }} <strong class="numbers">{{ $active_orders->count() }}</strong></label>
                        </div>
                        <div class="paymentGateway_add__item_seller_order custom_radio__single_seller_order radius-10">
                            <label for="Delivered" class="paymentGateway_add__item__img">{{ __('Delivered') }} <strong class="numbers">{{ $complete_orders->count() }}</strong></label>
                        </div>
                        <div class="paymentGateway_add__item_seller_order custom_radio__single_seller_order radius-10">
                            <label for="Completed" class="paymentGateway_add__item__img">{{ __('Completed') }} <strong class="numbers">{{ $deliver_orders->count() }}</strong></label>
                        </div>
                        <div class="paymentGateway_add__item_seller_order custom_radio__single_seller_order radius-10">
                            <label for="Cancelled" class="paymentGateway_add__item__img">{{ __('Cancelled') }} <strong class="numbers">{{ $cancel_orders->count() }}</strong></label>
                        </div>
                        <div class="paymentGateway_add__item_seller_order custom_radio__single_seller_order radius-10">
                            <label for="All" class="paymentGateway_add__item__img">{{ __('All') }} <strong class="numbers">{{ $orders->count() }}</strong> </label>
                        </div>
                    </div>
                    <!-- Order count section end -->
                    <div class="mt-3">
                        <x-msg.success/>
                        <x-msg.error/>
                    </div>
                    @if($all_orders->count() >= 1)
                        <div class="dashboard_table__main custom--table mt-4">
                            <table>
                                <thead>
                                <tr>
                                    <th>{{ __('Order item') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach ($all_orders as $order)
                                  @if($order->payment_status !='' )
                                    <tr>
                                        <td>
                                            <div class="dashboard_table__main__order">
                                                <div class="dashboard_table__main__order__flex">
                                                    <div class="dashboard_table__main__order__thumb">
                                                         <a class="load_only_page_this_tab"
                                                   href="{{ route('seller.order.details', $order->id) }}"
                                                   style="display:inline-block;">
                                                        @if(request()->path() == 'seller/job-orders')
                                                            @if(!empty(render_image_markup_by_attachment_id(optional($order->job)->image, '', 'thumb')))
                                                                {!! render_image_markup_by_attachment_id(optional($order->job)->image, '', 'thumb') !!}
                                                            @else
                                                                <img src="{{ asset('assets/frontend/img/no-image-one.jpg') }}"  alt="No Image" style="height: 77px">
                                                            @endif
                                                        @else
                                                            @if(!empty(render_image_markup_by_attachment_id(optional($order->service)->image, '', 'thumb')))
                                                                {!! render_image_markup_by_attachment_id(optional($order->service)->image, '', 'thumb') !!}
                                                            @else
                                                                <img src="{{ asset('assets/frontend/img/no-image-one.jpg') }}"  alt="No Image" style="height: 77px">
                                                            @endif
                                                        @endif
                                                        </a>
                                                    </div>
                                                    <div class="dashboard_table__main__order__contents">
                                                        @if(request()->path() == 'seller/job-orders')
                                                            <h5 class="dashboard_table__main__order__contents__title"> @if($order->order_from_job == 'yes') {{ Str::limit(optional($order->job)->title,60) }} @endif </h5>
                                                        @else
                                                            <h5 class="dashboard_table__main__order__contents__title">{{ optional($order->service)->title }}</h5>
                                                        @endif
                                                        <span class="dashboard_table__main__order__contents__subtitle mt-2">
                                                    <a href="javascript:void(0)" class="dashboard_table__main__order__contents__id"> <strong class="text-dark">{{ __('Order ID:') }}</strong> {{ $order->id }}</a> ,
                                                    <!--<a href="javascript:void(0)" class="dashboard_table__main__order__contents__author"> <strong class="text-dark">{{ __('Buyer Name:') }}</strong>{{ optional($order->buyer)->name }} </a>-->
                                                </span>
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
                                        <td>
                                            <div class="dashboard_recentOrder__item__icon">
                                    <span class="dashboard_recentOrder__item__icon__single" data-bs-toggle="dropdown">
                                      
                                        <button class="btn btn-primary">{{ __('Actions') }}</button>
                                        <ul class="dropdown-menu">
                                            <!--review section start -->
                                            @if ($order->status == 2)
                                                <li><a class="dropdown-item review_add_modal"
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#reviewModal"
                                                       data-buyer_id="{{ $order->buyer_id }}"
                                                       data-service_id="{{ $order->service_id }}"
                                                       data-order_id="{{  $order->id }}"
                                                       class="review_add_modal"
                                                    ><i class="las la-star text-success"></i> {{ __('Review') }} </a>
                                            </li>
                                            @endif
                                            <!--review section end -->
                                             <!--review section end -->
                                            
                                                     
                                               @if($order->location_request ==0 && $order->status == 1)
                                            
                                               
                                                <li>
                                               <a  class="dropdown-item new_tab_open_page" href="{{route('location.request',$order->id)}}">
                                                   <i class="las la-map-marker "></i> {{ __('Request clients location') }} </a>
                                                   
                                           </li>
                                                @elseif($order->location_request == 1)
                                             
                                                <li>
                                                     <a  class="dropdown-item " >
                                                   <i class="las la-map-marker "></i> {{ __('Waiting For location') }}  </a>
                                           </li>
                                          
                                            @elseif($order->location_request == 2)
                                             
                                                 <li>
                                               <a  class="dropdown-item new_tab_open_page" href="{{route('location.view',$order->id)}}">
                                                   <i class="las la-map-marker "></i> {{ __('View location') }} </a>
                                                   
                                           </li>
                                            
                                            @else
                                            @endif

                                            <li><a class="dropdown-item load_only_page_this_tab" href="{{ route('seller.order.details', $order->id) }}"><i class="fa-regular fa-eye text-success"></i>{{ __('View Details') }}</a></li>
                                           @if($order->is_order_online != 1)
                                                @if($order->buyer_id != NULL)
                                                    <li> <a class="dropdown-item load_only_page_this_tab" href="{{ route('seller.support.ticket.new', $order->id) }}"><i class="las la-ticket-alt text-success"></i> {{ __('New Ticket') }} </a> </li>
                                                @endif
                                            @else
                                                @if(!empty($order->online_order_ticket->id))
                                                    <li><a class="dropdown-item load_only_page_this_tab" href="{{ route('seller.support.ticket.view', optional($order->online_order_ticket)->id ?? 0) }}">
                                                        <i class="las la-eye-slash text-success"></i> {{ __('View Ticket') }}</a>
                                                </li>
                                                @endif
                                            @endif

                                            <li><a class="dropdown-item new_tab_open_page" href="{{ route('seller.order.invoice.details',$order->id) }}" target="_blank"><i class="las la-print text-danger"></i> {{ __('Print Pdf') }} </a></li>

                                            <!-- report section Start -->
                                            @if($order->status != 2)
                                                <li><a class="dropdown-item report_add_modal"
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#reportModal"
                                                       data-buyer_id="{{ $order->buyer_id }}"
                                                       data-service_id="{{ $order->service_id }}"
                                                       data-order_id="{{  $order->id }}"
                                                    ><i class="lar la-file text-danger"></i> {{ __('Report') }} </a>
                                            </li>
                                            @endif
                                            <!-- report section end -->

                                            @php
                                                if (request()->path() == 'seller/job-orders'){
                                                    $service_title =  optional($order->job)->title;
                                                }else{
                                                     $service_title =  optional($order->service)->title;
                                                }
                                                $details = __('Order Successfully Created');
                                                $address =  optional($order->seller)->address;
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
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="blog-pagination margin-top-55">
                            <div class="custom-pagination mt-4 mt-lg-5">
                                {!! $all_orders->links() !!}
                            </div>
                        </div>
                    @else
                        <div class="chat_wrapper__details__inner__chat__contents">
                            <h2 class="chat_wrapper__details__inner__chat__contents__para">{{ __('No Orders Found') }}</h2>
                        </div>
                    @endif
                </div>
                <!-- order table section end-->
            </div>
        </div>
    </div>
    
    
    <!--parital payment model-->

 <div class="modal fade" id="paritalpaymentModal" tabindex="-1" role="dialog" aria-labelledby="editModal"
         aria-hidden="true">
        <form action="{{ route('seller.order.partialpayment') }}" method="post">
            <input type="hidden" class="order_id_input" name="order_id" >
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModal">{{ __('Create Parital Payment Request') }}</h5>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="up_day_id" class="label_title">{{ __('Select Percentage') }}</label>
                              <select name="Percent" id="Percent" class="form-select">
        <option value="" disabled selected>{{ __('-- Select Percentage --') }}</option>
        <option value="25" @selected(old('status') == '25')>{{ __('25 Percent') }}</option>
        <option value="50" @selected(old('status') == '50')>{{ __('50 Percent') }}</option>
    </select>
                           
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit Request') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
<!--TimeExtensionModal-->


 <div class="modal fade" id="TimeExtensionModal" tabindex="-1" role="dialog" aria-labelledby="TimeExtensionModal"
         aria-hidden="true">
        <form action="{{ route('seller.order.timeextension') }}" method="post">
            <input type="hidden" id="order_ids" name="order_ids" >
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModal">{{ __('Create Time Extension Request') }}</h5>
                    </div>
                    <div class="modal-body">

                         <div class="form-group mt-2">
                            <label class="payout-request-note d-block label_title" for="amount">{{ __('How Many Days') }}</label>
                            <input type="text" name="Days" class="form-control" placeholder="{{ __('Days') }}">
                        </div>

                        

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Make Request') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!--Status Modal -->
    <div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editModal"
         aria-hidden="true">
        <form action="{{ route('seller.order.status') }}" method="post">
            <input type="hidden" id="order_id" name="order_id">
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModal">{{ __('Create Order Complete Request') }}</h5>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="up_day_id" class="label_title">{{ __('Select Status') }}</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="2">{{ __('Completed') }}</option>
                            </select>
                            <p class="text-info mt-2">{{ __('Completed: Order is completed and closed.') }}</p>
                        </div>

                        <div class="form-group m-3">
                            <div class="media-upload-btn-wrapper">
                                <div class="img-wrap"></div>
                                <input type="hidden" name="image">
                                <button type="button" class="btn btn-info media_upload_form_btn"
                                        data-btntitle="{{__('Select Image')}}"
                                        data-modaltitle="{{__('Upload Image')}}" data-bs-toggle="modal"
                                        data-bs-target="#media_upload_modal">
                                    {{__('Upload Image')}}
                                </button>
                                <small>{{ __('image format: jpg,jpeg,png')}}</small>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </div>
        </form>
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
                        <form action="{{ route('seller.to.buyer.review') }}" method="post">
                            @csrf
                            <input type="hidden" id="rating" name="rating" class="form-control form-control-sm">
                            <input type="hidden" id="buyer_id" name="buyer_id" class="form-control form-control-sm">
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
                        <form action="{{ route('seller.order.report') }}" method="post">
                            @csrf
                            <input type="hidden" id="buyer_id" name="buyer_id" class="form-control form-control-sm">
                            <input type="hidden" id="service_id" name="service_id" class="form-control form-control-sm">
                            <input type="hidden" id="order_id" name="order_id" class="form-control form-control-sm">

                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="single-input">
                                        <label for="ticketTitle" class="label_title">{{ __('Report Here') }}</label>
                                        <textarea name="report" cols="30" rows="4"  class="form--control radius-10" placeholder="{{ __('Report Here') }}"></textarea>
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

    {{-- Extra Service Request Modal Start --}}
    <div class="modal fade" id="extraServiceRequest" tabindex="-1" role="dialog" aria-labelledby="editReportModal"
         aria-hidden="true">
        <form action="{{ route('seller.order.extra.service') }}" method="post">
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" >{{ __('Request For Extra Service') }}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="border: none">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="comments-flex-item">
                            <input type="hidden" name="order_id" class="form-control form-control-sm">
                        </div>
                        <div class="form-group mt-2">
                            <label class="payout-request-note d-block label_title" for="amount">{{ __('Title') }}</label>
                            <input type="text" name="title" class="form-control" placeholder="{{ __('title') }}">
                        </div>
                        <div class="form-group mt-2">
                            <label class="payout-request-note d-block label_title" for="quantity">{{ __('Quantity') }}</label>
                            <input type="number" name="quantity" class="form-control" placeholder="{{ __('Quantity') }}">
                        </div>
                        <div class="form-group mt-2">
                            <label class="payout-request-note d-block label_title" for="price">{{ __('Price') }}</label>
                            <input type="number" name="price" class="form-control" step="0.05" placeholder="{{ __('price') }}">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{--    edit payment status--}}
    <div class="modal fade" id="editPaymentStatusModal" tabindex="-1" role="dialog" aria-labelledby="editModal"
         aria-hidden="true">
        <form action="{{ route('seller.order.payment.status') }}" method="post">
            <input type="hidden"  name="order_id">
            @csrf
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModal">{{ __('Change Payment Status') }}</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="border: none">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="up_day_id">{{ __('Select Status') }}</label>
                            <select name="status" id="status" class="form-control nice-select">
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="complete">{{ __('Completed') }}</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!--pop uop-->
<!-- Bootstrap Modal -->
<div class="modal fade" id="readMoreModal" tabindex="-1" aria-labelledby="readMoreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="readMoreModalLabel">Full Description</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalContent" style="word-wrap: break-word; overflow-wrap: break-word;">
        <!-- Full description will be inserted here -->
      </div>
    </div>
  </div>
</div>


    <x-media.markup :type="'web'"/>
@endsection
@section('scripts')

    <script src="{{ asset('assets/backend/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/rating.js') }}"></script>
    <x-media.js :type="'web'"/>
    
    <script>
         // <!--parital payment model-->
               $(document).ready(function() {
    $(document).on('click', '.parital_payment_modal', function(e) {
        e.preventDefault();

        let order_id = $(this).data('id');
        let status = $(this).data('status');

        $('.order_id_input').val(order_id);
    });
});
    </script>
                <script>
// Set the date we're counting down to
var countdowns = [
<?php foreach ($all_orders as $race) {?>
  {
    
    date: new Date("<?php echo date('M j, Y H:i:s', strtotime($race['offer_time_end']));?>").getTime(),
    id: <?php echo $race['id'] ?>
  },
<?php }?>
];

// Update the count down every 1 second
var timer = setInterval(function() {
  // Get todays date and time
  var now = Date.now();

  var index = countdowns.length - 1;

  // we have to loop backwards since we will be removing
  // countdowns when they are finished
  while(index >= 0) {
    var countdown = countdowns[index];

    // Find the distance between now and the count down date
    var distance = countdown.date - now;

    // Time calculations for days, hours, minutes and seconds
    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

    var timerElement = document.getElementById("race"+countdown.id);

    // If the count down is over, write some text 
    if (distance < 0) {
    //   timerElement.innerHTML = " fdfsd";
      // this timer is done, remove it
      countdowns.splice(index, 1);
    //   location.reload();
    } else {
      timerElement.innerHTML = days+ "D " + hours + "h " + minutes + "m " + seconds + "s "; 
    }

    index -= 1;
  }

  // if all countdowns have finished, stop timer
  if (countdowns.length < 1) {
    clearInterval(timer);
  }
}, 1000);
</script> 
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {


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

                // date range
                $('.flatpickr_input').flatpickr({
                    altFormat: "invisible",
                    altInput: false,
                    mode: "range",
                });


                // media upload modal hide
                $(document).on('click','.media_upload_modal_submit_btn',function(e){
                    e.preventDefault();
                    $('#editStatusModal').modal('show');
                });

                $(document).on('click','.close',function(e){
                    e.preventDefault();
                    $('#media_upload_modal').modal('hide');
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

                $(document).on('click', '.edit_payment_status_modal', function(e) {
                    e.preventDefault();
                    let modalContainer = $('#editPaymentStatusModal');
                    let order_id = $(this).data('id');
                    modalContainer.find('input[name="order_id"]').val(order_id);
                    $('.nice-select').niceSelect('update');
                });
/// time extension
                  $(document).on('click', '.time_extension', function(e) {
                    e.preventDefault();
                    
                    
                    let order_id = $(this).data('id');
                    let status = $(this).data('status');
                    $('#TimeExtensionModal').modal('show');
                    $('#order_ids').val(order_id);
                    // $('#status').val(status);
                    // $('.nice-select').niceSelect('update');
                });

                /* ------------------------------
                *   Request for extra service
                * -----------------------------*/
                $(document).on('click', '.extra_submit_request_btn', function(e) {
                    e.preventDefault();
                    let order_id = $(this).data('id');
                    let modalContainer = $('#extraServiceRequest');

                    modalContainer.find('input[name="order_id"]').val(order_id);
                });

                $(document).on('click', '.edit_status_modal', function(e) {
                    e.preventDefault();
                    let order_id = $(this).data('id');
                    let status = $(this).data('status');

                    $('#order_id').val(order_id);
                    $('#status').val(status);
                    $('.nice-select').niceSelect('update');
                });

                //report us
                $(document).on('click', '.report_add_modal', function () {
                    let el = $(this);
                    let buyer_id = el.data('buyer_id');
                    let service_id = el.data('service_id');
                    let order_id = el.data('order_id');
                    let form = $('#reportModal');
                    form.find('#buyer_id').val(buyer_id);
                    form.find('#service_id').val(service_id);
                    form.find('#order_id').val(order_id);
                });


                // seller to buyer review start
                $(document).on('click', '.review_add_modal', function () {
                    let el = $(this);

                    let buyer_id = el.data('buyer_id');

                    let service_id = el.data('service_id');

                    let order_id = el.data('order_id');

                    let form = $('#reviewModal');
                    form.find('#buyer_id').val(buyer_id);
                    form.find('#service_id').val(service_id);
                    form.find('#order_id').val(order_id);
                });

                // rating
                $("#review").rating({
                    "value": 5,
                    "click": function (e) {
                        $("#rating").val(e.stars);
                    }
                });

            });

        })(jQuery);

    </script>
  <script>
function setModalContent(content) {
    document.getElementById('modalContent').innerHTML = content;
}
</script>



@endsection
