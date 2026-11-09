{{--
    Admin notification bell.

    Replaces three separate loops over the same five rows (which grouped by
    type, so the newest five being orders meant tickets and jobs never
    appeared at all) with one ordered list. Each row is only shown to an admin
    whose role covers that area, since "New order #831" tells you about the
    business the same way the dashboard figures do.
--}}
@php
    $unread_count = 0;
    $admin_notifications = collect();

    try {
        $admin  = auth()->guard('admin')->user();
        $canSee = [];
        if ($admin) {
            if ($admin->can('order-list'))  { $canSee[] = 'order_id'; }
            if ($admin->can('ticket-list')) { $canSee[] = 'ticket_id'; }
            if ($admin->can('job-list'))    { $canSee[] = 'job_post_id'; }
        }

        if (! empty($canSee)) {
            $query = \App\AdminNotification::where('status', 0)->where(function ($q) use ($canSee) {
                foreach ($canSee as $column) {
                    $q->orWhereNotNull($column);
                }
            });

            $unread_count = (clone $query)->count();
            $admin_notifications = $query->latest('id')->take(8)->get();
        }
    } catch (\Exception $e) {
        // A broken notification table must never take the whole panel down.
    }
@endphp

<li class="dropdown hp-notify">
    <i class="ti-bell dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
       role="button" aria-label="{{ __('Notifications') }}">
        @if($unread_count > 0)
            <span>{{ $unread_count > 99 ? '99+' : $unread_count }}</span>
        @endif
    </i>

    <div class="dropdown-menu bell-notify-box notify-box hp-notify__box" x-placement="top-start">
        <div class="hp-notify__head">
            <span class="hp-notify__title">
                {{ __('Notifications') }}
                @if($unread_count > 0)
                    <em class="hp-notify__count">{{ $unread_count }}</em>
                @endif
            </span>
            <a href="{{ route('admin.notifications.all') }}" class="hp-notify__all">{{ __('View all') }}</a>
        </div>

        <div class="hp-notify__list">
            @forelse($admin_notifications as $data)
                @php
                    if (! empty($data->order_id)) {
                        $item = [
                            'url'   => route('admin.orders.details', $data->order_id),
                            'icon'  => 'las la-shopping-bag',
                            'kind'  => 'order',
                            'label' => __('New order'),
                            'ref'   => '#' . $data->order_id,
                        ];
                    } elseif (! empty($data->ticket_id)) {
                        $item = [
                            'url'   => route('admin.ticket.details', $data->ticket_id),
                            'icon'  => 'las la-life-ring',
                            'kind'  => 'ticket',
                            'label' => __('New support ticket'),
                            'ref'   => '#' . $data->ticket_id,
                        ];
                    } elseif (! empty($data->job_post_id)) {
                        $slug  = optional($data->buyerJob)->slug;
                        $item = [
                            'url'   => $slug ? route('job.post.details', $slug) : route('admin.notifications.all'),
                            'icon'  => 'las la-briefcase',
                            'kind'  => 'job',
                            'label' => __('New job posted'),
                            'ref'   => '#' . $data->job_post_id,
                        ];
                    } else {
                        $item = null;
                    }
                @endphp

                @if($item)
                    <a href="{{ $item['url'] }}" class="hp-notify__item">
                        <span class="hp-notify__icon is-{{ $item['kind'] }}"><i class="{{ $item['icon'] }}"></i></span>
                        <span class="hp-notify__body">
                            <span class="hp-notify__text">{{ $item['label'] }} <b>{{ $item['ref'] }}</b></span>
                            <span class="hp-notify__time">{{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }}</span>
                        </span>
                    </a>
                @endif
            @empty
                <div class="hp-notify__empty">
                    <i class="las la-bell-slash"></i>
                    <p>{{ __('Nothing new right now.') }}</p>
                </div>
            @endforelse
        </div>

        @if($unread_count > $admin_notifications->count())
            <a href="{{ route('admin.notifications.all') }}" class="hp-notify__more">
                {{ __('Show :count more', ['count' => $unread_count - $admin_notifications->count()]) }}
            </a>
        @endif
    </div>
</li>
