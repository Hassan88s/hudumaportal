@extends('frontend.frontend-page-master')
@section('page-meta-data')
    <title> {{ $seller->name  }}</title>
@endsection
@section('style')
    <style>
        .profile-flex-content {
            flex-wrap: nowrap !important;
        }
        .seller-social-links {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
        }
        .seller-social-links a {
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 25px;
            width: 25px;
            background-color: #fff;
            color: var(--main-color-one);
            border-radius: 50%;
            transition: all .3s;
        }
        .seller-social-links a:hover{
            background-color: var(--main-color-one);
            color: #fff;
        }
        .seller-verified{
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 20px;
            width: 20px;
            background-color: var(--main-color-one);
            color: #fff;
            border-radius: 50%;
        }
        .profile-flex-content .profile-contents .title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Tooltip container */
        .tooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: black;
            color: #fff;
            text-align: center;
            padding: 5px 0;
            border-radius: 6px;
            position: absolute;
            z-index: 1;
        }
        .tooltip:hover .tooltiptext {
            visibility: visible;
        }

        .schedule_radioInput .custom_radio__single {
            margin-left: 10px;
        }

        /* Change background color on hover */
        .custom_radio__single_seller:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }

        .schedule_radioInput .custom_radio__single_seller {
            margin-left: 10px;
        }

        .schedule_radioInput .custom_radio__single_seller {
            padding: 20px 20px;
            border: 1px solid var(--new-border-color);
            border-radius: 10px;
        }
        .custom_radio__single_seller {
            -webkit-transition: 0.3s;
            transition: 0.3s;
            cursor: pointer;
            display: -webkit-inline-box;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            gap: 24px;
        }

    </style>
@endsection
@section('content')
    <!-- Banner Inner area Starts -->
    @if(!empty($seller))
        <div class="banner-inner-area section-bg-2 padding-top-40 padding-bottom-70">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-4 col-md-6 margin-top-30">
                        <div class="profile-author-contents">
                            <div class="profile-flex-content">
                                <div class="thumb">
                                    {!! render_image_markup_by_attachment_id($seller->image) !!}
                                </div>
                                <div class="profile-contents">
                                    <h4 class="title">
                                        <a href="{{ route('about.seller.profile',$seller->username) }}"> {{ $seller->username }} </a>
                                        @if(optional($seller->sellerVerify)->status==1)
                                            <div data-toggle="tooltip" data-placement="top" title="{{__('This seller is verified by the site admin according his national id card.')}}">
                                                <span class="seller-verified"> <i class="las la-check"></i> </span>
                                            </div>
                                        @endif
                                    </h4>
                                    @if($service_rating >=1)
                                        <div class="profiles-review">
                                    <span class="reviews">
                                        <b>{!! ratting_star(round($service_rating,1) ) !!} </b>
                                        ({{ $service_reviews->count() }})
                                    </span>
                                        </div>
                                    @endif
                                   <div class="seller-social-links mt-3">
                                            {{-- Website + Socials (only if Website_enabled = yes) --}}
                                            @if(optional($subscription)->Website_enabled === 'yes')
                                                
                                                {{-- Facebook --}}
                                                @if(!empty($seller->fb_url))
                                                    <a href="{{ Str::startsWith($seller->fb_url, ['http://', 'https://']) ? $seller->fb_url : 'https://' . $seller->fb_url }}" target="_blank">
                                                        <i class="lab la-facebook-f"></i>
                                                    </a>
                                                @endif
                                        
                                                {{-- Twitter --}}
                                                @if(!empty($seller->tw_url))
                                                    <a href="{{ Str::startsWith($seller->tw_url, ['http://', 'https://']) ? $seller->tw_url : 'https://' . $seller->tw_url }}" target="_blank">
                                                        <i class="lab la-twitter"></i>
                                                    </a>
                                                @endif
                                        
                                                {{-- Linkedin --}}
                                                @if(!empty($seller->li_url))
                                                    <a href="{{ Str::startsWith($seller->li_url, ['http://', 'https://']) ? $seller->li_url : 'https://' . $seller->li_url }}" target="_blank">
                                                        <i class="lab la-linkedin-in"></i>
                                                    </a>
                                                @endif
                                        
                                                {{-- Instagram --}}
                                                @if(!empty($seller->in_url))
                                                    <a href="{{ Str::startsWith($seller->in_url, ['http://', 'https://']) ? $seller->in_url : 'https://' . $seller->in_url }}" target="_blank">
                                                        <i class="lab la-instagram"></i>
                                                    </a>
                                                @endif
                                        
                                                {{-- Website --}}
                                                @if(!empty($seller->website_url))
                                                    <a href="{{ Str::startsWith($seller->website_url, ['http://', 'https://']) ? $seller->website_url : 'https://' . $seller->website_url }}" target="_blank">
                                                        <i class="las la-globe"></i>
                                                    </a>
                                                @endif
                                        
                                            @endif
                                        
                                            {{-- Personal info (only if Personal_enabled = yes) --}}
                                            @if(optional($subscription)->personal_enabled === 'yes')
                                        
                                                {{-- Email --}}
                                                @if(!empty($seller->email))
                                                    <a href="mailto:{{ $seller->email }}">
                                                        <i class="las la-envelope"></i>
                                                    </a>
                                                @endif
                                        
                                                {{-- Phone --}}
                                                @if(!empty($seller->phone))
                                                    <a href="tel:{{ $seller->phone }}">
                                                        <i class="las la-phone"></i>
                                                    </a>
                                                @endif
                                        
                                            @endif
                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 margin-top-30">
                        <div class="profile-author-contents">
                            <ul class="profile-about">
                                <li> {{ __('From:') }} <span> {{ optional($seller->city)->service_city }} </span> </li>
                                <li> {{ __('Seller Since:') }} <span> {{ Carbon\Carbon::parse($seller_since->created_at)->year }}  </span> </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-5 margin-top-30">
                        <div class="profile-author-contents">
                            <div class="profile-single-achieve">
                                <div class="single-achieve">
                                    <div class="achieve-inner">
                                        <div class="icon">
                                            <i class="las la-check"></i>
                                        </div>
                                        <div class="contents margin-top-10">
                                            <h3 class="title">@if(!empty($completed_order)){{ $completed_order }} @endif</h3>
                                            <span class="ratings-span"> {{ __('Order Completed') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="single-achieve">
                                    <div class="achieve-inner">
                                        <div class="icon">
                                            <i class="las la-star"></i>
                                        </div>
                                        <div class="contents margin-top-10">
                                            <h3 class="title">@if(!empty($seller_rating_percentage_value)) {{ ceil($seller_rating_percentage_value) }}% @endif</h3>
                                            <span class="ratings-span">{{ __('Seller Rating') }} </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- Banner Inner area end -->

    <section class="services-area">
        <div class="container">
            <!-- Schedule -->
            <fieldset class="confirm-date-time padding-top-50 edit_style_schedule">
                <div class="row g-4 date-overview">
                    <div class="col-xxl-4 col-xl-5 col-md-6">
                        <h4 class="date-time-title"> {{ get_static_option('service_available_date_title') ?? __('Available Date') }} </h4>
                        <div class="overview-location">
                            <input type="hidden" class="flatpickr_calendar d-none" id="service_available_dates" name="service_available_dates">
                            <ul class="date-time-list margin-top-20 show-date">
                                <span class="seller-id-for-schedule d-none">{{ $seller->id }}</span>
                            </ul>
                        </div>
                    </div>

                    <div class="col-xxl-8 col-xl-7 col-md-6">
                        <div class="schedule_radioInput mt-4">
                            <div class="custom_radio custom_radio__inline">
                                <h4 class="date-time-title"> {{ get_static_option('service_available_schudule_title') ?? __('Available Schedule') }} </h4>
                                <div class="show-schedule"> </div>
                                <div class="schedule_loader"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </fieldset>

        </div>
    </section>

    <!-- Featured Service area starts -->
    @if(!empty($services))
        <section class="services-area padding-top-100 padding-bottom-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title-two">
                            <h3 class="title">{{ __('Services of this Seller') }} </h3>
                        </div>
                    </div>
                </div>
                <div class="row margin-top-50">
                    <div class="col-lg-12">
                        <div class="services-slider dot-style-one">
                            @forelse($services as $service)
                                <div class="single-services-item">
                                    <div class="single-service">
                                        <a href="{{ route('service.list.details',$service->slug) }}" class="service-thumb service-bg-thumb-format"
                                                {!! render_background_image_markup_by_attachment_id($service->image) !!}>

                                            @if($service->featured == 1)
                                                <div class="award-icons">
                                                       <span class="badge bg-warning">Promoted </span>
                                                </div>
                                            @endif
                                            <div class="country_city_location">
                                                <span class="single_location"> <i class="las la-map-marker-alt"></i>
                                                    {{ sellerServiceLocation($service) }}
                                                </span>
                                            </div>
                                        </a>
                                        <div class="services-contents">
                                            <ul class="author-tag">
                                                <li class="tag-list">
                                                    <a href="{{ route('about.seller.profile',optional($service->seller)->username) }}">
                                                        <div class="authors">
                                                            <div class="thumb">
                                                                {!! render_image_markup_by_attachment_id(optional($service->seller)->image) !!}
                                                                <span class="notification-dot"></span>
                                                            </div>
                                                            <span class="author-title">{{ optional($service->seller)->username }} </span>
                                                                   @if(optional($service->seller->sellerVerify)->status==1)
                                            <div data-toggle="tooltip" data-placement="top" title="{{__('This seller is verified by the site admin according his national id card.')}}">
                                                <span class="seller-verified"> <i class="las la-check"></i> </span>
                                            </div>
                                             @endif 
                                             
                                                                       <div class="single_authors__thumb">
                                                           
                                               
                                         
                                                            
                                 @php 
$value = \Modules\Subscription\Entities\SellerSubscription::where(['seller_id' =>$service->seller->id,'status'=>'1'])->first();
if($value != NULL){
if($value->subscription_id != NULL){
$image = \Modules\Subscription\Entities\Subscription::where(['id' =>$value->subscription_id])->first('image');
    }
    }
@endphp                             @if($value != NULL)
                                    @if($value->subscription_id != NULL)
                                    @if($image->image != NULL)
                                {!!  render_image_markup_by_attachment_id($image->image) !!}
                                    @endif
                                    @endif
                                       @endif
                                </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                @if($service->reviews->where('type', 1)->count() >= 1)
                                                    <li class="tag-list">
                                                        <a href="javascript:void(0)">
                                                <span class="reviews">
                                                    {!! ratting_star(round(optional($service->reviews->where('type', 1))->avg('rating'),1)) !!}
                                                    ({{ optional($service->reviews->where('type', 1))->count() }})

                                                </span>
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                            <h5 class="common-title"> <a href="{{ route('service.list.details',$service->slug) }}">{{ $service->title }} </a> </h5>
                                            <p class="common-para"> {{ Str::limit(strip_tags($service->description),100) }} </p>
                                            <div class="service-price">
                                                <span class="starting">{{ __('Starting at') }} </span>
                                                <span class="prices"> 
                                                
                                                 @if(is_null($service->starting_price))
                                                         {{ amount_with_currency_symbol($service->price) }}
                                                            @else
                                                          {{ amount_with_currency_symbol($service->starting_price) }}
                                                            @endif
                                                
                                                </span>
                                            </div>
                                            <div class="btn-wrapper">
                                                <a href="{{ route('service.list.book',$service->slug) }}" class="cmn-btn btn-appoinment btn-bg-1">{{ __('Book Appointment') }} </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            <h3 class="text-warning">{{__('No Service Found')}}</h3>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- Featured Service area ends -->
    <!-- Review seller area Starts -->
    @if($service_reviews-> count() >= 1)
        <div class="review-seller-area padding-bottom-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title-two">
                            <h3 class="title">{{ get_static_option('service_reviews_title') ?? __('Reviews as Seller') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="review-seller-wrapper">
                            <div class="about-review-tab">
                                @foreach($service_reviews as $review)
                              
                                    <div class="about-seller-flex-content style-02">
                                        <div class="about-seller-thumb">
                                            {!! render_image_markup_by_attachment_id(optional($review->buyer)->image) !!}
                                        </div>
                                        <div class="about-seller-content">
                                            <h5 class="title"> {{ getEnterpriseNamewithoutAuth( $review->buyer)}}  </h5>
                                            <div class="about-seller-list">
                                                @for ($i = 1; $i <= $review->rating; $i++)
                                                <span class="icon">  <i class="las la-star"></i>  </span>
                                               
                                                @endfor
                                            </div>
                                            <p class="about-review-para">{{ $review->message }}</p>
                                            <span class="review-date"> {{ optional($review->created_at)->toFormattedDateString() }} </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="blog-pagination margin-top-55">
                        <div class="custom-pagination mt-4 mt-lg-5">
                            {!! $service_reviews->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- Review seller area ends -->
    @php
       $days_count = \App\Day::select('total_day')->where('seller_id',$seller->id)->first();
       $days_count = optional($days_count)->total_day;
    @endphp
@endsection
@section('scripts')
    <script>
        const UserSelectedLangSlug = "{{current(explode('_',\App\Helpers\LanguageHelper::user_lang_slug()))}}";
    </script>
    <script src="{{asset('assets/common/js/flatpickr.js')}}"></script>
    <script>
        (function($) {
            "use strict";

            $(document).ready(async function() {
                <!-- Date and time -->
                $("#service_available_dates").flatpickr({
                    minDate: "today",
                    maxDate: new Date().fp_incr({{ $days_count }}),
                    inline: true,
                    altInput: true,
                    altFormat: "F j, Y",
                    dateFormat: "Y-m-d",
                    locale: UserSelectedLangSlug
                });

              // Function to load the schedule
                function loadSchedule(date) {
                    $(".schedule_loader").show();
                    let day_date = new Date(date);
                    let day = day_date.toDateString().split(' ')[0];
                    let seller_id = $('.seller-id-for-schedule').text();
                    $('.confirm-overview-left .available_date').text(date);

                    $.ajax({
                        url: "{{ route('service.schedule.by.day') }}",
                        method: 'post',
                        data: {
                            day: day,
                            date_string: date,
                            seller_id: seller_id
                        },
                        success: function (res) {
                            if (res.status === 'success') {
                                let all_lists = '';
                                let all_schedules = res.schedules;
                                $.each(all_schedules, function (index, value) {
                                    all_lists += '<div class="custom_radio__single_seller mt-2"><label for="radio3">' + value.schedule + '</label></div>';
                                });
                                $(".show-schedule").html(all_lists);
                                $(".schedule_loader").hide();
                            } else if (res.status === 'no schedule') {
                                $(".show-schedule").html('<div class="alert alert-warning mt-3"><li class="list">{{ __("Schedule not available") }}</li></div>');
                                $(".schedule_loader").hide();
                            }
                        }
                    });
                }

                // Event handler for date picker change
                $(document).on('change', '#service_available_dates', function () {
                    let selectedDate = $(this).val();
                    loadSchedule(selectedDate);
                });

                // Load schedule when the page loads
                $(document).ready(function () {
                    let defaultDate = $("#service_available_dates").val();
                    loadSchedule(defaultDate);
                });

            });
        })(jQuery);
 </script>
@endsection