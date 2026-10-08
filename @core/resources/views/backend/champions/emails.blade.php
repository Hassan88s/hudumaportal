@extends('backend.admin-master')
@section('site-title'){{ __('Champions Email Alerts') }}@endsection

@section('style')
@include('backend.champions._style')
<style>
    .hc-adm .sw{position:relative;display:inline-block;width:42px;height:22px}
    .hc-adm .sw input{opacity:0;width:0;height:0}
    .hc-adm .sw span{position:absolute;inset:0;background:#cbd5e1;border-radius:999px;transition:.2s;cursor:pointer}
    .hc-adm .sw span:before{content:"";position:absolute;height:16px;width:16px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
    .hc-adm .sw input:checked + span{background:#16a34a}
    .hc-adm .sw input:checked + span:before{transform:translateX(20px)}
    .hc-adm .em-row td{padding:8px 12px}
    .hc-adm .grp{background:#f8f9fb;font-size:11px;text-transform:uppercase;letter-spacing:.4px;font-weight:700;color:#6b7280 !important}
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 hc-adm">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @include('backend.champions._nav')

            <form method="post" action="{{ route('admin.champions.emails.save') }}">
                @csrf
                <div class="box">
                    <div class="hd">
                        <h3>{{ __('Email alerts for earning points') }}</h3>
                        <button class="btn btn-sm btn-primary">{{ __('Save email alerts') }}</button>
                    </div>
                    <div class="bd">
                        <small class="text-muted d-block">{{ __('Every activity always sends an in-app notification telling the user how much HP they earned. Turn the switch on to ALSO email them for that activity. Keep the frequent small ones (chat replies, saves) off so the mail server is not flooded.') }}</small>
                    </div>
                </div>

                {{-- Special switches --}}
                <div class="box">
                    <div class="hd"><h3>{{ __('Key moments') }}</h3></div>
                    <div class="bd" style="padding:0">
                        <table>
                            <tbody>
                                <tr class="em-row">
                                    <td><strong>{{ __('Level up') }}</strong><div class="meta text-muted" style="font-size:12px">{{ __('When a user reaches a new level (Bronze, Silver, Gold…).') }}</div></td>
                                    <td style="width:60px;text-align:right"><label class="sw"><input type="checkbox" name="email[level_up]" @checked($email['level_up'] ?? true)><span></span></label></td>
                                </tr>
                                <tr class="em-row">
                                    <td><strong>{{ __('Winner announcement') }}</strong><div class="meta text-muted" style="font-size:12px">{{ __('Emails the Top Five when you announce a season.') }}</div></td>
                                    <td style="width:60px;text-align:right"><label class="sw"><input type="checkbox" name="email[winner_announcement]" @checked($email['winner_announcement'] ?? true)><span></span></label></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid2">
                    @foreach(['provider' => __('Huduma Pro League — providers'), 'client' => __('Huduma Client League — clients')] as $lg => $lgTitle)
                        <div class="box">
                            <div class="hd"><h3>{{ $lgTitle }}</h3></div>
                            <div class="bd" style="padding:0;overflow-x:auto">
                                <table>
                                    <thead><tr><th>{{ __('Activity') }}</th><th style="width:70px;text-align:right">{{ __('Email') }}</th></tr></thead>
                                    <tbody>
                                    @foreach(($groups[$lg] ?? []) as $groupTitle => $keys)
                                        <tr><td class="grp" colspan="2">{{ __($groupTitle) }}</td></tr>
                                        @foreach($keys as $key)
                                            @continue(!isset($rules[$key]))
                                            <tr class="em-row">
                                                <td>{{ __($rules[$key]['label']) }}
                                                    <span class="text-muted" style="font-size:11px">({{ $rules[$key]['hp'] > 0 ? '+' : '' }}{{ $rules[$key]['hp'] }} HP)</span>
                                                </td>
                                                <td style="text-align:right">
                                                    <label class="sw"><input type="checkbox" name="email[{{ $key }}]" @checked($email[$key] ?? false)><span></span></label>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="form-row"><button class="btn btn-sm btn-primary">{{ __('Save email alerts') }}</button></div>
            </form>

            <form method="post" action="{{ route('admin.champions.emails.reset') }}" onsubmit="return confirm('{{ __('Reset email alerts to the defaults?') }}')" style="margin-top:8px">
                @csrf
                <button class="btn btn-sm btn-secondary">{{ __('Reset to defaults') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
