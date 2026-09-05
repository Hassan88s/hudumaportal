@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Services')}}
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
                        <form action="{{ route('seller.services') }}" method="GET">
                            <div class="dashboard__headerGlobal__flex">
                                <div class="dashboard__headerGlobal__content">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <h4 class="dashboard_table__title">{{ __('Search Service Module') }}</h4> <i class="las la-angle-down search_by_all"></i>
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
                                @if(request()->get('service_id'))  show
                                @elseif(request()->get('service_status')) show
                                @elseif(request()->get('service_title')) show
                                @elseif(request()->get('online_offline_status')) show
                                @elseif(request()->get('service_price')) show
                                @elseif(request()->get('service_date')) show
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
                                                                    <label for="order_id" class="info-title"> {{__('Service ID')}} </label>
                                                                    <input class="form--control" name="service_id" value="{{ request()->get('service_id') }}" type="text" placeholder="{{ __('Service ID') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="service_title" class="info-title"> {{__('Service Title')}} </label>
                                                                    <input class="form--control" name="service_title" value="{{ request()->get('service_title') }}" type="text" placeholder="{{ __('Service Title') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="service_status" class="info-title"> {{__('Service Status')}} </label>
                                                                    <select name="service_status">
                                                                        <option value="">{{__('Select Status')}}</option>
                                                                        <option value="pending" @if(request()->get('service_status') == 'pending') selected @endif>{{ __('Pending') }}</option>
                                                                        <option value="1" @if(request()->get('service_status') == 1) selected @endif>{{ __('Active') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="service_price" class="info-title"> {{__('Service Price')}} </label>
                                                                    <input class="form--control" name="service_price" value="{{ request()->get('service_price') }}" type="number" placeholder="{{ __('Service Price') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="service_date" class="info-title"> {{__('Created Date Range')}} </label>
                                                                    <input class="form--control flatpickr_input" value="{{ request()->get('service_date') }}" name="service_date" type="text" value="{{ request()->get('service_date') }}" placeholder="{{ __('Created Date Range') }}">
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

                <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboard_table__title"> {{ __('All Services') }} </h4>
                        </div>
                        @if(moduleExists('Subscription') && $commissionGlobal->system_type == 'subscription')
                            @if(!empty(auth('web')->user()->subscribedSeller))
                                @if(Carbon\Carbon::parse(auth('web')->user()->subscribedSeller->expire_date) <= \Carbon\Carbon::today())
                                    <div class="col-lg-12">
                                        <div class="alert alert-warning d-flex justify-content-between">{{__('your package has been expired, please renew it')}}
                                            <a href="{{getSlugFromReadingSetting('price_plan_page') ? url('/'.getSlugFromReadingSetting('price_plan_page')) : url('/price-plan')}}" target="_self" class="dashboard_table__title__btn btn-bg-1 radius-5">{{__('view packages')}}</a>
                                        </div>
                                    </div>
                                      @elseif(auth('web')->user()?->subscribedSeller?->status == 0)
                         <div class="col-lg-12 mt-1">
                                <div class="alert alert-warning d-flex justify-content-between">
                                    <strong style="font-size: 16px">{{__('Please wait for Admin Approval to active Your subscription.')}}</strong>
                                 
                                </div>
                            </div>
                                @else
                                    <div class="btn-wrapper margin-top-50 text-right">
                                        <a href="{{route('seller.add.services')}}" class="dashboard_table__title__btn btn-bg-1 radius-5">{{__('Add Services')}}</a>
                                    </div>
                                @endif
                            @else
                                <div class="col-lg-12">
                                    <div class="alert alert-warning d-flex justify-content-between">{{__('you must have to subscribe any of our package in order to start selling your services.')}}
                                        <a href="{{getSlugFromReadingSetting('price_plan_page') ? url('/'.getSlugFromReadingSetting('price_plan_page')) : url('/price-plan')}}" target="_self" class="dashboard_table__title__btn btn-bg-1 radius-5">{{__('view packages')}}</a>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="btn-wrapper margin-top-50 text-right">
                                <a href="{{route('seller.add.services')}}" class="dashboard_table__title__btn btn-bg-1 radius-5"> {{__('Add Services')}}</a>
                            </div>
                        @endif
                    </div>
                </div>

                @if($services->count() > 0)
                    @foreach($services as $data)
                        <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                            <div class="rows dash-single-inner">
                                <div class="dash-left-service">
                                    <div class="dashboard-services">
                                        <div class="dashboar-flex-services">
                                            <div class="thumb bg-image" {!! render_background_image_markup_by_attachment_id($data->image,'','thumb') !!}>
                                            </div>

                                            <div class="thumb-contents">
                                                <h4 class="title"> <a href="javascript:void(0)"> {{ $data->title }} </a> </h4>
                                                <div class="thumb-contents-review-flex">
                                                    <div class="thumb-contents-review-inner">
                                                        <span class="service-review">
                                                            <i class="las la-star"></i>
                                                            {{ round(optional($data->reviews)->avg('rating'),1) }}
                                                            <b>({{ optional($data->reviews)->count() }})</b>
                                                        </span>
                                                        <span class="service-review style-02"> <i class="las la-eye"></i> {{ $data->view }} </span>
                                                        @if($data->is_service_online == 1)
                                                            <span class="service-review style-02"> <i class="las la-map-marker"></i> {{ __('Online') }} </span>
                                                        @else
                                                            <span class="service-review style-02"> <i class="las la-map-marker"></i> {{ __('Offline') }} </span>
                                                        @endif
                                                    </div>
                                                    <div class="service-bottom-flex mt-1">
                                                        <a href="{{ route('seller.pending.orders') }}">
                                                            <div class="dashboard-service-bottom-flex color-1">
                                                                <div class="icon">
                                                                    <i class="las la-sync-alt"></i>
                                                                </div>
                                                                <div class="content">
                                                                    <span class="queue"> {{__('In Queue')}} <span class="num"> {{ optional($data->pendingOrder)->count() }} </span> </span>
                                                                </div>
                                                            </div>
                                                        </a>
                                                        <div class="dashboard-service-bottom-flex color-2">
                                                            <div class="icon">
                                                                <i class="las la-check"></i>
                                                            </div>
                                                            <div class="content">
                                                                <span class="queue"> {{__('Completed')}} <span class="num"> {{ optional($data->completeOrder)->count() }} </span> </span>
                                                            </div>
                                                        </div>
                                                        <div class="dashboard-service-bottom-flex color-3">
                                                            <div class="icon">
                                                                <i class="las la-times"></i>
                                                            </div>
                                                            <div class="content">
                                                                <span class="queue"> {{__('Cancelled')}} <span class="num"> {{ optional($data->cancelOrder)->count() }} </span></span>
                                                            </div>
                                                        </div>
                                                        <div class="dashboard-service-bottom-flex color-4">
                                                            @php 
 $seller_subscription = \Modules\Subscription\Entities\SellerSubscription::where('seller_id', Auth::guard('web')->user()->id)->first();
 $seller_idno=Auth::guard('web')->user()->id;
 $seller_prmoted_count = \App\Service::where(['seller_id'=>$seller_idno ,'featured' =>'1'])->count();
 $seller_check_request = \App\Featureservice::where(['seller_id'=>$seller_idno ,'service_id' =>$data->id,'Payment_status'=>'pending'])->count();

@endphp

                                                            @if($data->featured ==1)
                                                            <div class="icon">
                                                                <i class="las la-award"></i>
                                                            </div>
                                                            <div class="content">
                                                                <span class="queue"> {{__('Promoted')}} </span>
                                                            </div>
                                                            @else
                                                            @if($seller_subscription != null)
                                                                @if($seller_subscription->Intial_promoted_services  <= $seller_prmoted_count )
                                                                 <div class="content">
                                                                     @if($seller_check_request >= 1) 
                                                                     <span class='btn btn-warning'>{{__('Request Pending')}} </span>
                                                                     @else
                                                                       <a href="#" data-target="#my_modal" data-toggle="modal" class="identifyingClass btn btn-warning btn-sm" data-id="{{$data->id}}">Promote the Service</a>
                                                                     @endif
                                                             
                                                          
                                                          
                                                            </div><div class="content">
                                                             
                                                              </div>
                                                                @else
                                                                @if($seller_subscription->status==1)
                                                             <div class="content">
                                                              <a href="{{route('seller.promoted.services',$data->id)}}" class="btn btn-warning btn-sm">{{__('Make Promoted')}} </a>
                                                            </div>
                                                            @endif
                                                            @endif
                                                              @endif
                                                              @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="dash-righ-service">
                                    <div class="dashboard-switch-flex-content">
                                        <div class="dashboard-switch-single">
                                            <span class="dashboard-starting"> {{__('Starting From:')}} </span>
                                            <h2 class="title-price color-3"> 
                                            @if(is_null($data->starting_price))
                                                         {{ amount_with_currency_symbol($data->price) }}
                                                            @else
                                                          {{ amount_with_currency_symbol($data->starting_price) }}
                                                            @endif
                                            
                                            </h2>
                                        </div>
                                        <div class="dashboard-switch-single">
                                            <span class="dashboard-starting"> {{__('On/Off Service:')}} </span>
                                            @if($data->is_service_on==1)
                                                <input class="custom-switch style-02 service_on_off_btn" id="switch2_{{$data->id}}" type="checkbox" data-id="{{$data->id}}" />
                                                <label class="switch-label style-02" for="switch2_{{$data->id}}"></label>
                                            @else
                                                <input class="custom-switch service_on_off_btn" id="switch1_{{$data->id}}" type="checkbox" data-id="{{$data->id}}" />
                                                <label class="switch-label" for="switch1_{{$data->id}}"></label>
                                            @endif

                                        </div>
                                        <div class="dashboard-switch-single">
                                            <a href="{{route('seller.edit.services',$data->id)}}"> <span class="dash-icon color-1" data-toggle="tooltip" data-placement="top" title="{{ __('Edit Service') }}"> <i class="las la-pen"></i> </span> </a>
                                            <a href="{{route('seller.services.attributes.show.byid',$data->id)}}"> <span class="dash-icon color-1" data-toggle="tooltip" data-placement="top" title="{{ __('Show Attributes') }}"> <i class="las la-eye"></i> </span> </a>
                                            <a href="{{route('service.list.details',$data->slug ?? 'x')}}" target="_blank"> <span class="dash-icon color-1" data-toggle="tooltip" data-placement="top"  title="{{ __('Service in frontend') }}"> <i class="las la-external-link-square-alt"></i> </span> </a>
                                            <x-seller-delete-popup :url="route('seller.services.delete',$data->id)"/>
                                            <span class="dash-icon color-1 open-iframe-modal"
      data-id="{{ $data->id }}"
      data-title="{{ $data->title }}"
      data-toggle="tooltip"
      style="cursor:pointer;"
      title="Embed Code">
    <i class="las la-code"></i>
</span>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="blog-pagination margin-top-55">
                        <div class="custom-pagination mt-4 mt-lg-5">
                            {!! $services->links() !!}
                        </div>
                    </div>
                @else
                    <div class="chat_wrapper__details__inner__chat__contents">
                        <h2 class="chat_wrapper__details__inner__chat__contents__para">{{ __('No Service Created Yet') }}</h2>
                    </div>
                @endif

            </div>
        </div>
         <!-- Hire Modal -->
       <div class="modal fade" id="my_modal" tabindex="-1" role="dialog" aria-labelledby="my_modalLabel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-warning" id="couponModal">{{ __('At First Complete Your Payment and Only You Can Place an Order.') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="custom-form">
                            <form id="msform" class="ms-order-form" action="{{route('seller.paypromoted.services')}}" method="post" enctype="multipart/form-data">
                                @csrf
                               
            <input type="hidden" name="hiddenValue" id="hiddenValue" value="" />
            <h6 class="text-warning">We Charge 500.00 TSH PER Day</h6>
     <div class="form-group">
    <label for="exampleInputEmail1">How Many Days?</label>
 <input min="1" max="30" type="number" id="typeNumber" name="month" class="form-control" required />
   
   
  </div>
                                <div class="row g-4">
                                    <div class="col-12">

                                        <div class="confirm-bottom-content">
                                            @if(moduleExists('Wallet'))
                                                {!! \App\Helpers\PaymentGatewayRenderHelper::renderWalletForm() !!}
                                            @endif
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

                                        <div class="modal-footer mt-3">
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                            <button type="submit" class="btn btn-primary">{{ __('Make Promoted') }}</button>
                                        </div>

                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="iframeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title">Embed Service</h5>
              <button type="button" class="close" data-dismiss="modal">
                  &times;
              </button>
          </div>

          <div class="modal-body">
              <textarea id="iframeCodeBox"
                class="form-control"
                style="height:100px;"></textarea>

              <button class="btn btn-primary mt-2" onclick="copyIframeCode()">Copy</button>
          </div>
      </div>
  </div>
</div>

        @endsection
        @section('scripts')
        <script>
$(document).on('click', '.open-iframe-modal', function () {

    let id = $(this).data('id');

    let iframe = `<iframe src="{{ url('/embed') }}/${id}" 
                          width="400" height="550" 
                          frameborder="0"></iframe>`;

    $('#iframeCodeBox').val(iframe);
    $('#iframeModal').modal('show');
});

function copyIframeCode() {
    let copyText = document.getElementById("iframeCodeBox");
    copyText.select();
    document.execCommand("copy");
    alert("iFrame code copied!");
}
</script>

            <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
             <x-payment-gateway-two-js/>
<script type="text/javascript">
    $(function () {
        $(".identifyingClass").click(function () {
            var my_id_value = $(this).data('id');
             $(".modal-body #hiddenValue").val(my_id_value);
              $("#my_modal").modal("show");
        })
    });
</script>
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

                        $(document).on('change','.service_on_off_btn',function(e){
                            e.preventDefault();
                            if($(this).is(':checked')){
                                var service_id = $(this).data('id');
                                $.ajax({
                                    method:'post',
                                    url:"{{route('seller.services.on.of')}}",
                                    data:{service_id:service_id},
                                    success:function(res){
                                        if(res.status=='success'){
                                            toastr.options = {
                                                "closeButton": true,
                                                "debug": false,
                                                "newestOnTop": false,
                                                "progressBar": true,
                                                "preventDuplicates": true,
                                                "onclick": null,
                                                "showDuration": "100",
                                                "hideDuration": "1000",
                                                "timeOut": "5000",
                                                "extendedTimeOut": "1000",
                                                "showEasing": "swing",
                                                "hideEasing": "linear",
                                                "showMethod": "show",
                                                "hideMethod": "hide"
                                            };
                                            toastr.success("{{__('Service On/Off Change Success---')}}");
                                        }
                                    }
                                });
                            }else{
                                var service_id = $(this).data('id');
                                $.ajax({
                                    method:'post',
                                    url:"{{route('seller.services.on.of')}}",
                                    data:{service_id:service_id},
                                    success:function(res){
                                        if(res.status=='success'){
                                            toastr.options = {
                                                "closeButton": true,
                                                "debug": false,
                                                "newestOnTop": false,
                                                "progressBar": true,
                                                "preventDuplicates": true,
                                                "onclick": null,
                                                "showDuration": "100",
                                                "hideDuration": "1000",
                                                "timeOut": "5000",
                                                "extendedTimeOut": "1000",
                                                "showEasing": "swing",
                                                "hideEasing": "linear",
                                                "showMethod": "show",
                                                "hideMethod": "hide"
                                            };
                                            toastr.success("{{__('Service On/Off Change Success---')}}");
                                        }
                                    }
                                });
                            }

                        });

                        $(document).on('click','.swal_delete_button',function(e){
                            e.preventDefault();
                            Swal.fire({
                                title: '{{__("Are you sure?")}}',
                                text: '{{__("You would not be able to revert this item!")}}',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: "{{__('Yes, delete it!')}}"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $(this).next().find('.swal_form_submit_btn').trigger('click');
                                }
                            });
                        });

                    });

                })(jQuery);
            </script>
@endsection
