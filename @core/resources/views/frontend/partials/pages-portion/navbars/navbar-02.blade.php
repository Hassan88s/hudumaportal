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
@php
    if(request()->is('/')){
        $page__id = get_static_option('home_page');
        $page_details = App\Page::find($page__id);
        $page_post = isset($page_post) && is_null($page_details) ? $page_post : $page_details;
        
       
    }
@endphp

<nav class="navbar navbar-area navbar-two {{ $page_post->page_class ?? '' }} navbar-expand-lg">
    <div class="container container-two nav-container">
        <div class="responsive-mobile-menu">
            <div class="logo-wrapper">
                <a href="{{ route('homepage') }}" class="logo">
                    {!! render_image_markup_by_attachment_id(get_static_option('site_logo')) !!}
                </a>
            </div>

            <div class="onlymobile-device-account-navbar navtwo">
                <div class="onlymobile-device-account-navbar-flex">
                    <div class="navbar-right-inner">
                        <x-frontend.user-menu/>
                    </div>
                </div>
            </div>
            <button class="navbar-toggler black-color" type="button" data-bs-toggle="collapse"
                    data-bs-target="#bizcoxx_main_menu_navabar_two" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="bizcoxx_main_menu_navabar_two">
            <ul class="navbar-nav">
             
                        {!! render_frontend_menu($primary_menu) !!}
                @if(get_static_option('site_Challenege_Page') == 'on')
    <li> 
        <a href="{{ route('challenges') }}">{{ __('Challenges') }}</a>
    </li>
    
   
@endif

            </ul>
        </div>

      <div class="nav-right-content">
                <div class="navbar-right-inner">
                    <div class="info-bar-item">
                        @if(auth('web')->check() && Auth()->guard('web')->user()->unreadNotifications()->count() > 0)
                            @if(Auth::guard('web')->check() && Auth::guard('web')->user()->user_type==0)
                                <div class="notification-icon icon">
                                    @if(Auth::guard('web')->check())
                                        <span class="text-white"> <i class="las la-bell" style="color:black"></i> </span>
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
    <span class="text-white"><i class="las la-bell" style="color:black"></i></span>

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
    
</nav>