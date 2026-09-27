@extends('backend.admin-master')
@section('site-title'){{ __('Huduma Champions') }}@endsection

@section('style')
@include('backend.champions._style')
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 hc-adm">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @include('backend.champions._nav')

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
                        <a href="{{ route('admin.champions.rewards', ['season' => $season]) }}" class="btn btn-sm btn-outline-primary">{{ __('Prizes & budget') }}</a>
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

        </div>
    </div>
</div>
@endsection
