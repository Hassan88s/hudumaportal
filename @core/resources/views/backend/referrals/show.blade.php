@extends('backend.admin-master')
@section('site-title'){{ __('Referral') }} #{{ $referral->id }}@endsection

@section('style')
<style>
    .rf-detail{padding:0}
    .rf-detail .back-link{display:inline-flex;align-items:center;gap:6px;color:#6b7280;font-size:13px;text-decoration:none;margin-bottom:12px;font-weight:600}
    .rf-detail .back-link:hover{color:#1f2733}
    .rf-detail .hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
    .rf-detail .hd h1{font-size:22px;font-weight:700;color:#1f2733;margin:0}
    .rf-detail .hd .sub{font-size:13px;color:#6b7280;margin-top:2px}
    .rf-detail .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
    .rf-detail .card{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:18px}
    .rf-detail .card h3{font-size:14px;font-weight:700;color:#1f2733;margin:0 0 14px;text-transform:uppercase;letter-spacing:.4px}
    .rf-detail .kv{display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px dashed #f2f4f7;font-size:13px}
    .rf-detail .kv:last-child{border-bottom:none}
    .rf-detail .kv .k{color:#8892a0;font-weight:500;flex-shrink:0;margin-right:12px}
    .rf-detail .kv .v{color:#1f2733;font-weight:600;text-align:right;word-break:break-word}
    .rf-detail .kv .v.mono{font-family:monospace;font-size:12px}

    .rf-detail .badge-pill{display:inline-block;padding:3px 9px;font-size:10px;font-weight:700;border-radius:999px;text-transform:uppercase;letter-spacing:.4px}
    .rf-detail .badge-pill.pending{background:#fef3c7;color:#92400e}
    .rf-detail .badge-pill.qualifying{background:#e0e7ff;color:#3730a3}
    .rf-detail .badge-pill.approved{background:#d1fae5;color:#065f46}
    .rf-detail .badge-pill.paid{background:#dbeafe;color:#1e40af}
    .rf-detail .badge-pill.rejected,.rf-detail .badge-pill.blocked{background:#fee2e2;color:#991b1b}
    .rf-detail .badge-pill.provider{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
    .rf-detail .badge-pill.client{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
    .rf-detail .badge-pill.business{background:#f3e8ff;color:#6b21a8;border:1px solid #d8b4fe}

    .rf-detail .stages{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:20px}
    .rf-detail .stage-card{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:14px;text-align:center}
    .rf-detail .stage-card.done{background:linear-gradient(135deg,#d1fae5 0%,#fff 100%);border-color:#10b981}
    .rf-detail .stage-card .no{width:36px;height:36px;border-radius:50%;background:#f3f4f6;color:#6b7280;font-weight:700;font-size:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:8px}
    .rf-detail .stage-card.done .no{background:#10b981;color:#fff}
    .rf-detail .stage-card .stg-title{font-size:13px;font-weight:700;color:#1f2733}
    .rf-detail .stage-card .stg-when{font-size:11px;color:#6b7280;margin-top:4px}

    .rf-detail .tbl{width:100%;border-collapse:collapse}
    .rf-detail .tbl th{padding:8px 10px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#8892a0;text-align:left;font-weight:600;background:#f8f9fb;border-bottom:1px solid #e6e9ef}
    .rf-detail .tbl td{padding:10px;font-size:13px;border-bottom:1px solid #f2f4f7;vertical-align:middle}
    .rf-detail .tbl tr:last-child td{border-bottom:none}
    .rf-detail .tbl .empty{text-align:center;padding:20px;color:#8892a0}

    .rf-detail .actions{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .rf-detail .action-card{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:18px}
    .rf-detail .action-card h3{font-size:14px;font-weight:700;color:#1f2733;margin:0 0 6px}
    .rf-detail .action-card .desc{font-size:12px;color:#6b7280;margin:0 0 14px}
    .rf-detail .action-form{display:flex;flex-direction:column;gap:10px}
    .rf-detail .action-form select,.rf-detail .action-form input{padding:9px 12px;font-size:13px;border:1px solid #d1d5db;border-radius:6px;width:100%}
    .rf-detail .btn-block{padding:10px 14px;font-size:13px;font-weight:700;border:none;border-radius:6px;color:#fff;cursor:pointer;transition:opacity .15s}
    .rf-detail .btn-block:hover{opacity:.9}
    .rf-detail .btn-primary{background:#ff8a54}
    .rf-detail .btn-danger{background:#ef4444}
    .rf-detail .btn-success{background:#10b981}
    @media (max-width: 900px){
        .rf-detail .grid,.rf-detail .stages,.rf-detail .actions{grid-template-columns:1fr}
    }
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')

            <div class="rf-detail">
                <a href="{{ route('admin.referrals.index') }}" class="back-link">&larr; {{ __('Back to All Referrals') }}</a>

                <div class="hd">
                    <div>
                        <h1>{{ __('Referral') }} #{{ $referral->id }}
                            <span class="badge-pill {{ $referral->status }}">{{ ucfirst($referral->status) }}</span>
                            <span class="badge-pill {{ $referral->track }}">{{ ucfirst($referral->track) }}</span>
                        </h1>
                        <div class="sub">
                            {{ __('Created') }} {{ optional($referral->created_at)->format('d M Y H:i') }}
                            @if($referral->code_used) · {{ __('Code used') }}: <code>{{ $referral->code_used }}</code> @endif
                            · {{ __('Source') }}: {{ ucfirst($referral->source) }}
                        </div>
                    </div>
                </div>

                {{-- ═══ TWO USER CARDS ═══ --}}
                <div class="grid">
                    <div class="card">
                        <h3>{{ __('Referrer (Earns Rewards)') }}</h3>
                        @if($referral->referrer)
                        <div class="kv"><span class="k">{{ __('Name') }}</span><span class="v">{{ $referral->referrer->name }}</span></div>
                        <div class="kv"><span class="k">{{ __('Username') }}</span><span class="v">{{ $referral->referrer->username }}</span></div>
                        <div class="kv"><span class="k">{{ __('Email') }}</span><span class="v">{{ $referral->referrer->email }}</span></div>
                        <div class="kv"><span class="k">{{ __('Phone') }}</span><span class="v">{{ $referral->referrer->phone ?? '—' }}</span></div>
                        <div class="kv"><span class="k">{{ __('Referral Code') }}</span><span class="v mono">{{ $referral->referrer->referral_code }}</span></div>
                        <div class="kv"><span class="k">{{ __('Joined') }}</span><span class="v">{{ optional($referral->referrer->created_at)->format('d M Y') }}</span></div>
                        @else
                            <div style="color:#8892a0;font-size:13px">{{ __('Referrer no longer exists') }}</div>
                        @endif
                    </div>

                    <div class="card">
                        <h3>{{ __('Referred User (Gets Welcome Credit)') }}</h3>
                        @if($referral->referredUser)
                        <div class="kv"><span class="k">{{ __('Name') }}</span><span class="v">{{ $referral->referredUser->name }}</span></div>
                        <div class="kv"><span class="k">{{ __('Username') }}</span><span class="v">{{ $referral->referredUser->username }}</span></div>
                        <div class="kv"><span class="k">{{ __('Email') }}</span>
                            <span class="v">{{ $referral->referredUser->email }}
                                @if((int) ($referral->referredUser->email_verified ?? 0) === 1)
                                    <span class="badge-pill approved" style="margin-left:6px">{{ __('Verified') }}</span>
                                @else
                                    <span class="badge-pill pending" style="margin-left:6px">{{ __('Unverified') }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="kv"><span class="k">{{ __('Phone') }}</span><span class="v">{{ $referral->referredUser->phone ?? '—' }}</span></div>
                        <div class="kv"><span class="k">{{ __('User Type') }}</span><span class="v">{{ (int) $referral->referredUser->user_type === 2 ? __('Provider') : __('Client / Buyer') }}</span></div>
                        <div class="kv"><span class="k">{{ __('Signed up') }}</span><span class="v">{{ optional($referral->referredUser->created_at)->format('d M Y') }}</span></div>
                        @else
                            <div style="color:#8892a0;font-size:13px">{{ __('Referred user no longer exists') }}</div>
                        @endif
                    </div>
                </div>

                {{-- ═══ STAGES ═══ --}}
                <div class="card" style="margin-bottom:20px">
                    <h3>{{ __('Milestone Progress') }}</h3>
                    <div class="stages">
                        <div class="stage-card {{ $referral->stage1_at ? 'done' : '' }}">
                            <div class="no">1</div>
                            <div class="stg-title">{{ __('Sign-up + Profile') }}</div>
                            <div class="stg-when">{{ $referral->stage1_at ? optional($referral->stage1_at)->format('d M Y H:i') : __('Not yet') }}</div>
                        </div>
                        <div class="stage-card {{ $referral->stage2_at ? 'done' : '' }}">
                            <div class="no">2</div>
                            <div class="stg-title">{{ __('First Paid Order') }}</div>
                            <div class="stg-when">{{ $referral->stage2_at ? optional($referral->stage2_at)->format('d M Y H:i') : __('Not yet') }}</div>
                        </div>
                        <div class="stage-card {{ $referral->stage3_at ? 'done' : '' }}">
                            <div class="no">3</div>
                            <div class="stg-title">{{ __('2nd Order / Subscription') }}</div>
                            <div class="stg-when">{{ $referral->stage3_at ? optional($referral->stage3_at)->format('d M Y H:i') : __('Not yet') }}</div>
                        </div>
                    </div>
                </div>

                {{-- ═══ FRAUD SIGNALS ═══ --}}
                <div class="card" style="margin-bottom:20px">
                    <h3>{{ __('Attribution & Fraud Signals') }}</h3>
                    <div class="kv"><span class="k">{{ __('IP Address') }}</span><span class="v mono">{{ $referral->ip_address ?? '—' }}</span></div>
                    <div class="kv"><span class="k">{{ __('Device Fingerprint') }}</span><span class="v mono">{{ $referral->device_fingerprint ?? '—' }}</span></div>
                    <div class="kv"><span class="k">{{ __('User Agent') }}</span><span class="v" style="font-size:11px">{{ $referral->user_agent ?? '—' }}</span></div>
                    <div class="kv"><span class="k">{{ __('Landing URL') }}</span><span class="v mono" style="font-size:11px">{{ $referral->landing_url ?? '—' }}</span></div>
                    @if($referral->rejection_reason)
                        <div class="kv"><span class="k">{{ __('Rejection reason') }}</span><span class="v" style="color:#991b1b">{{ $referral->rejection_reason }}</span></div>
                    @endif
                </div>

                {{-- ═══ REWARDS LEDGER ═══ --}}
                <div class="card" style="margin-bottom:20px">
                    <h3>{{ __('Reward Events') }} ({{ $referral->rewards->count() }})</h3>
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Event') }}</th>
                                <th>{{ __('Recipient') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Protection Ends') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($referral->rewards->sortByDesc('id') as $rw)
                                <tr>
                                    <td>{{ optional($rw->created_at)->format('d M Y H:i') }}</td>
                                    <td><strong>{{ $rw->reason ?? $rw->event }}</strong></td>
                                    <td>
                                        @if((int)$rw->user_id === (int)$referral->referrer_id)
                                            {{ __('Referrer') }}
                                        @elseif((int)$rw->user_id === (int)$referral->referred_user_id)
                                            {{ __('Referred user') }}
                                        @else
                                            User #{{ $rw->user_id }}
                                        @endif
                                    </td>
                                    <td><strong>{{ number_format((float)$rw->amount, 0) }}</strong> {{ $rw->currency }}</td>
                                    <td><span class="badge-pill {{ $rw->type === 'cash' ? 'approved' : 'qualifying' }}">{{ $rw->type }}</span></td>
                                    <td><span class="badge-pill {{ $rw->status }}">{{ $rw->status }}</span></td>
                                    <td style="font-size:12px;color:#6b7280">{{ $rw->protection_ends_at ? optional($rw->protection_ends_at)->format('d M Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty">{{ __('No reward events yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ═══ ADMIN ACTIONS ═══ --}}
                <div class="actions">
                    {{-- Change status --}}
                    <div class="action-card">
                        <h3>{{ __('Change Status') }}</h3>
                        <p class="desc">{{ __('Set the overall referral status. Rejecting or blocking will also cancel all pending rewards on this referral.') }}</p>
                        <form method="post" action="{{ route('admin.referrals.status', $referral->id) }}" class="action-form">
                            @csrf
                            <select name="status" required>
                                <option value="qualifying" @selected($referral->status === 'qualifying')>{{ __('Qualifying (in progress)') }}</option>
                                <option value="approved" @selected($referral->status === 'approved')>{{ __('Approved') }}</option>
                                <option value="rejected" @selected($referral->status === 'rejected')>{{ __('Rejected') }}</option>
                                <option value="blocked" @selected($referral->status === 'blocked')>{{ __('Blocked (fraud)') }}</option>
                                <option value="pending" @selected($referral->status === 'pending')>{{ __('Pending') }}</option>
                            </select>
                            <input type="text" name="reason" placeholder="{{ __('Reason (optional, shown to admins)') }}" maxlength="255" value="{{ $referral->rejection_reason }}">
                            <button type="submit" class="btn-block btn-primary">{{ __('Update Status') }}</button>
                        </form>
                    </div>

                    {{-- Manual credit --}}
                    <div class="action-card">
                        <h3>{{ __('Manual Credit') }}</h3>
                        <p class="desc">{{ __('Add a one-off cash reward to the referrer. Immediately approved. Use for goodwill / manual corrections.') }}</p>
                        <form method="post" action="{{ route('admin.referrals.credit', $referral->id) }}" class="action-form">
                            @csrf
                            <input type="number" name="amount" step="0.01" min="1" required placeholder="{{ __('Amount (TZS)') }}">
                            <input type="text" name="reason" required maxlength="255" placeholder="{{ __('Reason (required)') }}">
                            <button type="submit" class="btn-block btn-success">{{ __('Add Manual Credit') }}</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
