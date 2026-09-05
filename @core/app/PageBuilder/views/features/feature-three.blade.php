
@section('style')
   <style>
   @media screen and (max-width : 1920px){
  .div-only-mobile{
   display: none;
  }
}
@media screen and (max-width : 906px){
 .desk{
   display: none;
  }
 .div-only-mobile{
  display: block;
  }
}
    </style>
@endsection<!-- Service area starts -->
 <div class="d-flex justify-content-center align-items-center mt-5 mb-5">
    <div class="col-lg-6 col-md-8 col-sm-10">
        <div class="card shadow">
            <div class="card-body text-center">
               
                    <h5 class="card-title" style="
    font-size: xx-large;
    font-weight: 700;
">{{__('Share HudumaPortal & Earn Rewards')}}</h5>
                    <p class="card-text">{{__('Invite your friends to join HudumaPortal and get rewarded up to 3000 Tsh for every successful refferal. Help others find job or hire top talents while earning cash')}}</p>
                    <a href="{{ url('/referral') }}" class="btn btn-warning" style="
    color: white;
    background: #ff6b2c;">{{__('Check Details')}} -></a>
              
            </div>
        </div>
    </div>
</div>
<section class="new_services_area padding-top-50 padding-bottom-50" data-padding-top="{{$padding_top}}" data-padding-bottom="{{$padding_bottom}}" style="background-color:{{$section_bg}}">
    
    <div class="container">
        <div class="new_sectionTitle text-left title_flex">
            <h2 class="title">{{ __($section_title) }}</h2>
            <!--<a href="{{ $explore_link }}" class="new_exploreBtn"> {{ $explore_text }} <i class="fa-solid fa-angle-right"></i></a>-->
        </div>
        <div class="row g-4 mt-4">

            @foreach($services as $service)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="new_service__single">
                    <div class="new_service__single__thumb">
                        <a href="{{ route('service.list.details',$service->slug) }}">
                            {!! render_image_markup_by_attachment_id($service->image, '','','thumb'); !!}
                        </a>
                        <div class="award_icons">
                            <!--<a href="javascript:void(0)" class="award_icons__item">-->
                                <!--<i class="las la-award"></i>-->
                             <span class="badge bg-warning">Promoted </span>
                            <!--</a>-->
                        </div>
                    </div>
                    <div class="new_service__single__contents">
                        <span class="new_jobs__single__contents__location mb-2">
                              <i class="fa-solid fa-location-dot"></i>
                                {{ sellerServiceLocation($service) }}
                            </span>

                        <h5 class="new_service__single__contents__title"><a href="{{ route('service.list.details',$service->slug) }}">{{ $service->title }}</a></h5>
                        <div class="new_service__single__price">
                            <span class="new_service__single__price__starting"> {{ $static_text['start_at'] ?? __('Starting at') }} </span>
                            <h5 class="new_service__single__price__title mt-1">  @if(is_null($service->starting_price))
                                                         {{ amount_with_currency_symbol($service->price) }}
                                                            @else
                                                          {{ amount_with_currency_symbol($service->starting_price) }}
                                                            @endif </h5>
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
                            </a>
                            
                             <div class="single_authors__thumb">
                                 @php 
$value = \Modules\Subscription\Entities\SellerSubscription::where(['seller_id' =>$service->seller->id])->first('subscription_id');
if($value != NULL){
if($value->subscription_id != NULL){
$image = \Modules\Subscription\Entities\Subscription::where(['id' =>$value->subscription_id])->first('image');
    }
    }
@endphp
                         @if($value != NULL)
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
                                @if($rating >= 1)
                                    <a href="javascript:void(0)" class="author_tag__review__para"> {!! ratting_star($rating) !!} {{ $total_count }}</a>
                                @endif
                            </div>
                        </div>

                        <div class="btn-wrapper border_top">
                            <a href="{{ route('service.list.book',$service->slug) }}" class="cmn-btn btn-outline-border w-100 radius-5"
                               style="background:{{$btn_color}}; color:{{$button_text_color}}">{{ __($book_appoinment) }} </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
     <div class="row margin-top-20 ">
             
                <!--<div class="explore-btn">-->
                    <div class="text-center">
                        <!--<a href="{$explore_more_link}" class="cmn-btn {$explore_btn_color}" style="color:{$title_text_color}"> {$btn_text} </a>-->
                         <a href="{{ $explore_link }}" class="cmn-btn btn-outline-1" style="color:rgb(29, 191, 115)">{{__('Explore More')}}</a>
                   </div>
                <!--</div>-->
                
                
  
            </div>
            
           

              
            @php
            $value = \App\Adspace::where(['id' =>'1'])->first();
            
            @endphp
            @if($value->ads_code_desktop!= NULL)
            <div class="text-center pt-5 desk" >
                <div >
            {{ $value->ads_code_desktop }}
             </div>
            </div>
            @endif
             @if($value->ads_code_mobile!= NULL)
            <div class="text-center pt-5 pb-5 div-only-mobile" >
            {{ $value->ads_code_mobile }}
            </div>
            @endif
   
            
</section>

<!-- Service area end -->