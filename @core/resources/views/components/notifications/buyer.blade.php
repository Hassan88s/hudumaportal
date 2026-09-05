@foreach($notifications as $notification)
    <li class="dashboard__header__notification__wrap__list__item">
        <div class="dashboard__header__notification__wrap__list__flex">
            <a class="dashboard__header__notification__wrap__list__contents__title"
               href="{{ route('buyer.support.ticket.view', $notification->data['last_ticket_id']) }}">
                <div class="dashboard__header__notification__wrap__list__icon"><i class="las la-bell"></i></div>
                <div class="dashboard__header__notification__wrap__list__contents">
                    {{ $notification->data['order_ticcket_message'] }} #{{ $notification->data['last_ticket_id'] }}
                    <span class="dashboard__header__notification__wrap__list__contents__sub">{{ date('Y/m/d h:i A', strtotime($notification->created_at)) }}</span>
                </div>
            </a>
        </div>
    </li>
@endforeach
