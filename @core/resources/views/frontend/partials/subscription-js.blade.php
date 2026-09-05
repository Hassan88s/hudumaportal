@php
    if(!moduleExists('Subscription')){
        return;
    }
@endphp
<x-payment-gateway-js/>
<script>
    (function($){
        "use strict";

        $(document).ready(function(){

            // get subscription id
            $(document).on('click', '.get_subscription_id',function(){
                let get_subscription_id = $(this).data('id');
                let type = $(this).data('type');
                let price = $(this).data('price');
                let connect = $(this).data('connect');
                let service = $(this).data('service');
                let job = $(this).data('job');
                let projob = $(this).data('projob');
                let month = $(this).data('month');
                
                let projects_allowed = $(this).data('projects_allowed');
                let cashback_percentage = $(this).data('cashback_percentage');
                let sms_notifications = $(this).data('sms_notifications');
                let Website_enabled = $(this).data('website_enabled');
                let personal_enabled = $(this).data('personal_enabled');
                let partialpayment_enabled = $(this).data('partialpayment_enabled');
                
                 let aidescription_enabled = $(this).data('aidescription_enabled');
                
                

                $('.subscription_id').val(get_subscription_id)
                $('.type').val(type)
                $('.price').val(price)
                $('.connect').val(connect)
                $('.service').val(service)
                $('.job').val(job)
                 $('.projob').val(projob)
                 $('.month').val(month)
                 
                 $('.projects_allowed').val(projects_allowed)
                 $('.cashback_percentage').val(cashback_percentage)
                 $('.sms_notifications').val(sms_notifications)
                 $('.Website_enabled').val(Website_enabled)
                 $('.personal_enabled').val(personal_enabled)
                 $('.partialpayment_enabled').val(partialpayment_enabled)
                 $('.aidescription_enabled').val(aidescription_enabled)
                 
                 
                
                $('#subscription_price').val(price)
            });

            @if(Route::has('seller.subscription.coupon.apply'))
            //coupon apply
            $(document).on('click','.coupon_apply_btn',function(e){
                e.preventDefault();
                let subscription_price = $('#subscription_price').val();
                let apply_coupon_code = $('#apply_coupon_code').val();

                $.ajax({
                    url: "{{ route('seller.subscription.coupon.apply') }}",
                    method:"POST",
                    data:{subscription_price:subscription_price,apply_coupon_code:apply_coupon_code},
                    success:function(res){
                        if(res.message != ''){
                            $('.display_error_msg').html('<p class="text-danger">'+res.message+'</p>');
                            $('.display_coupon_amount').html('');
                        }
                        if(res.discount >= 1){
                            $('.display_coupon_amount').html('<p class="text-success">Discounted Amount: ' +res.discount+'</p>');
                            $('.display_error_msg').html('');
                        }
                    }
                });
            });
            @endif

        });
    })(jQuery);
</script>
<script>

$(document).ready(function() {
    // Force the free-plan button label so it always reads "Use Free Plan"
    var freeBtn = document.getElementById("drp_1");
    if (freeBtn) { freeBtn.innerHTML = "{{ __('Use Free Plan') }}"; }
});
// $("#subscriptions input") // select the radio by its id
//     .on('change', function(){ // bind a function to the change event
//     var overwrite = $('#subscriptions input:radio:checked').val();

//      alert('value = ' + overwrite);
//             });
function checkRadio(e) {
  var id=$(e).val();
   html = document.getElementById("new_"+id).innerHTML;
    var dataId = $(e).data("price");
    var month = $(e).data("month");
    // alert(id);
// if(id != 1){
// if (month=='3') {


// var totalnew = month*dataId;
//  var total = totalnew - (totalnew * (20/100));
//  document.getElementById("new_"+id).innerHTML ='<span class="dollar"></span>'+Intl.NumberFormat('en-US').format(total)+' TSh <span class="month">/'+month+'Months <br> <span class="text-danger"> 20% Discount added</span></span>';
//   var elm = document.getElementById("drp_"+id);
//     elm.setAttribute("data-price", total);
//     elm.setAttribute("data-month", month);
// }
// else if(month=='6') {
// var totalnew = month*dataId;
//      var total = totalnew - (totalnew * (30/100));
// document.getElementById("new_"+id).innerHTML ='<span class="dollar"></span>'+Intl.NumberFormat('en-US').format(total)+' TSh <span class="month">/'+month+'Months <br> <span class="text-danger"> 30% Discount added</span></span>';
//  var elm = document.getElementById("drp_"+id);
//     elm.setAttribute("data-price", total);
//     elm.setAttribute("data-month", month);
    
// } else {
//     var total = month*dataId;
// document.getElementById("new_"+id).innerHTML ='<span class="dollar"></span>'+Intl.NumberFormat('en-US').format(total)+' TSh <span class="month">/'+month+'Months</span>';

//      var elm = document.getElementById("drp_"+id);
//     elm.setAttribute("data-price", total);
//     elm.setAttribute("data-month", month);
// }
// //   console.log( html);
// }

if(id != 1){
    if (month=='3') {
        var totalnew = month*dataId;
        var discount = totalnew * (20/100); // calculate discount amount
        var total = totalnew - discount;

        document.getElementById("new_"+id).innerHTML =
            '<span class="dollar"></span>'+Intl.NumberFormat('en-US').format(total)+
            ' TSh <span class="month">/'+month+' Months <br> '+
            '<span class="text-danger">Discount: ' + Intl.NumberFormat('en-US').format(discount) + ' TSh</span></span>';

        var elm = document.getElementById("drp_"+id);
        elm.setAttribute("data-price", total);
        elm.setAttribute("data-month", month);
    }
    else if(month=='6') {
        var totalnew = month*dataId;
        var discount = totalnew * (30/100); // calculate discount amount
        var total = totalnew - discount;

        document.getElementById("new_"+id).innerHTML =
            '<span class="dollar"></span>'+Intl.NumberFormat('en-US').format(total)+
            ' TSh <span class="month">/'+month+' Months <br> '+
            '<span class="text-danger">Discount: ' + Intl.NumberFormat('en-US').format(discount) + ' TSh</span></span>';

        var elm = document.getElementById("drp_"+id);
        elm.setAttribute("data-price", total);
        elm.setAttribute("data-month", month);
    } else {
        var total = month*dataId;
        document.getElementById("new_"+id).innerHTML =
            '<span class="dollar"></span>'+Intl.NumberFormat('en-US').format(total)+
            ' TSh <span class="month">/'+month+' Months</span>';

        var elm = document.getElementById("drp_"+id);
        elm.setAttribute("data-price", total);
        elm.setAttribute("data-month", month);
    }
}

}
</script>

