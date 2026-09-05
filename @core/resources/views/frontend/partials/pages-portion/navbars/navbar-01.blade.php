@php
    $navClasses = request()->is('/') ? 'navbar navbar-area white nav-absolute navbar-expand-lg navbar-border' : 'navbar navbar-area white navbar-expand-lg navbar-border';
@endphp
<style>


 @media (max-width: 768px) {
            .login-accounts {
                max-width: 90% !important; 
                margin: 0 auto; 
            }

            .input-group {
                width: 100%; 
            }

            .custom-select {
                width: 100% !important; 
                background-color: transparent !important; 
                border: 1px solid #ccc !important;
            }
            .form-group{
                padding-top:20px !important;
            }
        }
</style>

<header class="header-style-01">
    <nav class="{{ $navClasses }} {{ $page_post->page_class ?? '' }}">
        <div class="container container-two nav-container">
            <div class="responsive-mobile-menu">
                <div class="logo-wrapper">
                    <a href="{{ route('homepage') }}" class="logo">
                        {!! render_image_markup_by_attachment_id(get_static_option('site_white_logo')) !!}
                    </a>
                </div>

                <div class="onlymobile-device-account-navbar">
                    <div class="onlymobile-device-account-navbar-flex">
                        <x-frontend.user-menu/>
                    </div>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bizcoxx_main_menu_navbar_one" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="bizcoxx_main_menu_navbar_one">
                <ul class="navbar-nav">
                    {!! render_frontend_menu($primary_menu) !!}
                   @if(get_static_option('site_Challenege_Page') == 'on')
    <li> 
        <a href="{{ route('challenges') }}">{{ __('Challenges') }}</a>
    </li>
   
@endif

                </ul>
            </div>
<!--<select id="aioConceptName">-->
<!-- <option value="en">English</option>-->
<!-- <option value="fr">French</option>-->
<!-- <option value="es">Spanish</option>-->
<!--</select>-->
<!--<div id="google_translate_element" disp></div>-->



                           
            <div class="nav-right-content">
                <div class="navbar-right-inner">
                    <div class="info-bar-item">
                        @if(auth('web')->check() && Auth()->guard('web')->user()->unreadNotifications()->count() > 0)
                            @if(Auth::guard('web')->check() && Auth::guard('web')->user()->user_type==0)
                                <div class="notification-icon icon">
                                    @if(Auth::guard('web')->check())
                                        <span class="text-white"> <i class="las la-bell"></i> </span>
                                        <span class="notification-number">
                                        {{ Auth()->user()->unreadNotifications()->count() }}
                                    </span>
                                    @endif

                                    <div class="notification-list-item mt-2">
                                        <h5 class="notification-title">{{ __('Notifications') }}</h5>
                                        <div class="list">
                                            @if(Auth::guard('web')->check() && Auth::guard('web')->user()->unreadNotifications()->count() >=1)
                                                <span>

                                      <!-- seller ticket Notifications-->
                                        @foreach(Auth::guard('web')->user()->unreadNotifications->take(10) as $notification)
                                                        @if(isset($notification->data['seller_last_ticket_id']))
                                                            <a class="list-order" href="{{ route('seller.support.ticket.view',$notification->data['seller_last_ticket_id']) }}">
                                                            <span class="order-icon"> <i class="las la-check-circle"></i> </span>
                                                            {{ $notification->data['order_ticcket_message']  }} #{{ $notification->data['seller_last_ticket_id'] }}
                                                        </a>
                                            @endif
                                        @endforeach

                                               <!-- seller order Notifications-->
                                            @foreach(Auth::guard('web')->user()->unreadNotifications()->take(5)->get() as $notification)
                                           
                                                        @if(isset($notification->data['order_id']))
                                                            <a class="list-order" href="{{ route('seller.order.details',$notification->data['order_id']) }}">
                                                        <span class="order-icon"> <i class="las la-check-circle"></i> </span>
                                                        {{ $notification->data['order_message']  }} #{{ $notification->data['order_id'] }}
                                                    </a>
                                                        @endif
                                                    @endforeach
                                                    
                                                    
                                                    
                                                    
                                          @foreach(Auth::guard('web')->user()->unreadNotifications()->take(5)->get() as $notification)
                                            @php
                                             $type = $notification->data['data']['type'] ?? '';
                                              
                                            @endphp
                                               @if($type === 'gernalnotifications')
                                               
                                                 <a class="list-order" href="{{ route('seller.gernel.notifications', $notification->id) }}">
                                                        <span class="order-icon"> <i class="las la-check-circle"></i> </span>
                                                       {{ $notification->data['message'] ?? $notification->data['data']['details']   ?? 'General notification' }}
                                                    </a>
                                                        @endif
                                          
                                        @endforeach

                                        </span>

                                                <a class="p-2 text-center d-block" href="{{ route('seller.notification.all') }}">{{ __('View All Notification') }}</a>
                                            @else
                                                <p class="text-center text-white padding-3">{{ __('No New Notification') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                        
                        <!--?BUYER-->
                        
                        @if(auth('web')->check() && Auth::user()->user_type == 1)

@php
    $notifications = Auth::user()->unreadNotifications;
@endphp

<div class="notification-icon icon">
    <span class="text-white"><i class="las la-bell"></i></span>

    <span class="notification-number {{ $notifications->count() == 0 ? 'd-none' : '' }}">
        {{ $notifications->count() }}
    </span>

    <div class="notification-list-item mt-2">
        <h5 class="notification-title">{{ __('Notifications') }}</h5>

        <div class="list">
            @if($notifications->count())

                {{-- Buyer Order Notifications --}}
                @foreach($notifications as $notification)
                    @if(isset($notification->data['order_id']))
                        <a class="list-order"
                           href="{{ route('buyer.order.details', $notification->data['order_id']) }}">
                            <span class="order-icon"><i class="las la-check-circle"></i></span>
                            {{ $notification->data['order_message'] }}
                            #{{ $notification->data['order_id'] }}
                        </a>
                    @endif
                @endforeach

                {{-- Buyer General Notifications --}}
                @foreach($notifications as $notification)
                    @if(($notification->data['data']['type'] ?? '') === 'gernalnotifications')
                        <a class="list-order"
                           href="{{ route('buyer.gernel.notifications', $notification->id) }}">
                            <span class="order-icon"><i class="las la-info-circle"></i></span>
                            {{ $notification->data['message']
                                ?? $notification->data['data']['details']
                                ?? 'General notification' }}
                        </a>
                    @endif
                @endforeach

                <a class="p-2 text-center d-block"
                   href="{{ route('buyer.notification.all') }}">
                    {{ __('View All Notification') }}
                </a>

            @else
                <p class="text-center padding-3">{{ __('No New Notification') }}</p>
            @endif
        </div>
    </div>
</div>

@endif

                    </div>
                    <!--//userprofiel-->
                    <x-frontend.user-menu/>
                    <?php   
    $languages = App\Language::get();
?>

                </div>



            </div>
        <div class="login-account">
<div class="form-group">
   
    <div class="input-group " style="max-width: 200px;">
        <select name="language_id" id="language" class="custom-select" style="background-color: transparent; border: none;">
            @foreach($languages as $language)
                <option value="{{ $language->id }}" {{ session('lang') == $language->slug ? 'selected' : '' }}>
                    {{ $language->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
    </div>
        </div>
  <!--<button onclick="triggerOneSignal()">Enable Notifications</button>-->
    </nav>
  

</header>





<!--<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>-->

