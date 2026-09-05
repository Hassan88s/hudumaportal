@section('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.6.3/nouislider.min.css">
    <style>

        /*loader css start */
        .loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #map-container {
            display: none; /* Initially hide the map container */
        }
        /*loader css end */

        #ratingCollapse {
            display: grid;
            gap: 4px;
        }
        .common-title {
            font-size: 16px;
            line-height: 21px;
            font-weight: 700;
        }
        .service_filter_with_reset{
            display: flex;
            gap: 56PX;
        }

        /* for google map visible content not empty marker show */
        .single-service.service-map-style.no-margin.wow {
            visibility: visible !important;
        }

        /* Add your own custom styling here */
        .wrapper {
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .m-b-50 {
            margin-bottom: 50px;
        }

        .m-b-20 {
            margin-bottom: 20px;
        }
        .m-t-50 {
            margin-top: 50px;
        }

        .p-l-r{
            padding: 0 30px;
        }

        .tooltipdiv {
            display: block;
            position: absolute;
            bottom: 35px;
            left: 50%;
            transform: translateX(-50%);
            border: 1px solid #D9D9D9;
            border-radius: 3px;
            background: #fff;
            color: #000;
            padding: 5px;
            text-align: center;
            white-space: nowrap;
        }

        .noUi-value{
            margin-top: 10px;
        }


        /* Filter online offline service button bg color change start */
        .address-input-background-color {
            background-color: rgb(230, 231, 238) !important;
        }
        .filter_button_active{
            background-color: rgb(6, 18, 87);
        }

       /*google map wise filter button */
        .submit-btn {
            border: 2px solid var(--main-color-one);
            background-color: var(--main-color-one);
            color: var(--white);
            padding: 3px 20px;
            -webkit-transition: 300ms;
            transition: 300ms;
            border-radius: 5px;
        }

        .gm-style-iw.gm-style-iw-c{
            padding-right: 0px!important;
            padding-bottom: 0px!important;
            max-width: 191px!important;
            max-height: 208px!important;
            min-width: 0px!important;
        }

        /* google map section css start*/
        @if (!empty(get_static_option("google_map_settings")))
             .new_service__single__contents__title {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.3;
            color: var(--new-heading-color);
            -webkit-transition: all 0.3s;
            transition: all 0.3s;

            /* Limit to 2 lines of text */
            max-height: 2.6em; /* 2 lines * line-height */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Number of lines to show */
            -webkit-box-orient: vertical;
        }

        .new_jobs__single__contents__location {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--main-color-one);

            /* Limit to 2 lines of text */
            max-height: 34px; /* 2 lines * line-height (17px) */
            overflow: hidden;
            text-overflow: ellipsis;
            -webkit-line-clamp: 2; /* Number of lines to show */
            -webkit-box-orient: vertical;
        }

        .new_service__single__price__title {
            font-size: 18px;
        }


        .new_service__single__thumb {
            height: 133px;
          }

        .new_service__single__price {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 5px;
        }

        .author_tag.border_top {
            margin: 0;
            padding: 0;
        }
        .btn-wrapper.border_top {
            margin: 0;
            padding: 0px;
            padding-top: 5px;
            margin-top: 5px;
        }

        .btn-wrapper .cmn-btn {
            padding: 6px 35px;
        }

        .new_service__single__thumb img.no-image {
            height: 147px;
        }
        @endif

      .new_serviceDetails__side__author {
            /*border: 1px solid #ccc; !* Border color (adjust as needed) *!*/
            border-radius: 5px;
            padding: 10px; /* Adjust padding as needed */
            margin: 10px;
            padding-bottom: 0;
            /*box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); !* Add a subtle shadow effect *!*/
            /*background-color: #fff; !* Background color (adjust as needed) *!*/
            background-color: #f7f7f7; /* Background color (adjust as needed) */
        }


        /* Price range CSS start */
        .middle {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin-top: 10px;
            display: inline-block;
        }

        .slider {
            position: relative;
            z-index: 1;
            height: 10px;
            margin: 0 15px;
        }

        .slider>.track {
            position: absolute;
            z-index: 1;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            border-radius: 5px;
            background-color: #27a0ff;
        }

        .slider>.range {
            position: absolute;
            z-index: 2;
            left: 5%;
            right: 5%;
            top: 0;
            bottom: 0;
            border-radius: 5px;
            background-color: #27a0ff;
        }

        .slider>.thumb {
            position: absolute;
            z-index: 3;
            width: 30px;
            height: 30px;
            background-color: #0877cf;
            border-radius: 50%;
        }

        .slider>.thumb.left {
            left: 25%;
            transform: translate(-15px, -10px);
        }

        .slider>.thumb.right {
            right: 25%;
            transform: translate(-15px, -10px);
        }

        .range_slider {
            position: absolute;
            pointer-events: none;
            -webkit-appearance: none;
            z-index: 2;
            height: 10px;
            width: 100%;
            opacity: 0;
        }

        .range_slider::-webkit-slider-thumb {
            pointer-events: all;
            width: 30px;
            height: 30px;
            border-radius: 0;
            border: 0 none;
            background-color: red;
            cursor: pointer;
            -webkit-appearance: none;
        }

        #multi_range {
            margin: 0 auto;
            background-color: #27a0ff;
            border-radius: 20px;
            margin-top: 20px;
            text-align: center;
            width: 140px;
            font-weight: 500;
            font-size: 1.25em;
            color: #fff;
        }
        /* Price range CSS end */


        .form--control, .form-control {
          background-color: #FFFFFF;
        }
    /* Desktop: normal */
@media (min-width: 1200px){
    #filterSidebarCol {
        position: static;
        transform: none !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    .filter-overlay {
        display: none !important;
    }
}

/* Mobile / Tablet */
@media (max-width: 1199.98px){
    #filterSidebarCol {
        position: fixed;
        top: 0;
        left: 0;
        width: 88%;
        max-width: 360px;
        height: 100vh;
        background: #fff;
        z-index: 1055;
        overflow-y: auto;
        padding: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.15);

        /* hidden by default */
        transform: translateX(-105%);
        transition: transform 0.3s ease;
    }

    #filterSidebarCol.show-mobile-filter {
        transform: translateX(0);
    }

    .filter-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1050;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .filter-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    /* optional: prevent body scroll when filter open */
    body.filter-open {
        overflow: hidden;
    }
}
@media (max-width: 1199.98px){
  #filterSidebarCol{
    position: fixed;
    top: 0;
    left: 0;
    width: 88%;
    max-width: 360px;
    height: 100vh;
    background: #fff;
    z-index: 1055;
    overflow-y: auto;
    padding: 15px;
    box-shadow: 0 0 20px rgba(0,0,0,0.15);
    transform: translateX(-105%);
    transition: transform 0.3s ease;

    /* so content doesn't hide under X button */
    padding-top: 55px;
  }

  #filterSidebarCol.show-mobile-filter{
    transform: translateX(0);
  }

  .filter-close-btn{
    position: absolute;
    top: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 10px;
    background: #f1f3f5;
    font-size: 28px;
    line-height: 40px;
    text-align: center;
    cursor: pointer;
    z-index: 1060; /* above sidebar content */
  }
}
    </style>
    @include('pagebuilder::service.service-list-two-style')
@endsection
<!-- Service area starts -->
<section class="new_services_area padding-top-20 padding-bottom-20">
    <div class="container">
        <form method="get" action="{{$current_page_url}}" id="search_service_list_form">
            <div class="row">
                <!-- Mobile Filter Button -->
<div class="d-xl-none mb-3">
                <button class="btn btn-primary w-100" type="button" id="mobileFilterToggle">
                    <i class="las la-filter"></i> {{ __('Filter Services') }}
                </button>
            </div>
                <!--Service Filtering Section Start -->
                <div class="filter-overlay" id="filterOverlay"></div>
                <div class="col-xl-3" id="filterSidebarCol">
                    <button type="button" class="filter-close-btn d-xl-none" id="mobileFilterClose" aria-label="Close filter">
  &times;
</button>
                    <div class="new_serviceDetails__side">
                        <div class="new_serviceDetails__side__item">
                            <div class="service_filter_with_reset mb-3">
                                <h5 class="common-title">{{ __('Service Filter') }} </h5>
                               <a href="{{ url('/service-list') }}">
                                <strong class="text-danger">{{ __('Reset Filter') }} </strong>
                               </a>
                            </div>

                            <!--Search any title filter start -->
                            @if(!empty($service_search_by_text_on_off))
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#textCollapse" data-bs-toggle="collapse" aria-expanded="true">
                                                {{ __('Search By text') }} <i class="las la-angle-down"></i> </a>
                                        </h6>
                                        <div class="collapse show" id="textCollapse">
                                            <div class="single-category-service">
                                                <div class="single-select">
                                                    <input type="text" class="search-input form-control" id="search_by_query"
                                                           placeholder="{{$search_placeholder}}" name="q" value="{{$text_search_value}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!--Search any title filter end -->

                            <!--Distance google map filter -->
                            @if (!empty(get_static_option("google_map_settings")))
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#distanceCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By Location') }}
                                                <i class="las la-angle-down"></i> </a>
                                        </h6>

                                        <div class="collapse show" id="distanceCollapse">
                                            <!-- In person, remotely, all-->
                                            <div class="job_status_wise_section_start mt-2">
                                                <input type="hidden" name="remotely_button_filter" id="remotely_button_filter_value" value="{{$remote_task_title}}">
                                                <input type="hidden" name="all_button_filter_value" id="all_button_filter_value" value="{{$all_button_filter_value}}">
                                                <input type="hidden" name="in_person_filter_value" id="in_person_filter_value" value="{{$in_person_filter_value}}">
                                                <button type="button" class="@if(!empty($in_person_filter_value)) btn btn-primary btn-sm @else btn btn-secondary btn-sm  @endif in_person_button_filter">{{ __('Offline') }} </button>
                                                <button type="button" class="@if(!empty($remote_task_title)) btn btn-primary btn-sm @else btn btn-secondary btn-sm @endif remotely_button_filter" >{{ __('Online') }}</button>
                                                <button type="button" class=" @if(!empty($all_button_filter_value)) btn btn-primary btn-sm @elseif(empty($remote_task_title) && empty($in_person_filter_value)) btn btn-primary btn-sm @else btn btn-secondary btn-sm @endif
                                               all_button_filter">{{ __('All') }} </button>
                                            </div>

                                            <!-- autocomplete address -->
                                            <div class="suburb_section_start mt-2 mb-3">
                                                <input type="hidden" name="autocomplete_address" id="autocomplete_address">
                                                <input type="hidden" name="location_city_name" id="location_city_name">
                                                <input type="hidden" name="latitude" id="latitude">
                                                <input type="hidden" name="longitude" id="longitude">
                                                <label>{{ __('Location') }}</label>
                                                <input class="search-input form-control w-100 border-1 bg-white autocomplete_disable" name="autocomplete" id="autocomplete" placeholder="{{ __('Enter a Location') }}" type="text">
                                            </div>

                                            <!-- Distance range-->
                                            <div id="distance-slider"></div>
                                            <div class="slider-container m-2">
                                                <input type="hidden" name="distance_kilometers_value" id="distance_kilometers_value">
                                                <strong class="mb-2">{{__('Distance')}}</strong>
                                                <div id="slider" class="slider-range mt-2"></div>
                                                <div id="slider-value" class="slider-range-value mt-2"></div>
                                                <span class="km_title_text" style="display: flex; margin-left: 23px; margin-top: -21px;">{{ __('km') }}</span>
                                            </div>

                                            <!-- cancel and apply button start -->
                                            <div class="cancel_apply_section_start text-end mb-2">
                                                <button type="button" class="submit-btn btn-sm" id="distance_wise_filter_apply">{{ __('Filter') }}</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endif
                            <!--google map Distance filter end -->

                            <!--price range filter -->
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#distanceCollapseAnyPrice" data-bs-toggle="collapse" aria-expanded="false" class="toggle-collapse">
                                                {{ __('Search By Price') }} <i class="las la-angle-down"></i>
                                            </a>
                                        </h6>

                                        <div class="collapse show" id="distanceCollapseAnyPrice">
                                            <input type="hidden" name="price_range_value" id="price_range_value">
                                            <div class="middle">
                                                <div id="multi_range" class="mb-3">
                                                     <span id="currency">
                                                      <strong>{{ site_currency_symbol() }}</strong>
                                                     <span id="left_value"> {{ $min_price }}</span>
                                                     </span>
                                                    <span> ~ </span>
                                                    <span id="currency">
                                                    <strong>{{ site_currency_symbol() }}</strong>
                                                    <span id="right_value">{{ $max_price }}</span>
                                                    </span>
                                                </div>
                                                <div class="multi-range-slider my-2">
                                                    <input type="range" id="input_left" class="range_slider" min="1" max="{{$max_price}}" value="{{ $min_price }}">
                                                    <input type="range" id="input_right" class="range_slider" min="1" max="{{$max_price}}" value="{{ $max_price }}">
                                                    <div class="slider">
                                                        <div class="track"></div>
                                                        <div class="range"></div>
                                                        <div class="thumb left" ></div>
                                                        <div class="thumb right"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- cancel and apply button start -->
                                            <div class="cancel_apply_section_start text-end mt-3 mb-2">
                                                <button type="button" class="submit-btn btn-sm" id="price_wise_filter_apply">{{ __('Filter') }}</button>
                                            </div>
                                            <!-- End of cancel and apply button -->
                                        </div>

                                    </div>
                                </div>
                            <!--price range filter end -->

                            <!--Country filter start -->
                            @if(empty(get_static_option("google_map_settings")))
                                @if(!empty($country_on_off))
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#countryCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By Country') }} <i class="las la-angle-down"></i> </a>
                                        </h6>
                                        <div class="collapse show" id="countryCollapse">
                                            <div class="">
                                                <div class="single-category-service">
                                                    <div class="single-select">
                                                        <select id="search_by_country" name="country">
                                                            <option value="">{{$country_text}}</option>
                                                            @foreach ($countries as $cont)
                                                              <option @if(!empty(request()->get("country")) && request()->get("country") == $cont->id ) selected @endif  value="{{$cont->id}}">{{$cont->country}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endif
                            <!--Country filter end -->


                            <!--City filter start -->
                            @if(empty(get_static_option("google_map_settings")))
                            @if(!empty($city_on_off))
                            <div class="new_serviceDetails__side__author">
                                <div class="new_serviceDetails__side__author__contents">
                                    <h6 class="new_packageBook__addFeature__title">
                                        <a href="#cityCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By City') }} <i class="las la-angle-down"></i> </a>
                                    </h6>
                                    <div class="collapse show" id="cityCollapse">
                                        @php  $fetch_cities = '';  @endphp
                                        @if ($country_on_off !== "on")
                                           @php
                                               $get_service_city_id = $all_services->pluck('service_city_id');
                                                $all_cities = \App\ServiceCity::whereIn("id", $get_service_city_id)->where("status", 1)->get();
                                                foreach ($all_cities as $cities) {
                                                    $fetch_cities .=  "<option selected value=" .  $cities->id .   ">" . $cities->service_city .  "</option>";
                                                }
                                           @endphp
                                        @endif
                                        <div class="single-category-service">
                                            <div class="single-select">
                                                <select id="search_by_city" name="city">
                                                    <option value=""> {{$city_text}}</option>
                                                    @foreach ($services_city as $service_city) {
                                                      <option @if(!empty(request()->get("city")) && request()->get("city") == $service_city->id) selected @endif
                                                      value="{{$service_city->id}}">{{$service_city->service_city}}</option>
                                                    @endforeach
                                                    {{ $fetch_cities }}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endif
                            <!--Country filter end -->

                            <!--Area filter start -->
                            @if(empty(get_static_option("google_map_settings")))
                                @if(!empty($area_on_off))
                                    <div class="new_serviceDetails__side__author">
                                        <div class="new_serviceDetails__side__author__contents">
                                            <h6 class="new_packageBook__addFeature__title">
                                                <a href="#areaCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By Area') }} <i class="las la-angle-down"></i> </a>
                                            </h6>
                                            <div class="collapse show" id="areaCollapse">
                                                <div class="single-category-service">
                                                    <div class="single-select">
                                                        <select id="search_by_area" name="area">
                                                            <option value=""> {{$city_text}}</option>

                                                            @foreach ($services_area as $service_area) {
                                                              <option @if(!empty(request()->get("area")) && request()->get("area") == $service_area->id) selected @endif
                                                              value="{{$service_area->id}}">{{$service_area->service_area}}</option>
                                                            @endforeach
                                                                {{ $fetch_cities ?? 0 }}
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <!--Area filter end -->


                            <!--Category filter start -->
                            @if(!empty($category_on_off))
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#categoryCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By Category') }} <i class="las la-angle-down"></i> </a>
                                        </h6>
                                        <div class="collapse show" id="categoryCollapse">
                                            <div class="single-category-service">
                                                <div class="single-select">
                                                    <select id="search_by_category" name="cat">
                                                        <option value="">{{$category_text}}</option>
                                                        @foreach($categories as $cat)
                                                            <option @if(!empty(request()->get("cat")) && request()->get("cat") == $cat->id) selected @endif value="{{$cat->id}}">{{$cat->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!--Category filter end -->


                            <!--Sub Category filter start -->
                            @if(!empty($subcategory_on_off))
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#cubCategoryCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By Sub-Category') }} <i class="las la-angle-down"></i> </a>
                                        </h6>
                                        <div class="collapse show" id="cubCategoryCollapse">
                                            <div class="single-category-service">
                                                <div class="single-select">
                                                    <select id="search_by_subcategory" name="subcat">
                                                        <option value="">{{$subcategory_text}}</option>
                                                        @foreach($sub_categories as $sub_cat)
                                                            <option @if(!empty(request()->get("subcat")) && request()->get("subcat") == $sub_cat->id) selected @endif value="{{$sub_cat->id}}">{{$sub_cat->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!--Sub Category filter end -->

                            <!--Child Category filter start -->
                            @if(!empty($subcategory_on_off))
                                <!--<div class="new_serviceDetails__side__author">-->
                                <!--    <div class="new_serviceDetails__side__author__contents">-->
                                <!--        <h6 class="new_packageBook__addFeature__title">-->
                                <!--            <a href="#childCategoryCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By Child Category') }} <i class="las la-angle-down"></i> </a>-->
                                <!--        </h6>-->
                                <!--        <div class="collapse show" id="childCategoryCollapse">-->
                                <!--            <div class="single-category-service">-->
                                <!--                <div class="single-select">-->
                                <!--                    <select id="search_by_child_category" name="child_cat">-->
                                <!--                        <option value="">{{$child_category_text}}</option>-->
                                <!--                        @foreach($child_categories as $child_cat)-->
                                <!--                            <option @if(!empty(request()->get("child_cat")) &&  request()->get("child_cat") == $child_cat->id) selected @endif value="{{$child_cat->id}}">{{$child_cat->name}}</option>-->
                                <!--                        @endforeach-->
                                <!--                    </select>-->
                                <!--                </div>-->
                                <!--            </div>-->
                                <!--        </div>-->
                                <!--    </div>-->
                                <!--</div>-->
                            @endif
                            <!--Child Category filter end -->


                            <!--Rating star filter start -->
                            @if(!empty($subcategory_on_off))
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#starRatingCollapse" data-bs-toggle="collapse" aria-expanded="true">  {{ __('Search By Rating') }} <i class="las la-angle-down"></i> </a>
                                        </h6>
                                        <div class="collapse show" id="starRatingCollapse">
                                            <div class="single-category-service">
                                                <div class="single-select">
                                                    <select id="search_by_rating" name="rating">
                                                        <option value="">{{ __("Select Rating Star") }}</option>
                                                        @foreach($rating_stars as $value => $text)
                                                            <option @if(!empty(request()->get("rating")) && request()->get("rating") == $value) selected @endif value="{{$value}}">{{$text}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!--Rating star filter end -->

                            <!-- Sort-by filter start -->
                            @if(!empty($sort_by_on_off))
                                <div class="new_serviceDetails__side__author">
                                    <div class="new_serviceDetails__side__author__contents">
                                        <h6 class="new_packageBook__addFeature__title">
                                            <a href="#sortbyCollapse" data-bs-toggle="collapse" aria-expanded="false">  {{ __('Search By Sort-by') }} <i class="las la-angle-down"></i> </a>
                                        </h6>
                                        <div class="collapse show" id="sortbyCollapse">
                                            <div class="single-category-service">
                                                <div class="single-select">
                                                    <select id="search_by_sorting" name="sortby">
                                                        <option value="">{{ __("Sort By") }}</option>
                                                        @foreach($sortby_search as $value => $text)
                                                            <option @if(!empty(request()->get("sortby")) && request()->get("sortby") == $value) selected @endif value="{{$value}}">{{$text}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!-- Sort-by star filter end -->

                        </div>
                    </div>
                </div>
                <!--Service Filtering Section end -->


                <!--All Service List Section Start -->
                @if (!empty(get_static_option("google_map_settings")))
                    <!--google map section -->
                    <div class="col-xl-9">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- loader -->
                                <div class="loader-container">
                                    <div class="loader"></div>
                                </div>

                                <!--google map section start -->
                                <div class="service-locationMap" id="map-container">
                                    <div class="fullwidth-sidebar-container">
                                        <div class="sidebar top-sidebar">
                                            <!--<div id="map-canvas" style="height: 400px; width: 100%; position: relative; overflow: hidden;">-->
                                            <!--</div>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                     <div class="row mt-4">
                    @if ($all_services->count() > 0)
                        @foreach ($all_services as $service)
                     
                                    <div class="{{$columns ?? 'col-lg-4'}} mt-3">
                                        <div class="new_service__single {{ $google_map_style_class }}">
                                            <div class="new_service__single__thumb">
                                                <a href="{{route("service.list.details", $service->slug)}}">
                                                    {!! render_image_markup_by_attachment_id($service->image, '','','thumb'); !!}
                                                </a>
                                                @if ($service->featured == 1)
                                                    <div class="award_icons">
                                                        <!--<a href="javascript:void(0)" class="award_icons__item">-->
                                                        <!--    <i class="las la-award"></i>-->
                                                        <!--</a>-->
                                                        <span class="badge bg-warning">Promoted </span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="new_service__single__contents">

                                             <span class="new_jobs__single__contents__location mb-2">
                                              <i class="fa-solid fa-location-dot"></i>
                                                 {{ sellerServiceLocation($service) }}
                                             </span>

                                                <h5 class="new_service__single__contents__title">
                                                    <a href="{{ route('service.list.details',$service->slug) }}">{{ $service->title }}</a></h5>
                                                <div class="new_service__single__price">
                                                    <span class="new_service__single__price__starting"> {{ $static_text['start_at'] ?? __('Starting at') }} </span>
                                                    <h5 class="new_service__single__price__title mt-1">
                                                       @if(is_null($service->starting_price))
                                                         {{ amount_with_currency_symbol($service->price) }}
                                                            @else
                                                          {{ amount_with_currency_symbol($service->starting_price) }}
                                                            @endif
                                                         </h5>
                                                </div>
                                                <div class="author_tag border_top">
                                                    <a href="{{ route('about.seller.profile',optional($service->seller)->username) }}" class="single_authors">
                                                        <div class="single_authors__thumb">
                                                            {!! render_image_markup_by_attachment_id(optional($service->seller)->image,'','','thumb') !!}
                                                            <span class="notification-dot"></span>
                                                        </div>
                                                        <span class="single_authors__title"> {{ optional($service->seller)->username }} </span>
                                                          @if(optional($service->seller->sellerVerify)->status==1)
                                            <div data-toggle="tooltip" data-placement="top" title="{{__('This seller is verified by the site admin according his national id card.')}}">
                                                <span class="seller-verified"> <i class="las la-check"></i> </span>
                                            </div>
                                            @endif
                                                             <div class="single_authors__thumb">
                                 @php 
$value = \Modules\Subscription\Entities\SellerSubscription::where(['seller_id' =>$service->seller->id])->first('subscription_id');
if($value != NULL){
if($value->subscription_id != NULL){
$image = \Modules\Subscription\Entities\Subscription::where(['id' =>$value->subscription_id])->first('image');
    }
    }
@endphp                               @if($value != NULL)
                                    @if($value->subscription_id != NULL)
                                     @if($image->image != NULL)
                                {!!  render_image_markup_by_attachment_id($image->image) !!}
                                    @endif
                                    @endif
                                    @endif
                                </div>
                                                    </a>
                                                </div>
                                                
                                                <div class="btn-wrapper border_top">
                                                    <a href="{{ route("service.list.book", $service->slug) }}" class="cmn-btn btn-outline-border w-100 radius-5"> {{ $book_now_text }} </a>
                                                </div>
                                            </div>
                                        </div>

                                  </div>
                        @endforeach
                             

                      </div>
                     </div>
                    @else
                        <!--google map section start -->
                    <div class="row">
                        <div class="col-xl-9">
                            <div class="justify-content-end mt-5">
                              <h5 class="common-title text-danger">{{ __('no service found') }}</h5>
                            </div>
                        </div>
                    </div>
                    @endif


                @else
                    <!--not google map  -->
                    <div class="col-xl-9">
                        <div class="row g-4">
                    @if ($all_services->count() > 0)
                        @foreach ($all_services as $service)
                      
                                    <div class="{{ $columns ?? 'col-lg-4' }}">
                                        <div class="new_service__single {{ $google_map_style_class }}">
                                            <div class="new_service__single__thumb">
                                                <a href="{{route("service.list.details", $service->slug)}}">
                                                    {!! render_image_markup_by_attachment_id($service->image, '','','thumb'); !!}
                                                </a>

                                                @if ($service->featured == 1)
                                                    <div class="award_icons">
                                                        <!--<a href="javascript:void(0)" class="award_icons__item">-->
                                                        <!--    <i class="las la-award"></i>-->
                                                        <!--</a>-->
                                                          <span class="badge bg-warning">Promoted </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="new_service__single__contents">

                                             <span class="new_jobs__single__contents__location mb-2">
                                              <i class="fa-solid fa-location-dot"></i>
                                                 {{ sellerServiceLocation($service) }}
                                             </span>

                                                <h5 class="new_service__single__contents__title">
                                                    <a href="{{ route('service.list.details',$service->slug) }}">{{ $service->title }}</a></h5>
                                                <div class="new_service__single__price">
                                                    <span class="new_service__single__price__starting"> {{ $static_text['start_at'] ?? __('Starting at') }} </span>
                                                    <h5 class="new_service__single__price__title mt-1"> {{ amount_with_currency_symbol($service->price) }} </h5>
                                                </div>
                                                <div class="author_tag border_top">
                                                    <a href="{{ route('about.seller.profile',optional($service->seller)->username) }}" class="single_authors">
                                                        <div class="single_authors__thumb">
                                                            {!! render_image_markup_by_attachment_id(optional($service->seller)->image,'','','thumb') !!}
                                                            <span class="notification-dot"></span>
                                                        </div>
                                                        <span class="single_authors__title"> {{ optional($service->seller)->username }} </span>
                                                    </a>
                                                      @if(optional($service->seller->sellerVerify)->status==1)
                                            <div data-toggle="tooltip" data-placement="top" title="{{__('This seller is verified by the site admin according his national id card.')}}">
                                                <span class="seller-verified"> <i class="las la-check"></i> </span>
                                            </div>
                                            @endif
                                                         <div class="single_authors__thumb">
                                 @php 
$value = \Modules\Subscription\Entities\SellerSubscription::where(['seller_id' =>$service->seller->id])->first('subscription_id');
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
                                                    <div class="author_tag__review radius-5">
                                                        @php
                                                            $total_review = optional($service->reviews->where('type', 1));
                                                            $total_count = $total_review ->count();
                                                            $rating = round($total_review->avg('rating'),1);
                                                       @endphp

                                                        <a href="javascript:void(0)" class="author_tag__review__para"> {!! ratting_star($rating) !!} {{ $total_count }} </a>
                                                    </div>
                                                </div>
                                                <div class="btn-wrapper border_top">
                                                    <a href="{{ route("service.list.book", $service->slug) }}" class="cmn-btn btn-outline-border w-100 radius-5"> {{ $book_now_text }} </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                        @endforeach
                              
                        </div>
                    </div>
                    @else
                        <div class="row">
                            <div class="col-xl-9 mt-5">
                                <h5 class="common-title text-danger">{{ __('no service found') }}</h5>
                            </div>
                        </div>
                    @endif
                @endif
                <!--All Service List Section end -->
            </div>
        </form>
    </div>
</section>

<!-- Service area end -->
@section('scripts')
@if (!empty(get_static_option("google_map_settings")))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/14.6.3/nouislider.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{$google_api_key}}&libraries=places">
    <script defer src="//cdn.jsdelivr.net/npm/markerclustererplus/dist/markerclusterer.min.js"> </script>
    <script>
        // Wait for the page to fully load
        window.addEventListener('load', function() {
            var loaderContainer = document.querySelector('.loader-container');
            var mapContainer = document.getElementById('map-container');
            loaderContainer.style.display = 'none';
            mapContainer.style.display = 'block';
        });

        // goolge map html markup show section
        let book_now_title = @json($book_now_text);
        function generateContent(place){
            var content = `<div class=\"single-service service-map-style no-margin wow\">
                      <a href=\"{{$service_details_route}}/`+place.slug+`\" class=\"service-thumb service-bg-thumb-format\" `+place.image_url+`>
                  </a>
                  <div class=\"services-contents\">
                      <h5 class=\"common-title map-view-service-title mt-2\"> <a href=\"{{$service_details_route}}/`+place.slug+`\" title=\"View: `+place.title+`\"> `+place.title+` </a> </h5>
                      <div class=\"service-price\">
                          <span class=\"starting\"> Starting at </span>
                          <span class=\"prices\">`+place.service_main_price+`</span>
                      </div>
                      <div class=\"btn-wrapper d-flex flex-wrap\">
                          <a href=\"{{$service_book_route}}/`+place.slug+`\" class=\"cmn-btn btn-small btn-bg-1\"> `+book_now_title+` </a>
                      </div>
                  </div>
              </div>`;

            return content;
        }

        var map;
        var markers = [];
        var infowindow = new google.maps.InfoWindow();
        var places = @json($all_services_list_json);

        // first check lat, long if lat long not empty map initialize play
        var latitude;
        var longitude;
        @if(!empty($latitude) && !empty($longitude))
            latitude = '{{$latitude}}'
            longitude = '{{$longitude}}'
        @else
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;
                    // local storage
                    localStorage.setItem('latitude', latitude);
                    localStorage.setItem('longitude', longitude);
                }, function (error) {
                    console.error('Error getting location:', error);
                    // Set default values in case of an error
                    latitude = 0;
                    longitude = 0;
                });
            }
                latitude = localStorage.getItem('latitude');
                longitude = localStorage.getItem('longitude');
        @endif



        var centerLatLng = new google.maps.LatLng(latitude, longitude);
        function initialize() {
            var mapOptions = {
                zoom: 12,
                // minZoom: 2,
                // maxZoom: 20,
                zoomControl: true,
                zoomControlOptions: {
                    style: google.maps.ZoomControlStyle.DEFAULT
                },
                center: centerLatLng,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                scrollwheel: true,
                panControl: true,
                mapTypeControl: true,
                scaleControl: true,
                overviewMapControl: true,
                rotateControl: true,
            };
           // map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            // all markers show this map
            addMarkers();
            initializeRangeSlider();
        }



        google.maps.event.addDomListener(window, 'load', initialize);

        // empty check for online services
        @if(empty($online_check_service))
            function addMarkers() {
                var min = 0.999999;
                var max = 1.000001;

                for (var place in places) {
                    place = places[place];

                    if (place.seller !== null && place.seller.latitude && place.seller.longitude) {
                        var image = new google.maps.MarkerImage("//docs.maptiler.com/openlayers/default-marker/marker-icon.png", null, null, null, new google.maps.Size(40, 52));

                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(
                                place.seller.latitude * (Math.random() * (max - min) + min),
                                place.seller.longitude * (Math.random() * (max - min) + min)
                            ),
                            map: map,
                            title: place.title,
                            icon: image,
                        });

                        markers.push(marker);
                        google.maps.event.addListener(marker, 'click', (function (marker, place) {
                            return function () {
                                map.setZoom(20);
                                infowindow.setContent(generateContent(place));
                                infowindow.open(map, marker);
                            };
                        })(marker, place));
                    }
                }
            }
        @endif

        function initializeRangeSlider() {
            var slider = document.getElementById('slider');
            var sliderValue = document.getElementById('slider-value');

            noUiSlider.create(slider, {
                start: {{ !empty($radius) ? $radius : 50 }},
                range: {
                    'min': 1,
                    'max': 150
                }
            });

            slider.noUiSlider.on('update', function (values) {
                var newValue = Math.round(values[0]);
                sliderValue.innerHTML = newValue;
            });
        }
    </script>

    <script>
        (function($){
            "use strict";
            $(document).ready(function(){

                // Function to handle filter button clicks
                function handleFilterButtonClick(button) {
                    $('.job_status_wise_section_start button').removeClass('btn-primary').addClass('btn-secondary');
                    $(button).removeClass('btn-secondary').addClass('btn-primary');
                }

                var check_distance_range_slider = false;
                $(".in_person_button_filter").click(function() {
                    $('#all_button_filter_value').val('');
                    $('#remotely_button_filter_value').val('');
                    $('#in_person_filter_value').val('in_person');
                    handleFilterButtonClick(this);
                    $('.autocomplete_disable').prop('disabled', false);
                    $('.autocomplete_disable').removeClass('address-input-background-color');

                    if (check_distance_range_slider === true) {
                        if(typeof slider === 'undefined' || !$('.slider-range').hasClass('noUi-target') ) {
                            initializeRangeSlider();
                        }
                        $('#slider').show();
                        $('.slider-range-value').show();
                        $('.km_title_text').show();
                    }

                });


                // Remote tasks wise filter jobs start
                var remotely_filter_check = $('#remotely_button_filter_value').val();
                if (remotely_filter_check !== '') {
                    $('#all_button_filter_value').val('');
                    $('#in_person_filter_value').val('');
                    $('#remotely_button_filter_value').val('remotely');
                    $('.autocomplete_disable').prop('disabled', true);
                    $('.autocomplete_disable').addClass('address-input-background-color');

                    initializeRangeSlider();
                    // Check if the slider object exists before trying to destroy it
                    if (typeof slider !== 'undefined') {
                        slider.noUiSlider.destroy();
                        $('#slider').hide();
                        $('.slider-range-value').hide();
                        $('.km_title_text').hide();
                        check_distance_range_slider = true;
                    }

                }


                //  remotely  jobs filter
                $(".remotely_button_filter").click(function() {

                    // empty lat, long value
                    $('#latitude').val('');
                    $('#longitude').val('');

                    $('#all_button_filter_value').val('');
                    $('#in_person_filter_value').val('');
                    $('#remotely_button_filter_value').val('remotely');
                    handleFilterButtonClick(this);
                    $('.autocomplete_disable').prop('disabled', true);
                    $('.autocomplete_disable').addClass('address-input-background-color');
                    // Disable the distance slider
                    // initializeRangeSlider();
                    slider.noUiSlider.destroy();
                    $('.slider-range-value').hide();
                    $('.km_title_text').hide();
                    check_distance_range_slider = true;
                });
                // Remote tasks wise filter jobs end



                // google map all jobs filter
                $(".all_button_filter").click(function() {
                    handleFilterButtonClick(this);
                    $('#remotely_button_filter_value').val('');
                    $('#in_person_filter_value').val('');
                    $('#all_button_filter_value').val('all_filter_jobs');

                    $('.autocomplete_disable').prop('disabled', false);
                    $('.autocomplete_disable').removeClass('address-input-background-color');

                    if (check_distance_range_slider === true) {
                        if(typeof slider === 'undefined' || !$('.slider-range').hasClass('noUi-target') ) {
                            initializeRangeSlider();
                        }
                        $('#slider').show();
                        $('.slider-range-value').show();
                        $('.km_title_text').show();
                    }


                });


                //========google map autocomplete address start
                // Initialize Google Places autocomplete
                var input = document.getElementById('autocomplete');
                var autocomplete = new google.maps.places.Autocomplete(input);

                // Get current location name and lat/long
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;

                    // Reverse geocode to get location name
                    var geocoder = new google.maps.Geocoder();
                    var latlng = new google.maps.LatLng(lat, lng);

                    geocoder.geocode({ 'location': latlng }, function(results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            if (results[0]) {
                                // Extract city and division
                                var addressComponents = results[0].address_components;
                                var city = '';
                                var division = '';

                                for (var i = 0; i < addressComponents.length; i++) {
                                    var component = addressComponents[i];
                                    if (component.types.includes('locality')) {
                                        city = component.long_name;
                                    } else if (component.types.includes('administrative_area_level_1')) {
                                        division = component.long_name;
                                    }
                                }

                                // Format as "City, Division"
                                var formattedLocation = city + ', ' + division;

                                @if(!empty($location_city_name))
                                   var city_name_formatted_location = `{{$location_city_name}}`;
                                @else
                                   var city_name_formatted_location = city;
                                @endif


                                // set address in input box current location
                                @if(!empty($autocomplete_address))
                                    input.value = `{{$autocomplete_address}}`;
                                @else
                                    input.value = formattedLocation;
                                @endif

                                if(formattedLocation){
                                    $('#location_city_name').val(city);

                                    $('#latitude').val(lat);
                                    $('#longitude').val(lng);


                                    // Set the filter title by combining the distance and formatted location by Hasib
                                    var distance_set_default = `{{ $distance_radius_km_get ?? 50 }}`;
                                    var in_person_filter_value_get = `{{$in_person_filter_value}}`;

                                    if(in_person_filter_value_get === ''){
                                        $('.distance_wise_filter_title').text(`${distance_set_default}km ${city_name_formatted_location} & remotely`);
                                    }else {
                                        $('.distance_wise_filter_title').text(`${distance_set_default}km ${city_name_formatted_location}`);
                                    }
                                }


                            } else {
                                console.error('No results found');
                            }
                        } else {
                            console.error('Geocoder failed due to: ' + status);
                        }
                    });
                });


                // Define the options for the autocomplete
                // if change country, get Restricted value
                @php
                    $countryCodes = \App\Country::where('status', 1)->pluck('country_code')->toArray();
                    $countryCodesStr = implode(',', $countryCodes);
                @endphp
                var countryCodesStr = "{{ $countryCodesStr }}";
                var countryCodesArray = countryCodesStr.split(',');
                var autocompleteOptions = {
                    types: ['(regions)'],
                    componentRestrictions: { country: countryCodesArray }
                };

                // Initialize the autocomplete with the options
                var autocomplete = new google.maps.places.Autocomplete(
                    document.getElementById('autocomplete'),
                    autocompleteOptions
                );


                // Autocomplete address get
                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();
                    if (!place.geometry) {
                        return;
                    }
                    var suburb = place.name;
                    var lat = place.geometry.location.lat();
                    var lng = place.geometry.location.lng();


                    var city_name = '';
                    for (var i = 0; i < place.address_components.length; i++) {
                        var component = place.address_components[i];
                        if (component.types.includes('locality')) {
                            city_name = component.long_name;
                            break;
                        }
                    }

                    // set lat long value
                    if(suburb){
                        $('#location_city_name').val(city_name);
                        $('#latitude').val(lat);
                        $('#longitude').val(lng);
                    }
                });
                //========== google map autocomplete address end

                // google map distance, current location, autocomplete address wise filter jobs
                $("#distance_wise_filter_apply").click(function() {

                    let get_lan_value = $('#latitude').val();
                    let get_long_value = $('#longitude').val();

                    let distance_km_value = $('#slider-value').text();
                    $('#distance_kilometers_value').val(distance_km_value);

                    // get autocomplete address old value get
                    let get_autocomplete_value = $('#autocomplete').val();
                    $('#autocomplete_address').val(get_autocomplete_value);

                    $('#search_service_list_form').trigger('submit');
                });

            });
        })(jQuery);
    </script>
@endif

<script>
    (function($){
        "use strict";
        $(document).ready(function(){

            //============== price_wise_filter_apply apply =========
            $(document).on('click', '#price_wise_filter_apply', function (){
                let left_value = $('#left_value').text();
                let right_value = $('#right_value').text();
                $('#price_range_value').val(left_value + ',' + right_value);
                $('#search_service_list_form').trigger('submit');
            });

            //============= price range slider start
            const maxPrice = {{ $max_price }};
            const numSteps = maxPrice;
            const steps = Array.from({ length: numSteps }, (_, index) => index + 1);

            const $inputLeft = $("#input_left");
            const $inputRight = $("#input_right");
            const $thumbLeft = $(".thumb.left");
            const $thumbRight = $(".thumb.right");
            const $range = $(".slider > .range");

            function setLeftValue(value) {
                const step = getClosestStep(value);
                $inputLeft.val(step);
                updateThumbAndRange($inputLeft, $thumbLeft, $range);
                leftSlider(step);
            }

            function setRightValue(value) {
                const step = getClosestStep(value);
                $inputRight.val(step);
                updateThumbAndRange($inputRight, $thumbRight, $range);
                rightSlider(step);
            }

            function getClosestStep(value) {
                let closestStep = steps[0];
                let minDiff = Math.abs(value - steps[0]);

                for (const step of steps) {
                    const diff = Math.abs(value - step);
                    if (diff < minDiff) {
                        minDiff = diff;
                        closestStep = step;
                    }
                }

                return closestStep;
            }

            function updateThumbAndRange($input, $thumb, $range) {
                const min = parseInt($input.prop("min"));
                const max = parseInt($input.prop("max"));
                const value = parseInt($input.val());

                const percent = ((value - min) / (max - min)) * 100;
                $thumb.css("left", percent + "%");
                $range.css("left", percent + "%");
            }

            $inputLeft.on("input", function() {
                setLeftValue(parseInt($inputLeft.val()));
            });

            $inputRight.on("input", function() {
                setRightValue(parseInt($inputRight.val()));
            });

            function leftSlider(value) {
                $("#left_value").html(value);
            }

            function rightSlider(value) {
                $("#right_value").html(value);
            }

            // Initial setup
            setLeftValue(parseInt($inputLeft.val()));
            setRightValue(parseInt($inputRight.val()));
            //============= price range slider end


        });
    })(jQuery);
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const filterBtn  = document.getElementById("mobileFilterToggle");
  const filterCol  = document.getElementById("filterSidebarCol");
  const overlay    = document.getElementById("filterOverlay");
  const closeBtn   = document.getElementById("mobileFilterClose");

  const openFilter = () => {
    filterCol.classList.add("show-mobile-filter");
    overlay.classList.add("show");
    document.body.classList.add("filter-open");
  };

  const closeFilter = () => {
    filterCol.classList.remove("show-mobile-filter");
    overlay.classList.remove("show");
    document.body.classList.remove("filter-open");
  };

  if (filterBtn) filterBtn.addEventListener("click", openFilter);
  if (overlay) overlay.addEventListener("click", closeFilter);
  if (closeBtn) closeBtn.addEventListener("click", closeFilter);
});
</script>
@endsection