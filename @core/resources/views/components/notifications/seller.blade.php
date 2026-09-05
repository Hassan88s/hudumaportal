@foreach($notifications as $notification)
    @if(isset($notification->data['seller_last_ticket_id']))
        <li class="dashboard__header__notification__wrap__list__item">
            <div class="dashboard__header__notification__wrap__list__flex">
                <a class="dashboard__header__notification__wrap__list__contents__title"
                   href="{{ route('seller.support.ticket.view', $notification->data['seller_last_ticket_id']) }}">
                    <div class="dashboard__header__notification__wrap__list__icon"><i class="las la-bell"></i></div>
                    <div class="dashboard__header__notification__wrap__list__contents">
                        {{ $notification->data['order_ticcket_message'] }} #{{ $notification->data['seller_last_ticket_id'] }}
                        <span class="dashboard__header__notification__wrap__list__contents__sub">{{ date('Y/m/d h:i A', strtotime($notification->created_at)) }}</span>
                    </div>
                </a>
            </div>
        </li>
    @endif

    @if(isset($notification->data['order_id']))
        <li class="dashboard__header__notification__wrap__list__item">
            <div class="dashboard__header__notification__wrap__list__flex">
                <a class="dashboard__header__notification__wrap__list__contents__title"
                   href="{{ route('seller.order.details', $notification->data['order_id']) }}">
                    <div class="dashboard__header__notification__wrap__list__icon"><i class="las la-bell"></i></div>
                    <div class="dashboard__header__notification__wrap__list__contents">
                        {{ $notification->data['order_message'] }} #{{ $notification->data['order_id'] }}
                        <span class="dashboard__header__notification__wrap__list__contents__sub">{{ date('Y/m/d h:i A', strtotime($notification->created_at)) }}</span>
                    </div>
                </a>
            </div>
        </li>
    @endif
@endforeach
