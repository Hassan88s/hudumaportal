@extends('backend.admin-master')
@section('site-title'){{ __('Rafiki Rewards — Click Analytics') }}@endsection

@section('style')
<style>
    .rf-cx{padding:0}
    .rf-cx .totals{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px}
    .rf-cx .card-tile{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:16px 18px}
    .rf-cx .card-tile .label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#8892a0;font-weight:600;margin-bottom:6px}
    .rf-cx .card-tile .value{font-size:24px;font-weight:800;color:#1f2733}
    .rf-cx .card-tile.orange{background:linear-gradient(135deg,#ff8a54 0%,#ff6b3d 100%);border-color:transparent}
    .rf-cx .card-tile.orange .label,.rf-cx .card-tile.orange .value{color:#fff}
    .rf-cx .card-tile.orange .label{color:rgba(255,255,255,.9)}

    .rf-cx .filters{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:14px 18px;margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    .rf-cx .filters label{font-size:12px;color:#6b7280;font-weight:600;margin:0 6px 0 0}
    .rf-cx .filters a{padding:6px 14px;background:#fff;border:1px solid #d1d5db;color:#1f2733;border-radius:999px;text-decoration:none;font-size:13px;font-weight:600}
    .rf-cx .filters a.active{background:#ff8a54;color:#fff;border-color:#ff8a54}

    .rf-cx .row-cards{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px}
    .rf-cx .card-box{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:20px}
    .rf-cx .card-box h3{font-size:14px;font-weight:700;color:#1f2733;margin:0 0 14px;text-transform:uppercase;letter-spacing:.4px}

    .rf-cx table{width:100%;border-collapse:collapse;font-size:13px}
    .rf-cx table th{padding:9px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8892a0;text-align:left;font-weight:600;background:#f8f9fb;border-bottom:1px solid #e6e9ef}
    .rf-cx table td{padding:10px 12px;border-bottom:1px solid #f2f4f7;color:#1f2733;vertical-align:middle}
    .rf-cx table tr:last-child td{border-bottom:none}
    .rf-cx table tr:hover td{background:#fafbfc}
    .rf-cx table .code{font-family:monospace;background:#f3f4f6;padding:2px 6px;border-radius:4px;font-size:12px}
    .rf-cx table .cv{font-weight:700;color:#c2410c}
    .rf-cx .empty{text-align:center;padding:30px;color:#8892a0;font-size:14px}

    /* mini bar chart for trend */
    .rf-cx .trend{display:flex;align-items:flex-end;gap:4px;height:120px;padding:10px 0;justify-content:space-between}
    .rf-cx .trend .bar{flex:1;background:linear-gradient(180deg,#ff8a54,#ff6b3d);border-radius:4px 4px 0 0;position:relative;min-width:12px;min-height:2px}
    .rf-cx .trend .bar::after{content:attr(data-day);position:absolute;bottom:-16px;left:50%;transform:translateX(-50%);font-size:9px;color:#8892a0;white-space:nowrap}
    .rf-cx .trend .bar:hover::before{content:attr(data-count);position:absolute;top:-22px;left:50%;transform:translateX(-50%);background:#1f2733;color:#fff;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:600;white-space:nowrap}

    @media (max-width: 900px){ .rf-cx .row-cards{grid-template-columns:1fr} }
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')

            <div class="rf-cx">
                {{-- Timeframe filter --}}
                <div class="filters">
                    <label>{{ __('Timeframe:') }}</label>
                    @foreach([7=>'7 days', 30=>'30 days', 90=>'90 days', 365=>'1 year'] as $d => $lbl)
                        <a href="{{ route('admin.referrals.clicks', ['days'=>$d]) }}" class="{{ $days == $d ? 'active' : '' }}">{{ $lbl }}</a>
                    @endforeach
                </div>

                {{-- Totals --}}
                <div class="totals">
                    <div class="card-tile orange">
                        <div class="label">{{ __('Total Clicks') }}</div>
                        <div class="value">{{ number_format($totalClicks) }}</div>
                    </div>
                    <div class="card-tile">
                        <div class="label">{{ __('Unique IPs') }}</div>
                        <div class="value">{{ number_format($uniqueIps) }}</div>
                    </div>
                    <div class="card-tile">
                        <div class="label">{{ __('Converted to Signup') }}</div>
                        <div class="value">{{ number_format($converted) }}</div>
                    </div>
                    <div class="card-tile">
                        <div class="label">{{ __('Conversion Rate') }}</div>
                        <div class="value">{{ $conversionRate }}%</div>
                    </div>
                    <div class="card-tile">
                        <div class="label">{{ __('Invalid Codes') }}</div>
                        <div class="value" style="color:#991b1b">{{ number_format($invalid) }}</div>
                    </div>
                </div>

                {{-- Trend chart --}}
                <div class="card-box" style="margin-bottom:20px">
                    <h3>{{ __('Clicks — Last 14 Days') }}</h3>
                    <div class="trend">
                        @php
                            $max = max(1, $trend->max('clicks'));
                            $trendMap = $trend->keyBy('day');
                        @endphp
                        @for($i = 13; $i >= 0; $i--)
                            @php
                                $day = now()->subDays($i)->format('Y-m-d');
                                $count = (int) optional($trendMap->get($day))->clicks;
                                $h = $max > 0 ? max(2, ($count / $max) * 100) : 2;
                            @endphp
                            <div class="bar" style="height:{{ $h }}%" data-day="{{ now()->subDays($i)->format('d M') }}" data-count="{{ $count }} clicks"></div>
                        @endfor
                    </div>
                </div>

                {{-- Top codes + channels --}}
                <div class="row-cards">
                    <div class="card-box">
                        <h3>{{ __('Top Referral Codes') }} — {{ __('by clicks') }}</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Referrer') }}</th>
                                    <th>{{ __('Clicks') }}</th>
                                    <th>{{ __('Conversions') }}</th>
                                    <th>{{ __('CR') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCodes as $row)
                                    <tr>
                                        <td><span class="code">{{ $row->code }}</span></td>
                                        <td>{{ $row->referrer_name ?? '—' }}<br><small style="color:#8892a0;font-size:11px">{{ $row->referrer_email }}</small></td>
                                        <td><strong>{{ number_format($row->clicks) }}</strong></td>
                                        <td class="cv">{{ number_format($row->conversions) }}</td>
                                        <td>{{ $row->clicks > 0 ? round(($row->conversions / $row->clicks) * 100, 1) : 0 }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="empty">{{ __('No clicks in this window yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-box">
                        <h3>{{ __('Channels') }}</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('Channel') }}</th>
                                    <th>{{ __('Clicks') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($channels as $ch)
                                    <tr>
                                        <td>{{ $ch->channel ?? __('(direct)') }}</td>
                                        <td><strong>{{ number_format($ch->clicks) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="empty">{{ __('No channel data yet.') }}</td></tr>
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
