@extends('backend.admin-master')
@section('site-title'){{ __('Champions Prizes & Budget') }}@endsection

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

            <div class="box">
                <div class="hd">
                    <h3>{{ __('Prizes & monthly reward budget') }}</h3>
                    <div class="form-row">
                        <a href="{{ route('admin.champions.index', ['season' => $season]) }}" class="btn btn-sm btn-outline-primary">{{ __('Back to Champions') }}</a>
                        @if(Route::has('champions.rewards'))
                            <a href="{{ route('champions.rewards') }}" target="_blank" class="btn btn-sm btn-outline-secondary">{{ __('Public rewards page') }}</a>
                        @endif
                    </div>
                </div>
                <div class="bd">
                    <small class="text-muted d-block" style="margin-bottom:14px">{{ __('What each of the top five places wins in both leagues. Saving re-prices winners still awaiting approval this season; anyone already approved or paid keeps what they were promised.') }}</small>

                    <form method="post" action="{{ route('admin.champions.rewards.save') }}">
                        @csrf
                        <input type="hidden" name="season" value="{{ $season }}">
                        <div class="grid2">
                            @foreach(['provider' => __('Huduma Pro League — providers'), 'client' => __('Huduma Client League — clients')] as $lg => $lgLabel)
                                <div>
                                    <h5 style="font-size:13px;margin:0 0 8px">{{ $lgLabel }}</h5>
                                    <table style="margin:0">
                                        <thead><tr>
                                            <th style="width:56px">{{ __('Place') }}</th>
                                            <th style="width:120px">{{ __('Type') }}</th>
                                            <th style="width:120px">{{ __('Amount (TZS)') }}</th>
                                            <th>{{ __('Other benefits') }}</th>
                                        </tr></thead>
                                        <tbody>
                                        @foreach($rewards[$lg] as $rank => $r)
                                            <tr>
                                                <td><strong>#{{ $rank }}</strong></td>
                                                <td>
                                                    <select name="rewards[{{ $lg }}][{{ $rank }}][type]" style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px">
                                                        <option value="cash" @selected($r[0] === 'cash')>{{ __('Cash') }}</option>
                                                        <option value="credit" @selected($r[0] === 'credit')>{{ __('Service credit') }}</option>
                                                    </select>
                                                </td>
                                                <td><input name="rewards[{{ $lg }}][{{ $rank }}][amount]" type="number" min="0" step="1000" value="{{ (int) $r[1] }}" style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px"></td>
                                                <td><input name="rewards[{{ $lg }}][{{ $rank }}][benefits]" value="{{ $r[2] }}" style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px" title="{{ __('Shown on the public rewards page and stored with the winner') }}"></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-row" style="margin-top:14px">
                            <button class="btn btn-sm btn-primary">{{ __('Save prize table') }}</button>
                        </div>
                    </form>

                    <form method="post" action="{{ route('admin.champions.rewards.reset') }}" onsubmit="return confirm('{{ __('Put the programme default amounts back?') }}')" style="margin-top:8px">
                        @csrf
                        <button class="btn btn-sm btn-secondary">{{ __('Reset to programme defaults') }}</button>
                    </form>
                </div>
            </div>

            <div class="grid2">
                <div class="box">
                    <div class="hd"><h3>{{ __('Budget at these amounts') }}</h3></div>
                    <div class="bd">
                        <table style="margin:0">
                            <tbody>
                                <tr><td>{{ __('Provider prizes') }}</td><td class="text-right"><strong>TZS {{ number_format($budget['provider']) }}</strong></td></tr>
                                <tr><td>{{ __('Client prizes') }}</td><td class="text-right"><strong>TZS {{ number_format($budget['client']) }}</strong></td></tr>
                                <tr><td>{{ __('Of which cash — paid out by hand') }}</td><td class="text-right">TZS {{ number_format($budget['cash']) }}</td></tr>
                                <tr><td>{{ __('Of which service credit') }}</td><td class="text-right">TZS {{ number_format($budget['credit']) }}</td></tr>
                                <tr><td><strong>{{ __('Per month') }}</strong></td><td class="text-right"><strong style="font-size:15px;color:#c2410c !important">TZS {{ number_format($budget['total']) }}</strong></td></tr>
                                <tr><td>{{ __('Per year (× 12)') }}</td><td class="text-right">TZS {{ number_format($budget['total'] * 12) }}</td></tr>
                            </tbody>
                        </table>
                        <small class="text-muted d-block" style="margin-top:10px">{{ __('Prize money only. Visibility rewards — featured profile, membership months, service boosts — are fulfilled by hand and are not priced here.') }}</small>
                    </div>
                </div>

                <div class="box">
                    <div class="hd">
                        <h3>{{ __('Committed this season') }} · {{ $season }}</h3>
                        <form method="get" class="form-row">
                            <select name="season" onchange="this.form.submit()" style="padding:5px 8px;border:1px solid #d1d5db;border-radius:6px">
                                @foreach($seasons as $s)
                                    <option value="{{ $s }}" @selected($s === $season)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="bd">
                        <table style="margin:0">
                            <thead><tr><th>{{ __('Status') }}</th><th class="text-right">{{ __('Winners') }}</th><th class="text-right">{{ __('Cash') }}</th><th class="text-right">{{ __('Credit') }}</th></tr></thead>
                            <tbody>
                            @forelse($committed as $row)
                                <tr>
                                    <td><span class="pill {{ $row->status }}">{{ __(ucfirst($row->status)) }}</span></td>
                                    <td class="text-right">{{ $row->winners }}</td>
                                    <td class="text-right">{{ number_format($row->cash) }}</td>
                                    <td class="text-right">{{ number_format($row->credit) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted" style="padding:18px">{{ __('No winners written for this season yet — run the month-end job.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        @if($committed->isNotEmpty())
                            <small class="text-muted d-block" style="margin-top:10px">{{ __('Cash still to pay (approved but not marked paid):') }} <strong>TZS {{ number_format($unpaidCash) }}</strong></small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
