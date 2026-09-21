@extends('frontend.frontend-master')
@section('site-title'){{ __('Huduma Champions — Official Rules') }}@endsection

@section('style')
    @include('frontend.champions._style')
@endsection

@section('content')
@php
    $limitText = function ($r) use ($caps) {
        $bits = [];
        if (($r['limit'] ?? null) === 'once')  $bits[] = __('once');
        elseif (($r['limit'] ?? null) === 'month') $bits[] = __('once per month');
        elseif (is_int($r['limit'] ?? null))   $bits[] = __('max :n per month', ['n' => $r['limit']]);
        if (!empty($r['cap']))     $bits[] = __('shared cap :n HP/month', ['n' => $caps[$r['cap']] ?? '']);
        if (!empty($r['pending'])) $bits[] = __('pending until refund window closes');
        return implode(' · ', $bits);
    };
    $leagues = ['provider' => __('Huduma Pro League (Service Providers)'), 'client' => __('Huduma Client League (Clients)')];
@endphp
<div class="hc-page">
    <section class="hc-hero">
        <div class="container">
            <span class="kicker">🏆 {{ __('Huduma Champions') }}</span>
            <h1>{{ __('Official') }} <span>{{ __('Rules') }}</span></h1>
            <p class="sub">{{ __('A new season starts on the 1st of every month (East Africa Time) and ends on the last day at 23:59. Points reset every month; lifetime levels and badges stay.') }}</p>
            @include('frontend.champions._tabs', ['active' => 'rules'])
        </div>
    </section>

    <section class="hc-body">
        <div class="container">
            @foreach($leagues as $league => $title)
                <div class="hc-card" style="padding:0">
                    <div style="padding:16px 22px;border-bottom:1px solid #eef0f3"><h3 style="margin:0">{{ $title }}</h3></div>
                    <div class="hc-scroll">
                        <table class="hc-table">
                            <thead><tr><th>{{ __('Activity') }}</th><th style="text-align:right">{{ __('HP') }}</th><th>{{ __('Limits') }}</th></tr></thead>
                            <tbody>
                            @foreach($rules as $key => $r)
                                @continue(($r['league'] ?? null) !== $league)
                                <tr>
                                    <td>{{ __($r['label']) }}</td>
                                    <td class="hp" style="text-align:right;{{ $r['hp'] < 0 ? 'color:#b91c1c' : '' }}">{{ $r['hp'] > 0 ? '+' : '' }}{{ number_format($r['hp']) }}</td>
                                    <td style="font-size:12px;color:#6b7280">{{ $limitText($r) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="hc-grid" style="grid-template-columns:1fr 1fr">
                    <div class="hc-card" style="margin:0">
                        <h3>{{ __('Monthly rewards') }}</h3>
                        <table class="hc-table">
                            @foreach($rewards[$league] as $rank => [$type, $amount, $benefits])
                                <tr><td class="rank">#{{ $rank }}</td><td><strong>TZS {{ number_format($amount) }}</strong> {{ $type === 'cash' ? __('cash') : __('service credit') }}<div style="font-size:12px;color:#6b7280">{{ $benefits }}</div></td></tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="hc-card" style="margin:0">
                        <h3>{{ __('Lifetime levels') }}</h3>
                        <table class="hc-table">
                            @foreach($levels[$league] as $min => $name)
                                <tr><td><strong>{{ $name }}</strong></td><td style="text-align:right">{{ number_format($min) }}+ HP</td></tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endforeach

            <div class="hc-card">
                <h3>{{ __('Fair play') }}</h3>
                <ul style="margin:0;padding-left:18px;line-height:1.8;color:#374151">
                    <li>{{ __('Only real, completed and paid transactions earn transaction points. Self-bookings never count.') }}</li>
                    <li>{{ __('Transaction points stay pending until the refund/dispute window closes. Refunded or cancelled jobs lose their points.') }}</li>
                    <li>{{ __('The same provider and client can earn full points for a limited number of transactions per month.') }}</li>
                    <li>{{ __('Fake bookings, fake reviews, duplicate accounts or collusion lead to disqualification from the season.') }}</li>
                    <li>{{ __('Ties are broken by: completed transactions, completion rate, verified rating, fewer cancellations, then who reached the score first.') }}</li>
                    <li>{{ __('Last month\'s #1 cannot win #1 again the following month.') }}</li>
                    <li>{{ __('The Top 20 in each league are reviewed manually. Winners are announced on the 5th of the following month, after audit.') }}</li>
                    <li>{{ __('Client names are shown as first name and last initial only.') }}</li>
                </ul>
            </div>
        </div>
    </section>
</div>
@endsection
