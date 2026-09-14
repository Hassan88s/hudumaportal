@extends('frontend.frontend-master')
@section('site-title'){{ __('Top 100 Sellers — Huduma Portal') }}@endsection

@section('style')
<style>
    .ts-page{background:#fafbfc;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#1f2733;padding:0}
    .ts-page .container{max-width:1180px;margin:0 auto;padding:0 20px}

    /* Hero */
    .ts-hero{background:linear-gradient(135deg,#1f2733 0%,#2d3748 100%);color:#fff;padding:60px 0 80px;position:relative;overflow:hidden}
    .ts-hero::before{content:'';position:absolute;top:-100px;right:-100px;width:340px;height:340px;background:rgba(255,138,84,.15);border-radius:50%}
    .ts-hero::after{content:'';position:absolute;bottom:-120px;left:-100px;width:420px;height:420px;background:rgba(251,191,36,.08);border-radius:50%}
    .ts-hero .container{position:relative;z-index:1}
    .ts-hero .kicker{display:inline-block;background:rgba(255,255,255,.14);color:#fff;padding:6px 14px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:16px}
    .ts-hero h1{font-size:46px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-.5px}
    .ts-hero h1 span{background:linear-gradient(135deg,#fbbf24,#f59e0b);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent}
    .ts-hero .sub{font-size:17px;color:rgba(255,255,255,.85);margin:0 0 28px;max-width:640px;line-height:1.5}
    .ts-hero .periods{display:inline-flex;gap:5px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:999px;padding:5px}
    .ts-hero .periods a{padding:8px 18px;font-size:13px;font-weight:700;color:rgba(255,255,255,.85);text-decoration:none;border-radius:999px;transition:all .15s}
    .ts-hero .periods a.active{background:#fff;color:#1f2733}

    .ts-body{padding:0 0 60px;margin-top:-40px;position:relative;z-index:2}

    /* Podium (top 3) */
    .ts-podium{display:grid;grid-template-columns:1fr 1.2fr 1fr;gap:16px;margin-bottom:24px;align-items:end}
    .ts-podium .pod{background:#fff;border-radius:16px;padding:24px 18px 22px;text-align:center;border:1px solid #eef0f3;position:relative;box-shadow:0 10px 30px rgba(0,0,0,.06)}
    .ts-podium .pod .rank{position:absolute;top:14px;left:14px;width:32px;height:32px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#1f2733;box-shadow:0 2px 6px rgba(0,0,0,.15)}
    .ts-podium .pod.p1{background:linear-gradient(180deg,#fbbf24 0%,#fde68a 45%,#fff 100%);border-color:#fde68a;transform:scale(1.05);padding:30px 20px 28px}
    .ts-podium .pod.p2{background:linear-gradient(180deg,#9ca3af 0%,#e5e7eb 45%,#fff 100%);border-color:#e5e7eb}
    .ts-podium .pod.p3{background:linear-gradient(180deg,#fb923c 0%,#fed7aa 45%,#fff 100%);border-color:#fed7aa}
    .ts-podium .medal{font-size:34px;margin-bottom:8px}
    .ts-podium .avatar{width:80px;height:80px;border-radius:50%;background:#fff;border:4px solid #fff;box-shadow:0 6px 16px rgba(0,0,0,.15);overflow:hidden;margin:0 auto 10px}
    .ts-podium .avatar img{width:100%;height:100%;object-fit:cover;display:block}
    .ts-podium .name{font-weight:800;font-size:17px;color:#1f2733;margin:0 0 3px;text-decoration:none}
    .ts-podium .name:hover{color:#ff6b3d}
    .ts-podium .username{font-size:11px;color:#6b7280;margin-bottom:10px}
    .ts-podium .score{font-size:26px;font-weight:800;color:#1f2733}
    .ts-podium .score small{font-size:11px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.4px;display:block;margin-top:2px}

    /* List (ranks 4-100) */
    .ts-list{background:#fff;border:1px solid #eef0f3;border-radius:16px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,.04)}
    .ts-list-row{display:grid;grid-template-columns:60px 60px 1fr auto auto;gap:14px;align-items:center;padding:12px 20px;border-bottom:1px solid #f2f4f7;transition:background .15s}
    .ts-list-row:last-child{border-bottom:none}
    .ts-list-row:hover{background:#fafbfc}
    .ts-list-row .rank{width:40px;height:40px;border-radius:50%;background:#f3f4f6;color:#6b7280;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
    .ts-list-row.top10 .rank{background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff}
    .ts-list-row.top50 .rank{background:linear-gradient(135deg,#60a5fa,#3b82f6);color:#fff}
    .ts-list-row .avatar{width:44px;height:44px;border-radius:50%;overflow:hidden;background:#f3f4f6}
    .ts-list-row .avatar img{width:100%;height:100%;object-fit:cover;display:block}
    .ts-list-row .who .name{font-weight:700;font-size:14px;margin:0 0 3px;color:#1f2733;text-decoration:none}
    .ts-list-row .who .name:hover{color:#ff6b3d}
    .ts-list-row .who .meta{font-size:12px;color:#8892a0}
    .ts-list-row .score{font-weight:800;color:#c2410c;font-size:17px;text-align:right;min-width:80px}
    .ts-list-row .score small{font-size:11px;color:#8892a0;font-weight:500;display:block;text-transform:uppercase;letter-spacing:.3px;margin-top:1px}
    .ts-list-row .btn-view{background:#1f2733;color:#fff;padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:background .15s}
    .ts-list-row .btn-view:hover{background:#ff6b3d;color:#fff}

    .ts-empty{padding:60px 20px;text-align:center;color:#8892a0}

    @media (max-width: 760px){
        .ts-hero h1{font-size:32px}
        .ts-podium{grid-template-columns:1fr;gap:12px}
        .ts-podium .pod.p1{transform:none}
        .ts-list-row{grid-template-columns:44px 44px 1fr auto;padding:12px 14px;gap:10px}
        .ts-list-row .btn-view{display:none}
    }
</style>
@endsection

@section('content')
<div class="ts-page">
    {{-- Hero --}}
    <section class="ts-hero">
        <div class="container">
            <span class="kicker">🏆 {{ __('Huduma Champions') }}</span>
            <h1>{{ __('Top') }} <span>100</span> {{ __('Sellers') }}</h1>
            <p class="sub">{{ __('The freelancers, professionals and businesses that complete the most bookings on Huduma Portal — ranked by verified completed orders.') }}</p>

            <div class="periods">
                <a href="{{ route('top.sellers.public', ['period'=>'month']) }}" class="{{ $period === 'month' ? 'active' : '' }}">{{ __('This Month') }}</a>
                <a href="{{ route('top.sellers.public', ['period'=>'year']) }}"  class="{{ $period === 'year'  ? 'active' : '' }}">{{ __('This Year') }}</a>
                <a href="{{ route('top.sellers.public', ['period'=>'all']) }}"   class="{{ $period === 'all'   ? 'active' : '' }}">{{ __('All Time') }}</a>
            </div>
        </div>
    </section>

    <section class="ts-body">
        <div class="container">
            @php
                $top3 = $sellers->take(3);
                $rest = $sellers->slice(3);
                $defaultAvatar = asset('assets/frontend/img/dashboard/dummy-profile.svg');
                $avatarFor = function ($row) use ($defaultAvatar) {
                    return $row->image
                        ? asset('assets/uploads/' . $row->image)
                        : $defaultAvatar;
                };
            @endphp

            {{-- Podium --}}
            @if($top3->count() >= 3)
                <div class="ts-podium">
                    @php $s2 = $top3[1]; $s1 = $top3[0]; $s3 = $top3[2]; @endphp

                    {{-- 2nd --}}
                    <div class="pod p2">
                        <span class="rank">2</span>
                        <div class="medal">🥈</div>
                        <div class="avatar"><img src="{{ $avatarFor($s2) }}" alt="{{ $s2->name }}"></div>
                        <a href="{{ url('/'.$s2->username) }}" class="name">{{ $s2->name ?? '—' }}</a>
                        <div class="username">@{{ $s2->username ?? '' }}</div>
                        <div class="score">{{ number_format($s2->completed_orders) }}<small>{{ __('Completed') }}</small></div>
                    </div>

                    {{-- 1st --}}
                    <div class="pod p1">
                        <span class="rank">1</span>
                        <div class="medal">🥇</div>
                        <div class="avatar"><img src="{{ $avatarFor($s1) }}" alt="{{ $s1->name }}"></div>
                        <a href="{{ url('/'.$s1->username) }}" class="name">{{ $s1->name ?? '—' }}</a>
                        <div class="username">@{{ $s1->username ?? '' }}</div>
                        <div class="score">{{ number_format($s1->completed_orders) }}<small>{{ __('Completed') }}</small></div>
                    </div>

                    {{-- 3rd --}}
                    <div class="pod p3">
                        <span class="rank">3</span>
                        <div class="medal">🥉</div>
                        <div class="avatar"><img src="{{ $avatarFor($s3) }}" alt="{{ $s3->name }}"></div>
                        <a href="{{ url('/'.$s3->username) }}" class="name">{{ $s3->name ?? '—' }}</a>
                        <div class="username">@{{ $s3->username ?? '' }}</div>
                        <div class="score">{{ number_format($s3->completed_orders) }}<small>{{ __('Completed') }}</small></div>
                    </div>
                </div>
            @endif

            {{-- List (ranks 4-100) --}}
            <div class="ts-list">
                @forelse($rest as $row)
                    @php
                        $tierClass = $row->rank <= 10 ? 'top10' : ($row->rank <= 50 ? 'top50' : '');
                    @endphp
                    <div class="ts-list-row {{ $tierClass }}">
                        <div><span class="rank">#{{ $row->rank }}</span></div>
                        <div class="avatar"><img src="{{ $avatarFor($row) }}" alt="{{ $row->name }}"></div>
                        <div class="who">
                            <a href="{{ url('/'.$row->username) }}" class="name">{{ $row->name ?? '—' }}</a>
                            <div class="meta">@{{ $row->username ?? '' }}</div>
                        </div>
                        <div class="score">{{ number_format($row->completed_orders) }}<small>{{ __('Completed') }}</small></div>
                        <a href="{{ url('/'.$row->username) }}" class="btn-view">{{ __('View Profile') }} →</a>
                    </div>
                @empty
                    @if($sellers->isEmpty())
                        <div class="ts-empty">
                            <h3 style="margin:0 0 8px;color:#1f2733">{{ __('No completed orders yet') }}</h3>
                            <p style="margin:0">{{ __('The Top 100 leaderboard fills up as sellers deliver work. Check back soon.') }}</p>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
