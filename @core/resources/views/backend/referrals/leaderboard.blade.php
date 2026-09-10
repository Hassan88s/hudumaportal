@extends('backend.admin-master')
@section('site-title'){{ __('Rafiki Rewards — Leaderboard') }}@endsection

@section('style')
<style>
    .rf-lb{padding:0}
    .rf-lb .filters{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    .rf-lb .filters label{font-size:12px;color:#6b7280;font-weight:600;margin:0 6px 0 0}
    .rf-lb .filters a{padding:6px 14px;background:#fff;border:1px solid #d1d5db;color:#1f2733;border-radius:999px;text-decoration:none;font-size:13px;font-weight:600}
    .rf-lb .filters a.active{background:#ff8a54;color:#fff;border-color:#ff8a54}

    .rf-lb .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .rf-lb .card-box{background:#fff;border:1px solid #e6e9ef;border-radius:10px;overflow:hidden}
    .rf-lb .card-box .hd{padding:16px 20px;background:#f8f9fb;border-bottom:1px solid #e6e9ef;display:flex;justify-content:space-between;align-items:center}
    .rf-lb .card-box .hd h3{font-size:14px;font-weight:700;margin:0;color:#1f2733;text-transform:uppercase;letter-spacing:.4px}
    .rf-lb .card-box .hd .sub{font-size:11px;color:#8892a0}

    .rf-lb table{width:100%;border-collapse:collapse;font-size:13px}
    .rf-lb table th{padding:9px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8892a0;text-align:left;font-weight:600;background:#fff;border-bottom:1px solid #e6e9ef}
    .rf-lb table td{padding:12px 14px;border-bottom:1px solid #f2f4f7;color:#1f2733;vertical-align:middle}
    .rf-lb table tr:last-child td{border-bottom:none}
    .rf-lb table tr:hover td{background:#fafbfc}
    .rf-lb .rank{width:32px;height:32px;border-radius:50%;background:#f3f4f6;color:#6b7280;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
    .rf-lb tr:nth-child(1) .rank{background:linear-gradient(135deg,#fde68a,#fbbf24);color:#78350f}
    .rf-lb tr:nth-child(2) .rank{background:linear-gradient(135deg,#e5e7eb,#9ca3af);color:#1f2937}
    .rf-lb tr:nth-child(3) .rank{background:linear-gradient(135deg,#fed7aa,#fb923c);color:#7c2d12}
    .rf-lb .user strong{display:block;font-weight:600;color:#1f2733}
    .rf-lb .user small{color:#8892a0;font-size:11px}
    .rf-lb .code{font-family:monospace;background:#f3f4f6;padding:2px 6px;border-radius:4px;font-size:11px;color:#1f2733}
    .rf-lb .num{font-weight:800;color:#c2410c}
    .rf-lb .empty{text-align:center;padding:30px;color:#8892a0;font-size:14px}

    /* Rafiki level badges */
    .rf-lb .lvl{display:inline-block;padding:2px 8px;font-size:9px;font-weight:700;border-radius:999px;text-transform:uppercase;letter-spacing:.4px;margin-left:6px}
    .rf-lb .lvl.rafiki{background:#f3f4f6;color:#374151}
    .rf-lb .lvl.balozi{background:#fef3c7;color:#92400e}
    .rf-lb .lvl.super{background:#e0e7ff;color:#3730a3}
    .rf-lb .lvl.champion{background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff}

    @media (max-width: 900px){ .rf-lb .grid{grid-template-columns:1fr} }
</style>
@endsection

@section('content')
@php
    // Rafiki level thresholds from the strategy PDF
    $level = function ($count) {
        if ($count >= 50) return ['champion', 'HUDUMA CHAMPION'];
        if ($count >= 15) return ['super',    'SUPER BALOZI'];
        if ($count >= 5)  return ['balozi',   'BALOZI'];
        return ['rafiki', 'RAFIKI'];
    };
@endphp

<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')

            <div class="rf-lb">
                {{-- Period filter --}}
                <div class="filters">
                    <label>{{ __('Timeframe:') }}</label>
                    <a href="{{ route('admin.referrals.leaderboard', ['period'=>'month']) }}" class="{{ $period === 'month' ? 'active' : '' }}">{{ __('This Month') }}</a>
                    <a href="{{ route('admin.referrals.leaderboard', ['period'=>'year']) }}"  class="{{ $period === 'year'  ? 'active' : '' }}">{{ __('This Year') }}</a>
                    <a href="{{ route('admin.referrals.leaderboard', ['period'=>'all']) }}"   class="{{ $period === 'all'   ? 'active' : '' }}">{{ __('All Time') }}</a>
                </div>

                <div class="grid">
                    {{-- By count --}}
                    <div class="card-box">
                        <div class="hd">
                            <h3>{{ __('Top by Referrals') }}</h3>
                            <span class="sub">{{ __('Levels: Rafiki 0-4 · Balozi 5-14 · Super Balozi 15-49 · Champion 50+') }}</span>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Referrer') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Referrals') }}</th>
                                    <th>{{ __('Approved') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byCount as $i => $row)
                                    @php [$class, $label] = $level((int) $row->ref_count); @endphp
                                    <tr>
                                        <td><span class="rank">{{ $i + 1 }}</span></td>
                                        <td class="user">
                                            <strong>{{ $row->name ?? '—' }} <span class="lvl {{ $class }}">{{ $label }}</span></strong>
                                            <small>{{ $row->email }}</small>
                                        </td>
                                        <td><span class="code">{{ $row->referral_code ?? '—' }}</span></td>
                                        <td><span class="num">{{ number_format($row->ref_count) }}</span></td>
                                        <td>{{ number_format($row->approved_count) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="empty">{{ __('No referrals in this window yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- By earnings --}}
                    <div class="card-box">
                        <div class="hd">
                            <h3>{{ __('Top by Earnings') }}</h3>
                            <span class="sub">{{ __('Approved + paid cash rewards') }}</span>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Referrer') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Earnings') }}</th>
                                    <th>{{ __('Events') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($byEarnings as $i => $row)
                                    <tr>
                                        <td><span class="rank">{{ $i + 1 }}</span></td>
                                        <td class="user">
                                            <strong>{{ $row->name ?? '—' }}</strong>
                                            <small>{{ $row->email }}</small>
                                        </td>
                                        <td><span class="code">{{ $row->referral_code ?? '—' }}</span></td>
                                        <td><span class="num">{{ number_format($row->earnings, 0) }} TZS</span></td>
                                        <td>{{ number_format($row->reward_count) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="empty">{{ __('No rewards paid in this window yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
