@extends('backend.admin-master')
@section('site-title'){{ __('Champions Disqualifications') }}@endsection

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
                <div class="hd"><h3>{{ __('Disqualify from a season') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.disqualify') }}" class="form-row" onsubmit="return confirm('{{ __('Disqualify this user?') }}')">
                        @csrf
                        <label style="font-size:12px">{{ __('User ID') }}
                            <input name="user_id" type="number" required style="width:110px">
                        </label>
                        <label style="font-size:12px">{{ __('League') }}
                            <select name="league">
                                <option value="provider">{{ __('Provider') }}</option>
                                <option value="client">{{ __('Client') }}</option>
                            </select>
                        </label>
                        <label style="font-size:12px">{{ __('Season') }}
                            <input name="season_key" value="{{ $season }}" required style="width:100px">
                        </label>
                        <label style="font-size:12px;flex:1;min-width:240px">{{ __('Reason — kept in the record') }}
                            <input name="reason" required>
                        </label>
                        <button class="btn btn-sm btn-danger" style="align-self:flex-end">{{ __('Disqualify') }}</button>
                    </form>
                    <small class="text-muted d-block" style="margin-top:10px">{{ __('A disqualified user earns no further points in that league for that season and cannot win a prize. Points they already earned stay in the record.') }}</small>
                </div>
            </div>

            <div class="box">
                <div class="hd">
                    <h3>{{ __('Disqualified') }}</h3>
                    <form method="get" class="form-row">
                        <select name="season" onchange="this.form.submit()">
                            @foreach($seasons as $s)<option value="{{ $s }}" @selected($s === $season)>{{ $s }}</option>@endforeach
                        </select>
                    </form>
                </div>
                <div class="bd" style="padding:0;overflow-x:auto">
                    <table>
                        <thead><tr><th>{{ __('When') }}</th><th>{{ __('User') }}</th><th>{{ __('League') }}</th><th>{{ __('Season') }}</th><th>{{ __('Reason') }}</th></tr></thead>
                        <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y, H:i') }}</td>
                                <td><a href="{{ route('admin.champions.ledger', ['userId' => $r->user_id, 'season' => $r->season_key]) }}">{{ $r->name ?: '#' . $r->user_id }}</a><br><small class="text-muted">{{ $r->email }}</small></td>
                                <td><span class="pill">{{ ucfirst($r->league) }}</span></td>
                                <td>{{ $r->season_key }}</td>
                                <td>{{ $r->reason }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted" style="padding:18px">{{ __('Nobody is disqualified for this season.') }}</td></tr>
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
