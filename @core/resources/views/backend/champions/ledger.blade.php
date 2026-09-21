@extends('backend.admin-master')
@section('site-title'){{ __('Huduma Champions — User Ledger') }}@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

            <div class="card">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px">
                        <div>
                            <h4 style="margin:0">{{ $user->name ?? __('Unknown user') }} <small class="text-muted">#{{ $user->id ?? '' }} · {{ $user->email ?? '' }}</small></h4>
                            <small class="text-muted">{{ __('Season') }} {{ $season }} · {{ (int) ($user->user_type ?? 1) === 0 ? __('Pro League') : __('Client League') }}</small>
                        </div>
                        <a href="{{ route('admin.champions.index', ['season' => $season]) }}" class="btn btn-sm btn-outline-secondary">← {{ __('Back') }}</a>
                    </div>

                    {{-- PDF §29 risk signals — for review only, never automatic --}}
                    <div style="margin-bottom:14px">
                        <strong style="font-size:13px">{{ __('Risk signals') }}</strong>
                        @forelse($flags as $f)
                            @php $cls = ['high' => 'danger', 'medium' => 'warning', 'low' => 'secondary'][$f['severity']] ?? 'secondary'; @endphp
                            <div class="alert alert-{{ $cls }}" style="padding:6px 10px;margin:6px 0 0;font-size:13px">
                                <strong>{{ ucfirst($f['severity']) }}</strong> · {{ str_replace('_', ' ', $f['type']) }} — {{ $f['message'] }}
                            </div>
                        @empty
                            <div class="text-muted" style="font-size:13px">{{ __('No risk signals for this season.') }}</div>
                        @endforelse
                    </div>

                    <div style="overflow-x:auto">
                        <table class="table table-sm">
                            <thead><tr><th>ID</th><th>{{ __('Date') }}</th><th>{{ __('Rule') }}</th><th>{{ __('Reason') }}</th><th>{{ __('Source') }}</th><th>HP</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                            <tbody>
                            @forelse($rows as $r)
                                <tr>
                                    <td>{{ $r->id }}</td>
                                    <td style="white-space:nowrap">{{ $r->created_at }}</td>
                                    <td><code>{{ $r->rule_key }}</code></td>
                                    <td>{{ $r->reason }}@if($r->reversal_reason)<br><small class="text-danger">{{ $r->reversal_reason }}</small>@endif</td>
                                    <td>{{ $r->source_type }} {{ $r->source_id ? '#'.$r->source_id : '' }}</td>
                                    <td><strong>{{ $r->points }}</strong></td>
                                    <td>{{ $r->status }}@if($r->status === 'pending')<br><small class="text-muted">{{ __('after') }} {{ $r->confirm_after }}</small>@endif</td>
                                    <td>
                                        @if($r->status !== 'reversed')
                                            <form method="post" action="{{ route('admin.champions.reverse', $r->id) }}" onsubmit="return confirm('{{ __('Reverse this entry?') }}')" style="display:flex;gap:4px">
                                                @csrf
                                                <input name="reason" placeholder="{{ __('Reason') }}" class="form-control form-control-sm" style="width:130px">
                                                <button class="btn btn-sm btn-outline-danger">{{ __('Reverse') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">{{ __('No entries this season.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $rows->appends(['season' => $season])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
