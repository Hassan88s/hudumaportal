@extends('backend.admin-master')
@section('site-title'){{ __('Huduma Champions') }}@endsection

@section('style')
<style>
    /* The admin dark theme forces headings/labels to white — keep text dark inside our white boxes */
    /* dark-mode.css uses `h1,h3,h5,h6{color:#f0f0f0 !important}`, so these need !important too */
    .hc-adm .box,.hc-adm .stat{color:#1f2733 !important}
    .hc-adm .box h3,.hc-adm .box h4,.hc-adm .box h5,.hc-adm .box label,.hc-adm .box td,.hc-adm .box strong,.hc-adm .box span:not(.pill){color:#1f2733 !important}
    .hc-adm .box th,.hc-adm .box small,.hc-adm .box .text-muted,.hc-adm .stat small{color:#6b7280 !important}
    .hc-adm .box a{color:#2563eb !important}
    .hc-adm .box input,.hc-adm .box select{color:#1f2733 !important;background:#fff !important}
    .hc-adm .box label{font-weight:600;display:inline-flex;flex-direction:column;gap:4px;margin:0}
    .hc-adm .box{background:#fff;border:1px solid #e6e9ef;border-radius:10px;margin-bottom:18px;overflow:hidden}
    .hc-adm .box .hd{padding:14px 18px;background:#f8f9fb;border-bottom:1px solid #e6e9ef;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
    .hc-adm .box .hd h3{font-size:14px;font-weight:700;margin:0;text-transform:uppercase;letter-spacing:.4px}
    .hc-adm .box .bd{padding:16px 18px}
    .hc-adm .stats{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:18px}
    .hc-adm .stat{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:14px}
    .hc-adm .stat small{color:#8892a0;text-transform:uppercase;font-size:11px;font-weight:600}
    .hc-adm .stat div{font-size:22px;font-weight:800;color:#c2410c}
    .hc-adm table{width:100%;border-collapse:collapse;font-size:13px}
    .hc-adm th{padding:8px 12px;font-size:11px;text-transform:uppercase;color:#8892a0;text-align:left;border-bottom:1px solid #e6e9ef}
    .hc-adm td{padding:9px 12px;border-bottom:1px solid #f2f4f7;vertical-align:middle}
    .hc-adm .grid2{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    .hc-adm .form-row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .hc-adm .form-row input,.hc-adm .form-row select{padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px}
    .hc-adm .pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;background:#f3f4f6}
    .hc-adm .pill.provisional{background:#fef3c7;color:#92400e}.hc-adm .pill.approved{background:#dbeafe;color:#1e40af}
    .hc-adm .pill.paid{background:#dcfce7;color:#166534}.hc-adm .pill.disqualified{background:#fee2e2;color:#991b1b}
    .hc-adm .pill.risk-high{background:#fee2e2;color:#991b1b}.hc-adm .pill.risk-medium{background:#fef3c7;color:#92400e}
    .hc-adm .pill.risk-low{background:#f3f4f6;color:#4b5563}
    .hc-adm .pill[title]{cursor:help;margin:1px 2px 1px 0}
    @media (max-width:1000px){.hc-adm .grid2{grid-template-columns:1fr}.hc-adm .stats{grid-template-columns:repeat(2,1fr)}}
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 hc-adm">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <div class="box">
                <div class="hd">
                    <h3>{{ __('Season') }} {{ $season }} · <span class="pill">{{ $seasonRow->status ?? 'open' }}</span></h3>
                    <form method="get" class="form-row">
                        <select name="season" onchange="this.form.submit()">
                            @foreach($seasons->push($season)->unique()->sortDesc() as $s)
                                <option value="{{ $s }}" @selected($s === $season)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.champions.analytics', ['season' => $season]) }}" class="btn btn-sm btn-outline-primary">{{ __('Analytics') }}</a>
                        @if(Route::has('champions.board'))
                            <a href="{{ route('champions.board') }}" target="_blank" class="btn btn-sm btn-outline-secondary">{{ __('Public board') }}</a>
                        @else
                            <span class="badge badge-secondary">{{ __('Public pages hidden') }}</span>
                        @endif
                    </form>
                </div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.run') }}" class="form-row">
                        @csrf
                        <select name="action">
                            <option value="confirm">{{ __('Confirm matured pending HP') }}</option>
                            <option value="sync">{{ __('Sync recent activity') }}</option>
                            <option value="bonuses">{{ __('Month-end quality/loyalty bonuses') }}</option>
                            <option value="finalize">{{ __('Finalize season (provisional Top Five)') }}</option>
                        </select>
                        <input name="season_key" placeholder="YYYY-MM ({{ __('default last month') }})" value="">
                        <button class="btn btn-sm btn-primary">{{ __('Run') }}</button>
                        <small class="text-muted">{{ __('These also run automatically via the scheduler.') }}</small>
                    </form>
                </div>
            </div>

            <div class="stats">
                <div class="stat"><small>{{ __('Players') }}</small><div>{{ number_format($stats['players']) }}</div></div>
                <div class="stat"><small>{{ __('Confirmed HP') }}</small><div>{{ number_format($stats['confirmed']) }}</div></div>
                <div class="stat"><small>{{ __('Pending HP') }}</small><div>{{ number_format($stats['pending']) }}</div></div>
                <div class="stat"><small>{{ __('Reversed entries') }}</small><div>{{ number_format($stats['reversed']) }}</div></div>
                <div class="stat"><small>{{ __('Disqualified') }}</small><div>{{ number_format($stats['dq']) }}</div></div>
            </div>

            {{-- Winners --}}
            <div class="box">
                <div class="hd">
                    <h3>{{ __('Top Five — review & approve') }}</h3>
                    @if($winners->whereIn('status', ['approved', 'paid'])->count())
                        <form method="post" action="{{ route('admin.champions.announce', $season) }}" onsubmit="return confirm('{{ __('Notify approved winners and mark season announced?') }}')">
                            @csrf <button class="btn btn-sm btn-success">{{ __('Announce winners') }}</button>
                        </form>
                    @endif
                </div>
                <div class="bd" style="padding:0;overflow-x:auto">
                    <table>
                        <thead><tr><th>{{ __('League') }}</th><th>#</th><th>{{ __('User') }}</th><th>HP</th><th>{{ __('Reward') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                        @forelse($winners as $w)
                            <tr>
                                <td>{{ ucfirst($w->league) }}</td>
                                <td><strong>{{ $w->rank }}</strong></td>
                                <td><a href="{{ route('admin.champions.ledger', ['userId' => $w->user_id, 'season' => $season]) }}">{{ $w->name }}</a><br><small>{{ $w->email }}</small></td>
                                <td>{{ number_format($w->final_hp) }}</td>
                                <td>TZS {{ number_format($w->reward_amount) }} {{ $w->reward_type }}</td>
                                <td><span class="pill {{ $w->status }}">{{ $w->status }}</span></td>
                                <td>
                                    <form method="post" action="{{ route('admin.champions.winner', $w->id) }}" class="form-row">
                                        @csrf
                                        <select name="status">
                                            <option value="approved">{{ __('Approve') }}</option>
                                            <option value="paid">{{ __('Mark paid') }}</option>
                                            <option value="disqualified">{{ __('Disqualify') }}</option>
                                        </select>
                                        <input name="audit_notes" placeholder="{{ __('Audit note') }}" value="{{ $w->audit_notes }}">
                                        <button class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted" style="padding:20px">{{ __('No winners yet — finalize runs on day 4 of the next month.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top 20 per league --}}
            <div class="grid2">
                @foreach(['provider' => $provider, 'client' => $client] as $lg => $rows)
                    <div class="box">
                        <div class="hd"><h3>{{ $lg === 'provider' ? __('Pro League — Top 20') : __('Client League — Top 20') }}</h3></div>
                        <div class="bd" style="padding:0;overflow-x:auto">
                            <table>
                                <thead><tr><th>#</th><th>{{ __('User') }}</th><th>HP</th><th>{{ __('Done') }}</th><th>{{ __('Cancel') }}</th><th>{{ __('Risk') }}</th></tr></thead>
                                <tbody>
                                @forelse($rows as $r)
                                    @php $flags = $risk[$lg][$r->user_id] ?? []; @endphp
                                    <tr>
                                        <td>{{ $r->rank }}</td>
                                        <td><a href="{{ route('admin.champions.ledger', ['userId' => $r->user_id, 'season' => $season]) }}">{{ $r->name }}</a> <small class="text-muted">#{{ $r->user_id }}</small></td>
                                        <td><strong>{{ number_format($r->hp) }}</strong></td>
                                        <td>{{ $r->completed }}</td>
                                        <td>{{ $r->cancelled }}</td>
                                        <td>
                                            @forelse($flags as $f)
                                                <span class="pill risk-{{ $f['severity'] }}" title="{{ $f['message'] }}">{{ str_replace('_', ' ', $f['type']) }}</span>
                                            @empty
                                                <span class="text-muted" style="font-size:11px">{{ __('clean') }}</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted" style="padding:20px">{{ __('No confirmed HP yet.') }}</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($rows->hasPages())<div style="padding:10px 18px">{{ $rows->links() }}</div>@endif
                    </div>
                @endforeach
            </div>

            {{-- Month-end bonus preview (PDF §7 and §19) --}}
            @php
                $qLabels = [
                    'p_q_response_90'     => ['90%+ response rate', 100],
                    'p_q_completion_95'   => ['95%+ completion rate', 150],
                    'p_q_rating'          => ['Excellent rating', 200],
                    'p_q_zero_cancel'     => ['Zero cancellations (5+ jobs)', 150],
                    'p_q_zero_complaints' => ['Zero upheld complaints', 100],
                    'p_q_repeat_5'        => ['5+ repeat clients', 250],
                ];
                $lLabels = [
                    'c_l_two_categories'   => ['Booked 2 categories', 50],
                    'c_l_three_categories' => ['Booked 3 categories', 100],
                    'c_l_three_no_cancel'  => ['3 bookings, no cancels', 100],
                    'c_l_five_bookings'    => ['5 completed bookings', 250],
                    'c_l_same_provider'    => ['Same provider twice', 100],
                ];
                $yes = fn ($ok) => $ok
                    ? '<span class="pill" style="background:#dcfce7;color:#166534">✓</span>'
                    : '<span class="pill" style="background:#f3f4f6;color:#9ca3af">—</span>';
            @endphp

            <div class="box">
                <div class="hd">
                    <h3>{{ __('Month-end bonus preview') }} · {{ $season }}</h3>
                    <small class="text-muted">{{ __('Who qualifies right now. Paid when the month-end job runs.') }}</small>
                </div>
                <div class="bd" style="padding:0;overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('Provider') }}</th>
                                <th>{{ __('Done') }}</th>
                                <th>{{ __('Cancel') }}</th>
                                <th>{{ __('Completion') }}</th>
                                <th>{{ __('Rating') }}</th>
                                <th>{{ __('Replies') }}</th>
                                <th>{{ __('Repeat') }}</th>
                                @foreach($qLabels as [$label, $hp])<th title="{{ $label }}">+{{ $hp }}</th>@endforeach
                                <th>{{ __('Would earn') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($quality as $q)
                            @php $sum = collect($q->bonuses)->filter()->keys()->sum(fn ($k) => $qLabels[$k][1]); @endphp
                            <tr>
                                <td><a href="{{ route('admin.champions.ledger', ['userId' => $q->user_id, 'season' => $season]) }}">{{ $q->name }}</a></td>
                                <td>{{ $q->done }}</td>
                                <td>{{ $q->cancelled }}</td>
                                <td>{{ is_null($q->completion_rate) ? '—' : $q->completion_rate . '%' }}</td>
                                <td>{{ $q->rating ?: '—' }}</td>
                                <td>{{ is_null($q->response_rate) ? '—' : round($q->response_rate * 100) . '%' }}</td>
                                <td>{{ $q->repeat_clients }}</td>
                                @foreach($qLabels as $key => $x)<td>{!! $yes($q->bonuses[$key]) !!}</td>@endforeach
                                <td><strong>{{ $sum ? '+' . number_format($sum) : '—' }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="14" class="text-center text-muted" style="padding:18px">{{ __('No provider activity this season yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    @if($quality->hasPages())<div style="padding:8px 18px">{{ $quality->links() }}</div>@endif

                    <table style="margin-top:6px">
                        <thead>
                            <tr>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Bookings') }}</th>
                                <th>{{ __('Cancel') }}</th>
                                <th>{{ __('Categories') }}</th>
                                <th>{{ __('Same provider') }}</th>
                                @foreach($lLabels as [$label, $hp])<th title="{{ $label }}">+{{ $hp }}</th>@endforeach
                                <th>{{ __('Would earn') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($loyalty as $l)
                            @php $sum = collect($l->bonuses)->filter()->keys()->sum(fn ($k) => $lLabels[$k][1]); @endphp
                            <tr>
                                <td><a href="{{ route('admin.champions.ledger', ['userId' => $l->user_id, 'season' => $season]) }}">{{ $l->name }}</a></td>
                                <td>{{ $l->done }}</td>
                                <td>{{ $l->cancelled }}</td>
                                <td>{{ $l->categories }}</td>
                                <td>{{ $l->same_provider }}</td>
                                @foreach($lLabels as $key => $x)<td>{!! $yes($l->bonuses[$key]) !!}</td>@endforeach
                                <td><strong>{{ $sum ? '+' . number_format($sum) : '—' }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-muted" style="padding:18px">{{ __('No client activity this season yet.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    @if($loyalty->hasPages())<div style="padding:8px 18px">{{ $loyalty->links() }}</div>@endif
                    <div style="padding:10px 18px">
                        <small class="text-muted">{{ __('Hover a +HP column for the rule. A dash means the threshold is not met yet — for example the response-rate bonus needs at least 3 client conversations, and the zero-cancellation bonus needs 5 completed jobs.') }}</small>
                    </div>
                </div>
            </div>

            {{-- Prize table + monthly reward budget --}}
            <div class="box">
                <div class="hd"><h3>{{ __('Prize table & monthly reward budget') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.rewards.save') }}">
                        @csrf
                        <input type="hidden" name="season" value="{{ $season }}">
                        <div class="grid2">
                            @foreach(['provider' => __('Huduma Pro League (providers)'), 'client' => __('Huduma Client League (clients)')] as $lg => $lgLabel)
                                <div>
                                    <h5 style="font-size:13px;margin:0 0 8px;color:#f0f0f0 !important">{{ $lgLabel }}</h5>
                                    <table class="table" style="margin:0">
                                        <thead><tr>
                                            <th style="width:60px">{{ __('Place') }}</th>
                                            <th style="width:110px">{{ __('Type') }}</th>
                                            <th style="width:130px">{{ __('Amount (TZS)') }}</th>
                                            <th>{{ __('Other benefits') }}</th>
                                        </tr></thead>
                                        <tbody>
                                        @foreach($rewards[$lg] as $rank => $r)
                                            <tr>
                                                <td><strong>#{{ $rank }}</strong></td>
                                                <td>
                                                    <select name="rewards[{{ $lg }}][{{ $rank }}][type]" style="width:100%">
                                                        <option value="cash" @selected($r[0] === 'cash')>{{ __('Cash') }}</option>
                                                        <option value="credit" @selected($r[0] === 'credit')>{{ __('Service credit') }}</option>
                                                    </select>
                                                </td>
                                                <td><input name="rewards[{{ $lg }}][{{ $rank }}][amount]" type="number" min="0" step="1000" value="{{ (int) $r[1] }}" style="width:100%"></td>
                                                <td><input name="rewards[{{ $lg }}][{{ $rank }}][benefits]" value="{{ $r[2] }}" style="width:100%" title="{{ __('Shown on the rewards page and saved with the winner') }}"></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-row" style="margin-top:12px;align-items:center">
                            <button class="btn btn-sm btn-primary">{{ __('Save prize table') }}</button>
                            <span class="text-muted" style="font-size:12px">{{ __('Cash prizes are paid by hand and marked paid below. Service credits go on the winner\'s account.') }}</span>
                        </div>
                    </form>
                    <form method="post" action="{{ route('admin.champions.rewards.reset') }}" onsubmit="return confirm('{{ __('Put the programme default amounts back?') }}')" style="margin-top:6px">
                        @csrf
                        <button class="btn btn-sm btn-secondary">{{ __('Reset to programme defaults') }}</button>
                    </form>

                    <table class="table" style="margin:14px 0 0;max-width:520px">
                        <tbody>
                            <tr><td>{{ __('Provider prizes') }}</td><td class="text-right"><strong>TZS {{ number_format($budget['provider']) }}</strong></td></tr>
                            <tr><td>{{ __('Client prizes') }}</td><td class="text-right"><strong>TZS {{ number_format($budget['client']) }}</strong></td></tr>
                            <tr><td>{{ __('Of which cash (paid out)') }}</td><td class="text-right">TZS {{ number_format($budget['cash']) }}</td></tr>
                            <tr><td>{{ __('Of which service credit') }}</td><td class="text-right">TZS {{ number_format($budget['credit']) }}</td></tr>
                            <tr><td><strong>{{ __('Monthly reward budget') }}</strong></td><td class="text-right"><strong>TZS {{ number_format($budget['total']) }}</strong></td></tr>
                            <tr><td>{{ __('Annualised (× 12)') }}</td><td class="text-right">TZS {{ number_format($budget['total'] * 12) }}</td></tr>
                        </tbody>
                    </table>
                    <small class="text-muted d-block" style="margin-top:8px">{{ __('This is the prize money only. Visibility rewards — featured profile, membership months, boosts — are fulfilled by hand and are not counted here.') }}</small>
                </div>
            </div>

            {{-- Program settings --}}
            <div class="box">
                <div class="hd"><h3>{{ __('Program settings') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.settings') }}" class="form-row">
                        @csrf
                        <label style="font-size:12px">{{ __('Minimum order (TZS)') }}
                            <input name="champions_min_order_tzs" type="number" min="0" step="1" value="{{ $settings['champions_min_order_tzs'] }}" style="width:110px" title="{{ __('Completed orders below this total earn no points. 0 = off.') }}">
                        </label>
                        <label style="font-size:12px">{{ __('Full-points orders per pair / month') }}
                            <input name="champions_pair_txn_cap" type="number" min="1" max="50" value="{{ $settings['champions_pair_txn_cap'] }}" style="width:70px">
                        </label>
                        <label style="font-size:12px">{{ __('Pending hold (days)') }}
                            <input name="champions_pending_hold_days" type="number" min="0" max="60" value="{{ $settings['champions_pending_hold_days'] }}" style="width:70px">
                        </label>
                        <label style="font-size:12px">{{ __('Last #1 cannot win #1 again') }}
                            <select name="champions_block_repeat_winner">
                                <option value="1" @selected((string) $settings['champions_block_repeat_winner'] === '1')>{{ __('Yes') }}</option>
                                <option value="0" @selected((string) $settings['champions_block_repeat_winner'] === '0')>{{ __('No') }}</option>
                            </select>
                        </label>
                        <button class="btn btn-sm btn-primary">{{ __('Save settings') }}</button>
                    </form>
                    <small class="text-muted d-block" style="margin-top:8px">{{ __('Risk flags in the Top 20 tables are signals for review only — shared homes, offices and networks are normal. Hover a flag to see details; open the user to see all flags.') }}</small>
                </div>
            </div>

            {{-- Adjust + disqualify --}}
            <div class="grid2">
                <div class="box">
                    <div class="hd"><h3>{{ __('Manual HP adjustment') }}</h3></div>
                    <div class="bd">
                        <form method="post" action="{{ route('admin.champions.adjust') }}" class="form-row">
                            @csrf
                            <input name="user_id" type="number" placeholder="{{ __('User ID') }}" required style="width:100px">
                            <select name="league"><option value="provider">{{ __('Provider') }}</option><option value="client">{{ __('Client') }}</option></select>
                            <input name="points" type="number" placeholder="{{ __('HP (+/-)') }}" required style="width:100px">
                            <input name="reason" placeholder="{{ __('Reason (e.g. verified problem report)') }}" required style="flex:1;min-width:180px">
                            <button class="btn btn-sm btn-primary">{{ __('Apply') }}</button>
                        </form>
                    </div>
                </div>
                <div class="box">
                    <div class="hd"><h3>{{ __('Apply a penalty') }}</h3></div>
                    <div class="bd">
                        <form method="post" action="{{ route('admin.champions.penalty') }}" class="form-row"
                              onsubmit="return confirm('{{ __('Apply this penalty?') }}')">
                            @csrf
                            <input name="user_id" type="number" placeholder="{{ __('User ID') }}" required style="width:100px">
                            <select name="rule" required>
                                @foreach(\App\Http\Controllers\ChampionsAdminController::PENALTIES as $key => $label)
                                    <option value="{{ $key }}">{{ __($label) }}</option>
                                @endforeach
                            </select>
                            <input name="reason" placeholder="{{ __('What happened (kept in the record)') }}" required style="flex:1;min-width:180px">
                            <button class="btn btn-sm btn-danger">{{ __('Apply penalty') }}</button>
                        </form>
                        <small class="text-muted d-block" style="margin-top:8px">
                            {{ __('Set amounts from the rules: cancellation -100, slow responses -50, fake listing -500, fake review -500, policy violation -500, fake request -100, repeated client cancellation -75. For a confirmed fake booking, reverse that order\'s rows on the user page instead.') }}
                        </small>
                    </div>
                </div>

                <div class="box">
                    <div class="hd"><h3>{{ __('Disqualify from season') }}</h3></div>
                    <div class="bd">
                        <form method="post" action="{{ route('admin.champions.disqualify') }}" class="form-row" onsubmit="return confirm('{{ __('Disqualify this user?') }}')">
                            @csrf
                            <input name="user_id" type="number" placeholder="{{ __('User ID') }}" required style="width:100px">
                            <select name="league"><option value="provider">{{ __('Provider') }}</option><option value="client">{{ __('Client') }}</option></select>
                            <input name="season_key" value="{{ $season }}" required style="width:90px">
                            <input name="reason" placeholder="{{ __('Reason') }}" required style="flex:1;min-width:160px">
                            <button class="btn btn-sm btn-danger">{{ __('Disqualify') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Missions + demand bonuses --}}
            <div class="box">
                <div class="hd"><h3>{{ __('Missions & demand bonuses') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.mission.store') }}" class="form-row" style="margin-bottom:14px">
                        @csrf
                        <select name="league"><option value="provider">{{ __('Provider') }}</option><option value="client">{{ __('Client') }}</option></select>
                        <select name="type"><option value="mission">{{ __('Mission') }}</option><option value="demand_bonus">{{ __('Demand bonus') }}</option></select>
                        <select name="mission_key">
                            @foreach(['completed_services','completed_bookings','repeat_bookings','reviews','portfolio_items','fast_responses','proposals_sent','new_categories','requests_created','demand'] as $k)
                                <option value="{{ $k }}">{{ $k }}</option>
                            @endforeach
                        </select>
                        <input name="title" placeholder="{{ __('Title') }}" required>
                        <input name="description" placeholder="{{ __('Description') }}">
                        <input name="target" type="number" placeholder="{{ __('Target') }}" style="width:80px">
                        <input name="reward_hp" type="number" placeholder="{{ __('Reward HP') }}" style="width:100px">
                        <input name="bonus_percent" type="number" placeholder="{{ __('Bonus %') }}" style="width:90px">
                        <input name="city_id" type="number" placeholder="{{ __('City ID') }}" style="width:80px">
                        <input name="category_id" type="number" placeholder="{{ __('Category ID') }}" style="width:100px">
                        <input name="season_key" placeholder="YYYY-MM ({{ __('blank = every month') }})" style="width:170px">
                        <button class="btn btn-sm btn-primary">{{ __('Create') }}</button>
                    </form>
                    <div style="overflow-x:auto">
                        <table>
                            <thead><tr><th>{{ __('League') }}</th><th>{{ __('Type') }}</th><th>{{ __('Title') }}</th><th>{{ __('Counter') }}</th><th>{{ __('Target / Reward') }}</th><th>{{ __('Season') }}</th><th>{{ __('Active') }}</th></tr></thead>
                            <tbody>
                            @forelse($missions as $m)
                                <tr>
                                    <td>{{ $m->league }}</td>
                                    <td>{{ $m->type }}</td>
                                    <td colspan="4">
                                        <form method="post" action="{{ route('admin.champions.mission.update', $m->id) }}" class="form-row" style="gap:6px">
                                            @csrf
                                            <input name="title" value="{{ $m->title }}" required style="min-width:150px" title="{{ __('Title') }}">
                                            <input name="description" value="{{ $m->description }}" placeholder="{{ __('Description') }}" style="min-width:150px">
                                            <select name="mission_key" title="{{ __('Counter') }}">
                                                @foreach(['completed_services','completed_bookings','repeat_bookings','reviews','portfolio_items','fast_responses','proposals_sent','new_categories','requests_created','demand'] as $k)
                                                    <option value="{{ $k }}" @selected($m->mission_key === $k)>{{ $k }}</option>
                                                @endforeach
                                            </select>
                                            <input name="target" type="number" min="1" value="{{ $m->target }}" style="width:70px" title="{{ __('Target') }}">
                                            <input name="reward_hp" type="number" min="0" value="{{ $m->reward_hp }}" style="width:85px" title="{{ __('Reward HP') }}">
                                            <input name="bonus_percent" type="number" min="1" value="{{ $m->bonus_percent }}" placeholder="%" style="width:65px" title="{{ __('Bonus %') }}">
                                            <input name="city_id" type="number" value="{{ $m->city_id }}" placeholder="{{ __('City') }}" style="width:70px">
                                            <input name="category_id" type="number" value="{{ $m->category_id }}" placeholder="{{ __('Cat') }}" style="width:70px">
                                            <input name="season_key" value="{{ $m->season_key }}" placeholder="{{ __('every') }}" style="width:90px" title="{{ __('Season, blank = every month') }}">
                                            <button class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                        </form>
                                    </td>
                                    <td style="white-space:nowrap">
                                        <form method="post" action="{{ route('admin.champions.mission.toggle', $m->id) }}" style="display:inline">@csrf
                                            <button class="btn btn-sm {{ $m->is_active ? 'btn-success' : 'btn-outline-secondary' }}">{{ $m->is_active ? __('On') : __('Off') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.champions.mission.delete', $m->id) }}" style="display:inline"
                                              onsubmit="return confirm('{{ __('Delete this mission? Points already earned from it are kept.') }}')">@csrf
                                            <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted" style="padding:16px">{{ __('No missions yet.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        @if($missions->hasPages())<div style="padding:8px 0">{{ $missions->links() }}</div>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
