@extends('backend.admin-master')

@section('site-title')
    {{ __('Ride Booking') }}
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">

            {{-- Nothing below means anything while the feature is off, so say
                 so once, at the top, with the way to switch it on. --}}
            @unless ($enabled)
                <div class="col-12 mt-5">
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <span>
                            <strong>{{ __('Ride booking is off.') }}</strong>
                            {{ __('Passengers cannot book and riders cannot apply.') }}
                        </span>
                        @can('ride-settings')
                            <a href="{{ route('admin.ride.settings') }}" class="btn btn-sm btn-warning">
                                {{ __('Turn it on') }}
                            </a>
                        @endcan
                    </div>
                </div>
            @endunless

            {{-- On the road right now. --}}
            <div class="col-12 {{ $enabled ? 'mt-5' : '' }}">
                @include('backend.pages.ride.partials.stat-cards', [
                    'cardCol' => 'col-xl col-md-4 col-6',
                    'cards'   => [
                        ['value' => number_format($now['online']),    'label' => __('Riders on duty'),      'tone' => 'green',  'icon' => 'user-check'],
                        ['value' => number_format($now['approved']),  'label' => __('Approved riders'),     'tone' => 'slate',  'icon' => 'id-card'],
                        ['value' => number_format($now['searching']), 'label' => __('Looking for a rider'), 'tone' => 'amber',  'icon' => 'search-location'],
                        ['value' => number_format($now['heading']),   'label' => __('Rider on the way'),    'tone' => 'blue',   'icon' => 'motorcycle'],
                        ['value' => number_format($now['ongoing']),   'label' => __('On the way'),           'tone' => 'teal',   'icon' => 'route'],
                    ],
                ])
            </div>

            {{-- Money. One row per period so the periods read against each
                 other rather than needing to be held in your head. --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{ __('Trips and money') }}</h4>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Period') }}</th>
                                        <th class="text-right">{{ __('Trips') }}</th>
                                        <th class="text-right">{{ __('Completed') }}</th>
                                        <th class="text-right">{{ __('Fares') }}</th>
                                        <th class="text-right">{{ __('Our commission') }}</th>
                                        <th class="text-right">{{ __('To riders') }}</th>
                                        <th class="text-right">{{ __('Refunded') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($periods as $label => $p)
                                        <tr>
                                            <td><strong>{{ $label }}</strong></td>
                                            <td class="text-right">{{ number_format($p['trips']) }}</td>
                                            <td class="text-right">{{ number_format($p['completed']) }}</td>
                                            <td class="text-right">{{ $currency }} {{ number_format($p['fares'], 0) }}</td>
                                            <td class="text-right text-success">
                                                {{ $currency }} {{ number_format($p['commission'], 0) }}
                                            </td>
                                            <td class="text-right">{{ $currency }} {{ number_format($p['earnings'], 0) }}</td>
                                            <td class="text-right {{ $p['refunded'] > 0 ? 'text-danger' : 'text-muted' }}">
                                                {{ $currency }} {{ number_format($p['refunded'], 0) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="text-muted small mb-0">
                            {{ __('Money is counted from completed trips only. A fare being held for a trip still running is not revenue yet.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- What is waiting on somebody. A zero is worth showing: it says
                 there is nothing to do. --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{ __('Needs attention') }}</h4>

                        <ul class="list-group list-group-flush">
                            @foreach ($attention as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                                    <span class="badge badge-pill {{ $item['count'] > 0 ? 'badge-danger' : 'badge-light' }}">
                                        {{ number_format($item['count']) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- The numbers a fare is built from, so the pricing in force
                     is visible without opening the settings screen. --}}
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="header-title mb-0">{{ __('Pricing in force') }}</h4>
                            @can('ride-settings')
                                <a href="{{ route('admin.ride.settings') }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('Change') }}
                                </a>
                            @endcan
                        </div>

                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td>{{ __('Our commission') }}</td>
                                    <td class="text-right"><strong>{{ $fares['commission'] }}%</strong></td>
                                </tr>
                                <tr>
                                    <td>{{ __('Base fare') }}</td>
                                    <td class="text-right">{{ $currency }} {{ number_format($fares['base'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Per km') }}</td>
                                    <td class="text-right">{{ $currency }} {{ number_format($fares['per_km'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Per minute') }}</td>
                                    <td class="text-right">{{ $currency }} {{ number_format($fares['per_min'], 0) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Dispatch radius') }}</td>
                                    <td class="text-right">{{ $fares['radius'] }} {{ __('km') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Busiest riders, last 30 days. --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="header-title mb-0">{{ __('Busiest riders') }}</h4>
                            <span class="text-muted small">{{ __('last 30 days') }}</span>
                        </div>

                        @if ($top->isEmpty())
                            <p class="text-muted mb-0">{{ __('No completed trips yet.') }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Rider') }}</th>
                                            <th class="text-right">{{ __('Trips') }}</th>
                                            <th class="text-right">{{ __('Earned') }}</th>
                                            <th class="text-right">{{ __('Rating') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($top as $r)
                                            <tr>
                                                <td>
                                                    @can('ride-rider-list')
                                                        <a href="{{ route('admin.ride.rider.details', $r['id']) }}">
                                                            {{ $r['name'] }}
                                                        </a>
                                                    @else
                                                        {{ $r['name'] }}
                                                    @endcan
                                                </td>
                                                <td class="text-right">{{ number_format($r['trips']) }}</td>
                                                <td class="text-right">
                                                    {{ $currency }} {{ number_format($r['earned'], 0) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ $r['rating'] ? $r['rating'] . ' ★' : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- The last few trips, as a way in rather than a list to read. --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="header-title mb-0">{{ __('Latest trips') }}</h4>
                            @can('ride-trip-list')
                                <a href="{{ route('admin.ride.trips') }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('All trips') }}
                                </a>
                            @endcan
                        </div>

                        @if ($recent->isEmpty())
                            <p class="text-muted mb-0">{{ __('Nobody has booked a trip yet.') }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Passenger') }}</th>
                                            <th>{{ __('Rider') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="text-right">{{ __('Fare') }}</th>
                                            <th>{{ __('Booked') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recent as $ride)
                                            <tr>
                                                <td>
                                                    @can('ride-trip-list')
                                                        <a href="{{ route('admin.ride.trip.details', $ride->id) }}">
                                                            {{ $ride->id }}
                                                        </a>
                                                    @else
                                                        {{ $ride->id }}
                                                    @endcan
                                                </td>
                                                <td>{{ optional($ride->passenger)->name ?: __('Removed') }}</td>
                                                <td>
                                                    {{ $ride->rider_id
                                                        ? (optional($ride->rider)->full_name
                                                            ?: optional(optional($ride->rider)->user)->name
                                                            ?: __('Removed'))
                                                        : '—' }}
                                                </td>
                                                <td>
                                                    <span class="badge badge-light">
                                                        {{ $labels[$ride->status] ?? __('Unknown') }}
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    {{ $currency }} {{ number_format($ride->fare, 0) }}
                                                </td>
                                                <td class="text-muted small">
                                                    {{ $ride->created_at ? $ride->created_at->diffForHumans() : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
