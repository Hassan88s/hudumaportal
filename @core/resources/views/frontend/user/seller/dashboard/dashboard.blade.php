@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Seller Dashboard')}}
@endsection
@section('style')
    @include('frontend.user.seller.dashboard.dashboard-style')
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php $default_lang = get_default_language(); @endphp
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">

                {{-- Welcome Header --}}
                <div class="d-welcome">
                    <div>
                        <h2>{{ __('Welcome back') }}, {{ optional(Auth::user())->username ?: optional(Auth::user())->name }} 👋</h2>
                        <p>{{ __('Here is what is happening with your account today.') }}</p>
                    </div>
                    <div class="d-welcome-actions">
                        <a href="{{ route('seller.add.services') }}"><i class="las la-plus"></i> {{ __('New Service') }}</a>
                        <a href="{{ route('seller.start.stream') }}"><i class="las la-broadcast-tower"></i> {{ __('Go Live') }}</a>
                    </div>
                </div>

                {{-- Subscription Alerts --}}
                @if(moduleExists('Subscription') && $commissionGlobal->system_type == 'subscription' )
                    @if(empty(auth('web')->user()->subscribedSeller))
                        <div class="d-alert warning">
                            <strong>{{__('You must subscribe to a package to start selling your services.')}}</strong>
                            <a href="https://hudumaportal.co.tz/price-plan" class="btn-sm">{{__('View Packages')}}</a>
                        </div>
                    @elseif(auth('web')->user()?->subscribedSeller?->status == 0)
                        <div class="d-alert warning">
                            <strong>{{__('Please wait for Admin Approval to activate your subscription.')}}</strong>
                        </div>
                    @else
                        @if(!empty(Auth::guard('web')->user()->subscribedSeller))
                            @if(Carbon\Carbon::parse(auth('web')->user()->subscribedSeller->expire_date) <= \Carbon\Carbon::today())
                                <div class="d-alert warning">
                                    <strong>{{__('Your package has expired, please renew it')}}</strong>
                                    <a href="/price-plan" class="btn-sm">{{__('View Packages')}}</a>
                                </div>
                            @else
                                <div class="d-alert info">
                                    <span>{{__('Your Subscribed Package:')}} <strong>{{auth('web')->user()?->subscribedSeller?->subscription?->title}}</strong> · {{__('Expires:')}} <strong>{{ auth('web')->user()?->subscribedSeller?->expire_date?->translatedFormat('d F Y') }}</strong></span>
                                </div>
                            @endif
                        @endif
                    @endif
                @endif

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
                    <a href="{{ route('seller.orders') }}" class="{{ request()->is('seller/orders*') ? 'active' : '' }}">
                        <i class="las la-list-alt"></i> {{ __('Service Orders') }}
                    </a>
                    @if(moduleExists('JobPost'))
                        <a href="{{ route('seller.job.orders') }}" class="{{ request()->is('seller/job-orders*') ? 'active' : '' }}">
                            <i class="las la-bars"></i> {{ __('Job Orders') }}
                        </a>
                    @endif
                </div>

                {{-- Stat Cards Grid --}}
                <div class="d-stats-grid">
                    <a href="{{ route('seller.payout') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico pink"><i class="las la-user-friends"></i></div>
                            <div class="d-stat-label">{{ __('Total Referral') }}</div>
                            <h4 class="d-stat-value">{{ $referral_count ?? 0 }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('seller.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico warning"><i class="fa-solid fa-hourglass-end"></i></div>
                            <div class="d-stat-label">{{ __('Order In Progress') }}</div>
                            <h4 class="d-stat-value">{{ $active_order }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('seller.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico info"><i class="fa-solid fa-list-ul"></i></div>
                            <div class="d-stat-label">{{ __('Order Pending') }}</div>
                            <h4 class="d-stat-value">{{ $pending_order }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('seller.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico success"><i class="fa-regular fa-square-check"></i></div>
                            <div class="d-stat-label">{{ __('Order Completed') }}</div>
                            <h4 class="d-stat-value">{{ $complete_order }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('seller.orders') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico primary"><i class="fa-solid fa-clipboard-list"></i></div>
                            <div class="d-stat-label">{{ __('Total Order') }}</div>
                            <h4 class="d-stat-value">{{ $total_order }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('seller.payout') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico purple"><i class="fa-solid fa-dollar"></i></div>
                            <div class="d-stat-label">{{ __('Total Withdraw') }}</div>
                            <h4 class="d-stat-value">{{ float_amount_with_currency_symbol($total_earnings) }}</h4>
                        </div>
                    </a>
                    <a href="{{ route('seller.payout') }}" class="d-stat">
                        <div class="d-stat-row">
                            <div class="d-stat-ico success"><i class="las la-file-invoice-dollar"></i></div>
                            <div class="d-stat-label">{{ __('Remaining Balance') }}</div>
                            <h4 class="d-stat-value">{{ float_amount_with_currency_symbol($remaning_balance - $total_earnings) }}</h4>
                        </div>
                    </a>
                </div>

                {{-- Recent Orders + Notifications --}}
                <div class="row dashboard-redesign g-0">
                    <div class="col-xxl-8 col-lg-7 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-shopping-bag"></i> {{ __('Recent Orders') }}</h4>
                                <a href="{{ route('seller.orders') }}" class="d-card-action">{{ __('View All') }} →</a>
                            </div>
                            @if($last_five_order->count() >= 1)
                                <div class="d-orders">
                                    @foreach($last_five_order as $order)
                                        <div class="d-order">
                                            <div class="d-order-top">
                                                <div class="d-order-thumb">
                                                    <a href="{{ route('seller.order.details', $order->id) }}">
                                                        {!! render_image_markup_by_attachment_id(optional($order->service)->image, '', 'thumb') !!}
                                                    </a>
                                                </div>
                                                <div class="d-order-body">
                                                    <a href="{{ route('seller.order.details', $order->id) }}" class="d-order-id">{{ __('Order') }} <span>#{{ $order->id }}</span></a>
                                                    <h4 class="d-order-title">
                                                        @if(!empty($order->job_post_id))
                                                            {{ __('Job Order') }}
                                                        @else
                                                            @if(!is_null(optional($order->service)->slug))
                                                                <a href="{{ route('service.list.details', optional($order->service)->slug) }}">{{ optional($order->service)->title }}</a>
                                                            @endif
                                                        @endif
                                                    </h4>
                                                    <div class="d-order-meta">
                                                        {{ __('Date:') }} <strong>{{ Carbon\Carbon::parse($order->created_at)->format('d/m/y') }}</strong> · {{ __('Buyer:') }} <a href="{{ route('about.buyer.profile',optional($order->buyer)->username) }}">{{ optional($order->buyer)->username }}</a>
                                                    </div>
                                                </div>
                                                <div class="d-order-actions">
                                                    <a href="{{ route('seller.order.details', $order->id) }}" class="d-view" title="{{ __('View') }}"><i class="fa-regular fa-eye"></i></a>
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

                    <div class="col-xxl-4 col-lg-5 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-bell"></i> {{ __('Notifications') }}</h4>
                                <a href="{{ route('seller.clear.notifications') }}" class="d-card-action">{{ __('Clear All') }}</a>
                            </div>
                            <div class="d-notif-list">
                                @if(Auth::guard('web')->check() && Auth::guard('web')->user()->user_type==0)
                                    @if(Auth::guard('web')->user()->unreadNotifications->count() >=1)
                                        @foreach(Auth::guard('web')->user()->unreadNotifications->take(5) as $notification)
                                            <div class="d-notif-item">
                                                <div class="d-notif-icon"><i class="las la-bell"></i></div>
                                                <div class="d-notif-body">
                                                    <p class="d-notif-text">
                                                        @if(isset($notification->data['seller_last_ticket_id']))
                                                            <a href="{{ route('seller.support.ticket.view',$notification->data['seller_last_ticket_id']) }}">{{$notification->data['order_ticcket_message']}} #{{ $notification->data['seller_last_ticket_id'] }}</a>
                                                        @endif
                                                        @if(isset($notification->data['order_id']))
                                                            <a href="{{ route('seller.order.details',$notification->data['order_id']) }}">{{$notification->data['order_message']}} #{{$notification->data['order_id']}}</a>
                                                        @endif
                                                        @if(isset($notification->data['data']['id']) && $notification->data['data']['type'] == 'gernalnotifications')
                                                            <a href="{{ route('seller.gernel.notifications', $notification->id) }}">{{$notification->data['message']}}</a>
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

                {{-- This Month + Todo --}}
                <div class="row dashboard-redesign g-0">
                    <div class="col-xxl-6 col-lg-6 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-calendar"></i> {{ __('This Month Summary') }}</h4>
                            </div>
                            <div class="d-summary-grid">
                                <div class="d-summary-item">
                                    <div class="d-summary-ico" style="background:var(--d-primary-light);color:var(--d-primary)"><i class="fa-solid fa-tasks"></i></div>
                                    <div>
                                        <h5>{{ __('Orders') }}</h5>
                                        <div class="d-summary-val">{{ $this_month_order_count }}</div>
                                    </div>
                                </div>
                                <div class="d-summary-item">
                                    <div class="d-summary-ico" style="background:var(--d-success-light);color:var(--d-success)"><i class="fa-solid fa-dollar"></i></div>
                                    <div>
                                        <h5>{{ __('Earning') }}</h5>
                                        <div class="d-summary-val">{{ float_amount_with_currency_symbol($this_month_earnings) }}</div>
                                    </div>
                                </div>
                                <div class="d-summary-item">
                                    <div class="d-summary-ico" style="background:var(--d-warning-light);color:var(--d-warning)"><i class="las la-file-invoice-dollar"></i></div>
                                    <div>
                                        <h5>{{ __('Balance') }}</h5>
                                        <div class="d-summary-val">{{ float_amount_with_currency_symbol($this_month_balance_without_tax_and_admin_commission) }}</div>
                                    </div>
                                </div>
                                <div class="d-summary-item">
                                    <div class="d-summary-ico" style="background:var(--d-info-light);color:var(--d-info)"><i class="fa-solid fa-user"></i></div>
                                    <div>
                                        <h5>{{ __('Total Buyers') }}</h5>
                                        <div class="d-summary-val">{{ $buyer_count }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-lg-6 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-clipboard-list"></i> {{ __('To Do List') }}</h4>
                                <a href="{{ route('seller.todolist') }}" class="d-card-action">{{ __('See All') }} →</a>
                            </div>
                            @if($to_do_list->count() >= 1)
                                <table class="d-todo-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('ID') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($to_do_list as $todo)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('seller.support.ticket.view', $todo->id) }}" class="d-todo-id">#{{ $todo->id }}</a>
                                                </td>
                                                <td>{{ $todo->description }}</td>
                                                <td>
                                                    <x-seller-coupon-status :url="route('seller.todolist.status',$todo->id)"/>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="d-empty"><i class="las la-clipboard-list"></i> {{ __('No To Do Tasks') }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Charts --}}
                <div class="row dashboard-redesign g-0">
                    <div class="col-xl-6 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-chart-line"></i> {{ __('Total Order Overview') }}</h4>
                            </div>
                            <div class="d-chart-wrap">
                                <canvas id="line-chart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-12">
                        <div class="d-card">
                            <div class="d-card-header">
                                <h4 class="d-card-title"><i class="las la-chart-bar"></i> {{ __('Weekly Work Summary') }}</h4>
                            </div>
                            <div class="d-chart-wrap">
                                <canvas id="bar-chart-grouped"></canvas>
                            </div>
                        </div>
                    </div>
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

                /* Line Charts */
                new Chart(document.getElementById("line-chart"), {
                    type: 'line',
                    data: {
                        labels: [@foreach($month_list as $list) "{{ $list }}", @endforeach],
                        datasets: [{
                            data: [@foreach($monthly_order_list as $list) "{{ $list }}", @endforeach],
                            label: "{{__('Order')}}",
                            borderColor: "#ff6b2c",
                            backgroundColor: "rgba(255,107,44,0.15)",
                            borderWidth: 3,
                            fill: true,
                            tension: 0.35,
                            pointBorderWidth: 2,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#ff6b2c',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointHoverBackgroundColor: "#ff6b2c",
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } }
                    }
                });

                /* Group Bar Charts */
                new Chart(document.getElementById("bar-chart-grouped"), {
                    type: 'bar',
                    data: {
                        labels: [@foreach($days_list as $list) "{{ $list }}", @endforeach],
                        datasets: [
                            {
                                label: "{{__('Pending')}}",
                                backgroundColor: "#ffb38a",
                                data: [@foreach($pending_order_list as $list) "{{ $list }}", @endforeach],
                                barThickness: 12,
                                borderRadius: 4,
                            },
                            {
                                label: "{{__('Active')}}",
                                backgroundColor: "#ff6b2c",
                                data: [@foreach($active_order_list as $list) "{{ $list }}", @endforeach],
                                barThickness: 12,
                                borderRadius: 4,
                            },
                            {
                                label: "{{__('Complete')}}",
                                backgroundColor: "#34d399",
                                data: [@foreach($complete_order_list as $list) "{{ $list }}", @endforeach],
                                barThickness: 12,
                                borderRadius: 4,
                            }
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
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
