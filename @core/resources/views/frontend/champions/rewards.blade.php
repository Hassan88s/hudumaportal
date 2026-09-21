@extends('frontend.frontend-master')
@section('site-title'){{ __('Huduma Champions — Rewards') }}@endsection

@section('style')
    @include('frontend.champions._style')
@endsection

@section('content')
<div class="hc-page">
    <section class="hc-hero">
        <div class="container">
            <span class="kicker">🏆 {{ __('Huduma Champions') }}</span>
            <h1>{{ __('Monthly') }} <span>{{ __('Rewards') }}</span></h1>
            <p class="sub">{{ __('The Top Five in each league win every month. Everyone else still earns levels and badges that stay on their account.') }}</p>
            @include('frontend.champions._tabs', ['active' => 'rewards'])
        </div>
    </section>

    <section class="hc-body">
        <div class="container">
            <div class="hc-grid" style="grid-template-columns:1fr 1fr">
                @foreach(['provider' => __('Huduma Pro League — Providers'), 'client' => __('Huduma Client League — Clients')] as $lg => $title)
                    <div class="hc-card" style="margin:0">
                        <h3>{{ $title }}</h3>
                        <table class="hc-table">
                            @foreach($rewards[$lg] as $rank => [$type, $amount, $benefits])
                                <tr class="top5">
                                    <td class="rank" style="width:50px">#{{ $rank }}</td>
                                    <td>
                                        <strong>TZS {{ number_format($amount) }}</strong> {{ $type === 'cash' ? __('cash') : ($lg === 'client' ? __('service credits') : __('promotional credits')) }}
                                        @if($benefits)<div style="font-size:12px;color:#6b7280">{{ $benefits }}</div>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach
            </div>

            <div class="hc-grid" style="grid-template-columns:1fr 1fr;margin-top:18px">
                @foreach(['provider' => __('Provider levels'), 'client' => __('Client levels')] as $lg => $title)
                    <div class="hc-card" style="margin:0">
                        <h3>{{ $title }}</h3>
                        <table class="hc-table">
                            @foreach($levels[$lg] as $min => $name)
                                <tr><td><strong>{{ $name }}</strong></td><td style="text-align:right">{{ number_format($min) }}+ HP {{ __('in a month') }}</td></tr>
                            @endforeach
                        </table>
                    </div>
                @endforeach
            </div>

            <div class="hc-card" style="margin-top:18px">
                <h3>{{ __('Permanent badges') }}</h3>
                <ul style="margin:0 0 12px;padding-left:18px;line-height:1.8;color:#374151">
                    <li>{{ __('Champion, Runner-Up and Top Five badges for each month you win, e.g. "September 2026 Provider Champion".') }}</li>
                    <li>{{ __('A level badge for every level you reach in a month, e.g. "Gold Provider · September 2026".') }}</li>
                    <li>{{ __('An achievement badge for each monthly mission you complete.') }}</li>
                </ul>
                <p style="margin:0;font-size:13px;color:#6b7280">{{ __('Credits are added to your HudumaPortal wallet once winners are verified. Rewards are distributed within 7 days of the announcement.') }}</p>

                @if($badges->count())
                    <h3 style="margin-top:18px">{{ __('Your badges') }}</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        @foreach($badges as $b)
                            <span class="hc-pill" style="background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;padding:6px 12px">🏆 {{ $b->label }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
