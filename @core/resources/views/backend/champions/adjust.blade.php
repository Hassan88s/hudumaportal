@extends('backend.admin-master')
@section('site-title'){{ __('Champions Manual HP') }}@endsection

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
                <div class="hd"><h3>{{ __('Manual HP adjustment') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.adjust') }}" class="form-row">
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
                        <label style="font-size:12px">{{ __('HP (+ or −)') }}
                            <input name="points" type="number" required style="width:110px">
                        </label>
                        <label style="font-size:12px;flex:1;min-width:240px">{{ __('Reason — kept in the record') }}
                            <input name="reason" placeholder="{{ __('e.g. verified problem report') }}" required>
                        </label>
                        <button class="btn btn-sm btn-primary" style="align-self:flex-end">{{ __('Apply') }}</button>
                    </form>
                    <small class="text-muted d-block" style="margin-top:10px">{{ __('Use this for anything the rules do not cover. For a set penalty from the rules use the Penalties page instead, and to undo one wrong entry open the user and reverse that row.') }}</small>
                </div>
            </div>

            <div class="box">
                <div class="hd">
                    <h3>{{ __('Recent manual adjustments') }}</h3>
                    <form method="get" class="form-row">
                        <select name="season" onchange="this.form.submit()">
                            @foreach($seasons as $s)<option value="{{ $s }}" @selected($s === $season)>{{ $s }}</option>@endforeach
                        </select>
                    </form>
                </div>
                <div class="bd" style="padding:0;overflow-x:auto">
                    <table>
                        <thead><tr><th>{{ __('When') }}</th><th>{{ __('User') }}</th><th>{{ __('League') }}</th><th>HP</th><th>{{ __('Reason') }}</th><th>{{ __('By') }}</th></tr></thead>
                        <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($r->created_at)->format('d M, H:i') }}</td>
                                <td><a href="{{ route('admin.champions.ledger', ['userId' => $r->user_id, 'season' => $season]) }}">{{ $r->name ?: '#' . $r->user_id }}</a></td>
                                <td>{{ ucfirst($r->league) }}</td>
                                <td><strong style="color:{{ $r->points < 0 ? '#b91c1c' : '#166534' }} !important">{{ $r->points > 0 ? '+' : '' }}{{ number_format($r->points) }}</strong></td>
                                <td>{{ $r->reason }}</td>
                                <td>{{ $r->admin_name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted" style="padding:18px">{{ __('No manual adjustments this season.') }}</td></tr>
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
