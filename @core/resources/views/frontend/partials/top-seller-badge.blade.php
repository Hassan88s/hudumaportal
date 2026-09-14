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
    @php
        // Tier styling — matches the leaderboard podium colours
        if ($rank <= 3) {
            $tier      = 'gold';
            $medal     = ['🥇','🥈','🥉'][$rank - 1];
            $bg        = ['linear-gradient(135deg,#fbbf24,#f59e0b)', 'linear-gradient(135deg,#d1d5db,#9ca3af)', 'linear-gradient(135deg,#fb923c,#c2410c)'][$rank - 1];
            $textColor = '#1f2733';
        } elseif ($rank <= 10) {
            $tier      = 'orange';
            $medal     = '';
            $bg        = 'linear-gradient(135deg,#ff8a54,#ff6b3d)';
            $textColor = '#fff';
        } elseif ($rank <= 50) {
            $tier      = 'blue';
            $medal     = '';
            $bg        = 'linear-gradient(135deg,#60a5fa,#3b82f6)';
            $textColor = '#fff';
        } else {
            $tier      = 'grey';
            $medal     = '';
            $bg        = 'linear-gradient(135deg,#6b7280,#4b5563)';
            $textColor = '#fff';
        }
    @endphp

    @if($variant === 'full')
        <a href="{{ route('top.sellers.public') }}"
           style="display:inline-flex;align-items:center;gap:10px;padding:8px 16px;background:{{ $bg }};color:{{ $textColor }};border-radius:999px;font-weight:800;font-size:14px;text-decoration:none;box-shadow:0 4px 12px rgba(255,138,84,.25);white-space:nowrap"
           title="{{ __('This seller is ranked #:rank in the Top 100 Sellers on Huduma Portal', ['rank' => $rank]) }}">
            @if($medal)<span style="font-size:18px">{{ $medal }}</span>@endif
            <span style="text-transform:uppercase;letter-spacing:.5px;font-size:11px;opacity:.85">{{ __('Top') }}</span>
            <span style="font-size:16px;font-weight:900">#{{ $rank }}</span>
        </a>
    @else
        {{-- Compact chip for cards and lists --}}
        <a href="{{ route('top.sellers.public') }}"
           style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;background:{{ $bg }};color:{{ $textColor }};border-radius:999px;font-weight:800;font-size:11px;text-decoration:none;line-height:1;vertical-align:middle"
           title="{{ __('Top :rank Seller', ['rank' => $rank]) }}">
            @if($medal)<span style="font-size:13px">{{ $medal }}</span>@else<span style="opacity:.85;font-size:9px;letter-spacing:.4px">TOP</span>@endif
            <span>#{{ $rank }}</span>
        </a>
    @endif
@endif
