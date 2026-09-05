@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Add Services')}}
@endsection
@section('style')
    <x-media.css/>
    <x-summernote.css/>
    <link rel="stylesheet" href="{{asset('assets/backend/css/bootstrap-tagsinput.css')}}">
    @include('frontend.user.seller.services.add-service-style')
    <style>
        .close{ border: none; }
        .note-editable{ min-height: 200px; }
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="dashboard-settings margin-top-40">
                                <h4 class="dashboard_table__title"> {{__('Add Service')}} </h4>
                                @php $seller = App\SellerVerify::select('seller_id','status')->where('seller_id',Auth::guard('web')->user()->id)->first() @endphp
                                
                                @if(!is_null($seller))

                                    <!--<div class="notice-board">-->
                                    <!--    <p class="text-danger">{{__('You can not add services if you are not verified.')}}</p>-->
                                    <!--</div>-->
                                      @if(get_static_option('service_create_settings') != 'all_seller' || !is_null($seller) && $seller->status == 1)
                                @if($seller->status == 0)
                                    <div class="notice-board">
                                        <p class="text-danger">{{__('You can not add services if you are not verified.')}}</p>
                                    </div>
                                     @endif
                                @endif
                                 @endif
                                <div class="notice-board mb-0" style="border-left:5px solid #9e9e9e !important;">
                                    <p class="text-secondary">{{__('This part is common for both online and offline services. After creating a service, you will be redirected to a page where you can create service attributes for offline or online.')}}</p> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div> <x-msg.error/> </div>
                    @if(get_static_option('service_create_settings') == 'all_seller' || !is_null($seller) && $seller->status == 1)
                        <livewire:add-service/>
                    @endif
                </div>
            </div>
        </div>  
         <!-- service Info end-->
                            <!-- Modal -->
                            <div class="modal fade" id="promptModal" tabindex="-1" aria-labelledby="promptModalLabel" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="promptModalLabel">{{ __('Enter AI Prompt') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <form id="aiPromptForm">
                                      <div class="mb-3">
                                        <label for="aiPromptInput" class="form-label">{{ __('Your complete prompt') }}</label>
                                        <textarea id="aiPromptInput" class="form-control" rows="4" 
                                            placeholder="{{ __('Type your full prompt here...') }}" 
                                            style="line-height:1.2; padding:4px 8px; resize:vertical;"></textarea>
                                      </div>
                                    </form>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                    <button type="button" class="btn btn-success" id="submitPromptBtn">{{ __('Generate') }}</button>
                                  </div>
                                </div>
                              </div>
                            </div>
        <x-media.markup :type="'web'"/>
        <!-- Dashboard area end -->
        @endsection
        @section('scripts')
            <script src="{{asset('assets/backend/js/bootstrap-tagsinput.js')}}"></script>
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
                        $(document).on('click', '.close', function () {
                            $("#media_upload_modal").modal('hide');
                        });

                        //Permalink Code
                        $('.permalink_label').hide();
                        $(document).on('keyup input', '#title', function (e) {
                            var slug = converToSlug($(this).val());
                            var url = "{{url('/service/')}}/" + slug;
                            $('.permalink_label').show();
                            var data = $('#slug_show').text(url).css('color', 'blue');
                            $('.service_slug').val(slug);

                        });

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
                            var url = "{{url('/service-list/')}}/" + slug;
                            $('#slug_show').text(url);
                            $('.service_slug').hide();
                        });



                        // service category and sub category
                        $('#category').on('change',function(){
                            var category_id = $(this).val();
                            $.ajax({
                                method:'post',
                                url:"{{route('seller.subcategory')}}",
                                data:{category_id:category_id},
                                success:function(res){
                                    if(res.status=='success'){
                                        var alloptions = "<option value=''>{{ __('Select Sub Category') }}</option>";
                                        var allSubCategory = res.sub_categories;
                                        $.each(allSubCategory,function(index,value){
                                            alloptions +="<option value='" + value.id + "'>" + value.name + "</option>";
                                        });

                                        $(".subcategory").html(alloptions);

                                    }
                                }
                            })
                        })


                        // service sub category and child category
                        $(document).on('change','#subcategory', function() {
                            var sub_cat_id = $(this).val();
                            $.ajax({
                                method: 'post',
                                url: "{{ route('seller.subcategory.child.category') }}",
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


                        $("#is_service_all_cities").on('change', function() {
                            if ($("#is_service_all_cities").is(':checked')){
                                $('#is_service_all_cities_id').val(1)
                            }else {
                                $('#is_service_all_cities_id').val('')
                            }
                        });


                        //total price
                        $(document).on("change", ".include-price", function() {
                            var sum = 0;
                            $(".include-price").each(function() {
                                if(isNaN($(this).val())){
                                    alert('Please Enter Numeric Value only')
                                }else{
                                    sum += +$(this).val();
                                }
                            });
                            $("#service_total_price").val(sum);
                        });

                        // //include quantity
                        // $(document).on("change", ".numeric-value", function() {
                        //     if(isNaN($(this).val())){
                        //         alert('Please Enter Numeric Value only')
                        //     }
                        // });


                        // is service online
                        $('.day_review_show_hide').hide()
                        $('.faq_show_hide').show()
                        $('.online_service_price_show_hide').hide()

                        $("#is_service_online").on('change', function() {
                            if ($("#is_service_online").is(':checked')){
                                $('.is_service_online_hide').hide();
                                $('#is_service_online_id').val(1)
                                $('.day_review_show_hide').show()
                                $('.faq_show_hide').show()
                                $('.service-price-show-hide').hide()
                                $('.online_service_price_show_hide').show()
                            }else {
                                $('.is_service_online_hide').show();
                                $('#is_service_online_id').val('')
                                $('.day_review_show_hide').hide()
                                $('.faq_show_hide').hide()
                                $('.service-price-show-hide').show()
                                $('.online_service_price_show_hide').hide()

                            }
                        });

                        // service attribute js end
                        $('#submitBtn').hide();
                        // Service Add next previous tab js
                        var totalTab = $('#add-service-tab .nav-link').length;
                        var tabNavList = $('#add-service-tab .nav-link');
                        let currentTabIndex = 1;

                        $(document).on('click','#add-service-tab .nav-link', function () {
                            // console.log(totalTab,tabNavList.index($(this)));
                            if((totalTab - 1) === tabNavList.index($(this))){
                                // $('#nextBtn').text("Submit").attr('type', 'submit');
                                $('#nextBtn').hide();
                                $('#submitBtn').show();
                            }else{
                                $('#nextBtn').show();
                                $('#submitBtn').hide();
                            }

                        })


                        //service next and previous js start
                        $(document).on('click','#nextBtn',function(e) {
                            let currentState = $('#add-service-tab .nav-link.active');
                            let currentContent = $('#add-service-tabContent .step.active');
                            currentTabIndex = currentState.index() + 1;

                            if(currentTabIndex === totalTab) {
                                return false;
                            }
                            if(currentTabIndex === totalTab - 1) {
                                // $(this).text("Submit").attr('type', 'submit');
                                $('#nextBtn').hide();
                                $('#submitBtn').show();
                            }else {
                                $('#nextBtn').show();
                                $('#submitBtn').hide();
                            }
                            currentState.removeClass('active show').next().addClass('active show');
                            currentState.next();

                            currentContent.siblings().removeClass('active show')
                            currentContent.removeClass('active show').next().addClass('active show');
                            currentContent.next();

                            currentTabIndex++;
                        });

                        $(document).on('click','#prevBtn',function(e) {
                            let currentState = $('#add-service-tab .nav-link.active');
                            let currentContent = $('#add-service-tabContent .step.active');

                            currentTabIndex = currentState.index() + 1;
                            if(currentTabIndex === 1) return ;

                            if(currentTabIndex === totalTab) {
                                $('#nextBtn').show();
                                $('#submitBtn').hide();
                            }else {
                                $('#nextBtn').show();
                                $('#submitBtn').hide();
                            }

                            currentState.removeClass('active show').prev().addClass('active show');
                            currentState.prev();

                            currentContent.siblings().removeClass('active show')
                            currentContent.removeClass('active show').prev().addClass('active show');
                            currentContent.prev();

                            currentTabIndex--;
                        });
                        //service next and previous js end

                    })
                })(jQuery);

            </script>
            
                            
//                 <script>
//                 $(document).ready(function() {
//     $("#submitPromptBtn").click(function() {
//         var userPrompt = $("#aiPromptInput").val().trim();

//         if (userPrompt === "") {
//             alert("Please enter a prompt.");
//             return;
//         }

//         $.ajax({
//             url: "/seller/generate-description",
//             type: "POST",
//             data: {
//                 prompt: userPrompt,
//                 _token: "{{ csrf_token() }}"
//             },
//             beforeSend: function() {
//                 $("#submitPromptBtn").text("Generating...").prop("disabled", true);
//             },
//             success: function(response) {
//                 var aiText = response.description;

//                 // // Insert result into Summernote editor
//                 // $('#summernote').summernote('code', aiText);
//                 // $('#summernote').val(aiText).trigger('change');

//                 // // Close modal
//                 // $("#promptModal").modal('hide');
//                 // $("#aiPromptInput").val(""); // reset input
//                 // // Fire keyup event to simulate typing 
//                 // $('#summernote').next('.note-editor').find('.note-editable') .trigger('keyup');
//                   // remove markdown characters
//                     aiText = aiText.replace(/[#*`_>~-]/g, '');
                
//                     // preserve new lines (convert \n to <br>)
//                     aiText = aiText.replace(/\n/g, '<br>');
                
//                     // Insert result into Summernote editor
//                     $('#summernote').summernote('code', aiText);
//                     $('#summernote').val(aiText).trigger('change');
                
//                     // Close modal
//                     $("#promptModal").modal('hide');
//                     $("#aiPromptInput").val(""); // reset input
                
//                     // Fire keyup event to simulate typing
//                     $('#summernote').next('.note-editor').find('.note-editable').trigger('keyup');
//             },
//             error: function() {
//                 alert("Error generating description");
//             },
//             complete: function() {
//                 $("#submitPromptBtn").text("Generate").prop("disabled", false);
//             }
//         });
//     });
// });

//                 </script>

@if($seller_subscription_check->aidescription_enabled === 'yes')
<script>
$(document).ready(function() {
    $("#submitPromptBtn").click(function() {
        var userPrompt = $("#aiPromptInput").val().trim();

        if (userPrompt === "") {
            alert("Please enter a prompt.");
            return;
        }

        $.ajax({
            url: "/seller/generate-description",
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

                // remove markdown characters
                aiText = aiText.replace(/[#*`_>~-]/g, '');

                // preserve new lines
                aiText = aiText.replace(/\n/g, '<br>');

                // Insert into Summernote
                $('#summernote').summernote('code', aiText);
                $('#summernote').val(aiText).trigger('change');

                // Close modal
                $("#promptModal").modal('hide');
                $("#aiPromptInput").val("");

                // Fire keyup
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
@else
<script>
$(document).ready(function() {
    $("#submitPromptBtn").click(function() {
        alert("Please upgrade your package to use AI description feature.");
    });
});
</script>
@endif


@endsection