@extends('frontend.ride.master')

@section('title', __('Earnings'))
@section('back', route('rider.dashboard'))

@section('style')
<style>
    .big { text-align: center; padding: 24px 18px; }
    .big .n { font-size: 32px; font-weight: 800; letter-spacing: -.6px; }
    .big .l { color: var(--muted); font-size: 14px; }

    .trip .top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; }
    .trip .to  { font-weight: 600; font-size: 15px; }
    .trip .amt { font-weight: 700; white-space: nowrap; color: var(--accent); }
    .trip .when { color: var(--muted); font-size: 13px; margin-top: 2px; }
    .trip .amt.none { color: var(--muted); }
</style>
@endsection

@section('content')

    <div class="card big">
        <div class="n">{{ $currency }} {{ number_format($balance, 0) }}</div>
        <div class="l">{{ __('In your wallet now') }}</div>
    </div>

    <div class="card">
        <div class="kv"><span class="k">{{ __('Today') }}</span><span class="v">{{ $currency }} {{ number_format($today, 0) }}</span></div>
        <div class="kv"><span class="k">{{ __('Last 7 days') }}</span><span class="v">{{ $currency }} {{ number_format($week, 0) }}</span></div>
        <div class="kv"><span class="k">{{ __('All time') }}</span><span class="v">{{ $currency }} {{ number_format($lifetime, 0) }}</span></div>
        <div class="kv"><span class="k">{{ __('Trips finished') }}</span><span class="v">{{ $rider->total_trips }}</span></div>
    </div>

    <div class="card">
        <h2>{{ __('Trip history') }}</h2>

        @forelse ($trips as $trip)
            <div class="trip" style="padding:11px 0; border-bottom:1px solid var(--line)">
                <div class="top">
                    <span class="to">{{ \Illuminate\Support\Str::limit($trip->dropoff_address ?: __('Trip #:id', ['id' => $trip->id]), 30) }}</span>
                    <span class="amt {{ $trip->rider_earning > 0 ? '' : 'none' }}">
                        @if ($trip->rider_earning > 0)
                            + {{ $currency }} {{ number_format($trip->rider_earning, 0) }}
                        @elseif ($trip->cancellation_fee > 0)
                            + {{ $currency }} {{ number_format($trip->cancellation_fee, 0) }}
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="when">
                    {{ optional($trip->completed_at ?? $trip->cancelled_at ?? $trip->created_at)->format('d M, H:i') }}
                    · {{ $trip->statusLabel() }}
                </div>
            </div>
        @empty
            <p class="muted" style="margin:0">{{ __('No finished trips yet.') }}</p>
        @endforelse

        @if ($trips->hasPages())
            <div style="margin-top:16px; text-align:center">{{ $trips->links() }}</div>
        @endif
    </div>

@endsection
