@extends('frontend.frontend-page-master')
@section('page-meta-data')
    <title>{{ __('Featured Services') }}</title>
@endsection
@section('page-title')
{{ __('Featured Service') }}
@endsection
@section('inner-title')
{{ __('All Featured Service') }}
@endsection
@section('content')
    <!-- Category Service area starts -->
    <section class="category-services-area padding-top-70 padding-bottom-100">
        <div class="container">
            <div class="row">
                @if(!empty($all_featurd_service))
                    @foreach($all_featurd_service as $service)
                        <div class="col-lg-4 col-md-6 margin-top-30 all-services">
                            <div class="single-service no-margin wow fadeInUp" data-wow-delay=".2s">
                                <a href="{{ route('service.list.book',$service->slug) }}" class="service-thumb service-bg-thumb-format" {!! render_background_image_markup_by_attachment_id($service->image) !!}>

                                    <div class="award-icons">
                                        <span class="badge bg-warning">Promoted </span>
                                    </div>

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
                                                    <span class="author-title"> {{ optional($service->seller)->username }} </span>
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
                                    <h5 class="common-title"> <a href="{{ route('service.list.details',$service->slug) }}"> {{ Str::limit($service->title) }} </a> </h5>
                                    <p class="common-para"> {{ Str::limit(strip_tags($service->description),100) }} </p>
                                    <div class="service-price">
                                        <span class="starting"> {{ __('Starting at') }} </span>
                                        <span class="prices"> @include('frontend.pages.services.startingprice') </span>
                                    </div>
                                    <div class="btn-wrapper d-flex flex-wrap">
                                        <a href="{{ route('service.list.book',$service->slug) }}" class="cmn-btn btn-small btn-bg-1"> {{ __('Book Now') }} </a>
                                        <a href="{{ route('service.list.details',$service->slug) }}" class="cmn-btn btn-small btn-outline-1 ml-auto"> {{ __('View Details') }} </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                 
                @endif
            </div>
        </div>
    </section>
    <!-- Category Service area end -->

@endsection
