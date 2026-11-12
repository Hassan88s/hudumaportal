{{--
    All notifications.

    The old table had a column for each id and each message — Order ID,
    Ticket ID, Job Post ID, Order Message, Ticket Message, Job Post Message —
    so every row carried five empty cells and you had to read across six
    columns to learn one fact. A notification is one thing: a type, what it
    points at, and when it arrived.
--}}
@extends('backend.admin-master')
@section('site-title')
    {{__('All Notifications')}}
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40"></div>
                <x-msg.success/>
                <x-msg.error/>
            </div>
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between align-items-center flex-wrap">
                            <div class="left-content">
                                <h4 class="header-title">
                                    {{__('All Notifications')}}
                                    @if($unread_count > 0)
                                        <em class="hp-notify__count ml-2">{{ $unread_count }} {{ __('unread') }}</em>
                                    @endif
                                </h4>
                            </div>
                            @if($unread_count > 0)
                                <form action="{{ route('admin.notification.read.all') }}" method="post" class="mb-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="las la-check-double mr-1"></i>{{ __('Mark all as read') }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="table-wrap table-responsive mt-3">
                            <table class="table table-default">
                                <thead>
                                <th>{{__('Type')}}</th>
                                <th>{{__('Reference')}}</th>
                                <th>{{__('Message')}}</th>
                                <th>{{__('Received')}}</th>
                                <th>{{__('Status')}}</th>
                                <th class="text-right">{{__('Action')}}</th>
                                </thead>
                                <tbody>
                                @forelse($notifications as $data)
                                    @php
                                        /*
                                         * A job notification used to link to
                                         * the ticket screen with the job's id,
                                         * which opened the wrong record or
                                         * none at all.
                                         */
                                        if (! empty($data->order_id)) {
                                            $item = [
                                                'kind'  => 'order',
                                                'icon'  => 'las la-shopping-bag',
                                                'label' => __('Order'),
                                                'ref'   => '#' . $data->order_id,
                                                'text'  => __('New order placed'),
                                                'url'   => route('admin.orders.details', $data->order_id),
                                            ];
                                        } elseif (! empty($data->ticket_id)) {
                                            $item = [
                                                'kind'  => 'ticket',
                                                'icon'  => 'las la-life-ring',
                                                'label' => __('Support ticket'),
                                                'ref'   => '#' . $data->ticket_id,
                                                'text'  => __('New support ticket opened'),
                                                'url'   => route('admin.ticket.details', $data->ticket_id),
                                            ];
                                        } elseif (! empty($data->job_post_id)) {
                                            $slug = optional($data->buyerJob)->slug;
                                            $item = [
                                                'kind'  => 'job',
                                                'icon'  => 'las la-briefcase',
                                                'label' => __('Job post'),
                                                'ref'   => '#' . $data->job_post_id,
                                                'text'  => optional($data->buyerJob)->title ?: __('New job posted'),
                                                'url'   => $slug ? route('job.post.details', $slug) : null,
                                            ];
                                        } else {
                                            $item = null;
                                        }
                                    @endphp

                                    @if($item)
                                        <tr @class(['hp-notify__row--unread' => $data->status == 0])>
                                            <td>
                                                <span class="hp-notify__icon is-{{ $item['kind'] }} d-inline-flex align-middle mr-2">
                                                    <i class="{{ $item['icon'] }}"></i>
                                                </span>
                                                {{ $item['label'] }}
                                            </td>
                                            <td><b>{{ $item['ref'] }}</b></td>
                                            <td>{{ $item['text'] }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }}
                                                <span class="hp-notify__time d-block">
                                                    {{ \Carbon\Carbon::parse($data->created_at)->format('d M Y, H:i') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($data->status == 0)
                                                    <span class="badge badge-danger">{{ __('Unread') }}</span>
                                                @else
                                                    <span class="badge badge-success">{{ __('Read') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right text-nowrap">
                                                @if($item['url'])
                                                    <a class="btn btn-info btn-sm" target="_blank"
                                                       title="{{ __('Open') }}" href="{{ $item['url'] }}">
                                                        <i class="ti-eye"></i>
                                                    </a>
                                                @endif

                                                @if($data->status == 0)
                                                    <form action="{{ route('admin.notification.read', $data->id) }}"
                                                          method="post" class="d-inline-block mb-0">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary btn-sm"
                                                                title="{{ __('Mark as read') }}">
                                                            <i class="las la-check"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @can('notification-delete')
                                                    <x-delete-popover :url="route('admin.notification.delete',$data->id)"/>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="hp-notify__empty">
                                                <i class="las la-bell-slash"></i>
                                                <p>{{ __('Nothing new right now.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
