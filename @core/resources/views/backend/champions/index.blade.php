@extends('backend.admin-master')
@section('site-title'){{ __('Huduma Champions') }}@endsection

@section('style')
<style>
    /* The admin dark theme forces headings/labels to white — keep text dark inside our white boxes */
    .hc-adm .box,.hc-adm .stat{color:#1f2733}
    .hc-adm .box h3,.hc-adm .box h4,.hc-adm .box h5,.hc-adm .box label,.hc-adm .box td,.hc-adm .box strong{color:#1f2733}
    .hc-adm .box th,.hc-adm .box small,.hc-adm .box .text-muted{color:#6b7280 !important}
    .hc-adm .box a{color:#2563eb}
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
                    </div>
                @endforeach
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
                                    <td>{{ $m->league }}</td><td>{{ $m->type }}</td><td>{{ $m->title }}</td><td><code>{{ $m->mission_key }}</code></td>
                                    <td>{{ $m->type === 'demand_bonus' ? '+'.$m->bonus_percent.'%' : $m->target.' → +'.$m->reward_hp.' HP' }}</td>
                                    <td>{{ $m->season_key ?? __('every') }}</td>
                                    <td>
                                        <form method="post" action="{{ route('admin.champions.mission.toggle', $m->id) }}">@csrf
                                            <button class="btn btn-sm {{ $m->is_active ? 'btn-success' : 'btn-outline-secondary' }}">{{ $m->is_active ? __('On') : __('Off') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted" style="padding:16px">{{ __('No missions yet.') }}</td></tr>
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
