@extends('frontend.ride.master')

@section('title', __('My ratings'))
@section('back', route('rider.dashboard'))

@section('style')
<style>
    .score { text-align: center; padding: 24px 18px; }
    .score .n { font-size: 44px; font-weight: 800; letter-spacing: -1.5px; line-height: 1; }
    .score .s { color: var(--warn); font-size: 20px; letter-spacing: 3px; margin: 8px 0 4px; }
    .score .l { color: var(--muted); font-size: 14px; }

    .bar { display: flex; align-items: center; gap: 10px; padding: 5px 0; }
    .bar .k { width: 34px; color: var(--muted); font-size: 13px; text-align: right; }
    .bar .track { flex: 1; height: 8px; border-radius: 999px; background: #0d1420; overflow: hidden; }
    .bar .fill { height: 100%; background: var(--warn); border-radius: 999px; }
    .bar .v { width: 28px; font-size: 13px; color: var(--muted); }

    .note { padding: 13px 0; border-bottom: 1px solid var(--line); }
    .note:last-child { border-bottom: 0; }
    .note .head { display: flex; justify-content: space-between; gap: 10px; align-items: baseline; }
    .note .stars { color: var(--warn); letter-spacing: 2px; font-size: 14px; }
    .note .when { color: var(--muted); font-size: 12px; white-space: nowrap; }
    .note .what { margin-top: 4px; font-size: 14px; }
</style>
@endsection

@section('content')

    @php $avg = $rider->rating_count ? $rider->averageRating() : 0; @endphp

    <div class="card score">
        @if ($rider->rating_count)
            <div class="n">{{ number_format($avg, 1) }}</div>
            <div class="s">{{ str_repeat('★', (int) round($avg)) }}{{ str_repeat('☆', 5 - (int) round($avg)) }}</div>
            <div class="l">
                {{ trans_choice('{1} from one rating|[2,*] from :count ratings', $rider->rating_count, ['count' => $rider->rating_count]) }}
            </div>
        @else
            <div class="n">—</div>
            <div class="l">{{ __('No ratings yet. They start arriving once you finish trips.') }}</div>
        @endif
    </div>

    @if ($rider->rating_count)
        <div class="card">
            <h2>{{ __('How the ratings split') }}</h2>
            @php $most = max(1, max($breakdown)); @endphp
            @foreach ($breakdown as $stars => $count)
                <div class="bar">
                    <span class="k">{{ $stars }} ★</span>
                    <span class="track">
                        <span class="fill" style="width: {{ round(($count / $most) * 100) }}%"></span>
                    </span>
                    <span class="v">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <h2>{{ __('What passengers said') }}</h2>

        @php $withWords = $ratings->filter(fn ($r) => filled($r->rating_comment)); @endphp

        @forelse ($ratings as $rated)
            <div class="note">
                <div class="head">
                    <span class="stars">{{ str_repeat('★', $rated->rating) }}</span>
                    <span class="when">
                        {{ optional($rated->completed_at ?? $rated->created_at)->format('d M Y') }}
                    </span>
                </div>
                @if (filled($rated->rating_comment))
                    <div class="what">{{ $rated->rating_comment }}</div>
                @endif
            </div>
        @empty
            <p class="muted" style="margin:0">{{ __('Nothing yet.') }}</p>
        @endforelse

        @if ($ratings->count() && ! $withWords->count())
            <p class="muted" style="margin:14px 0 0">
                {{ __('Nobody has left a written comment yet.') }}
            </p>
        @endif
    </div>

    <p class="muted" style="text-align:center">
        {{ __('Ratings are anonymous — you can see what was said, not who said it.') }}
    </p>

@endsection
