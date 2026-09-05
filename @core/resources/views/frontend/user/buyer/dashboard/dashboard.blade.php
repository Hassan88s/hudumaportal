@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Buyer Dashboard')}}
@endsection
@section('style')
    @include('frontend.user.buyer.dashboard.dashboard-style')
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php $default_lang = get_default_language(); @endphp
    @include('frontend.user.buyer.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">

                {{-- Welcome Header --}}
                <div class="d-welcome">
                    <div>
                        <h2>{{ __('Welcome back') }}, {{ optional(Auth::user())->username ?: optional(Auth::user())->name }} 👋</h2>
                        <p>{{ __('Find services, post jobs and manage your orders.') }}</p>
                    </div>
                    <div class="d-welcome-actions">
                        <a href="{{ url('/service-list') }}"><i class="las la-search"></i> {{ __('Browse Services') }}</a>
                        @if(moduleExists('JobPost'))
                            <a href="{{ route('buyer.add.job') }}"><i class="las la-plus"></i> {{ __('Post a Job') }}</a>
                        @endif
                    </div>
                </div>

                {{-- Referral --}}
                @if(!empty(Auth::user()->referral_code))
                <div class="d-referral">
                    <div class="d-referral-title"><i class="las la-share-alt"></i> {{ __('Your Referral Link') }}</div>
                    <div class="input-group">
                        <input type="text" value="{{ url('/register?ref='.Auth::user()->referral_code) }}" id="referralLink" readonly>
                        <button onclick="copyReferral()" title="{{ __('Copy') }}"><i class="fas fa-copy"></i> {{ __('Copy') }}</button>
                    </div>
                    <div class="d-share-row">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/register?ref='.Auth::user()->referral_code)) }}" target="_blank" class="fb" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://api.whatsapp.com/send?text={{ urlencode('Freelancers – Offer your services and reach more clients.
Clients – Hire skilled professionals for any task, online or offline.

Join today and experience the fastest way to get things done. ' . url('/register?ref='.Auth::user()->referral_code)) }}" target="_blank" class="wa" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://x.com/intent/tweet?url={{ urlencode(url('/register?ref='.Auth::user()->referral_code)) }}&text={{ urlencode('Freelancers – Offer your services and reach more clients.
Clients – Hire skilled professionals for any task, online or offline.

Join today and experience the fastest way to get things done.') }}" target="_blank" class="tw" title="X (Twitter)"><i class="fab fa-x-twitter">X</i></a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url('/register?ref='.Auth::user()->referral_code)) }}" target="_blank" class="li" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                @endif

                {{-- Quick Nav --}}
                <div class="d-quicknav">
                    <a href="{{ route('buyer.orders') }}" class="{{ request()->is('buyer/orders*') ? 'active' : '' }}">
                        <i class="las la-list-alt"></i> {{ __('Service Orders') }}
                    </a>
                    @if(moduleExists('JobPost'))
                        <a href="{{ route('buyer.job.orders') }}" class="{{ request()->is('buyer/job-orders*') ? 'active' : '' }}">
                            <i class="las la-bars"></i> {{ __('Job Orders') }}
                        </a>
                    @endif
                </div>

                {{-- Stat Cards Grid --}}
                <div class="d-stats-grid">
                    <a href="javascript:void(0)" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico pink"><i class="las la-user-friends"></i></div>
                            <div class="d-stat-label">{{ __('Total Referral') }}</div>
                            <h4 class="d-stat-value">{{ $referral_count ?? 0 }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('buyer.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico warning"><i class="fa-solid fa-hourglass-end"></i></div>
                            <div class="d-stat-label">{{ __('Order In Progress') }}</div>
                            <h4 class="d-stat-value">{{ $active_order }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('buyer.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico info"><i class="fa-solid fa-list-ul"></i></div>
                            <div class="d-stat-label">{{ __('Order Pending') }}</div>
                            <h4 class="d-stat-value">{{ $pending_order }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('buyer.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico success"><i class="fa-regular fa-square-check"></i></div>
                            <div class="d-stat-label">{{ __('Order Completed') }}</div>
                            <h4 class="d-stat-value">{{ $complete_order }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('buyer.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico primary"><i class="fa-solid fa-clipboard-list"></i></div>
                            <div class="d-stat-label">{{ __('Total Order') }}</div>
                            <h4 class="d-stat-value">{{ $total_order }}</h4>
                        </div>
                    </a>
                </div>

                {{-- Recent Orders + Notifications --}}
                <div class="row dashboard-redesign g-0">
                    <div class="col-xxl-7 col-lg-7 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-shopping-bag"></i> {{ __('Recent Orders') }}</h4>
                                <a href="{{ route('buyer.orders') }}" class="d-card-action">{{ __('View All') }} →</a>
                            </div>
                            @if($last_6_order_dash_two->count() >= 1)
                                <div class="d-orders">
                                    @foreach($last_6_order_dash_two as $order)
                                        <div class="d-order">
                                            <div class="d-order-top">
                                                <div class="d-order-thumb">
                                                    @if(!empty($order->job_post_id))
                                                        <a href="{{ route('buyer.order.details', $order->id) }}">
                                                            {!! render_image_markup_by_attachment_id(optional($order->job)->image, '', 'thumb') !!}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('buyer.order.details', $order->id) }}">
                                                            {!! render_image_markup_by_attachment_id(optional($order->service)->image, '', 'thumb') !!}
                                                        </a>
                                                    @endif
                                                </div>
                                                <div class="d-order-body">
                                                    <a href="{{ route('buyer.order.details', $order->id) }}" class="d-order-id">{{ __('Order') }} <span>#{{ $order->id }}</span></a>
                                                    <h4 class="d-order-title">
                                                        @if(!empty($order->job_post_id))
                                                            {{ __('Job Order') }}
                                                        @else
                                                            @if(!is_null(optional($order->service)->slug))
                                                                <a href="{{ route('service.list.details', optional($order->service)->slug ?? '') }}">{{ optional($order->service)->title }}</a>
                                                            @endif
                                                        @endif
                                                    </h4>
                                                    <div class="d-order-meta">
                                                        {{ __('Date:') }} <strong>{{ Carbon\Carbon::parse($order->created_at)->format('d/m/y') }}</strong> · {{ __('Seller:') }} <a href="{{ route('about.seller.profile',optional($order->seller)->username) }}">{{ optional($order->seller)->username }}</a>
                                                    </div>
                                                </div>
                                                <div class="d-order-actions">
                                                    <a href="{{ route('buyer.order.details', $order->id) }}" class="d-view" title="{{ __('View') }}"><i class="fa-regular fa-eye"></i></a>
                                                    <button type="button" class="d-order-toggle" title="{{ __('Toggle') }}"><i class="fa-solid fa-angle-down"></i></button>
                                                </div>
                                            </div>
                                            <div class="d-order-details">
                                                <div class="d-order-row">
                                                    <span>{{ __('Booking Date & Time:') }}</span>
                                                    @if($order->date === 'No Date Created')
                                                        <strong>{{ __('No Date Created') }}</strong>
                                                    @else
                                                        <strong>{{ Carbon\Carbon::parse($order->date)->format('d/m/y') }} {{ $order->schedule }}</strong>
                                                    @endif
                                                </div>
                                                <div class="d-order-row">
                                                    <span>{{ __('Order type:') }}</span>
                                                    <strong>@if($order->is_order_online == 1) {{ __('Online') }} @else {{ __('Offline') }} @endif</strong>
                                                </div>
                                                <div class="d-order-row">
                                                    <span>{{ __('Order amount:') }}</span>
                                                    <strong>{{ amount_with_currency_symbol($order->total) }}</strong>
                                                </div>
                                                <div class="d-order-row">
                                                    <span>{{ __('Order status:') }}</span>
                                                    @if ($order->status == 0)<span class="d-badge pending">{{ __('Pending') }}</span>@endif
                                                    @if ($order->status == 1)<span class="d-badge active">{{ __('Active') }}</span>@endif
                                                    @if ($order->status == 2)<span class="d-badge completed">{{ __('Completed') }}</span>@endif
                                                    @if ($order->status == 3)<span class="d-badge delivered">{{ __('Delivered') }}</span>@endif
                                                    @if ($order->status == 4)<span class="d-badge cancel">{{ __('Cancel') }}</span>@endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="d-empty"><i class="las la-shopping-bag"></i> {{ __('No New Orders') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-xxl-5 col-lg-5 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-bell"></i> {{ __('Notifications') }}</h4>
                                <a href="{{ route('buyer.clear.notifications') }}" class="d-card-action">{{ __('Clear All') }}</a>
                            </div>
                            <div class="d-notif-list">
                                @if(Auth::guard('web')->check() && Auth::guard('web')->user()->user_type==1)
                                    @if(Auth::guard('web')->user()->unreadNotifications->count() >=1)
                                        @foreach(Auth::guard('web')->user()->unreadNotifications->take(10) as $notification)
                                            <div class="d-notif-item">
                                                <div class="d-notif-icon"><i class="las la-bell"></i></div>
                                                <div class="d-notif-body">
                                                    <p class="d-notif-text">
                                                        @if(isset($notification->data['last_ticket_id']))
                                                            @php $ticket_id_find = \App\SupportTicket::find($notification->data['last_ticket_id']) @endphp
                                                            <a href="@if(!empty($ticket_id_find)) {{ route('buyer.support.ticket.view',$notification->data['last_ticket_id']) }} @endif">
                                                                {{ $notification->data['order_ticcket_message'] }} #{{ $notification->data['last_ticket_id'] }}
                                                            </a>
                                                        @endif
                                                        @if(isset($notification->data['data']['id']) && $notification->data['data']['type'] == 'gernalnotifications')
                                                            <a href="{{ route('buyer.gernel.notifications', $notification->id) }}">{{$notification->data['message']}}</a>
                                                        @endif
                                                    </p>
                                                    <span class="d-notif-time">{{ date('d/m/Y h:i A', strtotime($notification->created_at)) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="d-empty"><i class="las la-bell-slash"></i> {{ __('No New Notifications') }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Recent Tickets --}}
                <div class="d-card">
                    <div class="d-card-header">
                        <h4 class="d-card-title"><i class="las la-ticket-alt"></i> {{ __('Recent Tickets') }}</h4>
                        <a href="{{ route('buyer.support.ticket') }}" class="d-card-action">{{ __('View All') }} →</a>
                    </div>
                    @if($last_10_tickets->count() >= 1)
                        <div style="overflow-x:auto">
                        <table class="d-tk-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Ticket') }}</th>
                                    <th>{{ __('Order ID') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($last_10_tickets as $ticket)
                                    <tr>
                                        <td>
                                            <a href="{{ route('buyer.support.ticket.view', $ticket->id) }}" class="tk-title">{{ $ticket->title }}</a>
                                            <span class="tk-id">{{ __('ID:') }} #{{ $ticket->id }}</span>
                                        </td>
                                        <td>{{ $ticket->order_id }}</td>
                                        <td>
                                            @if ($ticket->priority == 'high')<span class="d-priority high">{{ __('High') }}</span>@endif
                                            @if ($ticket->priority == 'low')<span class="d-priority low">{{ __('Low') }}</span>@endif
                                            @if ($ticket->priority == 'medium')<span class="d-priority medium">{{ __('Medium') }}</span>@endif
                                            @if ($ticket->priority == 'urgent')<span class="d-priority urgent">{{ __('Urgent') }}</span>@endif
                                        </td>
                                        <td>
                                            @if($ticket->status === 'open')
                                                <span class="d-status open">{{ __(ucfirst($ticket->status)) }}</span>
                                            @else
                                                <span class="d-status close">{{ __(ucfirst($ticket->status)) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('buyer.support.ticket.view', $ticket->id) }}" class="tk-view" title="{{ __('View') }}"><i class="fa-regular fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    @else
                        <div class="d-empty"><i class="las la-ticket-alt"></i> {{ __('No New Tickets') }}</div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
    <script>
        (function($){
            "use strict";

            $(document).ready(function(){

                // Recent order toggle (new design)
                $(document).on('click','.d-order-toggle',function(){
                    $(this).closest('.d-order').find('.d-order-details').toggleClass('open');
                    $(this).find('i').toggleClass('fa-angle-down fa-angle-up');
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
                        confirmButtonText: "{{__('Yes, delete it!')}}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).next().find('.swal_form_submit_btn').trigger('click');
                        }
                    });
                });

                $(document).on('click','.swal_status_button',function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: '{{__("Are you sure to close status?")}}',
                        text: '{{__("You will not able to open it!")}}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{__('Yes, change it!')}}"
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
        function copyReferral() {
            var referralLink = document.getElementById("referralLink").value;
            var textToCopy = `Freelancers – Offer your services and reach more clients.
Clients – Hire skilled professionals for any task, online or offline.

Join today and experience the fastest way to get things done.

${referralLink}`;

            var tempTextArea = document.createElement("textarea");
            tempTextArea.value = textToCopy;
            document.body.appendChild(tempTextArea);
            tempTextArea.select();
            document.execCommand("copy");
            document.body.removeChild(tempTextArea);
            alert("Referral message copied!");
        }
    </script>
@endsection
