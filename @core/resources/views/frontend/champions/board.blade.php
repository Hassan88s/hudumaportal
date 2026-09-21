@extends('frontend.frontend-master')
@section('site-title'){{ __('Huduma Champions — :season', ['season' => $seasonLabel]) }}@endsection

@section('style')
    @include('frontend.champions._style')
@endsection

@section('content')
<div class="hc-page">
    <section class="hc-hero">
        <div class="container">
            <span class="kicker">🏆 {{ __('Huduma Champions') }} · {{ $seasonLabel }}</span>
            <h1>{{ $league === 'provider' ? __('Huduma Pro League') : __('Huduma Client League') }}</h1>
            <p class="sub">
                {{ $league === 'provider'
                    ? __('Service providers compete every month by completing quality jobs, responding fast and earning verified reviews.')
                    : __('Clients compete every month by booking services, rebooking trusted providers and leaving verified reviews.') }}
                @if(!is_null($daysLeft))
                    <br><strong>{{ trans_choice(':n day left|:n days left', $daysLeft, ['n' => $daysLeft]) }}</strong> {{ __('in this season.') }}
                @endif
            </p>
            <div class="hc-tabs">
                <a href="{{ route('champions.board', ['league' => 'provider', 'period' => $period]) }}" class="{{ $league === 'provider' ? 'active' : '' }}">{{ __('Pro League') }}</a>
                <a href="{{ route('champions.board', ['league' => 'client', 'period' => $period]) }}" class="{{ $league === 'client' ? 'active' : '' }}">{{ __('Client League') }}</a>
                <a href="{{ route('champions.hall') }}">{{ __('Hall of Fame') }}</a>
                <a href="{{ route('champions.rules') }}">{{ __('Rules') }}</a>
            </div>
            <div class="hc-tabs" style="margin-top:10px">
                <a href="{{ route('champions.board', ['league' => $league, 'period' => 'week']) }}"  class="{{ $period === 'week'  ? 'active' : '' }}">{{ __('This Week') }}</a>
                <a href="{{ route('champions.board', ['league' => $league, 'period' => 'month']) }}" class="{{ $period === 'month' ? 'active' : '' }}">{{ __('This Month') }}</a>
                <a href="{{ route('champions.board', ['league' => $league, 'period' => 'year']) }}"  class="{{ $period === 'year'  ? 'active' : '' }}">{{ __('This Year') }}</a>
            </div>
        </div>
    </section>

    <section class="hc-body">
        <div class="container">
            @if($me)
                <div class="hc-grid">
                    <div class="hc-stat"><div class="lbl">{{ __('Your rank') }}</div><div class="val">{{ $me['rank'] ? '#'.$me['rank'] : '—' }}</div><div class="hint">{{ __('of :n players', ['n' => $me['participants']]) }}</div></div>
                    <div class="hc-stat"><div class="lbl">{{ __('Your HP') }}</div><div class="val">{{ number_format($me['hp']) }}</div><div class="hint">{{ __('Confirmed Huduma Points') }}</div></div>
                    <div class="hc-stat"><div class="lbl">{{ __('To Top 10') }}</div><div class="val">{{ $me['to_top10'] ? number_format($me['to_top10']) : '✓' }}</div><div class="hint">{{ __('HP needed') }}</div></div>
                    <div class="hc-stat"><div class="lbl">{{ __('To Top 5') }}</div><div class="val">{{ $me['to_top5'] ? number_format($me['to_top5']) : '✓' }}</div><div class="hint">{{ __('HP needed') }}</div></div>
                </div>
            @endif

            @if(!is_null($daysLeft) && $daysLeft <= 5)
                {{-- PDF §27 — near month-end, say clearly that nothing is final yet --}}
                <div class="hc-card" style="background:#fff7ed;border-color:#fed7aa;display:flex;gap:10px;align-items:center">
                    <span style="font-size:20px">⏳</span>
                    <div style="font-size:13px;color:#7c2d12">
                        <strong>{{ __('Leaderboard positions are provisional until final verification.') }}</strong><br>
                        {{ __('Pending points, refunds and fraud checks are settled in the first days of next month. Winners are confirmed on day 4 and announced on day 5.') }}
                    </div>
                </div>
            @endif

            <div class="hc-card" style="padding:0">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;padding:16px 22px;border-bottom:1px solid #eef0f3">
                    <div>
                        <strong>{{ __('Most points earned') }} — {{ $periodLabel }}</strong>
                        <div style="font-size:12px;color:#8892a0">
                            @if($period === 'week') {{ __('Includes points still pending the refund window.') }}
                            @elseif($period === 'year') {{ __('Confirmed points from every month this year. Prizes are awarded monthly.') }}
                            @else {{ __('Official monthly season — confirmed points only.') }} @endif
                        </div>
                    </div>
                    @if($period === 'month')
                    <form method="get" action="{{ route('champions.board') }}" style="margin:0">
                        <input type="hidden" name="league" value="{{ $league }}">
                        <select name="season" onchange="this.form.submit()" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                            @foreach($seasons->push($season)->unique()->sortDesc() as $s)
                                <option value="{{ $s }}" @selected($s === $season)>{{ \Carbon\Carbon::createFromFormat('Y-m', $s)->format('F Y') }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>
                <div class="hc-scroll">
                    <table class="hc-table">
                        <thead><tr><th>#</th><th>{{ $league === 'provider' ? __('Provider') : __('Client') }}</th><th>{{ __('Level') }}</th><th style="text-align:right">{{ __('HP') }}</th></tr></thead>
                        <tbody>
                        @forelse($board as $row)
                            @php $lvl = app(\App\Services\ChampionsService::class)->levelFor($league, (int) $row->hp); @endphp
                            <tr class="{{ $row->rank <= 5 ? 'top5' : '' }} {{ auth('web')->id() === (int) $row->user_id ? 'me' : '' }}">
                                <td class="rank">#{{ $row->rank }}</td>
                                <td>
                                    @if($league === 'provider' && $row->username)
                                        <a href="{{ url('/'.$row->username) }}" style="font-weight:700;color:#1f2733;text-decoration:none">{{ $row->display_name }}</a>
                                    @else
                                        <strong>{{ $row->display_name }}</strong>
                                    @endif
                                    @if($league === 'provider')<div style="font-size:12px;color:#8892a0">{{ __(':n completed this month', ['n' => $row->completed]) }}</div>@endif
                                </td>
                                <td><span class="hc-pill">{{ $lvl['current']['name'] ?? '' }}</span></td>
                                <td class="hp" style="text-align:right">{{ number_format($row->hp) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="hc-empty">{{ __('No confirmed points yet this season — the first completed jobs will appear here.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="hc-card">
                <h3>{{ __('How to climb') }}</h3>
                <p style="margin:0 0 12px;color:#4b5563;line-height:1.6">
                    {{ __('Points come from real marketplace activity. Transaction points stay pending until the refund/dispute window closes, then count on the board. The leaderboard refreshes every 15 minutes; final results are audited before the Top Five are announced on the 5th of the following month.') }}
                </p>
                @auth('web')
                    <a class="hc-btn" href="{{ (int) auth('web')->user()->user_type === 0 ? route('seller.champions') : route('buyer.champions') }}">{{ __('Open my Champions dashboard') }} →</a>
                @else
                    <a class="hc-btn" href="{{ route('user.register') }}">{{ __('Join and start earning HP') }} →</a>
                @endauth
                <a class="hc-btn ghost" href="{{ route('champions.rules') }}">{{ __('Official rules') }}</a>
            </div>
        </div>
    </section>
</div>
@endsection
