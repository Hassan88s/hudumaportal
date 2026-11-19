@extends('backend.admin-master')

@section('site-title')
    {{ __('Trip #:id', ['id' => $ride->id]) }}
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row mt-5">

            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-body">
                        <x-msg.success/>
                        <x-msg.error/>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="header-title mb-0">{{ __('Trip #:id', ['id' => $ride->id]) }}</h4>
                            <span class="badge badge-pill badge-info">{{ $ride->statusLabel() }}</span>
                        </div>

                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="pl-0">{{ __('Pick-up') }}</th>
                                <td>
                                    {{ $ride->pickup_address ?: __('Point on the map') }}
                                    <div class="text-muted small">{{ $ride->pickup_lat }}, {{ $ride->pickup_lng }}</div>
                                </td>
                            </tr>
                            <tr>
                                <th class="pl-0">{{ __('Drop-off') }}</th>
                                <td>
                                    {{ $ride->dropoff_address ?: __('Point on the map') }}
                                    <div class="text-muted small">{{ $ride->dropoff_lat }}, {{ $ride->dropoff_lng }}</div>
                                </td>
                            </tr>
                            <tr>
                                <th class="pl-0">{{ __('Distance / time') }}</th>
                                <td>{{ $ride->distance_km }} km · {{ $ride->duration_min }} {{ __('min') }}</td>
                            </tr>
                            <tr>
                                <th class="pl-0">{{ __('Map') }}</th>
                                <td>
                                    <a target="_blank" rel="noopener"
                                       href="https://www.google.com/maps/dir/?api=1&origin={{ $ride->pickup_lat }},{{ $ride->pickup_lng }}&destination={{ $ride->dropoff_lat }},{{ $ride->dropoff_lng }}">
                                        {{ __('Open the route') }}
                                    </a>
                                </td>
                            </tr>
                            @if ($ride->cancel_reason)
                                <tr>
                                    <th class="pl-0">{{ __('Reason given') }}</th>
                                    <td>{{ $ride->cancel_reason }}</td>
                                </tr>
                            @endif
                            @if ($ride->rating)
                                <tr>
                                    <th class="pl-0">{{ __('Rating') }}</th>
                                    <td>
                                        {{ str_repeat('★', $ride->rating) }}
                                        @if ($ride->rating_comment)
                                            <div class="text-muted small">{{ $ride->rating_comment }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{ __('What happened when') }}</h4>

                        @php
                            $steps = [
                                __('Requested')  => $ride->requested_at,
                                __('Accepted')   => $ride->accepted_at,
                                __('Rider there') => $ride->arrived_at,
                                __('Started')    => $ride->started_at,
                                __('Completed')  => $ride->completed_at,
                                __('Cancelled')  => $ride->cancelled_at,
                            ];
                        @endphp

                        <table class="table table-borderless mb-0">
                            @foreach ($steps as $label => $at)
                                @if ($at)
                                    <tr>
                                        <th class="pl-0">{{ $label }}</th>
                                        <td>{{ $at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{ __('Money') }}</h4>

                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="pl-0">{{ __('Fare charged') }}</th>
                                <td class="text-right">{{ $currency }} {{ number_format($ride->fare, 0) }}</td>
                            </tr>
                            <tr>
                                <th class="pl-0">{{ __('Our commission') }}</th>
                                <td class="text-right">{{ $currency }} {{ number_format($ride->commission, 0) }}</td>
                            </tr>
                            <tr>
                                <th class="pl-0">{{ __('Paid to the rider') }}</th>
                                <td class="text-right">{{ $currency }} {{ number_format($ride->rider_earning, 0) }}</td>
                            </tr>
                            @if ($ride->cancellation_fee > 0)
                                <tr>
                                    <th class="pl-0">{{ __('Cancellation fee') }}</th>
                                    <td class="text-right">{{ $currency }} {{ number_format($ride->cancellation_fee, 0) }}</td>
                                </tr>
                            @endif
                            @if ($ride->refunded_amount > 0)
                                <tr>
                                    <th class="pl-0">{{ __('Returned to the passenger') }}</th>
                                    <td class="text-right">{{ $currency }} {{ number_format($ride->refunded_amount, 0) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th class="pl-0">{{ __('Wallet') }}</th>
                                <td class="text-right">
                                    @php
                                        $pay = [
                                            \App\Ride::PAY_NONE     => __('nothing taken'),
                                            \App\Ride::PAY_HELD     => __('fare held'),
                                            \App\Ride::PAY_SETTLED  => __('settled'),
                                            \App\Ride::PAY_REFUNDED => __('refunded'),
                                        ][$ride->payment_state] ?? '—';
                                    @endphp
                                    {{ $pay }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="header-title mb-3">{{ __('Passenger') }}</h4>
                        @if ($ride->passenger)
                            <p class="mb-1"><strong>{{ $ride->passenger->name }}</strong></p>
                            <p class="text-muted mb-0">{{ $ride->passenger->email }}</p>
                        @else
                            <p class="text-muted mb-0">{{ __('This account no longer exists.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-3">{{ __('Rider') }}</h4>
                        @if ($ride->rider)
                            <p class="mb-1">
                                <strong>{{ $ride->rider->full_name ?: optional($ride->rider->user)->name }}</strong>
                            </p>
                            <p class="text-muted mb-1">
                                {{ $ride->rider->bike_plate }}
                                @if ($ride->rider->bike_model) · {{ $ride->rider->bike_model }} @endif
                            </p>
                            <p class="text-muted mb-3">{{ $ride->rider->phone }}</p>
                            @can('ride-rider-list')
                                <a href="{{ route('admin.ride.rider.details', $ride->rider->id) }}"
                                   class="btn btn-sm btn-outline-primary">{{ __('Open the rider') }}</a>
                            @endcan
                        @else
                            <p class="text-muted mb-0">{{ __('Nobody accepted this trip.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        @if ($ride->isLive())
            @can('ride-trip-resolve')
                {{-- Only shown for a trip that is still open: a rider who
                     accepted and then disappeared leaves the fare held, and
                     this is the only way to release it. --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="header-title mb-2">{{ __('This trip is still open') }}</h4>
                        <p class="text-muted">
                            {{ __('Close it only if it has clearly stalled. The passenger is refunded the whole fare and the rider is paid nothing.') }}
                        </p>
                        <form method="POST" action="{{ route('admin.ride.trip.resolve', $ride->id) }}"
                              onsubmit="return confirm('{{ __('Close this trip and refund the passenger?') }}');">
                            @csrf
                            <div class="form-group">
                                <input type="text" name="reason" class="form-control"
                                       maxlength="255" placeholder="{{ __('Why are you closing it? (optional)') }}">
                            </div>
                            <button type="submit" class="btn btn-danger">
                                {{ __('Close the trip and refund') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        @endif

        <a href="{{ route('admin.ride.trips') }}" class="btn btn-outline-secondary">{{ __('Back to trips') }}</a>
    </div>
@endsection
