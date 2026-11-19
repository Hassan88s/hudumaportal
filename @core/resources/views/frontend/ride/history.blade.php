@extends('frontend.ride.master')

@section('title', __('My trips'))
@section('back', route('ride.index'))

@section('style')
<style>
    .trip { display: block; text-decoration: none; color: inherit; }
    .trip .top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; }
    .trip .to  { font-weight: 600; font-size: 15px; }
    .trip .amt { font-weight: 700; white-space: nowrap; }
    .trip .when { color: var(--muted); font-size: 13px; margin-top: 2px; }
    .stars-ro { color: var(--warn); font-size: 13px; }
</style>
@endsection

@section('content')

    @forelse ($rides as $ride)
        <a class="card trip" href="{{ route('ride.trip', $ride->id) }}">
            <div class="top">
                <span class="to">{{ $ride->dropoff_address ?: __('Trip #:id', ['id' => $ride->id]) }}</span>
                <span class="amt">{{ $currency }} {{ number_format($ride->fare, 0) }}</span>
            </div>
            <div class="when">
                {{ optional($ride->requested_at ?? $ride->created_at)->format('d M Y, H:i') }}
                · {{ $ride->statusLabel() }}
            </div>
            @if ($ride->rating)
                <div class="stars-ro">{{ str_repeat('★', $ride->rating) }}</div>
            @endif
        </a>
    @empty
        <div class="card">
            <p class="muted" style="margin:0">{{ __('You have not taken a ride yet.') }}</p>
        </div>
        <a href="{{ route('ride.index') }}" class="btn">{{ __('Book your first ride') }}</a>
    @endforelse

    @if ($rides->hasPages())
        <div class="card" style="text-align:center">
            {{ $rides->links() }}
        </div>
    @endif

@endsection
