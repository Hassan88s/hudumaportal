{{--
    Founder Provider badge — small reusable component.

    Rank = order in which sellers completed their FIRST order on Huduma Portal
    (earliest first), not registration date and not order count.

    Usage:
        @include('frontend.partials.top-seller-badge', [
            'sellerId' => $seller->id,
            'variant'  => 'compact',   // 'compact' | 'full'  (default compact)
        ])

    Renders nothing if the seller is not among the first 100.
    (The /top-sellers page is hidden for now, so the badge is not a link.)
--}}

@php
    /** @var int|null $sellerId */
    $variant = $variant ?? 'compact';
    $rank    = app(\App\Services\TopSellersService::class)->getFounderRank((int) $sellerId);
@endphp

@if($rank)
    @if($variant === 'corner')
        {{-- Small round badge pinned to the corner of the profile photo --}}
        <span style="position:absolute;right:-6px;bottom:-6px;z-index:3;display:inline-flex;flex-direction:column;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 6px;background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;border:2px solid #fff;border-radius:999px;box-shadow:0 3px 8px rgba(0,0,0,.18);line-height:1"
              title="{{ __('Founder Provider #:rank — one of the first sellers to complete an order on Huduma Portal', ['rank' => $rank]) }}">
            <span style="font-size:7px;font-weight:700;letter-spacing:.3px;opacity:.95">{{ __('FOUNDER') }}</span>
            <span style="font-size:12px;font-weight:900">#{{ $rank }}</span>
        </span>
    @elseif($variant === 'full')
        <span style="display:inline-flex;align-items:center;gap:10px;padding:8px 16px;background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;border-radius:999px;font-weight:800;font-size:14px;box-shadow:0 4px 12px rgba(255,138,84,.25);white-space:nowrap"
              title="{{ __('Founder Provider #:rank — one of the first sellers to complete an order on Huduma Portal', ['rank' => $rank]) }}">
            <span style="text-transform:uppercase;letter-spacing:.5px;font-size:11px;opacity:.9">{{ __('Founder Provider') }}</span>
            <span style="font-size:16px;font-weight:900">#{{ $rank }}</span>
        </span>
    @else
        {{-- Compact chip for cards and lists --}}
        <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;border-radius:999px;font-weight:800;font-size:11px;line-height:1;vertical-align:middle"
              title="{{ __('Founder Provider #:rank', ['rank' => $rank]) }}">
            <span style="opacity:.9;font-size:9px;letter-spacing:.4px">{{ __('FOUNDER') }}</span>
            <span>#{{ $rank }}</span>
        </span>
    @endif
@endif
