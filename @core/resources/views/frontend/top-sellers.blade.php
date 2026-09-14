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

    /* Ranked list */
    .ts-list{background:#fff;border:1px solid #eef0f3;border-radius:16px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,.04)}
    .ts-list-row{display:grid;grid-template-columns:70px 60px 1fr auto auto;gap:14px;align-items:center;padding:14px 22px;border-bottom:1px solid #f2f4f7;transition:background .15s}
    .ts-list-row:last-child{border-bottom:none}
    .ts-list-row:hover{background:#fafbfc}
    .ts-list-row .rank{font-weight:800;font-size:16px;color:#6b7280;display:inline-flex;align-items:center;gap:3px}
    .ts-list-row .rank .lbl{font-size:10px;color:#8892a0;font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-right:3px}
    .ts-list-row.top10 .rank{color:#c2410c}
    .ts-list-row.top50 .rank{color:#1d4ed8}
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
        .ts-list-row{grid-template-columns:60px 44px 1fr auto;padding:12px 14px;gap:10px}
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
                $defaultAvatar = asset('assets/frontend/img/dashboard/dummy-profile.svg');
                $avatarFor = function ($row) use ($defaultAvatar) {
                    return $row->image
                        ? asset('assets/uploads/' . $row->image)
                        : $defaultAvatar;
                };
            @endphp

            {{-- Ranked list — plain numbering, no medals --}}
            <div class="ts-list">
                @forelse($sellers as $row)
                    @php
                        $tierClass = $row->rank <= 10 ? 'top10' : ($row->rank <= 50 ? 'top50' : '');
                    @endphp
                    <div class="ts-list-row {{ $tierClass }}">
                        <div class="rank"><span class="lbl">{{ __('Top Seller') }}</span> #{{ $row->rank }}</div>
                        <div class="avatar"><img src="{{ $avatarFor($row) }}" alt="{{ $row->name }}"></div>
                        <div class="who">
                            <a href="{{ url('/'.$row->username) }}" class="name">{{ $row->name ?? '—' }}</a>
                            <div class="meta">{{ '@'.($row->username ?? '') }}</div>
                        </div>
                        <div class="score">{{ number_format($row->completed_orders) }}<small>{{ __('Completed') }}</small></div>
                        <a href="{{ url('/'.$row->username) }}" class="btn-view">{{ __('View Profile') }} →</a>
                    </div>
                @empty
                    <div class="ts-empty">
                        <h3 style="margin:0 0 8px;color:#1f2733">{{ __('No completed orders yet') }}</h3>
                        <p style="margin:0">{{ __('The Top 100 leaderboard fills up as sellers deliver work. Check back soon.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
