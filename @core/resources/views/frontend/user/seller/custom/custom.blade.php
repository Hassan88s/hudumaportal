@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Custom Service')}}
@endsection
@section('style')
    <x-media.css/>
    <style>
        img.no-image {
            width: 119px;
        }
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php $default_lang = get_default_language(); @endphp
            <!-- Dashboard area Starts -->
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        <!-- buyer header -->
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
               
 <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboard__headerContents__title">
                             
                           
                        </div>
                        <div class="btn-wrapper">
                          
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
 <i class="fa-solid fa-plus"></i>
                              
                                {{__('Create Custom Service')}}
</button>
                        </div>
                        
                        
           
                    </div>
                </div>
                
      @if (count($errors) > 0)
         <div class = "alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif
                   <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboards-title"><strong>{{__('All Custom Services')}}</strong></h4>
                           
                        </div>
                    </div>
                </div>
  <!--cusotm offer-->
               @if($offer->count() >= 1)
                    <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
                        <div class="dashboard_table__main custom--table mt-4">
                            <table>
                                <thead>
                                <tr>
                                  
                               
                                
                                    <th> {{ __('Job Title') }} </th>
                                    <th> {{ __('Buyer Name') }} </th>
                                      <th> {{ __('Description') }} </th>
                                     <th> {{ __('Your Offer') }} </th>
                                    
                                      <th>{{ __('Delivery Time') }} </th>
                                       <th> {{ __('Status') }} </th>
                                    <th> {{ __('Action') }} </th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach ($offer as $job_req)
                                    <tr>
                                      
                                      
                                        <td data-label="{{ __('Job Title') }}"> {{ Str::limit($job_req->title,50) }} </td>
                                        @php 
$value = \App\User::where(['id' => $job_req->buyer_id])->first();
@endphp
                                    <td data-label="{{ __('Seller Name') }}">  
                                            {{ $value->username }}
                                         
                                        </td>
                                        
                                        <td data-label="{{ __('Description') }}"> 
                                               
  <button type="button" class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#readMoreModal" onclick="setModalContent(`{!! addslashes($job_req->description) !!}`)">{{ __('Read Description') }}</button>

                                           
                                         
                                        </td  >
                                        
                                        <td data-label="{{ __('Your Offer') }}">{{ float_amount_with_currency_symbol($job_req->price) }}</td>
                                          @if($job_req->status == 1 )
                                         <td id='race{{$job_req->id}}'>  
                                        @php                                          

                                                                       
                                $Draw_time = str_replace('/', '-', "$job_req->cjob_timelimit");
                                $start_datetime = new DateTime($job_req->cjob_timelimit); 
                                $diff = $start_datetime->diff(new DateTime("now")); 
                                
                                $current_date=new DateTime("now");
                                
                                 @endphp
                                  @if($current_date > $start_datetime)
                                  <span class="text-danger">{{ __('Delivery time is finished')}}</span>
                                @endif
                                           
                                         
                                        </td>
                                        @else
                                        <td>{{$job_req->end_date}} {{ __('days')}}</td>
                                       @endif
                                        <td>
                                            
                                               @if($job_req->status == 1)
                                         <strong class="replaceText text-success"> {{ __('Progress') }}</strong>
                                                           @elseif($job_req->status == 2)
                                                      <strong class="replaceText text-info">{{ __('You Withdrwal the Offer')}}</strong>      
                                                           @elseif($job_req->status == 0)
                                                               <strong class="replaceText text-danger">{{ __('Pending') }}</strong>
                                                               @elseif($job_req->status == 4)
                                                               <strong class="replaceText text-success">{{ __('Order Complete') }}</strong>
                                                                @elseif($job_req->status == 5)
                                                               <strong class="replaceText text-danger">{{ __('Offer Decline By Buyer') }}</strong>
                                                        @elseif($job_req->status == 6)
                                                               <strong class="replaceText text-danger">{{ __('Offer Decline By Buyer due to late Delivery') }}</strong>
                                                      @endif
                                        </td>
                                        <td>
                                            <!--<a href="" target="_blank">-->
                                            <!--    <span class="btn btn-info btn-sm">{{__('View Details')}}</span>-->
                                            <!--</a>-->
                                            @if($job_req->status == 1 || $job_req->status == 4 || $job_req->status == 6)
                                              <a href="{{ route('seller.job.orders') }}">
                                             <strong class="btn btn-primary btn-sm"> {{ __('View Order') }}</strong>
                                             
                                              </a>
                                            @elseif($job_req->status == 0)
                                            <a href="{{ route('seller.job.withdrwa', $job_req->id) }}">
                                                <span class="btn btn-danger btn-sm">{{ __('withdrawal Offer') }}</span>
                                            </a>
                                            @else
                                           
                                           @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="blog-pagination margin-top-55">
                            <div class="custom-pagination mt-4 mt-lg-5">
                                {!! $offer->links() !!}
                            </div>
                        </div>

                    </div>
                @else
                    <div class="chat_wrapper__details__inner__chat__contents">
                        <p class="no_data_found_for_buyer_seller_panel">
                            {{ __('No Job Request Found')}}
                        </p>
                    </div>
                @endif
<!-- Button trigger modal -->


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{ __('Send Custom Service To buyer')}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       <form  action="{{route('seller.add.customoffer')}}" method="post" enctype="multipart/form-data">
            @csrf
     <div class="form-group">
    <label for="exampleFormControlInput1">{{ __('Select Buyer')}}</label>      
<select class="form-select" name="buyer_id" required>
    @foreach($buyers as $buyer)
  <option value="{{ optional($buyer->buyerList)->id }}">{{ optional($buyer->buyerList)->username }}</option>
  @endforeach
</select>
</div>
  <div class="form-group">
    <label for="exampleFormControlInput1">{{ __('Job Title')}}</label>
    <input type="text" class="form-control" id="title" name="title" required>
  </div>
  <div class="form-group">
    <label for="exampleFormControlSelect1">{{ __('Job Price')}}</label>
     <input type="text" name="Price" class="form-control" id="Price" name="Price" required>
  </div>
  
   <div class="form-group">
    <label for="exampleFormControlSelect1">{{ __('days')}}</label>
     <input type="num" name="Days" class="form-control" id="Days" name="Days" required>
  </div>
 
  <div class="form-group">
    <label for="exampleFormControlTextarea1">{{ __('Job Description')}}</label>
    <textarea class="form-control" name='Description' id="exampleFormControlTextarea1" rows="3"></textarea>
  </div>

    
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close')}}</button>
        <button type="submit"  class="btn btn-primary">{{ __('Send Offer')}}</button>
      </div>
      </form>
        </div>
    </div>
  </div>
</div>
        
     
            </div>
        </div>
   
        </div>
    </div>
    </div>
      <!--pop uop-->
<!-- Bootstrap Modal -->
<div class="modal fade" id="readMoreModal" tabindex="-1" aria-labelledby="readMoreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="readMoreModalLabel">{{ __('Full Description')}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalContent" style="word-wrap: break-word; overflow-wrap: break-word;">
        <!-- Full description will be inserted here -->
      </div>
    </div>
  </div>
</div>
    <!-- Buyer Profile Edit Modal End-->
    <x-media.markup :type="'web'"/>
@endsection
@section('scripts')
    <x-media.js :type="'web'"/>
    <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
    <script>
// Set the date we're counting down to
var countdowns = [
<?php foreach ($offer as $race) {?>
  {
    
    date: new Date("<?php echo date('M j, Y H:i:s', strtotime($race['cjob_timelimit']));?>").getTime(),
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
    //   timerElement.innerHTML = "Delivery time is Finish";
      // this timer is done, remove it
      countdowns.splice(index, 1);
      
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
                (function($){
                    "use strict";

                    $(document).ready(function(){

                        // for date range
                        $('.flatpickr_input').flatpickr({
                            altFormat: "invisible",
                            altInput: false,
                            mode: "range",
                        });

                        $(document).on('change','.service_on_off_btn',function(e){
                            e.preventDefault();
                            if($(this).is(':checked')){
                                let job_post_id = $(this).data('id');
                                $.ajax({
                                    method:'post',
                                    url:"{{route('buyer.job.on.off')}}",
                                    data:{job_post_id:job_post_id},
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
                                            toastr.success('Job On/Off Change Success---');
                                        }
                                    }
                                });
                            }else{
                                let job_post_id = $(this).data('id');
                                $.ajax({
                                    method:'post',
                                    url:"{{route('buyer.job.on.off')}}",
                                    data:{job_post_id:job_post_id},
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
                                            toastr.success('Job On/Off Change Success---');
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
                                confirmButtonText: 'Yes, delete it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $(this).next().find('.swal_form_submit_btn').trigger('click');
                                }
                            });
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
