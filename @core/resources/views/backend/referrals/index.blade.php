@extends('backend.admin-master')
@section('site-title'){{ __('Rafiki Rewards — All Referrals') }}@endsection

@section('style')
<style>
    .rf-admin{padding:0}
    .rf-admin .stats-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px}
    .rf-admin .stat{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:14px 16px}
    .rf-admin .stat .label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#8892a0;font-weight:600;margin-bottom:4px}
    .rf-admin .stat .value{font-size:20px;font-weight:700;color:#1f2733}
    .rf-admin .stat .value small{font-size:11px;color:#8892a0;font-weight:500;margin-left:2px}
    .rf-admin .stat.qualifying .value{color:#3730a3}
    .rf-admin .stat.approved .value{color:#065f46}
    .rf-admin .stat.rejected .value{color:#991b1b}
    .rf-admin .stat.paid .value{color:#c2410c}

    .rf-admin .filters{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:16px;margin-bottom:16px;display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto auto;gap:10px;align-items:end}
    .rf-admin .filters label{font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8892a0;font-weight:600;margin-bottom:4px;display:block}
    .rf-admin .filters .form-control,.rf-admin .filters .form-select{padding:8px 12px;font-size:13px;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#1f2733;height:38px;width:100%}
    .rf-admin .filters .btn{padding:8px 14px;font-size:13px;font-weight:600;border-radius:6px;height:38px;white-space:nowrap}
    .rf-admin .filters .btn-primary{background:#ff8a54;border-color:#ff8a54;color:#fff}
    .rf-admin .filters .btn-primary:hover{background:#ff6b3d;border-color:#ff6b3d}
    .rf-admin .filters .btn-clear{background:#fff;color:#6b7280;border:1px solid #d1d5db;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}

    .rf-admin .tbl-wrap{background:#fff;border:1px solid #e6e9ef;border-radius:10px;overflow:hidden}
    .rf-admin table{width:100%;border-collapse:collapse;font-size:13px}
    .rf-admin table th{text-align:left;padding:10px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#8892a0;font-weight:600;background:#f8f9fb;border-bottom:1px solid #e6e9ef;white-space:nowrap}
    .rf-admin table td{padding:12px 14px;border-bottom:1px solid #f2f4f7;color:#1f2733;vertical-align:middle}
    .rf-admin table tr:hover td{background:#fafbfc}
    .rf-admin table tr:last-child td{border-bottom:none}
    .rf-admin .user-cell strong{display:block;font-weight:600;color:#1f2733}
    .rf-admin .user-cell small{color:#8892a0;font-size:11px}
    .rf-admin .badge-pill{display:inline-block;padding:3px 9px;font-size:10px;font-weight:700;border-radius:999px;text-transform:uppercase;letter-spacing:.4px}
    .rf-admin .badge-pill.pending{background:#fef3c7;color:#92400e}
    .rf-admin .badge-pill.qualifying{background:#e0e7ff;color:#3730a3}
    .rf-admin .badge-pill.approved{background:#d1fae5;color:#065f46}
    .rf-admin .badge-pill.rejected,.rf-admin .badge-pill.blocked{background:#fee2e2;color:#991b1b}
    .rf-admin .badge-pill.flagged{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .rf-admin .flag-indicator{display:inline-flex;align-items:center;gap:4px;padding:2px 7px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:999px;font-size:10px;font-weight:700;letter-spacing:.3px;margin-left:6px}
    .rf-admin .badge-pill.provider{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
    .rf-admin .badge-pill.client{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
    .rf-admin .badge-pill.business{background:#f3e8ff;color:#6b21a8;border:1px solid #d8b4fe}
    .rf-admin .milestone{display:inline-flex;gap:3px}
    .rf-admin .milestone .dot{width:9px;height:9px;border-radius:50%;background:#e4e7ec;border:1px solid #d1d5db}
    .rf-admin .milestone .dot.done{background:#10b981;border-color:#10b981}
    .rf-admin .amt{font-weight:700;color:#c2410c}
    .rf-admin .btn-view{background:#1f2733;color:#fff;padding:5px 10px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none;white-space:nowrap;display:inline-block}
    .rf-admin .btn-view:hover{background:#0f1520;color:#fff}
    .rf-admin .empty{text-align:center;padding:40px;color:#8892a0;font-size:14px}
    .rf-admin .paginator{padding:14px}
    @media (max-width: 992px){
        .rf-admin .filters{grid-template-columns:1fr 1fr}
    }
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')

            <div class="rf-admin">
                {{-- Top stats strip --}}
                <div class="stats-strip">
                    <div class="stat"><div class="label">{{ __('Total Referrals') }}</div><div class="value">{{ number_format($stats['total']) }}</div></div>
                    <div class="stat qualifying"><div class="label">{{ __('Qualifying') }}</div><div class="value">{{ number_format($stats['qualifying']) }}</div></div>
                    <div class="stat approved"><div class="label">{{ __('Approved') }}</div><div class="value">{{ number_format($stats['approved']) }}</div></div>
                    <div class="stat rejected"><div class="label">{{ __('Rejected / Blocked') }}</div><div class="value">{{ number_format($stats['rejected']) }}</div></div>
                    <div class="stat rejected" style="background:#fef2f2;border-color:#fecaca"><div class="label">{{ __('Flagged (Fraud Review)') }}</div><div class="value">{{ number_format($stats['flagged'] ?? 0) }}</div></div>
                    <div class="stat paid"><div class="label">{{ __('Paid Out (This Month)') }}</div><div class="value">{{ number_format($stats['paid_month'], 0) }} <small>TZS</small></div></div>
                    <div class="stat"><div class="label">{{ __('Pending (All Time)') }}</div><div class="value">{{ number_format($stats['pending_all'], 0) }} <small>TZS</small></div></div>
                </div>

                {{-- Filters --}}
                <form method="get" action="{{ route('admin.referrals.index') }}" class="filters">
                    <div>
                        <label>{{ __('Search') }}</label>
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Name, email, username, code…') }}">
                    </div>
                    <div>
                        <label>{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach(['pending','qualifying','approved','rejected','blocked','flagged'] as $s)
                                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>{{ __('Track') }}</label>
                        <select name="track" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach(['provider','client','business'] as $t)
                                <option value="{{ $t }}" @selected($track === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>{{ __('From') }}</label>
                        <input type="date" name="from" value="{{ $from }}" class="form-control">
                    </div>
                    <div>
                        <label>{{ __('To') }}</label>
                        <input type="date" name="to" value="{{ $to }}" class="form-control">
                    </div>
                    <div><button type="submit" class="btn btn-primary">{{ __('Filter') }}</button></div>
                    <div><a href="{{ route('admin.referrals.index') }}" class="btn btn-clear">{{ __('Clear') }}</a></div>
                </form>

                {{-- Referrals table --}}
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Referrer') }}</th>
                                <th>{{ __('Referred User') }}</th>
                                <th>{{ __('Track') }}</th>
                                <th>{{ __('Progress') }}</th>
                                <th>{{ __('Rewards') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($referrals as $r)
                                <tr>
                                    <td>
                                        #{{ $r->id }}
                                        @if(!empty($r->fraud_flags))
                                            <span class="flag-indicator" title="{{ count($r->fraud_flags) }} fraud flag(s) detected">⚠ {{ count($r->fraud_flags) }}</span>
                                        @endif
                                    </td>
                                    <td class="user-cell">
                                        <strong>{{ optional($r->referrer)->name ?? '—' }}</strong>
                                        <small>{{ optional($r->referrer)->email }} · {{ optional($r->referrer)->referral_code }}</small>
                                    </td>
                                    <td class="user-cell">
                                        <strong>{{ optional($r->referredUser)->name ?? '—' }}</strong>
                                        <small>{{ optional($r->referredUser)->email }}</small>
                                    </td>
                                    <td><span class="badge-pill {{ $r->track }}">{{ ucfirst($r->track) }}</span></td>
                                    <td>
                                        <span class="milestone" title="{{ __('Stage 1 → 2 → 3') }}">
                                            <span class="dot {{ $r->stage1_at ? 'done' : '' }}"></span>
                                            <span class="dot {{ $r->stage2_at ? 'done' : '' }}"></span>
                                            <span class="dot {{ $r->stage3_at ? 'done' : '' }}"></span>
                                        </span>
                                    </td>
                                    <td><span class="amt">{{ number_format((float) ($r->rewards_total ?? 0), 0) }} TZS</span></td>
                                    <td><span class="badge-pill {{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                                    <td>{{ optional($r->created_at)->format('d M Y') }}<br><small style="color:#8892a0;font-size:11px">{{ optional($r->created_at)->format('H:i') }}</small></td>
                                    <td><a href="{{ route('admin.referrals.show', $r->id) }}" class="btn-view">{{ __('View') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="empty">{{ __('No referrals match your filters.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($referrals->hasPages())
                        <div class="paginator">{{ $referrals->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
