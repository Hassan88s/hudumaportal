@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Edit Job Post') }}
@endsection
@section('style')
    <x-summernote.css/>
    <x-media.css/>
    <link rel="stylesheet" href="{{asset('assets/backend/css/bootstrap-tagsinput.css')}}">
    <link rel="stylesheet" href="{{asset('assets/common/css/flatpickr.min.css')}}">
    <style>

        .single-dashboard-input {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: flex-start;
            -ms-flex-align: flex-start;
            align-items: flex-start;
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
            width: 100%;
            gap: 24px;
        }

        @media (min-width: 320px) and (max-width: 991.98px) {
            .single-dashboard-input {
                -ms-flex-wrap: wrap;
                flex-wrap: wrap;
                gap: unset;
            }
        }

        @media only screen and (max-width: 575.98px) {
            .single-dashboard-input {
                display: block;
            }
        }

        .single-dashboard-input .single-info-input {
            width: 100%;
        }

        .single-dashboard-input .single-info-input .info-title {
            display: block;
            font-family: var(--heading-font);
            color: var(--heading-color);
            font-size: 16px;
            font-weight: 600;
            line-height: 26px;
            margin-bottom: 7px;
        }

        .single-dashboard-input .single-info-input .form--control {
            font-size: 14px;
            line-height: 22px;
            color: var(--light-color);
            height: 60px;
            border: 1px solid #dddddd;
            border-radius: 5px;
            padding: 13px 20px;
            width: 100%;
        }

        .single-dashboard-input .single-info-input .form--control::-webkit-input-placeholder {
            font-size: 14px;
            color: var(--extra-light-color);
        }

        .single-dashboard-input .single-info-input .form--control::-moz-placeholder {
            font-size: 14px;
            color: var(--extra-light-color);
        }

        .single-dashboard-input .single-info-input .form--control:-ms-input-placeholder {
            font-size: 14px;
            color: var(--extra-light-color);
        }

        .single-dashboard-input .single-info-input .form--control::-ms-input-placeholder {
            font-size: 14px;
            color: var(--extra-light-color);
        }

        .single-dashboard-input .single-info-input .form--control::placeholder {
            font-size: 14px;
            color: var(--extra-light-color);
        }

        .single-dashboard-input .single-info-input .textarea--form {
            padding: 20px;
            height: 162px;
        }
        .online_checkbox__title {
            color: var(--heading-color);
            font-size: 16px;
            font-weight: 700;
        }

        .form--control, .form-control {
            background-color: #FFFFFF!important;
        }
        .custom_radio__single {
    -webkit-transition: 0.3s;
    transition: 0.3s;
    cursor: pointer;
    display: -webkit-inline-box;
    display: -ms-inline-flexbox;
    display: inline-masonry;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    gap: 24px;
}
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @include('frontend.user.buyer.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <x-msg.error/>
                <x-msg.success/>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="dashboard-settings margin-top-40">
                            <h4 class="dashboards-title"> {{__('Edit Job Post')}} </h4>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="dashboard-settings margin-top-40">
                            <strong class="online_checkbox__title">@if($job->is_job_online == 1) {{ __('Job Type: Online') }} @else {{ __('Job Type: Offline') }} @endif</strong>
                            <label class="custom_switch" for="check_if_job_is_online">
                                <input class="switch_input service_on_off_btn" id="check_if_job_is_online" type="checkbox" @if($job->is_job_online == 1) checked @endif >
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <form action="{{route('buyer.edit.job',$job->id)}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-xxl-6">
                            <div class="single-settings">

                                <div class="single-dashboard-input mt-2">
                                    <div class="single-info-input">
                                        <label for="title" class="info-title"> {{__('Job Title')}} <span class="text-danger">*</span></label>
                                        <input class="form--control" value="{{ $job->title }}" name="title" id="title" type="text" placeholder="{{__('Add title')}}">
                                    </div>
                                </div>

                                <div class="single-dashboard-input mt-2">
                                    <div class="single-dashboard-input">
                                        <div class="single-info-input margin-top-30 permalink_label">
                                            <label for="title" class="info-title text-dark"> {{__('Permalink')}} <span class="text-danger">*</span></label>
                                            <span id="slug_show" class="display-inline">{{url('/job/details/'.$job->slug)}}</span>
                                            <span id="slug_edit" class="display-inline"></span>
                                            <button class="btn btn-warning btn-sm slug_edit_button">  <i class="las la-edit"></i> </button>

                                            <input class="form--control service_slug" name="slug" value="{{$job->slug}}" id="slug" style="display: none" type="text">
                                            <button class="btn btn-info btn-sm slug_update_button mt-2" style="display: none">{{__('Update')}}</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="single-dashboard-input mt-2">
                                    <div class="single-info-input">
                                        <label for="price" class="info-title"> {{__('Budget')}} </label>
                                        <input class="form--control" value="{{ $job->price }}" name="price" id="price" type="number" placeholder="{{__('Add Price')}}">
                                    </div>
                                    <div class="single-info-input">
                                        <label for="dead_line" class="info-title"> {{__('Deadline to Apply for this job')}} </label>
                                        <input class="form--control" value="{{ $job->dead_line }}" name="dead_line" id="dead_line" type="text" placeholder="{{__('Dead Line')}}">
                                    </div>
                                     <div class="single-info-input">
                                        <label for="dead_line" class="info-title"> {{__('Delivery Days')}} </label>
                                        <input class="form--control" value="{{ $job->Days }}" name="Days" id="dead_line" type="text" placeholder="{{__('Days')}}">
                                    </div>
                                </div>
                                
                                
                                
                                                        @if(Auth::user()->is_company == '1')
                            <div class="single-dashboard-input mt-4">
                                <div class="single-info-input">
                                    <label for="no_of_hiring" class="info-title"> 
                                        {{ __('No of Hiring') }} <span class="text-danger">*</span> 
                                    </label>
                                    <input class="form--control" 
                                           value="{{ $job->no_of_hiring }}" 
                                           name="no_of_hiring" 
                                           id="no_of_hiring" 
                                           type="number" 
                                           min="1" 
                                           placeholder="{{ __('No of Hiring') }}">
                                </div>
                            </div>
                        @endif
                            </div>
                        </div>

                        <div class="col-xxl-6">
                            <div class="single-settings">
                                @csrf
                                <div class="single-dashboard-input">
                                    <div class="single-info-input margin-top-30">
                                        <label for="category" class="info-title"> {{__('Select Category')}} <span class="text-danger">*</span></label>
                                        <select name="category" id="category">
                                            <option value="">{{__('Select Category')}}</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" @if($job->category_id === $cat->id) selected @endif>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="single-info-input margin-top-30 sub_category_wrapper">
                                        <label for="subcategory" class="info-title"> {{__('Select Subcategory')}} <span class="text-danger">*</span></label>
                                        <select  name="subcategory" id="subcategory" class="subcategory">
                                            <option value="{{ $job->subcategory_id }}">{{ optional($job->sub_category)->name }}</option>
                                        </select>
                                    </div>

                                 {{--   <div class="single-info-input margin-top-30 child_category_wrapper">
                                        <label for="subcategory" class="info-title"> {{__('Select Child Category')}} <span class="text-danger">*</span></label>
                                        <select name="child_category" id="child_category">
                                            <option @if(!empty( $job->child_category_id)) value="{{ $job->child_category_id }}"  @else value="" @endif>{{ optional($job->child_category)->name }}</option>
                                        </select>

                                    </div>  --}}
                                </div>
                                @if($job->is_job_online === 0)
                                    <div class="single-dashboard-input show_hide_job_for_online_offline mt-3">
                                        <div class="single-info-input">
                                            <label for="job_island" class="info-title"> {{__('Select Country')}} <span class="text-danger">*</span></label>
                                            <select name="country_id" id="country_id">
                                                <option value="">{{__('Select Country')}}</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}" @if($job->country_id === $country->id) selected @endif>{{ $country->country }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-danger">{{ __('Country which has city only show.') }}</small>
                                        </div>
                                        <div class="single-info-input">
                                            <label for="city_id" class="info-title"> {{__('Select City')}} <span class="text-danger">*</span></label>
                                            <select  name="city_id" id="city_id" class="city">
                                                <option value="{{ $job->city_id }}">{{ optional($job->city)->service_city }}</option>
                                            </select>
                                        </div>
                                        
                                         
                                                <div class="single-info-input ">
                                                <label for="area_id" class="info-title"> {{__('Select Area')}} <span class="text-danger">*</span> </label>
                                              <select name="area_id" id="area_id" class="area">
                                            <option value="">{{ __('Select Area') }}</option>
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}" {{ $job->area_id == $area->id ? 'selected' : '' }}>{{ $area->service_area }}</option>
                                            @endforeach
                                        </select>

                                            </div>
                                @else
                                    <div class="single-dashboard-input show_hide_job_for_online_offline">
                                        <div class="single-info-input margin-top-30">
                                            <label for="job_island" class="info-title"> {{__('Select Country')}} <span class="text-danger">*</span> </label>
                                            <select name="country_id" id="country_id">
                                                <option value="">{{__('Select Country')}}</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}">{{ $country->country }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-danger">{{ __('Country which has city only show.') }}</small>
                                        </div>
                                        <div class="single-info-input margin-top-30">
                                            <label for="city_id" class="info-title"> {{__('Select City')}} <span class="text-danger">*</span> </label>
                                            <select  name="city_id" id="city_id" class="city"></select>
                                        </div>
                                         <div class="single-info-input ">
                                                <label for="area_id" class="info-title"> {{__('Select Area')}} <span class="text-danger">*</span> </label>
                                                <select name="area_id" id="area_id" class="area"></select>
                                            </div>
                                    </div>
                                    
                                @endif
                                <input type="hidden" name="is_job_online" id="is_job_online" value="{{ $job->is_job_online }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xxl-6">
                            <div class="single-dashboard-input">
                                <div class="single-info-input margin-top-30">
                                     @if(Auth::user()->is_company == '1')
                                                    <div class="col-sm-12">
                                              <button type="button" class="btn btn-sm btn-success mb-2" data-bs-toggle="modal" data-bs-target="#promptModal">
                                            {{ __('Generate AI Description') }}
                                        </button>
                                                    @endif
                                    <label for="description" class="info-title"> {{__('Job Description')}}  <span class="text-danger">* {{ __('minimum 150 characters') }}</span></label>
                                    <textarea class="form--control textarea--form summernote" id="summernote" name="description" placeholder="{{__('Type Description')}}">{{ $job->description }}</textarea>
                                </div>
                            </div>
                        </div>
                       <!-- Keep the original package_id if the job is already promoted -->
                
@if($job->promoted == '1')
    <input type="hidden" name="package_id" value="{{ $job->package_id }}">
       
@else
    <!-- Show package selection only if the job is NOT promoted -->
    <div class="row mt-1">
        @foreach($packages as $package)
            <div class="col-md-3 mt-3">
                <div class="card text-center p-3">
                    <h5>{{ __($package->name) }}</h5>
                    <p class="fw-bold">${{ number_format($package->price, 2) }}</p>
                    <input type="radio" name="package_id" value="{{ $package->id }}" class="package-radio" 
                           data-name="{{ strtolower($package->name) }}" 
                           {{ $job->package_id == $package->id ? 'checked' : '' }}>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Hidden Days Input -->
    <div class="row mt-3">
        <div class="col-md-2">
            <div class="single-info-input form-group container">
                <div id="daysInput" class="mt-3" style="display: none;">
                    <!--<label for="days" class="form-label">Enter Days:</label>-->
                    <!--<input type="number" name="promoteddays" id="days" class="form-control form-control-sm" -->
                    <!--       min="1" value="{{ $job->promoteddays }}">-->
                    
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
                </div>
            </div>
        </div>
    </div> 
@endif


                    </div>

                    <div class="row">
                        <div class="col-xxl-6">
                            <div class="single-dashboard-input mt-3">
                                <div class="single-info-input margin-top-30">
                                    <div class="form-group">
                                        <div class="media-upload-btn-wrapper">
                                            <div class="img-wrap">
                                                {!! render_image_markup_by_attachment_id($job->image,'','thumb') !!}
                                            </div>
                                            <input type="hidden" id="image" name="image"
                                                   value="{{$job->image}}">
                                            <button type="button" class="btn btn-info media_upload_form_btn"
                                                    data-bs-btntitle="{{__('Select Image')}}"
                                                    data-bs-modaltitle="{{__('Upload Image')}}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#media_upload_modal">
                                               {{__('Upload Job Image')}}
                                            </button>
                                        </div>
                                        <small class="text-center">{{ __('image format: jpg,jpeg,png')}}</small>
                                        <br>
                                        <small class="text-center">{{ __('and recommended size 730x497') }}</small>
                                    </div>
                                </div>
                                <div class="single-info-input">
                                    <div class="btn-wrapper margin-top-40" style="text-align:end">
                                        <button type="submit" class="dashboard_table__title__btn btn-bg-1 radius-5" style="border: none"> {{__('Save Changes')}} </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        
         <!-- Modal -->
                            <div class="modal fade" id="promptModal" tabindex="-1" aria-labelledby="promptModalLabel" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="promptModalLabel">Enter AI Prompt</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <form id="aiPromptForm">
                                      <div class="mb-3">
                                        <label for="aiPromptInput" class="form-label">Your complete prompt</label>
                                        <textarea id="aiPromptInput" class="form-control" rows="4" placeholder="Type your full prompt here..." style="line-height:1.2; padding:4px 8px; resize:vertical;"></textarea>
                                      </div>
                                    </form>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-success" id="submitPromptBtn">Generate</button>
                                  </div>
                                </div>
                              </div>
                            </div>
        <x-media.markup :type="'web'"/>
        @endsection
        @section('scripts')
            <script src="{{asset('assets/backend/js/bootstrap-tagsinput.js')}}"></script>
            <script src="{{asset('assets/common/js/flatpickr.js')}}"></script>
            <script>
                const UserSelectedLangSlug = "{{current(explode('_',\App\Helpers\LanguageHelper::user_lang_slug()))}}";
            </script>
            <script src="//npmcdn.com/flatpickr/dist/l10n/{{current(explode('_',\App\Helpers\LanguageHelper::user_lang_slug()))}}.js"></script>
            <x-summernote.js/>

            <x-media.js :type="'web'"/>
            <script type="text/javascript">
                (function(){
                    "use strict";
                    $(document).ready(function(){

                        //media modal hide
                        $(document).on('click', '.close', function (e) {
                            e.preventDefault();
                            $("#media_upload_modal").modal('hide');
                        });

                        $("#dead_line").flatpickr({
                            altInput: true,
                            altFormat: "j, F Y",
                            dateFormat: "Y-m-d",
                            locale: UserSelectedLangSlug
                        });

                        //Permalink Code
                        $('#slug_show').css('color', 'blue');
                        //Slug Edit Code
                        $(document).on('click', '.slug_edit_button', function (e) {
                            e.preventDefault();
                            $('.service_slug').show();
                            $(this).hide();
                            $('.slug_update_button').show();
                        });

                        function converToSlug(slug){
                            let finalSlug = slug.replace(/[^a-zA-Z0-9]/g, ' ');
                            //remove multiple space to single
                            finalSlug = slug.replace(/  +/g, ' ');
                            // remove all white spaces single or multiple spaces
                            finalSlug = slug.replace(/\s/g, '-').toLowerCase().replace(/[^\w-]+/g, '-');
                            return finalSlug;
                        }

                        //Slug Update Code
                        $(document).on('click', '.slug_update_button', function (e) {
                            e.preventDefault();
                            $(this).hide();
                            $('.slug_edit_button').show();
                            var update_input = $('.service_slug').val();
                            var slug = converToSlug(update_input);
                            var url = `{{url('/job/details')}}/` + slug;
                            $('#slug_show').text(url);
                            $('.service_slug').hide();
                        });

                        //get subcategory while change category
                        $('#category').on('change',function(){
                            let category_id = $(this).val();
                            $.ajax({
                                method:'post',
                                url:"{{route('buyer.subcategory')}}",
                                data:{category_id:category_id},
                                success:function(res){
                                    if(res.status=='success'){
                                        let alloptions = "<option value=''>{{ __('Select Sub Category') }}</option>";
                                        let allSubCategory = res.sub_categories;
                                        $.each(allSubCategory,function(index,value){
                                            alloptions +="<option value='" + value.id + "'>" + value.name + "</option>";
                                        });
                                        $(".subcategory").html(alloptions);
                                        $('#subcategory').niceSelect('update');
                                    }
                                }
                            })
                        })

                        $('#city_id').select2({
                            placeholder: `{{__('search city')}}`,
                            ajax: {
                                type: 'get',
                                url: "{{route('user.country.city.ajax.search')}}",
                                dataType: 'json',
                                data: function (params) {
                                    let country_id = $("#country_id").val();
                                    return {
                                        q: params.term, // search term
                                        country_id: country_id,
                                    };
                                },
                                delay: 250,
                                processResults: function (response) {
                                    console.log(response.data);
                                    return {
                                        results:  $.map(response, function (item) {
                                            return {
                                                text: item.service_city,
                                                id: item.id
                                            }
                                        })
                                    };
                                },
                                cache: true
                            }
                        });

                        // get job sub-category and child-category
                        $(document).on('change','#subcategory', function() {
                            var sub_cat_id = $(this).val();
                            $.ajax({
                                method: 'post',
                                url: "{{ route('buyer.child_category') }}",
                                data: {
                                    sub_cat_id: sub_cat_id
                                },
                                success: function(res) {

                                    if (res.status == 'success') {
                                        var alloptions = "<option value=''>{{__('Select Child Category')}}</option>";
                                        var allList = "<li data-value='' class='option'>{{__('Select Child Category')}}</li>";
                                        var allChildCategory = res.child_category;

                                        $.each(allChildCategory, function(index, value) {
                                            alloptions += "<option value='" + value.id +
                                                "'>" + value.name + "</option>";
                                            allList += "<li class='option' data-value='" + value.id +
                                                "'>" + value.name + "</li>";
                                        });

                                        $("#child_category").html(alloptions);
                                        $(".child_category_wrapper ul.list").html(allList);
                                        $(".child_category_wrapper").find(".current").html("Select Child Category");
                                    }
                                }
                            })
                        })

                        // new add
                        $('#city_id').select2({
                            placeholder: `{{__('search city')}}`,
                            ajax: {
                                type: 'get',
                                url: "{{route('user.country.city.ajax.search')}}",
                                dataType: 'json',
                                data: function (params) {
                                    let country_id = $("#country_id").val();
                                    return {
                                        q: params.term, // search term
                                        country_id: country_id,
                                    };
                                },
                                delay: 250,
                                processResults: function (response) {
                                    console.log(response.data);
                                    return {
                                        results:  $.map(response, function (item) {
                                            return {
                                                text: item.service_city,
                                                id: item.id
                                            }
                                        })
                                    };
                                },
                                cache: true
                            }
                        });

                        //get city while change country
                        $('#country_id').on('change',function(){
                            let country_id = $(this).val();
                            $.ajax({
                                method:'post',
                                url:"{{route('buyer.city')}}",
                                data:{country_id:country_id},
                                success:function(res){
                                    if(res.status=='success'){
                                        let all_options = '';
                                        let all_cities= res.cities;
                                        $.each(all_cities,function(index,value){
                                            all_options +="<option value='" + value.id + "'>" + value.service_city + "</option>";
                                        });
                                        $(".city").html(all_options);
                                        $('#city_id').niceSelect('update');
                                    }
                                }
                            })
                        });

                        //  if job is online country and city default hide
                        if ($('#check_if_job_is_online').is(':checked')) {
                            $('.show_hide_job_for_online_offline').hide();
                        }

                        // job post online and offline change
                        $(document).on('change','#check_if_job_is_online',function(e) {
                            e.preventDefault();
                            if ($(this).is(':checked')) {
                                let is_job_online = 1;
                                $('#is_job_online').val(is_job_online);
                                $('.show_hide_job_for_online_offline').hide();
                            }else{
                                let is_job_online = 0;
                                $('#is_job_online').val(is_job_online);
                                $('.show_hide_job_for_online_offline').show();
                            }
                        });

                    })
                })(jQuery);

            </script>
            <!---->
             <script>
              
    document.addEventListener('DOMContentLoaded', function () {
        let packageRadios = document.querySelectorAll('.package-radio');
        let daysInput = document.getElementById('daysInput');

        packageRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                let packageName = this.getAttribute('data-name');
                if (packageName === 'featured' || packageName === 'urgent') {
                    daysInput.style.display = 'block';
                } else {
                    daysInput.style.display = 'none';
                    document.getElementById('days').value = ''; // Clear input if hidden
                }
            });
        });
    });

            </script>
                     <script>
    (function ($){

        $(document).ready(function (){

            //todo: if the wallet checkbox is checked need to show this value as current seleted payment gateway
            $(document).on('click', '.wallet_selected_payment_gateway',function(){
                let wallet_value = $(this).val();
                $('.wallet-payment-gateway-wrapper .wallet_selected_payment_gateway').addClass('selected');
                if($('.wallet_selected_payment_gateway').is(':checked')){
                    $('.payment-gateway-wrapper #order_from_user_wallet').val('wallet');

                    // if select Order From Wallet
                    $('.custom_radio__single').removeClass('active');
                    $('.custom_radio__single__input').prop('checked', false);

                    // if wallet not select
                    $('.custom_radio__single__input').on('click', function (){
                        $('#wallet_selected_payment_gateway').prop('checked', false);
                    });

                }else {
                    $('.payment-gateway-wrapper #order_from_user_wallet').val('');
                }
            });

            $(document).on('click', '.current_balance_selected_gateway',function(){
                $('.payment-gateway-wrapper li').removeClass('active');
                $('.payment-gateway-wrapper li').removeClass('selected');
                $('.current-balance-wrapper .current_balance_selected_gateway').addClass('selected');
                $('.payment-gateway-wrapper #order_from_user_wallet').val('current_balance');
            });


        //new add start
            // select payment gateway
            $(document).on('click', '.paymentGateway_add__item',function(){
                let value = $(this).data('gateway');
                $('#order_from_user_wallet').val(value);

                // manual payment image option show/hide
                if(value == 'manual_payment'){
                    $('.manual_payment_gateway_extra_field').show();
                }else {
                    $('.manual_payment_gateway_extra_field').hide();
                }
            });

            // for wallet
            $(document).on('click', '#wallet_selected_payment_gateway',function(){
                $('.confirm-payment').find('#order_from_user_wallet').val('wallet');
            });


            // select manual payment gateway
            if($('#order_from_user_wallet').val() == 'manual_payment'){
                $('.manual_payment_gateway_extra_field').show();
            }else {
                $('.manual_payment_gateway_extra_field').hide();
            }


               // kinetic select bank name option show/hide
                @if(get_static_option('site_default_payment_gateway') === 'kineticpay')
                     $('.kinetic_payment_show_hide').show();
                @else
                    $('.kinetic_payment_show_hide').hide();
                @endif
            $(document).on('click', '.paymentGateway_add__item',function(){
                let value = $(this).data('gateway');
                $('#order_from_user_wallet').val(value);
                if(value == 'kineticpay'){
                    $('.kinetic_payment_show_hide').show();
                }else {
                    $('.kinetic_payment_show_hide').hide();
                }
            });
         //new add end


            // if payment gateway name value is null and (I agree with terms and conditions) is null
            $(document).on('click', '#check3', function(){
                if($('#order_from_user_wallet').val() !== null &&  $('#check3').is(":checked")){
                    $('.all_check_for_order').removeClass('active');
                    $('.all_check_for_order').addClass('completed');
                }else{
                    $('.all_check_for_order').removeClass('completed');
                    $('.all_check_for_order').addClass('active');
                }
            });

            // if (I agree with) is not null and (Order From Wallet) is null
            $(document).on('click', '#wallet_selected_payment_gateway', function (){
                if($('#wallet_selected_payment_gateway').is(":checked") === false){
                        $('.all_check_for_order').removeClass('completed');
                        $('.all_check_for_order').addClass('active');
                }else if($('#check3').is(":checked")){
                    $('.all_check_for_order').removeClass('active');
                    $('.all_check_for_order').addClass('completed');
                }
            });


            // $(document).on('click', '.next', function (){
            //     if ($('.edit_payment_option').hasClass('completed')) {
            //         console.log('ok')
            //         $('.all_check_for_order.edit_payment_option.completed.active').removeClass('active');
            //     }
            // });


        });

    })(jQuery);

</script>

 <script>
                $(document).ready(function() {
    $("#submitPromptBtn").click(function() {
        var userPrompt = $("#aiPromptInput").val().trim();

        if (userPrompt === "") {
            alert("Please enter a prompt.");
            return;
        }

        $.ajax({
            url: "/buyer/jobpost/generate-description",
            type: "POST",
            data: {
                prompt: userPrompt,
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
                $("#submitPromptBtn").text("Generating...").prop("disabled", true);
            },
            success: function(response) {
                var aiText = response.description;

                
             
                aiText = aiText.replace(/[#*`_>~-]/g, '');
            
               
                aiText = aiText.replace(/\n/g, '<br>');
            
            
                $('#summernote').summernote('code', aiText);
                $('#summernote').val(aiText).trigger('change');
            
              
                $("#promptModal").modal('hide');
                $("#aiPromptInput").val("");
            
               
                $('#summernote').next('.note-editor').find('.note-editable').trigger('keyup');
            },
            error: function() {
                alert("Error generating description");
            },
            complete: function() {
                $("#submitPromptBtn").text("Generate").prop("disabled", false);
            }
        });
    });
});

                </script>
@endsection
