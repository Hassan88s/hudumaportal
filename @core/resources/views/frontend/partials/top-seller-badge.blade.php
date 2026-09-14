{{--
    Top Seller rank badge — small reusable component.

    Usage:
        @include('frontend.partials.top-seller-badge', [
            'sellerId' => $seller->id,
            'variant'  => 'compact',   // 'compact' | 'full'  (default compact)
        ])

    Renders nothing if the seller is not in the top 100.
--}}

@php
    /** @var int|null $sellerId */
    $variant  = $variant ?? 'compact';
    $rank     = app(\App\Services\TopSellersService::class)->getRankForSeller((int) $sellerId);
@endphp

@if($rank)
    @if($variant === 'full')
        <a href="{{ route('top.sellers.public') }}"
           style="display:inline-flex;align-items:center;gap:10px;padding:8px 16px;background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;border-radius:999px;font-weight:800;font-size:14px;text-decoration:none;box-shadow:0 4px 12px rgba(255,138,84,.25);white-space:nowrap"
           title="{{ __('This seller is ranked #:rank in the Top 100 Sellers on Huduma Portal', ['rank' => $rank]) }}">
            <span style="text-transform:uppercase;letter-spacing:.5px;font-size:11px;opacity:.9">{{ __('Top Seller') }}</span>
            <span style="font-size:16px;font-weight:900">#{{ $rank }}</span>
        </a>
    @else
        {{-- Compact chip for cards and lists --}}
        <a href="{{ route('top.sellers.public') }}"
           style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;border-radius:999px;font-weight:800;font-size:11px;text-decoration:none;line-height:1;vertical-align:middle"
           title="{{ __('Top :rank Seller', ['rank' => $rank]) }}">
            <span style="opacity:.9;font-size:9px;letter-spacing:.4px">TOP</span>
            <span>#{{ $rank }}</span>
        </a>
    @endif
@endif
