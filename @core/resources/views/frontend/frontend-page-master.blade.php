@include('frontend.partials.header')
@if(!in_array(Route::currentRouteName(),['about.seller.profile','about.buyer.profile']))
    <div class="banner-inner-area section-bg-2 padding-top-40 padding-bottom-40
        @if(request()->routeIs('homepage') || request()->routeIs('frontend.dynamic.page')  &&  empty($page_post->breadcrumb_status))
            d-none
        @endif">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="banner-inner-contents">
                        <ul class="inner-menu">
                            <li class="list"><a href="{{url('/')}}">{{__('Home')}} </a></li>
                            {{--<li class="list">{{ __($page_post->title) ?? '' }}  @yield('inner-title')</li>--}}
                        </ul>
                        <h1 class="banner-inner-title">  {{ __($page_post->title ?? '') }} @yield('inner-title')</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@yield('content')

{{-- Rafiki Rewards — Share Prompt Modal (PDF §16, §18). Triggered by
     session flash 'refer_prompt' set from happy-moment controllers. --}}
@auth
    @include('frontend.partials.referral-share-prompt')
@endauth

@include('frontend.partials.footer')
