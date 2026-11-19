@extends('frontend.user.rider.rider-master')

@section('title', __('Become a rider'))

@section('content')

    @if ($rider && $rider->status === \App\Rider::REJECTED)
        <div class="alert alert-error">
            <strong>{{ __('Your last application was not approved.') }}</strong>
            @if ($rider->rejection_reason)
                <div style="margin-top:6px">{{ $rider->rejection_reason }}</div>
            @endif
            <div style="margin-top:6px">{{ __('Fix what is noted above and send it again.') }}</div>
        </div>
    @elseif ($rider)
        <div class="alert alert-info">
            {{ __('Your application is already with us. Sending this form again replaces what we have.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('rider.apply') }}" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <h2>{{ __('About you') }}</h2>

            <label for="full_name">{{ __('Full name, as on your licence') }}</label>
            <input type="text" id="full_name" name="full_name"
                   value="{{ old('full_name', optional($rider)->full_name ?? auth()->user()->name) }}" required>
            @error('full_name') <div class="err">{{ $message }}</div> @enderror

            <label for="phone">{{ __('Phone number') }}</label>
            <input type="tel" id="phone" name="phone" inputmode="tel"
                   value="{{ old('phone', optional($rider)->phone ?? auth()->user()->phone) }}" required>
            @error('phone') <div class="err">{{ $message }}</div> @enderror

            <label for="national_id">{{ __('National ID number') }}</label>
            <input type="text" id="national_id" name="national_id"
                   value="{{ old('national_id', optional($rider)->national_id) }}" required>
            @error('national_id') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="card">
            <h2>{{ __('Your licence') }}</h2>

            <label for="licence_number">{{ __('Licence number') }}</label>
            <input type="text" id="licence_number" name="licence_number"
                   value="{{ old('licence_number', optional($rider)->licence_number) }}" required>
            @error('licence_number') <div class="err">{{ $message }}</div> @enderror

            <label for="licence_expiry">{{ __('Expires on') }}</label>
            <input type="date" id="licence_expiry" name="licence_expiry"
                   value="{{ old('licence_expiry', optional(optional($rider)->licence_expiry)->format('Y-m-d')) }}" required>
            @error('licence_expiry') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="card">
            <h2>{{ __('Your bike') }}</h2>

            <label for="bike_plate">{{ __('Plate number') }}</label>
            <input type="text" id="bike_plate" name="bike_plate"
                   value="{{ old('bike_plate', optional($rider)->bike_plate) }}" required>
            @error('bike_plate') <div class="err">{{ $message }}</div> @enderror

            <label for="bike_model">{{ __('Make and model') }} <span class="muted">({{ __('optional') }})</span></label>
            <input type="text" id="bike_model" name="bike_model"
                   value="{{ old('bike_model', optional($rider)->bike_model) }}">
            @error('bike_model') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="card">
            <h2>{{ __('Documents') }}</h2>
            <p class="muted" style="margin-top:-6px">
                {{ __('Photos or PDF, up to 5 MB each. A clear photo taken on your phone is fine.') }}
            </p>

            @php
                $docs = [
                    'national_id_photo' => [__('National ID'), true],
                    'licence_photo'     => [__('Driving licence'), true],
                    'bike_reg_photo'    => [__('Bike registration'), true],
                    'bike_photo'        => [__('Photo of the bike'), false],
                    'insurance_photo'   => [__('Insurance'), false],
                ];
            @endphp

            @foreach ($docs as $field => [$label, $required])
                <label for="{{ $field }}">
                    {{ $label }}
                    @unless ($required) <span class="muted">({{ __('optional') }})</span> @endunless
                </label>
                <input type="file" id="{{ $field }}" name="{{ $field }}"
                       accept="image/*,application/pdf"
                       @if ($required && ! optional($rider)->{$field}) required @endif>
                @if (optional($rider)->{$field})
                    <div class="have">
                        <i class="las la-check-circle"></i>
                        {{ __('Already uploaded. Choose a file only to replace it.') }}
                    </div>
                @endif
                @error($field) <div class="err">{{ $message }}</div> @enderror
            @endforeach
        </div>

        <button type="submit" class="btn">
            {{ $rider ? __('Send it again') : __('Send application') }}
        </button>

        <p class="muted" style="text-align:center;margin-top:14px">
            {{ __('We check every application by hand. You will see the result on this page.') }}
        </p>
    </form>

@endsection
