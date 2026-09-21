@extends('frontend.frontend-master')
@section('site-title'){{ __('Huduma Champions — Top Five') }}@endsection

@section('style')
    @include('frontend.champions._style')
@endsection

@section('content')
<div class="hc-page">
    <section class="hc-hero">
        <div class="container">
            <span class="kicker">🏆 {{ __('Huduma Champions') }}{{ $seasonLabel ? ' · '.$seasonLabel : '' }}</span>
            <h1>{{ __('Top') }} <span>{{ __('Five') }}</span> {{ __('Winners') }}</h1>
            <p class="sub">{{ __('The verified Top Five in each league, announced after the monthly audit.') }}</p>
            @include('frontend.champions._tabs', ['active' => 'winners'])
        </div>
    </section>

    <section class="hc-body">
        <div class="container">
            @if($published->count() > 1)
                <form method="get" class="hc-card" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <strong>{{ __('Season') }}</strong>
                    <select name="season" onchange="this.form.submit()" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                        @foreach($published as $s)
                            <option value="{{ $s }}" @selected($s === $season)>{{ \Carbon\Carbon::createFromFormat('Y-m', $s)->format('F Y') }}</option>
                        @endforeach
                    </select>
                </form>
            @endif

            @if(!$season)
                <div class="hc-card hc-empty">
                    <h3 style="margin:0 0 8px">{{ __('No winners announced yet') }}</h3>
                    <p style="margin:0">{{ __('The first Top Five will be published on the 5th of next month, after verification.') }}</p>
                    <a class="hc-btn" style="margin-top:14px" href="{{ route('champions.board') }}">{{ __('See the live leaderboard') }} →</a>
                </div>
            @else
                <div class="hc-grid" style="grid-template-columns:1fr 1fr">
                    @foreach(['provider' => [__('Huduma Pro League'), $provider], 'client' => [__('Huduma Client League'), $client]] as $lg => [$title, $rows])
                        <div class="hc-card" style="margin:0;padding:0">
                            <div style="padding:16px 22px;border-bottom:1px solid #eef0f3"><h3 style="margin:0">{{ $title }}</h3></div>
                            <table class="hc-table">
                                @forelse($rows as $w)
                                    <tr class="top5">
                                        <td class="rank" style="width:60px">#{{ $w->rank }}</td>
                                        <td>
                                            @if($lg === 'provider' && $w->username)
                                                <a href="{{ url('/'.$w->username) }}" style="font-weight:700;color:#1f2733;text-decoration:none">{{ $w->display_name }}</a>
                                            @else
                                                <strong>{{ $w->display_name }}</strong>
                                            @endif
                                            <div style="font-size:12px;color:#8892a0">TZS {{ number_format($w->reward_amount) }} {{ $w->reward_type === 'cash' ? __('cash') : __('credits') }}</div>
                                        </td>
                                        <td class="hp" style="text-align:right">{{ number_format($w->final_hp) }} HP</td>
                                    </tr>
                                @empty
                                    <tr><td class="hc-empty">{{ __('Not announced yet.') }}</td></tr>
                                @endforelse
                            </table>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
