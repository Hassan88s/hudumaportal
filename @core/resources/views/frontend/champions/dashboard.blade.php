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

                {{-- PDF §31 navigation: Leaderboard, Missions, My Points History, Rewards, Rules, Previous Winners --}}
                <div class="hc-card" style="padding:10px 14px;display:flex;gap:6px;flex-wrap:wrap">
                    @if(Route::has('champions.board'))
                        <a class="hc-btn ghost" style="padding:6px 12px" href="{{ route('champions.board', ['league' => $league]) }}">{{ __('Leaderboard') }}</a>
                    @endif
                    <a class="hc-btn ghost" style="padding:6px 12px" href="#hc-missions">{{ __('Missions') }}</a>
                    <a class="hc-btn ghost" style="padding:6px 12px" href="#hc-history">{{ __('My Points History') }}</a>
                    @if(Route::has('champions.rewards'))
                        <a class="hc-btn ghost" style="padding:6px 12px" href="{{ route('champions.rewards') }}">{{ __('Rewards') }}</a>
                        <a class="hc-btn ghost" style="padding:6px 12px" href="{{ route('champions.rules') }}">{{ __('Rules') }}</a>
                        <a class="hc-btn ghost" style="padding:6px 12px" href="{{ route('champions.winners') }}">{{ __('Previous Winners') }}</a>
                    @endif
                </div>

                @php $obRoute = $isSeller ? 'seller.onboarding' : 'buyer.onboarding'; @endphp
                @if(!$onboardingDone && Route::has($obRoute))
                    <div class="hc-card" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;background:#fff7ed;border-color:#fed7aa">
                        <div>
                            <strong style="color:#7c2d12">🚀 {{ __('New here? Finish the Getting Started tutorial') }}</strong>
                            <div style="font-size:13px;color:#9a3412">
                                {{ $isSeller
                                    ? __('5 quick steps to your first bookings — and +30 HP when you finish.')
                                    : __('5 quick steps to booking with confidence — and +25 HP when you finish.') }}
                            </div>
                        </div>
                        <a class="hc-btn" href="{{ route($obRoute) }}">{{ __('Start tutorial') }} →</a>
                    </div>
                @endif

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

                {{-- Referral points (PDF §8 for providers, §18 for clients) --}}
                <div class="hc-card">
                    <h3>{{ __('Referral points') }}</h3>
                    <p style="margin:-6px 0 12px;font-size:13px;color:#6b7280">
                        {{ __('Points for people you invite who become active. Up to :cap HP a month.', ['cap' => number_format($referral['cap'])]) }}
                    </p>
                    <div class="hc-bar"><span style="width:{{ min(100, $referral['cap'] ? round($referral['earned'] / $referral['cap'] * 100) : 0) }}%"></span></div>
                    <div style="font-size:13px;color:#4b5563;margin-top:8px">
                        <strong>{{ number_format($referral['earned']) }}</strong> / {{ number_format($referral['cap']) }} HP {{ __('this month') }}
                    </div>

                    @if($referral['rows']->count())
                        <table class="hc-table" style="margin-top:10px">
                            @foreach($referral['rows'] as $r)
                                <tr>
                                    <td>{{ $r->reason }} @if($r->n > 1)<span class="hc-pill">×{{ $r->n }}</span>@endif</td>
                                    <td class="hp" style="text-align:right">+{{ number_format($r->hp) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <ul style="margin:10px 0 0;padding-left:18px;line-height:1.8;color:#4b5563;font-size:13px">
                            @if($isSeller)
                                <li>{{ __('A provider you invited becomes qualified') }} — <strong>+75 HP</strong></li>
                                <li>{{ __('A client you invited completes their first transaction') }} — <strong>+100 HP</strong></li>
                                <li>{{ __('A provider you invited completes their first booking') }} — <strong>+100 HP</strong></li>
                            @else
                                <li>{{ __('A client you invited verifies their account') }} — <strong>+25 HP</strong></li>
                                <li>{{ __('A client you invited completes their first booking') }} — <strong>+100 HP</strong></li>
                                <li>{{ __('A provider you invited becomes qualified') }} — <strong>+75 HP</strong></li>
                                <li>{{ __('A provider you invited completes their first service') }} — <strong>+100 HP</strong></li>
                            @endif
                        </ul>
                        @if(Route::has($isSeller ? 'seller.earn' : 'buyer.earn'))
                            <a class="hc-btn ghost" style="margin-top:12px" href="{{ route($isSeller ? 'seller.earn' : 'buyer.earn') }}">{{ __('Get my invite link') }} →</a>
                        @endif
                    @endif
                </div>

                {{-- Month-end bonuses (PDF §7 for providers, §19 for clients) --}}
                @php
                    $bonusRules = $isSeller ? [
                        'p_q_response_90'     => [__('Answer 90% of client messages'), 100, is_null($bonus['response_rate']) ? __('needs 3+ conversations') : round($bonus['response_rate'] * 100) . '%'],
                        'p_q_completion_95'   => [__('Finish 95% of your jobs'), 150, is_null($bonus['completion_rate']) ? '—' : $bonus['completion_rate'] . '%'],
                        'p_q_rating'          => [__('Keep an excellent rating'), 200, ($bonus['rating'] ?: '—') . ' ★ · ' . $bonus['done'] . '/3 ' . __('jobs')],
                        'p_q_zero_cancel'     => [__('No cancellations after 5 jobs'), 150, $bonus['done'] . '/5 ' . __('jobs') . ' · ' . $bonus['cancelled'] . ' ' . __('cancelled')],
                        'p_q_zero_complaints' => [__('No upheld complaints'), 100, $bonus['complaints'] . ' ' . __('complaints')],
                        'p_q_repeat_5'        => [__('5 clients who booked twice'), 250, $bonus['repeat_clients'] . '/5'],
                    ] : [
                        'c_l_two_categories'   => [__('Book in 2 categories'), 50, $bonus['categories'] . '/2'],
                        'c_l_three_categories' => [__('Book in 3 categories'), 100, $bonus['categories'] . '/3'],
                        'c_l_three_no_cancel'  => [__('3 bookings, none cancelled'), 100, $bonus['done'] . '/3 · ' . $bonus['cancelled'] . ' ' . __('cancelled')],
                        'c_l_five_bookings'    => [__('5 completed bookings'), 250, $bonus['done'] . '/5'],
                        'c_l_same_provider'    => [__('Book the same provider twice'), 100, $bonus['same_provider'] . '/1'],
                    ];
                    $bonusTotal = collect($bonus['bonuses'])->filter()->keys()->sum(fn ($k) => $bonusRules[$k][1]);
                @endphp
                <div class="hc-card">
                    <h3>{{ __('Month-end bonuses') }}</h3>
                    <p style="margin:-6px 0 14px;font-size:13px;color:#6b7280">
                        {{ __('Extra points paid at the end of the month, once cancellations and refunds are settled.') }}
                        @if($bonusTotal)
                            <strong style="color:#15803d">{{ __('On track for +:n HP', ['n' => number_format($bonusTotal)]) }}</strong>
                        @endif
                    </p>
                    @foreach($bonusRules as $key => [$title, $hp, $progress])
                        @php $earned = $bonus['bonuses'][$key]; @endphp
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f2f4f7;flex-wrap:wrap">
                            <div>
                                <span style="font-weight:600">{{ $earned ? '✅' : '⬜' }} {{ $title }}</span>
                                <div style="font-size:12px;color:#8892a0">{{ $progress }}</div>
                            </div>
                            <span class="hc-pill {{ $earned ? 'confirmed' : '' }}">+{{ number_format($hp) }} HP</span>
                        </div>
                    @endforeach
                </div>

                @if($demand->count())
                    <div class="hc-card">
                        <h3>{{ __('Extra points right now') }}</h3>
                        <div style="font-size:12px;color:#6b7280;margin:-6px 0 12px">{{ $isSeller
                            ? __('Work in these places or categories this month and your points are increased.')
                            : __('Book in these places or categories this month and your points are increased.') }}</div>
                        @foreach($demand as $d)
                            <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;padding:8px 0;border-top:1px solid #f1f3f6">
                                <div>
                                    <strong style="font-size:14px">{{ $d->title }}</strong>
                                    @if($d->description)<div style="font-size:12px;color:#6b7280">{{ $d->description }}</div>@endif
                                    @if($d->city_name || $d->category_name)
                                        <div style="font-size:12px;color:#6b7280">
                                            @if($d->category_name){{ $d->category_name }}@endif
                                            @if($d->city_name && $d->category_name) · @endif
                                            @if($d->city_name){{ $d->city_name }}@endif
                                        </div>
                                    @endif
                                </div>
                                <span style="color:#c2410c;font-weight:700;white-space:nowrap">+{{ (int) $d->bonus_percent }}%</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="hc-card" id="hc-missions">
                    <h3>{{ __('Missions this month') }}</h3>
                    @if(!$missions->count())
                        <div style="font-size:13px;color:#6b7280">{{ __('No missions running this month. Points for your everyday work carry on as usual — check the Rules page for everything that earns points.') }}</div>
                    @endif
                    @if($missions->count())
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
                    @endif
                </div>

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
                    <div style="padding:16px 22px;border-bottom:1px solid #eef0f3"><h3 style="margin:0" id="hc-history">{{ __('Points history') }}</h3></div>
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
