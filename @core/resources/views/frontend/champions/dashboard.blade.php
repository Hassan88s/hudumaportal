@extends('frontend.user.buyer.buyer-master')
@section('site-title'){{ __('Huduma Champions') }}@endsection

@section('content')
    <x-frontend.seller-buyer-preloader/>
    @include($isSeller ? 'frontend.user.seller.partials.sidebar-two' : 'frontend.user.buyer.partials.sidebar-two')
    @include('frontend.champions._style')

    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner hc-page" style="background:transparent">
                @php
                    $cur  = $level['current']; $next = $level['next'];
                    $span = $next ? max(1, $next['min'] - $cur['min']) : 1;
                    $pct  = $next ? min(100, round(($totals['confirmed'] - $cur['min']) / $span * 100)) : 100;
                @endphp

                <div class="hc-card" style="background:linear-gradient(135deg,#1f2733,#2d3748);color:#fff;border:none">
                    <div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:center">
                        <div>
                            <div style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;opacity:.8">
                                {{ $isSeller ? __('Huduma Pro League') : __('Huduma Client League') }} · {{ $seasonLabel }}
                            </div>
                            <div style="font-size:26px;font-weight:800;margin-top:4px;color:#fff">
                                {{ $position['rank'] ? __('You are #:rank', ['rank' => $position['rank']]) : __('Not ranked yet') }}
                            </div>
                            <div style="opacity:.85;margin-top:4px">
                                {{ trans_choice(':n day left|:n days left', $daysLeft, ['n' => $daysLeft]) }} ·
                                @if($position['to_top10']) {{ __(':hp HP to enter the Top 10', ['hp' => number_format($position['to_top10'])]) }}
                                @elseif($position['to_top5']) {{ __(':hp HP to enter the Top Five', ['hp' => number_format($position['to_top5'])]) }}
                                @elseif($position['rank']) {{ __('You are in the Top Five — keep going!') }}
                                @else {{ $nextAction }}
                                @endif
                            </div>
                        </div>
                        @if(Route::has('champions.board'))
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <a class="hc-btn" href="{{ route('champions.board', ['league' => $league]) }}">{{ __('View leaderboard') }}</a>
                            <a class="hc-btn ghost" href="{{ route('champions.rules') }}">{{ __('How to earn HP') }}</a>
                        </div>
                        @endif
                    </div>
                </div>

                @if($daysLeft <= 5)
                    <div class="hc-card" style="background:#fff7ed;border-color:#fed7aa;display:flex;gap:10px;align-items:center">
                        <span style="font-size:20px">⏳</span>
                        <div style="font-size:13px;color:#7c2d12">
                            <strong>{{ __('Leaderboard positions are provisional until final verification.') }}</strong><br>
                            {{ __('Pending points, refunds and fraud checks are settled in the first days of next month.') }}
                        </div>
                    </div>
                @endif

                <div class="hc-grid">
                    <div class="hc-stat"><div class="lbl">{{ __('Monthly HP') }}</div><div class="val">{{ number_format($totals['confirmed']) }}</div><div class="hint">{{ __('Confirmed — counts on the board') }}</div></div>
                    <div class="hc-stat"><div class="lbl">{{ __('Pending HP') }}</div><div class="val" style="color:#b45309">{{ number_format($totals['pending']) }}</div><div class="hint">{{ __('Confirms after the refund window') }}</div></div>
                    <div class="hc-stat"><div class="lbl">{{ __('Lifetime HP') }}</div><div class="val" style="color:#1f2733">{{ number_format($totals['lifetime']) }}</div><div class="hint">{{ __('All seasons') }}</div></div>
                    <div class="hc-stat"><div class="lbl">{{ __('Next action') }}</div><div style="font-weight:700;margin-top:8px">{{ $nextAction }}</div></div>
                </div>

                <div class="hc-card">
                    <h3>{{ __('Level') }}: {{ $cur['name'] }}</h3>
                    <div class="hc-bar"><span style="width:{{ $pct }}%"></span></div>
                    <div style="font-size:13px;color:#6b7280;margin-top:8px">
                        @if($next) {{ __(':hp HP to reach :level', ['hp' => number_format($level['remaining']), 'level' => $next['name']]) }}
                        @else {{ __('Highest level reached') }} @endif
                    </div>
                </div>

                @if($missions->count())
                    <div class="hc-card">
                        <h3>{{ __('Missions this month') }}</h3>
                        @foreach($missions as $m)
                            @php $mp = min(100, round($m->progress / max(1, $m->target) * 100)); @endphp
                            <div style="margin-bottom:14px">
                                <div style="display:flex;justify-content:space-between;gap:10px;font-size:14px">
                                    <strong>{{ $m->title }} @if($m->completed_at)<span class="hc-pill confirmed">✓</span>@endif</strong>
                                    <span style="color:#c2410c;font-weight:700">+{{ number_format($m->reward_hp) }} HP</span>
                                </div>
                                @if($m->description)<div style="font-size:12px;color:#6b7280">{{ $m->description }}</div>@endif
                                <div class="hc-bar" style="margin-top:6px"><span style="width:{{ $mp }}%"></span></div>
                                <div style="font-size:12px;color:#6b7280;margin-top:3px">{{ min($m->progress, $m->target) }} / {{ $m->target }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($badges->count())
                    <div class="hc-card">
                        <h3>{{ __('Badges') }}</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            @foreach($badges as $b)
                                <span class="hc-pill" style="background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;padding:6px 12px">🏆 {{ $b->label }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="hc-card" style="padding:0">
                    <div style="padding:16px 22px;border-bottom:1px solid #eef0f3"><h3 style="margin:0">{{ __('Points history') }}</h3></div>
                    <div class="hc-scroll">
                        <table class="hc-table">
                            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Activity') }}</th><th>{{ __('Status') }}</th><th style="text-align:right">{{ __('HP') }}</th></tr></thead>
                            <tbody>
                            @forelse($history as $h)
                                <tr>
                                    <td style="white-space:nowrap;font-size:13px">{{ \Carbon\Carbon::parse($h->created_at)->format('d M, H:i') }}</td>
                                    <td>{{ $h->reason }}</td>
                                    <td><span class="hc-pill {{ $h->status }}">{{ __(ucfirst($h->status)) }}</span></td>
                                    <td class="hp" style="text-align:right;{{ $h->points < 0 ? 'color:#b91c1c' : '' }}">{{ $h->points > 0 ? '+' : '' }}{{ number_format($h->points) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="hc-empty">{{ __('No points yet this month. :action', ['action' => $nextAction]) }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($history->hasPages())<div style="padding:12px 22px">{{ $history->links() }}</div>@endif
                </div>
            </div>
        </div>
    </div>
@endsection
