@extends('backend.admin-master')

@section('site-title')
    {{ __('Ride Settings') }}
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <x-msg.success/>
                        <x-msg.error/>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="header-title mb-0">{{ __('Ride Settings') }}</h4>
                            @can('ride-rider-list')
                                <a href="{{ route('admin.ride.riders') }}" class="btn btn-secondary">{{ __('Riders') }}</a>
                            @endcan
                        </div>

                        <form method="POST" action="{{ route('admin.ride.settings.update') }}">
                            @csrf

                            <div class="custom-control custom-switch mb-4">
                                <input type="checkbox" class="custom-control-input" id="ride_enabled"
                                       name="ride_enabled" value="1"
                                       {{ old('ride_enabled', $settings['ride_enabled']) == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="ride_enabled">
                                    {{ __('Ride booking is switched on') }}
                                </label>
                                <div class="text-muted small mt-1">
                                    {{ __('While this is off, the rider pages return a 404 and no trip can be booked.') }}
                                </div>
                            </div>

                            <h5 class="mb-3">{{ __('Fare') }}</h5>
                            <div class="row">
                                @php
                                    $money = [
                                        'ride_base_fare'        => [__('Base fare'), __('Charged on every trip before distance and time.')],
                                        'ride_price_per_km'     => [__('Price per km'), ''],
                                        'ride_price_per_minute' => [__('Price per minute'), ''],
                                        'ride_minimum_fare'     => [__('Minimum fare'), __('No trip costs less than this, however short.')],
                                        'ride_cancellation_fee' => [__('Cancellation fee'), __('Set 0 to charge nothing for a cancelled trip.')],
                                    ];
                                @endphp
                                @foreach ($money as $name => [$label, $hint])
                                    <div class="col-md-6 mb-3">
                                        <label for="{{ $name }}">{{ $label }}</label>
                                        <input type="number" step="0.01" min="0" class="form-control"
                                               id="{{ $name }}" name="{{ $name }}"
                                               value="{{ old($name, $settings[$name]) }}" required>
                                        @if ($hint)<div class="text-muted small">{{ $hint }}</div>@endif
                                        @error($name)<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                            </div>

                            <h5 class="mb-3 mt-3">{{ __('Commission and dispatch') }}</h5>
                            <div class="row">
                                @php
                                    $other = [
                                        'ride_commission_percent' => [__('Our commission (%)'), __('Taken from the fare. The rider keeps the rest.'), '0.01', 0, 100],
                                        'ride_search_radius_km'   => [__('Search radius (km)'), __('How far to look for a free rider.'), '0.1', 0.5, 100],
                                        'ride_request_timeout'    => [__('Seconds to accept'), __('How long a rider has before the trip goes to the next one.'), '1', 5, 300],
                                    ];
                                @endphp
                                @foreach ($other as $name => [$label, $hint, $step, $min, $max])
                                    <div class="col-md-4 mb-3">
                                        <label for="{{ $name }}">{{ $label }}</label>
                                        <input type="number" step="{{ $step }}" min="{{ $min }}" max="{{ $max }}"
                                               class="form-control" id="{{ $name }}" name="{{ $name }}"
                                               value="{{ old($name, $settings[$name]) }}" required>
                                        <div class="text-muted small">{{ $hint }}</div>
                                        @error($name)<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">{{ __('Save settings') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
