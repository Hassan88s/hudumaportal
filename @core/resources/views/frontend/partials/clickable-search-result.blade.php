@extends('frontend.frontend-page-master')

@section('site-title')
    {{ __('Home') }}
    @endsection

    @section('page-title')
    {{ __('Search') }}
    @endsection 

    @section('inner-title')
    {{ __('Services') }}
@endsection 

@section('content')
    <!-- Category Service area starts -->
    <section class="category-services-area padding-top-100 padding-bottom-100">
        <div class="container">
            <div class="row margin-top-20">

                @if($services->count() >0)
                    @foreach($services as $service)
                        
                        <div class="col-lg-4 col-md-6 margin-top-30 all-services">
                            <div class="single-service no-margin wow fadeInUp" data-wow-delay=".2s">
                                    
                                <a href="{{ route('service.list.details',$service->slug) }}" class="service-thumb">
                                    {!! render_image_markup_by_attachment_id($service->image) !!}
                                    
                                    <div class="country_city_location">
                                        <span class="single_location"> <i class="las la-map-marker-alt"></i>
                                            {{ optional($service->serviceCity)->service_city }} ,
                                             {{ optional(optional($service->serviceCity)->countryy)->country }}
                                        </span>
                                    </div>
                                    @if($service->featured==1)
                                   <div class="award-icons">
                                                       <span class="badge bg-warning">Promoted </span>
                                                    </div>
                                    @endif
                                </a>
                                <div class="services-contents">
                                    <ul class="author-tag">
                                        <li class="tag-list">
                                            <a href="{{ route('about.seller.profile',optional($service->seller)->username) }}">
                                                <div class="authors">
                                                    <div class="thumb">
                                                        {!! render_image_markup_by_attachment_id(optional($service->seller)->image) !!}
                                                        
                                                    </div>
                                                    <span class="author-title"> {{ optional($service->seller)->username }}</span>
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
@endphp 
                            @if($value != NULL)
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
                                        <li class="tag-list">
                                            <a href="javascript:void(0)">
                                                <span class="icon"> <i class="las la-star"></i> </span>
                                                <span class="reviews"> 
                                                    {{ round(optional($service->reviews)->avg('rating'),1) }}
                                                    ({{ optional($service->reviews)->count() }})
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                    <h5 class="common-title"> <a href="{{ route('service.list.details',$service->slug) }}"> {{ Str::limit($service->title) }} </a> </h5>
                                    <p class="common-para"> {{ Str::limit(strip_tags($service->description,100)) }} </p>
                                    <div class="service-price">
                                        <span class="starting"> {{ __('Starting at') }} </span>
                                        <span class="prices">  @if(is_null($service->starting_price))
                                                         {{ amount_with_currency_symbol($service->price) }}
                                                            @else
                                                          {{ amount_with_currency_symbol($service->starting_price) }}
                                                            @endif </span>
                                    </div>
                                    <div class="btn-wrapper d-flex flex-wrap">
                                        <a href="{{ route('service.list.book',$service->slug) }}" class="cmn-btn btn-small btn-bg-1"> {{ __('Book Now') }} </a>
                                        <a href="{{ route('service.list.details',$service->slug) }}" class="cmn-btn btn-small btn-outline-1 ml-auto"> {{ __('View Details') }} </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                   

                @else 
                  <h2 class="text-warning">{{ __('Nothing Found...') }}</h2>
                @endif
            </div>
        </div>
    </section>
    <!-- Category Service area end -->
@endsection
