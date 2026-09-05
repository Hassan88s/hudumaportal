@if(Auth::guard('web')->check())


<div class="info-bar-item d-lg-none">
@if(auth('web')->check() && Auth::guard('web')->user()->user_type == 0)

@php
    $notifications = Auth::user()
        ->unreadNotifications()
        ->latest()
        ->take(10)
        ->get();

    $unreadCount = Auth::user()->unreadNotifications()->count(); // count ALL unread (badge)
@endphp
<div class="notification-icon icon">
    <i class="las la-bell"></i>

    <span class="notification-number {{ $unreadCount == 0 ? 'd-none' : '' }}">
        {{ $unreadCount }}
    </span>

    <div class="notification-list-item mt-2">
        <h5 class="notification-title">{{ __('Notifications') }}</h5>

        <div class="list">
            @if($unreadCount)

                {{-- seller ticket Notifications --}}
                @foreach($notifications as $notification)
                    @if(isset($notification->data['seller_last_ticket_id']))
                        <a class="list-order" href="{{ route('seller.support.ticket.view', $notification->data['seller_last_ticket_id']) }}">
                            <span class="order-icon"><i class="las la-check-circle"></i></span>
                            {{ $notification->data['order_ticcket_message'] }}
                            #{{ $notification->data['seller_last_ticket_id'] }}
                        </a>
                    @endif
                @endforeach

                {{-- seller order Notifications --}}
                @foreach($notifications as $notification)
                    @if(isset($notification->data['order_id']))
                        <a class="list-order" href="{{ route('seller.order.details', $notification->data['order_id']) }}">
                            <span class="order-icon"><i class="las la-check-circle"></i></span>
                            {{ $notification->data['order_message'] }}
                            #{{ $notification->data['order_id'] }}
                        </a>
                    @endif
                @endforeach

                {{-- general Notifications --}}
                @foreach($notifications as $notification)
                    @php
                        $type = $notification->data['data']['type'] ?? '';
                    @endphp
                    @if($type === 'gernalnotifications')
                        <a class="list-order" href="{{ route('seller.gernel.notifications', $notification->id) }}">
                            <span class="order-icon"><i class="las la-check-circle"></i></span>
                            {{ $notification->data['message']
                               ?? $notification->data['data']['details']
                               ?? 'General notification' }}
                        </a>
                    @endif
                @endforeach

                <a class="p-2 text-center d-block" href="{{ route('seller.notification.all') }}">
                    {{ __('View All Notification') }}
                </a>

            @else
                <p class="text-center padding-3">{{ __('No New Notification') }}</p>
            @endif
        </div>
    </div>
</div>

@endif

<!--buyer-->
@if(Auth::guard('web')->check() && Auth::guard('web')->user()->user_type == 1)

@php
    $notifications = Auth::user()->unreadNotifications;
    $unreadCount = $notifications->count();
@endphp

<div class="info-bar-item d-lg-none">
    <div class="notification-icon icon">
        <i class="las la-bell"></i>

        <span class="notification-number {{ $unreadCount == 0 ? 'd-none' : '' }}">
            {{ $unreadCount }}
        </span>

        <div class="notification-list-item mt-2">
            <h5 class="notification-title">{{ __('Notifications') }}</h5>

            <div class="list">
                @if($unreadCount)

                    {{-- Buyer order notifications --}}
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

                    {{-- Buyer general notifications --}}
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
</div>

@endif

</div>


<div class="login-account">
    <div class="info-bar-item-two">
        <div class="author-thumb">
            @if(!empty(Auth::guard('web')->user()->image))
                {!! render_image_markup_by_attachment_id(Auth::guard('web')->user()->image) !!}
            @else
                <img src="{{ asset('assets/frontend/img/static/user_profile.png') }}" alt="No Image">
            @endif

        </div>

        <a class="accounts loggedin" href="javascript:void(0)">
            <span class="title">  {{ getEnterpriseNamewithAuth() }}
 </span>
        </a>
        <ul class="account-list-item mt-2">
            <li class="list">
                @if(Auth::guard('web')->user()->user_type==0)
                <a href="{{ route('seller.dashboard')}}"> {{ __('Dashboard') }} </a>
                @else
                <a href="{{ route('buyer.dashboard')}}"> {{ __('Dashboard') }} </a>
                @endif
            </li>
            <li class="list"> <a href="{{ route('seller.logout')}}"> {{ __('Logout') }} </a> </li>
        </ul>
    </div>
</div>

@else
    <div class="login-account">
        <a class="accounts" href="javascript:void(0)"> <span class="account">{{ __('Account') }}</span> <i class="las la-user"></i> </a>
        <ul class="account-list-item mt-2">
            <li class="list"> <a href="{{ route('user.register') }}"> {{ __('Sign Up') }} </a> </li>
            <li class="list"> <a href="{{ route('user.login') }}">{{ __('Sign In') }} </a> </li>
        </ul>
    </div>
@endif

    
