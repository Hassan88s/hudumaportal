@extends('frontend.ride.master')

@section('title', __('Your application'))

@section('content')

    <div class="card" style="text-align:center">
        @php
            $pill = [
                \App\Rider::PENDING   => 'pill-pending',
                \App\Rider::REJECTED  => 'pill-rejected',
                \App\Rider::SUSPENDED => 'pill-suspended',
            ][$rider->status] ?? 'pill-pending';
        @endphp

        <span class="pill {{ $pill }}">{{ $rider->statusLabel() }}</span>

        <p style="margin:18px 0 0">
            @if ($rider->status === \App\Rider::PENDING)
                {{ __('We have your papers and are checking them. This usually takes a day or two.') }}
            @elseif ($rider->status === \App\Rider::REJECTED)
                {{ __('We could not approve this application.') }}
            @elseif ($rider->status === \App\Rider::SUSPENDED)
                {{ __('Your rider account is suspended, so you cannot take trips right now.') }}
            @endif
        </p>

        @if ($rider->rejection_reason)
            <div class="alert alert-error" style="text-align:left;margin-top:16px">
                {{ $rider->rejection_reason }}
            </div>
        @endif

        @if ($rider->submitted_at)
            <p class="muted" style="margin-top:14px">
                {{ __('Sent') }} {{ $rider->submitted_at->diffForHumans() }}
            </p>
        @endif
    </div>

    <div class="card">
        <h2>{{ __('What we have') }}</h2>

        @php
            $rows = [
                __('Name')     => $rider->full_name,
                __('Phone')    => $rider->phone,
                __('Licence')  => $rider->licence_number,
                __('Plate')    => $rider->bike_plate,
            ];
        @endphp

        @foreach ($rows as $label => $value)
            <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line)">
                <span class="muted">{{ $label }}</span>
                <span>{{ $value ?: '—' }}</span>
            </div>
        @endforeach

        @php $missing = $rider->missingDocuments(); @endphp
        @if ($missing)
            <div class="alert alert-error" style="margin-top:16px">
                {{ __('Still missing:') }} {{ count($missing) }} {{ __('document(s)') }}
            </div>
        @endif
    </div>

    @if (in_array($rider->status, [\App\Rider::PENDING, \App\Rider::REJECTED], true))
        <a href="{{ route('rider.apply') }}" class="btn" style="text-decoration:none;text-align:center">
            {{ $rider->status === \App\Rider::REJECTED ? __('Apply again') : __('Change my details') }}
        </a>
    @endif

@endsection
