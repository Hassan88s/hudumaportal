@extends('frontend.frontend-master')
@section('site-title'){{ __('Rafiki Leaderboard — Top Referrers') }}@endsection

@section('style')
<style>
    .lb-page{background:#fafbfc;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#1f2733;padding:0}
    .lb-page .container{max-width:1080px;margin:0 auto;padding:0 20px}

    /* ═══ Hero ═══ */
    .lb-hero{background:linear-gradient(135deg,#ff8a54 0%,#ff6b3d 100%);color:#fff;padding:60px 0 80px;position:relative;overflow:hidden}
    .lb-hero::before{content:'';position:absolute;top:-80px;right:-80px;width:340px;height:340px;background:rgba(255,255,255,.08);border-radius:50%}
    .lb-hero::after{content:'';position:absolute;bottom:-120px;left:-120px;width:420px;height:420px;background:rgba(255,255,255,.05);border-radius:50%}
    .lb-hero .container{position:relative;z-index:1}
    .lb-hero .kicker{display:inline-block;background:rgba(255,255,255,.2);padding:6px 14px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:16px}
    .lb-hero h1{font-size:44px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-.5px}
    .lb-hero .sub{font-size:17px;color:rgba(255,255,255,.92);margin:0 0 32px;max-width:640px}
    .lb-hero .trust{display:flex;gap:36px;flex-wrap:wrap;padding-top:24px;border-top:1px solid rgba(255,255,255,.22)}
    .lb-hero .trust .item .num{font-size:26px;font-weight:800}
    .lb-hero .trust .item .lbl{font-size:12px;color:rgba(255,255,255,.85);letter-spacing:.4px;text-transform:uppercase;margin-top:4px}
    .lb-hero .cta{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;background:#fff;color:#ff6b3d;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;transition:transform .15s,box-shadow .15s;box-shadow:0 4px 14px rgba(0,0,0,.15)}
    .lb-hero .cta:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);color:#ff6b3d}

    /* ═══ Section container ═══ */
    .lb-body{padding:50px 0 60px}

    /* ═══ Filters + tabs ═══ */
    .lb-controls{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:24px}
    .lb-tabs{display:flex;gap:6px;background:#fff;border:1px solid #e6e9ef;border-radius:12px;padding:5px}
    .lb-tabs a{padding:8px 18px;font-weight:700;font-size:14px;color:#6b7280;text-decoration:none;border-radius:8px;transition:all .15s}
    .lb-tabs a.active{background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff}
    .lb-periods{display:flex;gap:6px;background:#fff;border:1px solid #e6e9ef;border-radius:999px;padding:3px}
    .lb-periods a{padding:6px 14px;font-size:13px;font-weight:600;color:#6b7280;text-decoration:none;border-radius:999px;transition:all .15s}
    .lb-periods a.active{background:#1f2733;color:#fff}

    /* ═══ Podium (top 3) ═══ */
    .lb-podium{display:grid;grid-template-columns:1fr 1.2fr 1fr;gap:16px;margin-bottom:24px;align-items:end}
    .lb-podium .pod{background:#fff;border-radius:14px;padding:24px 16px 20px;text-align:center;border:1px solid #eef0f3;position:relative}
    .lb-podium .pod.p1{background:linear-gradient(180deg,#fbbf24 0%,#fde68a 55%,#fff 100%);border-color:#fde68a;padding-top:30px;padding-bottom:26px;transform:scale(1.05)}
    .lb-podium .pod.p2{background:linear-gradient(180deg,#9ca3af 0%,#e5e7eb 55%,#fff 100%);border-color:#e5e7eb}
    .lb-podium .pod.p3{background:linear-gradient(180deg,#fb923c 0%,#fed7aa 55%,#fff 100%);border-color:#fed7aa}
    .lb-podium .medal{font-size:26px;margin-bottom:8px}
    .lb-podium .rank{position:absolute;top:12px;left:12px;width:26px;height:26px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;color:#1f2733}
    .lb-podium .name{font-weight:800;font-size:16px;margin:0 0 4px;color:#1f2733}
    .lb-podium .code{font-family:monospace;background:rgba(255,255,255,.7);padding:2px 8px;border-radius:6px;font-size:11px;color:#374151;display:inline-block;margin-bottom:8px}
    .lb-podium .score{font-size:22px;font-weight:800;color:#1f2733}
    .lb-podium .score small{font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px;display:block;margin-top:2px}

    /* ═══ List (ranks 4-20) ═══ */
    .lb-list{background:#fff;border:1px solid #e6e9ef;border-radius:14px;overflow:hidden}
    .lb-list-row{display:grid;grid-template-columns:60px 1fr auto auto;gap:16px;align-items:center;padding:14px 20px;border-bottom:1px solid #f2f4f7;transition:background .15s}
    .lb-list-row:last-child{border-bottom:none}
    .lb-list-row:hover{background:#fafbfc}
    .lb-list-row .rank{width:36px;height:36px;border-radius:50%;background:#f3f4f6;color:#6b7280;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
    .lb-list-row .who .name{font-weight:700;color:#1f2733;font-size:14px;margin:0 0 3px}
    .lb-list-row .who .meta{font-size:12px;color:#8892a0}
    .lb-list-row .code-pill{font-family:monospace;background:#f3f4f6;padding:3px 8px;border-radius:6px;font-size:11px;color:#374151}
    .lb-list-row .score{font-weight:800;color:#c2410c;font-size:16px;text-align:right}
    .lb-list-row .score small{font-size:11px;color:#8892a0;font-weight:500;display:block;text-align:right;margin-top:1px;text-transform:uppercase;letter-spacing:.3px}
    .lb-list .empty{padding:40px;text-align:center;color:#8892a0;font-size:14px}

    /* Rafiki level badges */
    .lb-lvl{display:inline-block;padding:2px 8px;font-size:9px;font-weight:800;border-radius:999px;text-transform:uppercase;letter-spacing:.4px;margin-left:6px;vertical-align:middle}
    .lb-lvl.rafiki{background:#f3f4f6;color:#374151}
    .lb-lvl.balozi{background:#fef3c7;color:#92400e}
    .lb-lvl.super{background:#e0e7ff;color:#3730a3}
    .lb-lvl.champion{background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff}

    /* CTA card at bottom */
    .lb-cta{background:#1f2733;color:#fff;padding:36px 30px;border-radius:16px;margin-top:32px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
    .lb-cta h3{margin:0 0 6px;font-size:20px;font-weight:800}
    .lb-cta p{margin:0;color:rgba(255,255,255,.8);font-size:14px}
    .lb-cta a{background:#ff8a54;color:#fff;padding:12px 24px;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;transition:background .15s}
    .lb-cta a:hover{background:#ff6b3d;color:#fff}

    @media (max-width: 700px){
        .lb-hero h1{font-size:32px}
        .lb-podium{grid-template-columns:1fr;gap:12px}
        .lb-podium .pod.p1{transform:none}
        .lb-list-row{grid-template-columns:44px 1fr auto;padding:12px 14px}
        .lb-list-row .code-pill{display:none}
    }
</style>
@endsection

@php
    $level = function ($count) {
        if ($count >= 50) return ['champion', 'HUDUMA CHAMPION'];
        if ($count >= 15) return ['super',    'SUPER BALOZI'];
        if ($count >= 5)  return ['balozi',   'BALOZI'];
        return ['rafiki', 'RAFIKI'];
    };
    $periodLabel = ['month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'][$period] ?? 'This Month';
@endphp

@section('content')
<div class="lb-page">
    {{-- ═══ HERO ═══ --}}
    <section class="lb-hero">
        <div class="container">
            <span class="kicker">{{ __('Rafiki Leaderboard') }}</span>
            <h1>{{ __('Tanzania\'s top referrers') }}</h1>
            <p class="sub">{{ __('Live rankings of everyone bringing new users to Huduma Portal. Share your link, climb the levels, and earn real cash.') }}</p>

            @auth
                <a href="{{ (int) auth()->user()->user_type === 2 ? route('seller.earn') : route('buyer.earn') }}" class="cta">
                    <i class="las la-gift"></i> {{ __('Open My Earn Dashboard') }}
                </a>
            @else
                <a href="{{ url('/register') }}" class="cta">
                    <i class="las la-user-plus"></i> {{ __('Sign Up to Compete') }}
                </a>
            @endauth

            <div class="trust">
                <div class="item"><div class="num">{{ number_format($stats['total_referrers']) }}</div><div class="lbl">{{ __('Active Referrers') }}</div></div>
                <div class="item"><div class="num">{{ number_format($stats['total_referrals']) }}</div><div class="lbl">{{ __('People Referred') }}</div></div>
                <div class="item"><div class="num">{{ number_format($stats['top_score']) }}</div><div class="lbl">{{ __('Top Score') }} ({{ $periodLabel }})</div></div>
            </div>
        </div>
    </section>

    <section class="lb-body">
        <div class="container">
            <div class="lb-controls">
                <div class="lb-tabs">
                    <a href="{{ route('referral.leaderboard.public', ['tab'=>'top','period'=>$period]) }}"          class="{{ $tab === 'top' ? 'active' : '' }}">{{ __('Top Referrers') }}</a>
                    {{-- HIDDEN FOR NOW — city + university leagues (PDF §21, §22).
                         Uncomment when ready to launch these growth engines.
                    <a href="{{ route('referral.leaderboard.public', ['tab'=>'cities','period'=>$period]) }}"       class="{{ $tab === 'cities' ? 'active' : '' }}">{{ __('Cities') }}</a>
                    <a href="{{ route('referral.leaderboard.public', ['tab'=>'universities','period'=>$period]) }}" class="{{ $tab === 'universities' ? 'active' : '' }}">{{ __('University League') }}</a>
                    --}}
                </div>
                <div class="lb-periods">
                    <a href="{{ route('referral.leaderboard.public', ['tab'=>$tab,'period'=>'month']) }}" class="{{ $period === 'month' ? 'active' : '' }}">{{ __('This Month') }}</a>
                    <a href="{{ route('referral.leaderboard.public', ['tab'=>$tab,'period'=>'year']) }}"  class="{{ $period === 'year' ? 'active' : '' }}">{{ __('This Year') }}</a>
                    <a href="{{ route('referral.leaderboard.public', ['tab'=>$tab,'period'=>'all']) }}"   class="{{ $period === 'all' ? 'active' : '' }}">{{ __('All Time') }}</a>
                </div>
            </div>

            {{-- ═══ TOP REFERRERS TAB ═══ --}}
            @if($tab === 'top')
                @if($topRef->count() >= 3)
                    <div class="lb-podium">
                        {{-- 2nd place --}}
                        @php $r2 = $topRef[1]; [$c2,$l2] = $level((int)$r2->ref_count); @endphp
                        <div class="pod p2">
                            <span class="rank">2</span>
                            <div class="medal">🥈</div>
                            <div class="name">{{ $r2->name ?? $r2->username ?? '—' }} <span class="lb-lvl {{ $c2 }}">{{ $l2 }}</span></div>
                            <span class="code">{{ $r2->referral_code ?? '—' }}</span>
                            <div class="score">{{ number_format($r2->ref_count) }} <small>{{ __('Referrals') }}</small></div>
                        </div>

                        {{-- 1st place --}}
                        @php $r1 = $topRef[0]; [$c1,$l1] = $level((int)$r1->ref_count); @endphp
                        <div class="pod p1">
                            <span class="rank">1</span>
                            <div class="medal">🥇</div>
                            <div class="name">{{ $r1->name ?? $r1->username ?? '—' }} <span class="lb-lvl {{ $c1 }}">{{ $l1 }}</span></div>
                            <span class="code">{{ $r1->referral_code ?? '—' }}</span>
                            <div class="score">{{ number_format($r1->ref_count) }} <small>{{ __('Referrals') }}</small></div>
                        </div>

                        {{-- 3rd place --}}
                        @php $r3 = $topRef[2]; [$c3,$l3] = $level((int)$r3->ref_count); @endphp
                        <div class="pod p3">
                            <span class="rank">3</span>
                            <div class="medal">🥉</div>
                            <div class="name">{{ $r3->name ?? $r3->username ?? '—' }} <span class="lb-lvl {{ $c3 }}">{{ $l3 }}</span></div>
                            <span class="code">{{ $r3->referral_code ?? '—' }}</span>
                            <div class="score">{{ number_format($r3->ref_count) }} <small>{{ __('Referrals') }}</small></div>
                        </div>
                    </div>
                @endif

                <div class="lb-list">
                    @forelse($topRef->skip(3) as $i => $row)
                        @php [$class,$label] = $level((int)$row->ref_count); @endphp
                        <div class="lb-list-row">
                            <div><span class="rank">{{ $i + 4 }}</span></div>
                            <div class="who">
                                <div class="name">{{ $row->name ?? $row->username ?? '—' }} <span class="lb-lvl {{ $class }}">{{ $label }}</span></div>
                                <div class="meta">{{ $row->username ?? '' }}</div>
                            </div>
                            <div class="code-pill">{{ $row->referral_code ?? '—' }}</div>
                            <div class="score">{{ number_format($row->ref_count) }} <small>{{ __('Refs') }}</small></div>
                        </div>
                    @empty
                        @if($topRef->isEmpty())
                            <div class="empty">{{ __('No referrers yet — be the first! Sign up and start sharing.') }}</div>
                        @endif
                    @endforelse
                </div>
            @endif

            {{-- ═══ CITIES TAB — HIDDEN, keep for later (PDF §21) ═══ --}}
            @if(false && $tab === 'cities')
                <div class="lb-list">
                    @forelse($topCities as $i => $city)
                        <div class="lb-list-row">
                            <div><span class="rank">{{ $i + 1 }}</span></div>
                            <div class="who">
                                <div class="name">{{ $city->city_name ?? __('Unknown city') }}</div>
                                <div class="meta">{{ __('Total referrals from this city') }}</div>
                            </div>
                            <div></div>
                            <div class="score">{{ number_format($city->ref_count) }} <small>{{ __('Refs') }}</small></div>
                        </div>
                    @empty
                        <div class="empty">{{ __('No city data yet — the city rankings appear once referrers set their service city.') }}</div>
                    @endforelse
                </div>
            @endif

            {{-- ═══ UNIVERSITY LEAGUE TAB — HIDDEN, keep for later (PDF §22) ═══ --}}
            @if(false && $tab === 'universities')
                <div class="lb-list">
                    @forelse($topUnis as $i => $uni)
                        <div class="lb-list-row">
                            <div><span class="rank">{{ $i + 1 }}</span></div>
                            <div class="who">
                                <div class="name">{{ $uni->university }}</div>
                                <div class="meta">{{ $uni->referrer_count }} {{ __('unique referrers') }}</div>
                            </div>
                            <div></div>
                            <div class="score">{{ number_format($uni->ref_count) }} <small>{{ __('Refs') }}</small></div>
                        </div>
                    @empty
                        <div class="empty">
                            {{ __('The University League starts when students add their university to their profile.') }}
                            @auth
                                <br><br><a href="{{ route('buyer.profile.edit') }}" style="color:#ff6b3d;font-weight:700">{{ __('Set your university on your profile →') }}</a>
                            @endauth
                        </div>
                    @endforelse
                </div>
            @endif

            <div class="lb-cta">
                <div>
                    <h3>{{ __('Want to make this list?') }}</h3>
                    <p>{{ __('Share your unique referral link and every friend who joins moves you up the rankings.') }}</p>
                </div>
                @auth
                    <a href="{{ (int) auth()->user()->user_type === 2 ? route('seller.earn') : route('buyer.earn') }}">{{ __('Get My Link') }} →</a>
                @else
                    <a href="{{ url('/register') }}">{{ __('Sign Up Free') }} →</a>
                @endauth
            </div>
        </div>
    </section>
</div>
@endsection
