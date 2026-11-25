@extends('backend.admin-master')

@section('site-title')
    {{ __('Trips') }}
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">

            {{-- The totals follow the filter below, so they always describe
                 exactly the trips on screen rather than the whole table. --}}
            <div class="col-12 mt-5">
                @include('backend.pages.ride.partials.stat-cards', ['cards' => [
                    ['value' => number_format($totals['trips']),                        'label' => __('Trips'),           'tone' => 'slate',  'icon' => 'route'],
                    ['value' => number_format($totals['completed']),                    'label' => __('Completed'),       'tone' => 'green',  'icon' => 'check-circle'],
                    ['value' => number_format($totals['fares'], 0),   'label' => __('Fares collected') . ' (' . $currency . ')', 'tone' => 'blue',   'icon' => 'wallet'],
                    ['value' => number_format($totals['commission'], 0), 'label' => __('Our commission') . ' (' . $currency . ')', 'tone' => 'purple', 'icon' => 'percentage'],
                    ['value' => number_format($totals['earnings'], 0),   'label' => __('Paid to riders') . ' (' . $currency . ')', 'tone' => 'amber',  'icon' => 'motorcycle'],
                    ['value' => number_format($totals['refunded'], 0),   'label' => __('Refunded') . ' (' . $currency . ')',       'tone' => 'red',    'icon' => 'undo'],
                ]])
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <x-msg.success/>
                        <x-msg.error/>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="header-title mb-0">
                                {{ __('Trips') }}
                                @if ($totals['live'])
                                    <span class="badge badge-success ml-2">
                                        {{ $totals['live'] }} {{ __('running now') }}
                                    </span>
                                @endif
                            </h4>
                            @can('ride-rider-list')
                                <a href="{{ route('admin.ride.riders') }}" class="btn btn-outline-primary">
                                    {{ __('Riders') }}
                                </a>
                            @endcan
                        </div>

                        <form method="get" action="{{ route('admin.ride.trips') }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <select name="status" class="form-control">
                                        <option value="">{{ __('Any status') }}</option>
                                        @foreach ($labels as $value => $label)
                                            <option value="{{ $value }}"
                                                @if ((string) $status === (string) $value) selected @endif>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="date" name="from" value="{{ $from }}" class="form-control"
                                           placeholder="{{ __('From') }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="date" name="to" value="{{ $to }}" class="form-control"
                                           placeholder="{{ __('To') }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="submit" class="btn btn-primary btn-block">{{ __('Filter') }}</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-wrap table-responsive">
                            <table class="table text-center">
                                <thead class="text-capitalize">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('When') }}</th>
                                    <th>{{ __('Passenger') }}</th>
                                    <th>{{ __('Rider') }}</th>
                                    <th>{{ __('Trip') }}</th>
                                    <th>{{ __('Fare') }}</th>
                                    <th>{{ __('Commission') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($trips as $trip)
                                    <tr>
                                        <td>{{ $trip->id }}</td>
                                        <td>{{ optional($trip->requested_at ?? $trip->created_at)->format('d/m/y H:i') }}</td>
                                        <td>
                                            {{ optional($trip->passenger)->name ?: '—' }}
                                            <div class="text-muted small">{{ optional($trip->passenger)->email }}</div>
                                        </td>
                                        <td>
                                            @if ($trip->rider)
                                                {{ $trip->rider->full_name ?: optional($trip->rider->user)->name }}
                                                <div class="text-muted small">{{ $trip->rider->bike_plate }}</div>
                                            @else
                                                <span class="text-muted">{{ __('nobody') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $trip->distance_km }} km<div class="text-muted small">{{ $trip->duration_min }} min</div></td>
                                        <td>{{ $currency }} {{ number_format($trip->fare, 0) }}</td>
                                        <td>
                                            @if ($trip->commission > 0)
                                                {{ $currency }} {{ number_format($trip->commission, 0) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $class = [
                                                    \App\Ride::COMPLETED           => 'badge-success',
                                                    \App\Ride::EXPIRED             => 'badge-secondary',
                                                    \App\Ride::CANCELLED_PASSENGER => 'badge-danger',
                                                    \App\Ride::CANCELLED_RIDER     => 'badge-danger',
                                                ][$trip->status] ?? 'badge-warning';
                                            @endphp
                                            <span class="badge {{ $class }}">{{ $trip->statusLabel() }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ride.trip.details', $trip->id) }}"
                                               class="btn btn-sm btn-info">{{ __('Open') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-muted py-4">{{ __('No trips match that filter.') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $trips->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
