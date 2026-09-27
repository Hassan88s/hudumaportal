@extends('backend.admin-master')
@section('site-title'){{ __('Champions Penalties') }}@endsection

@section('style')
@include('backend.champions._style')
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 hc-adm">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @include('backend.champions._nav')

            <div class="box">
                <div class="hd"><h3>{{ __('Apply a penalty') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.penalty') }}" class="form-row"
                          onsubmit="return confirm('{{ __('Apply this penalty?') }}')">
                        @csrf
                        <label style="font-size:12px">{{ __('User ID') }}
                            <input name="user_id" type="number" required style="width:110px">
                        </label>
                        <label style="font-size:12px">{{ __('Penalty') }}
                            <select name="rule" required>
                                @foreach($penalties as $key => $label)
                                    <option value="{{ $key }}">{{ __($label) }} ({{ $amounts[$key] }} HP)</option>
                                @endforeach
                            </select>
                        </label>
                        <label style="font-size:12px;flex:1;min-width:240px">{{ __('What happened — kept in the record') }}
                            <input name="reason" required>
                        </label>
                        <button class="btn btn-sm btn-danger" style="align-self:flex-end">{{ __('Apply penalty') }}</button>
                    </form>
                    <small class="text-muted d-block" style="margin-top:10px">{{ __('Amounts are set by the programme rules and cannot be typed in. For a confirmed fake booking, open the user and reverse that order\'s rows instead — that takes back exactly what the order gave.') }}</small>
                </div>
            </div>

            <div class="box">
                <div class="hd"><h3>{{ __('The set amounts') }}</h3></div>
                <div class="bd" style="padding:0">
                    <table>
                        <thead><tr><th>{{ __('Penalty') }}</th><th style="width:120px">HP</th></tr></thead>
                        <tbody>
                        @foreach($penalties as $key => $label)
                            <tr><td>{{ __($label) }}</td><td><strong style="color:#b91c1c !important">{{ $amounts[$key] }}</strong></td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box">
                <div class="hd">
                    <h3>{{ __('Penalties applied') }}</h3>
                    <form method="get" class="form-row">
                        <select name="season" onchange="this.form.submit()">
                            @foreach($seasons as $s)<option value="{{ $s }}" @selected($s === $season)>{{ $s }}</option>@endforeach
                        </select>
                    </form>
                </div>
                <div class="bd" style="padding:0;overflow-x:auto">
                    <table>
                        <thead><tr><th>{{ __('When') }}</th><th>{{ __('User') }}</th><th>{{ __('Penalty') }}</th><th>HP</th><th>{{ __('Reason') }}</th><th>{{ __('By') }}</th></tr></thead>
                        <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($r->created_at)->format('d M, H:i') }}</td>
                                <td><a href="{{ route('admin.champions.ledger', ['userId' => $r->user_id, 'season' => $season]) }}">{{ $r->name ?: '#' . $r->user_id }}</a></td>
                                <td>{{ __($penalties[$r->rule_key] ?? $r->rule_key) }}</td>
                                <td><strong style="color:#b91c1c !important">{{ number_format($r->points) }}</strong></td>
                                <td>{{ $r->reason }}</td>
                                <td>{{ $r->admin_name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted" style="padding:18px">{{ __('No penalties this season.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    @if($rows->hasPages())<div style="padding:10px 18px">{{ $rows->links() }}</div>@endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
