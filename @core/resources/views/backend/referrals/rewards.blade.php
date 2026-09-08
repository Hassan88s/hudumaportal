@extends('backend.admin-master')
@section('site-title'){{ __('Rafiki Rewards — Rewards Ledger') }}@endsection

@section('style')
<style>
    .rf-ledger{padding:0}
    .rf-ledger .totals{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px}
    .rf-ledger .total-card{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:16px 18px}
    .rf-ledger .total-card .label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#8892a0;font-weight:600;margin-bottom:6px}
    .rf-ledger .total-card .value{font-size:22px;font-weight:700;color:#1f2733}
    .rf-ledger .total-card .value small{font-size:12px;color:#8892a0;font-weight:500;margin-left:4px}
    .rf-ledger .total-card.pending .value{color:#92400e}
    .rf-ledger .total-card.approved .value{color:#065f46}
    .rf-ledger .total-card.paid .value{color:#1e40af}
    .rf-ledger .total-card.rejected .value{color:#991b1b}

    .rf-ledger .filters{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:16px;margin-bottom:16px;display:grid;grid-template-columns:3fr 1fr 1fr auto auto;gap:10px;align-items:end}
    .rf-ledger .filters label{font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8892a0;font-weight:600;margin-bottom:4px;display:block}
    .rf-ledger .filters .form-control,.rf-ledger .filters .form-select{padding:8px 12px;font-size:13px;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#1f2733;height:38px;width:100%}
    .rf-ledger .filters .btn{padding:8px 14px;font-size:13px;font-weight:600;border-radius:6px;height:38px;white-space:nowrap}
    .rf-ledger .filters .btn-primary{background:#ff8a54;border-color:#ff8a54;color:#fff}
    .rf-ledger .filters .btn-clear{background:#fff;color:#6b7280;border:1px solid #d1d5db;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}

    .rf-ledger .tbl-wrap{background:#fff;border:1px solid #e6e9ef;border-radius:10px;overflow:hidden}
    .rf-ledger table{width:100%;border-collapse:collapse;font-size:13px}
    .rf-ledger table th{padding:10px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8892a0;text-align:left;font-weight:600;background:#f8f9fb;border-bottom:1px solid #e6e9ef;white-space:nowrap}
    .rf-ledger table td{padding:11px 14px;border-bottom:1px solid #f2f4f7;color:#1f2733;vertical-align:middle}
    .rf-ledger table tr:hover td{background:#fafbfc}
    .rf-ledger table tr:last-child td{border-bottom:none}
    .rf-ledger .user-cell strong{display:block;font-weight:600}
    .rf-ledger .user-cell small{color:#8892a0;font-size:11px}
    .rf-ledger .badge-pill{display:inline-block;padding:3px 9px;font-size:10px;font-weight:700;border-radius:999px;text-transform:uppercase;letter-spacing:.4px}
    .rf-ledger .badge-pill.pending{background:#fef3c7;color:#92400e}
    .rf-ledger .badge-pill.qualifying{background:#e0e7ff;color:#3730a3}
    .rf-ledger .badge-pill.approved{background:#d1fae5;color:#065f46}
    .rf-ledger .badge-pill.paid{background:#dbeafe;color:#1e40af}
    .rf-ledger .badge-pill.rejected{background:#fee2e2;color:#991b1b}
    .rf-ledger .badge-pill.cash{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .rf-ledger .badge-pill.credit{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
    .rf-ledger .amt{font-weight:700;color:#c2410c}
    .rf-ledger .empty{text-align:center;padding:40px;color:#8892a0;font-size:14px}
    .rf-ledger .paginator{padding:14px}
    .rf-ledger .ref-link{color:#1f2733;text-decoration:none;font-weight:600;font-size:12px}
    .rf-ledger .ref-link:hover{color:#ff6b3d}
    @media (max-width: 900px){
        .rf-ledger .filters{grid-template-columns:1fr 1fr}
    }
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')

            <div class="rf-ledger">
                {{-- Totals strip --}}
                <div class="totals">
                    <div class="total-card pending"><div class="label">{{ __('Pending (protection)') }}</div><div class="value">{{ number_format($totals['pending'], 0) }} <small>TZS</small></div></div>
                    <div class="total-card approved"><div class="label">{{ __('Approved (available)') }}</div><div class="value">{{ number_format($totals['approved'], 0) }} <small>TZS</small></div></div>
                    <div class="total-card paid"><div class="label">{{ __('Paid (transferred)') }}</div><div class="value">{{ number_format($totals['paid'], 0) }} <small>TZS</small></div></div>
                    <div class="total-card rejected"><div class="label">{{ __('Rejected') }}</div><div class="value">{{ number_format($totals['rejected'], 0) }} <small>TZS</small></div></div>
                </div>

                {{-- Filters --}}
                <form method="get" action="{{ route('admin.referrals.rewards') }}" class="filters">
                    <div>
                        <label>{{ __('Search recipient') }}</label>
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Name, email or username…') }}">
                    </div>
                    <div>
                        <label>{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach(['pending','qualifying','approved','paid','rejected'] as $s)
                                <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>{{ __('Type') }}</label>
                        <select name="type" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="cash" @selected($type === 'cash')>{{ __('Cash (referrer)') }}</option>
                            <option value="credit" @selected($type === 'credit')>{{ __('Credit (new user)') }}</option>
                        </select>
                    </div>
                    <div><button type="submit" class="btn btn-primary">{{ __('Filter') }}</button></div>
                    <div><a href="{{ route('admin.referrals.rewards') }}" class="btn btn-clear">{{ __('Clear') }}</a></div>
                </form>

                {{-- Rewards table --}}
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Recipient') }}</th>
                                <th>{{ __('Event') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Referral') }}</th>
                                <th>{{ __('Protection Ends') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rewards as $rw)
                                <tr>
                                    <td>#{{ $rw->id }}</td>
                                    <td>{{ optional($rw->created_at)->format('d M Y H:i') }}</td>
                                    <td class="user-cell">
                                        <strong>{{ optional($rw->user)->name ?? '—' }}</strong>
                                        <small>{{ optional($rw->user)->email }}</small>
                                    </td>
                                    <td>{{ $rw->reason ?? $rw->event }}</td>
                                    <td><span class="amt">{{ number_format((float) $rw->amount, 0) }}</span> {{ $rw->currency }}</td>
                                    <td><span class="badge-pill {{ $rw->type }}">{{ $rw->type }}</span></td>
                                    <td><span class="badge-pill {{ $rw->status }}">{{ $rw->status }}</span></td>
                                    <td>
                                        @if($rw->referral_id)
                                            <a href="{{ route('admin.referrals.show', $rw->referral_id) }}" class="ref-link">#{{ $rw->referral_id }}</a>
                                        @else
                                            <span style="color:#8892a0;font-size:12px">—</span>
                                        @endif
                                    </td>
                                    <td style="font-size:12px;color:#6b7280">{{ $rw->protection_ends_at ? optional($rw->protection_ends_at)->format('d M Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="empty">{{ __('No reward events match your filters.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($rewards->hasPages())
                        <div class="paginator">{{ $rewards->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
