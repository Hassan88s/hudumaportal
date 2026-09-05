<section class="dynamic-page-content-area padding-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                 
                @if(!auth()->guard('web')->check() && $page_post->visibility === 'all')
                    <div class="dynamic-page-content-wrap">
                      
                     {!! __($page_post->page_content_t ) !!}
                     @if($page_post->slug =='privacy-policy' )
                     @include('frontend.partials.policy')
                        @endif
                         @if($page_post->slug =='terms-and-conditions' )
                     @include('frontend.partials.termscondition')
                        @endif
                        

                    </div>
                @elseif(auth()->guard('web')->check())
                    <div class="dynamic-page-content-wrap">
                        {!! $page_post->page_content_t  !!}
                        @if($page_post->slug =='privacy-policy' )
                       @include('frontend.partials.policy')
                        @endif
                         @if($page_post->slug =='terms-and-conditions' )
                     @include('frontend.partials.termscondition')
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning">
                        <p><a class="text-primary" href="{{route('user.login')}}">{{__('Login')}}</a> {{__('to see this page')}} </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>