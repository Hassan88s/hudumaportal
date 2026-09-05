@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Create Job Post') }}
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
           height: 50px;
           border: 1px solid #dddddd;
           border-radius: 5px;
           padding: 13px 20px;
           width: 100%;
           background-color: #fff;
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
                            <h4 class="dashboards-title"> {{__('Create Job Post')}} </h4>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="dashboard-settings margin-top-40">
                            <strong class="online_checkbox__title">{{ __('Check If Job is Online') }}</strong>
                            <label class="custom_switch" for="check_if_job_is_online">
                                <input class="switch_input service_on_off_btn" id="check_if_job_is_online" type="checkbox">
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <form action="{{route('buyer.add.job')}}" method="post" enctype="multipart/form-data">
                    @csrf
                <div class="row mt-5">
                    <div class="col-xxl-6">
                            <div class="single-settings">
                                    <div class="single-dashboard-input mt-4">
                                        <div class="single-info-input">
                                            <label for="title" class="info-title"> {{__('Job Title')}} <span class="text-danger">*</span> </label>
                                            <input class="form--control" value="{{ old('title')}}" name="title" id="title" type="text" placeholder="{{__('Add title')}}">
                                        </div>
                                    </div>

                                    <div class="single-dashboard-input mt-2">
                                        <div class="single-dashboard-input">
                                            <div class="single-info-input margin-top-30 permalink_label">
                                                <label for="title" class="info-title text-dark"> {{__('Permalink')}} <span class="text-danger">*</span> </label>
                                                <span id="slug_show" class="display-inline"></span>
                                                <span id="slug_edit" class="display-inline"></span>
                                                <button class="btn btn-warning btn-sm slug_edit_button">  <i class="las la-edit"></i> </button>

                                                <input class="form--control service_slug" name="slug" id="slug" style="display: none" type="text">
                                                <button class="btn btn-info btn-sm slug_update_button mt-2" style="display: none">{{__('Update')}}</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="single-dashboard-input mt-2">
                                        <div class="single-info-input">
                                            <label for="price" class="info-title"> {{__('Budget')}} <span class="text-danger">*</span> </label>
                                            <input class="form--control" value="{{ old('price')}}" name="price" id="price" type="number" placeholder="{{__('Add Price')}}">
                                        </div>

                                        <div class="single-info-input">
                                            <label for="dead_line" class="info-title"> {{__('Deadline to Apply for this job')}} <span class="text-danger">*</span> </label>
                                            <input class="form--control" {{ old('dead_line') }} name="dead_line" id="dead_line" type="text" placeholder="{{__('Dead Line')}}">
                                        </div>
                                    </div>
                            </div>
                    </div>

                    <div class="col-xxl-6">
                        <div class="single-dashboard-input">
                            <div class="single-info-input margin-top-30">
                                <label for="category" class="info-title"> {{__('Select Category')}} <span class="text-danger">*</span> </label>
                                <select name="category" id="category">
                                    <option value="">{{__('Select Category')}}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="single-info-input margin-top-30 sub_category_wrapper">
                                <label for="subcategory" class="info-title"> {{__('Select Subcategory')}} <span class="text-danger">*</span> </label>
                                <select  name="subcategory" id="subcategory" class="subcategory"></select>
                            </div>
                            <div class="single-info-input margin-top-30 child_category_wrapper">
                                <label for="child_category" class="info-title"> {{__('Select Child Category')}} </label>
                                <select  name="child_category" id="child_category"></select>
                            </div>
                        </div>

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
                        </div>

                        <input type="hidden" name="is_job_online" id="is_job_online" value="0">

                    </div>
                </div>

                <div class="row">
                    <div class="col-xxl-6">
                        <div class="single-dashboard-input">
                            <div class="single-dashboard-input">
                                <div class="single-info-input">
                                    <label for="description" class="info-title"> {{__('Job Description')}} <span class="text-danger">*</span> </label>
                                    <textarea class="form--control textarea--form summernote" name="description" placeholder="{{__('Type Description')}}">{{ old('description')}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xxl-6">
                        <div class="single-dashboard-input">
                            <div class="single-dashboard-input">
                                <div class="single-info-input margin-top-35">
                                    <div class="form-group ">
                                        <div class="media-upload-btn-wrapper">
                                            <div class="img-wrap mt-2"></div>
                                            <input type="hidden" name="image">
                                            <button type="button" class="btn btn-info media_upload_form_btn w-50"
                                                    data-bs-btntitle="{{__('Select Image')}}"
                                                    data-bs-modaltitle="{{__('Upload Image')}}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#media_upload_modal">
                                                {{__('Upload Job Image')}}
                                            </button>
                                            <small class="text-center">{{ __('image format: jpg,jpeg,png')}}</small>
                                            <br>
                                            <small class="text-center">{{ __('and recommended size 730x497') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="single-info-input mt-4">
                                    <div class="btn-wrapper text-end">
                                        <button type="submit" class="dashboard_table__title__btn btn-bg-1 radius-5" style="border: none">  {{__('Create Job Post')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <x-media.markup :type="'web'"/>
        @endsection
        @section('scripts')
            <script src="{{asset('assets/backend/js/bootstrap-tagsinput.js')}}"></script>
            <script src="{{asset('assets/common/js/flatpickr.js')}}"></script>
            <x-summernote.js/>

            <script>
                $('.meta-content').show();
            </script>

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
                            altFormat: "F j, Y",
                            dateFormat: "Y-m-d",
                        });
                        function converToSlug(slug){
                            let finalSlug = slug.replace(/[^a-zA-Z0-9]/g, ' ');
                            //remove multiple space to single
                            finalSlug = slug.replace(/  +/g, ' ');
                            // remove all white spaces single or multiple spaces
                            finalSlug = slug.replace(/\s/g, '-').toLowerCase().replace(/[^\w-]+/g, '-');
                            return finalSlug;
                        }
                        //Permalink Code
                        $('.permalink_label').hide();
                        $(document).on('keyup', '#title', function (e) {
                            var slug = converToSlug($(this).val());
                            var url = "{{url('/job/details')}}/" + slug;
                            $('.permalink_label').show();
                            var data = $('#slug_show').text(url).css('color', '#3c3cf7');
                            $('.service_slug').val(slug);

                        });

                        //Slug Edit Code
                        $(document).on('click', '.slug_edit_button', function (e) {
                            e.preventDefault();
                            $('.service_slug').show();
                            $(this).hide();
                            $('.slug_update_button').show();
                        });

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
                                        var alloptions = "<option value=''>{{ __('Select Sub Category') }}</option>";
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


                        // job sub-category and child-category
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
@endsection