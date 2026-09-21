@extends('backend.admin-master')
@section('site-title'){{ __('Huduma Champions — Analytics') }}@endsection

@section('style')
<style>
    .hc-kpi .top{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px}
    .hc-kpi .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
    .hc-kpi .card-k{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:14px 16px}
    .hc-kpi .card-k .lbl{font-size:11px;color:#8892a0;text-transform:uppercase;letter-spacing:.4px;font-weight:700}
    .hc-kpi .card-k .val{font-size:24px;font-weight:800;color:#1f2733;margin-top:4px}
    .hc-kpi .card-k .cmp{font-size:12px;margin-top:4px}
    .hc-kpi .good{color:#15803d}.hc-kpi .bad{color:#b91c1c}.hc-kpi .flat{color:#6b7280}
    .hc-kpi .want{font-size:11px;color:#8892a0}
    @media (max-width:1100px){.hc-kpi .grid{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:560px){.hc-kpi .grid{grid-template-columns:1fr}}
</style>
@endsection

@section('content')
@php
    // [label, key, desired direction (+1 up, -1 down, 0 info), format]
    $rows = [
        [__('Provider activation'),        'provider_activation', 1,  'n', __('providers who published their first service')],
        [__('Provider response rate'),     'response_rate',       1,  '%', __('avg. share of client chats answered')],
        [__('Completed services'),         'completed',           1,  'n', __('orders completed')],
        [__('Client bookings'),            'bookings',            1,  'n', __('orders placed')],
        [__('Repeat bookings'),            'repeat',              1,  'n', __('completed with a provider used before')],
        [__('Verified reviews'),           'reviews',             1,  'n', __('client reviews')],
        [__('Qualified referral activation'), 'referrals',        1,  'n', __('referrals approved')],
        [__('30-day retention'),           'retention',           1,  '%', __('last month\'s active users active again')],
        [__('Cancellation rate'),          'cancel_rate',        -1,  '%', __('cancelled ÷ (completed + cancelled)')],
        [__('Fraud rate'),                 'fraud_rate',         -1,  '%', __('disqualified ÷ players')],
        [__('GMV'),                        'gmv',                 1,  'tzs', __('value of completed orders')],
        [__('Platform revenue'),           'revenue',             1,  'tzs', __('commission on completed orders')],
    ];
    $fmt = function ($v, $f) {
        if ($v === null) return '—';
        return match ($f) { '%' => number_format($v, 1) . '%', 'tzs' => 'TZS ' . number_format($v), default => number_format($v) };
    };
@endphp
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 hc-kpi">
            <div class="top">
                <div>
                    <h4 style="margin:0">{{ __('Huduma Champions — Analytics') }}</h4>
                    <small class="text-muted">{{ __('PDF §38: judge the program by marketplace outcomes, not by HP issued.') }} {{ __('Compared with') }} {{ $prev }}.</small>
                </div>
                <form method="get" style="display:flex;gap:8px;align-items:center">
                    <select name="season" onchange="this.form.submit()" class="form-control form-control-sm" style="width:auto">
                        @foreach($seasons->push($season)->unique()->sortDesc() as $s)
                            <option value="{{ $s }}" @selected($s === $season)>{{ $s }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('admin.champions.index', ['season' => $season]) }}" class="btn btn-sm btn-outline-secondary">← {{ __('Champions') }}</a>
                </form>
            </div>

            <div class="grid">
                @foreach($rows as [$label, $key, $want, $f, $hint])
                    @php
                        $a = $cur[$key] ?? null; $b = $last[$key] ?? null;
                        $cls = 'flat'; $arrow = '→'; $delta = '';
                        if ($a !== null && $b !== null && $a != $b) {
                            $up = $a > $b; $arrow = $up ? '▲' : '▼';
                            $cls = ($up ? 1 : -1) === $want ? 'good' : 'bad';
                            $delta = $f === '%' ? number_format(abs($a - $b), 1) . ' pts' : ($b ? number_format(abs($a - $b) / abs($b) * 100, 0) . '%' : 'new');
                        }
                    @endphp
                    <div class="card-k">
                        <div class="lbl">{{ $label }}</div>
                        <div class="val">{{ $fmt($a, $f) }}</div>
                        <div class="cmp"><span class="{{ $cls }}">{{ $arrow }} {{ $delta }}</span> <span class="text-muted">{{ __('vs') }} {{ $fmt($b, $f) }}</span></div>
                        <div class="want">{{ $hint }} · {{ $want > 0 ? __('want ↑') : __('want ↓') }}</div>
                    </div>
                @endforeach
            </div>

            <div class="card-k" style="margin-top:14px">
                <div class="lbl">{{ __('Program activity (for reference)') }}</div>
                <div style="margin-top:6px;font-size:14px">
                    {{ __('Players this season') }}: <strong>{{ number_format($cur['players']) }}</strong> ·
                    {{ __('Confirmed HP issued') }}: <strong>{{ number_format($cur['hp_confirmed']) }}</strong>
                    <span class="text-muted">({{ __('HP issued is not a success measure on its own') }})</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
