@extends('frontend.user.buyer.buyer-master')
@section('site-title'){{ __('Getting Started') }}@endsection

@section('content')
    <x-frontend.seller-buyer-preloader/>
    @include('frontend.user.seller.partials.sidebar-two')
    @include('frontend.champions._style')
    <style>
        .ob-dots{display:flex;gap:6px;margin:0 0 16px}
        .ob-dots span{flex:1;height:6px;border-radius:999px;background:#e5e7eb}
        .ob-dots span.on{background:linear-gradient(90deg,#ff8a54,#ff6b3d)}
        .ob-step{display:none}
        .ob-step.active{display:block}
        .ob-icon{width:52px;height:52px;border-radius:14px;background:#fff7ed;color:#ff6b3d;display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:10px}
        .ob-done{display:inline-block;background:#dcfce7;color:#166534;font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;margin-left:8px;vertical-align:middle}
        .ob-todo{display:inline-block;background:#fef3c7;color:#92400e;font-size:12px;font-weight:700;padding:3px 10px;border-radius:999px;margin-left:8px;vertical-align:middle}
        .ob-nav{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:18px;flex-wrap:wrap}
    </style>

    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner hc-page" style="background:transparent">
                <div class="hc-card" style="background:linear-gradient(135deg,#1f2733,#2d3748);color:#fff;border:none">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#fbbf24">{{ __('Getting started') }}</div>
                    <div style="font-size:22px;font-weight:800;margin-top:4px;color:#fff">{{ __('5 quick steps to your first bookings') }}</div>
                    <div style="opacity:.85;margin-top:4px">
                        @if($finished)
                            ✓ {{ __('You finished this tutorial and earned +30 HP.') }}
                        @else
                            {{ __('Finish the tutorial to earn +30 HP in the Huduma Pro League.') }}
                        @endif
                    </div>
                </div>

                <div class="hc-card" style="max-width:720px">
                    <div class="ob-dots">@foreach($steps as $i => $s)<span data-dot="{{ $i }}"></span>@endforeach</div>

                    @foreach($steps as $i => $s)
                        <div class="ob-step" data-step="{{ $i }}">
                            <div class="ob-icon"><i class="las {{ $s['icon'] }}"></i></div>
                            <div style="font-size:12px;color:#8892a0;font-weight:700;text-transform:uppercase">{{ __('Step :n of :t', ['n' => $i + 1, 't' => count($steps)]) }}</div>
                            <h3 style="margin:4px 0 8px;text-transform:none;letter-spacing:0;font-size:20px">
                                {{ $s['title'] }}
                                @if($s['done'] === true)<span class="ob-done">✓ {{ __('Done') }}</span>
                                @elseif($s['done'] === false)<span class="ob-todo">{{ __('To do') }}</span>@endif
                            </h3>
                            <p style="margin:0 0 12px;color:#4b5563;line-height:1.6">{{ $s['text'] }}</p>
                            @if(!empty($s['status']))<p style="margin:0 0 12px;font-size:13px;color:#6b7280">{{ $s['status'] }}</p>@endif
                            <a class="hc-btn ghost" href="{{ $s['link'] }}" target="_blank">{{ $s['cta'] }} ↗</a>
                        </div>
                    @endforeach

                    <div class="ob-nav">
                        <button type="button" class="hc-btn ghost" id="ob-back" style="border:1px solid #d1d5db">← {{ __('Back') }}</button>
                        <button type="button" class="hc-btn" id="ob-next" style="border:0">{{ __('Next') }} →</button>
                        <form method="post" action="{{ route('seller.onboarding.complete') }}" id="ob-finish" style="display:none;margin:0">
                            @csrf
                            <button type="submit" class="hc-btn" style="border:0">
                                {{ $finished ? __('Back to my Champions dashboard') : __('Finish tutorial (+30 HP)') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var steps = document.querySelectorAll('.ob-step'), dots = document.querySelectorAll('.ob-dots span');
            var back = document.getElementById('ob-back'), next = document.getElementById('ob-next'), finish = document.getElementById('ob-finish');
            var i = 0;
            function show(n) {
                i = Math.max(0, Math.min(n, steps.length - 1));
                steps.forEach(function (s, k) { s.classList.toggle('active', k === i); });
                dots.forEach(function (d, k) { d.classList.toggle('on', k <= i); });
                back.style.visibility = i === 0 ? 'hidden' : 'visible';
                var last = i === steps.length - 1;
                next.style.display = last ? 'none' : '';
                finish.style.display = last ? 'block' : 'none';
            }
            back.addEventListener('click', function () { show(i - 1); });
            next.addEventListener('click', function () { show(i + 1); });
            show(0);
        })();
    </script>
@endsection
