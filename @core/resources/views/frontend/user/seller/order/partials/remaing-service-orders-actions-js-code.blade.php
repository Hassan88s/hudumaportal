
<!--start pop modal-->

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
 <!-- Bootstrap Modal -->
<div class="modal fade" id="readMoreModal" tabindex="-1" aria-labelledby="readMoreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="readMoreModalLabel">{{__('Full Description') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalContent" style="word-wrap: break-word; overflow-wrap: break-word;">
        <!-- Full description will be inserted here -->
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
            <input type="hidden" id="order_id" name="order_id" >
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
<!--end pop modal-->


<x-media.markup :type="'web'"/>
@section('scripts')
    <script src="{{ asset('assets/backend/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/rating.js') }}"></script>
    <x-media.js :type="'web'"/>
    {{-- ================= COUNTDOWN SCRIPT ================= --}}
<script>
var countdowns = [{
    date: new Date("{{ date('M j, Y H:i:s', strtotime($order_details->offer_time_end)) }}").getTime(),
    id: {{ $order_details->id }}
}];

var timer = setInterval(function () {
    var now = Date.now();

    countdowns.forEach(function (countdown, index) {
        var distance = countdown.date - now;
        var el = document.getElementById("race" + countdown.id);

        if (!el) return;

        if (distance < 0) {
            el.innerHTML = "00D 00h 00m 00s";
            countdowns.splice(index, 1);
        } else {
            var d = Math.floor(distance / (1000 * 60 * 60 * 24));
            var h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var s = Math.floor((distance % (1000 * 60)) / 1000);

            el.innerHTML =
                d + "D " +
                h + "h " +
                m + "m " +
                s + "s";
        }
    });

    if (countdowns.length === 0) clearInterval(timer);
}, 1000);



  // <!--parital payment model-->
               $(document).ready(function() {
    $(document).on('click', '.parital_payment_modal', function(e) {
        e.preventDefault();

        let order_id = $(this).data('id');
        let status = $(this).data('status');

        $('.order_id_input').val(order_id);
    });
});
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

                // $(document).on('click', '.edit_payment_status_modal', function(e) {
                //     e.preventDefault();
                //     let modalContainer = $('#editPaymentStatusModal');
                //     let order_id = $(this).data('id');
                //     modalContainer.find('input[name="order_id"]').val(order_id);
                //     $('.nice-select').niceSelect('update');
                // });
                $(document).on('click', '.edit_status_modal', function(e) {
                // IMPORTANT: don't block bootstrap modal opening
                // e.preventDefault();  // remove if your trigger is an <a> with data-bs-toggle
            
                let order_id = $(this).data('id');
                let status   = $(this).data('status') ?? 2;
            
                let modal = $('#editStatusModal');
                modal.find('input[name="order_id"]').val(order_id);
                modal.find('select[name="status"]').val(status);
            
                // update niceSelect properly (update SELECT, not wrapper)
                modal.find('select[name="status"]').niceSelect('update');
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
                // <!--parital payment model-->
               $(document).ready(function() {
                $(document).on('click', '.parital_payment_modal', function(e) {
                    e.preventDefault();
            
                    let order_id = $(this).data('id');
                    let status = $(this).data('status');
            
                    $('.order_id_input').val(order_id);
                });
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
            <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
            <script>
                (function($){

                    "use strict";

                    $(document).ready(function (){
                        /* Delete */
                        //seller.order.extra.service.delete
                        $(document).on('click','.extra_service_delete_btn',function (e){
                            e.preventDefault();
                            var id = $(this).data('id');
                            var url = $(this).data('url')
                            Swal.fire({
                                title: '{{__("Are you sure?")}}',
                                text: '{{__("You would not be able to revert this item!")}}',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: "{{__('Yes, Delete it!')}}"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        "type" :"POST",
                                        'url' : url,
                                        data: {
                                            _token : "{{csrf_token()}}",
                                            id: id
                                        },
                                        success: function (data){
                                            Swal.fire({
                                                position: 'top-end',
                                                icon: 'warning',
                                                title: "{{__('delete success')}}",
                                                showConfirmButton: false,
                                                timer: 1500
                                            });
                                            location.reload();
                                        }
                                    })
                                }
                            });

                        });

                    });


                })(jQuery);
                //extra_service_edit_btn
            </script>

@endsection