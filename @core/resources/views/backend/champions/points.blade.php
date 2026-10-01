@extends('backend.admin-master')
@section('site-title'){{ __('Champions Points') }}@endsection

@section('style')
@include('backend.champions._style')
<style>
    .hc-adm .rule-row td{padding:7px 12px}
    .hc-adm .rule-row input{width:110px;padding:5px 8px;border:1px solid #d1d5db;border-radius:6px;text-align:right}
    .hc-adm .rule-row.edited input{border-color:#c2410c;background:#fff7ed !important}
    .hc-adm .rule-row .meta{font-size:11px;color:#8892a0 !important}
    .hc-adm .grp{background:#f8f9fb;font-size:11px;text-transform:uppercase;letter-spacing:.4px;font-weight:700;color:#6b7280 !important}
    .hc-adm .minus input{border-color:#fca5a5}
</style>
@endsection

@section('content')
@php
    $limitText = function ($r) use ($caps) {
        $bits = [];
        if (($r['limit'] ?? null) === 'once')  $bits[] = __('once ever');
        elseif (($r['limit'] ?? null) === 'month') $bits[] = __('once a month');
        elseif (is_int($r['limit'] ?? null))   $bits[] = __(':n a month', ['n' => $r['limit']]);
        if (!empty($r['cap']))     $bits[] = __('shares the :g cap', ['g' => $r['cap']]);
        if (!empty($r['pending'])) $bits[] = __('held before it counts');
        if (!empty($r['pair']))    $bits[] = __('same pair limited');
        return implode(' · ', $bits) ?: __('no limit');
    };
@endphp
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 hc-adm">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @include('backend.champions._nav')

            <form method="post" action="{{ route('admin.champions.points.save') }}">
                @csrf
                <div class="box">
                    <div class="hd">
                        <h3>{{ __('Points for every activity') }}</h3>
                        <div class="form-row">
                            @if(Route::has('champions.rules'))
                                <a href="{{ route('champions.rules') }}" target="_blank" class="btn btn-sm btn-outline-secondary">{{ __('Public rules page') }}</a>
                            @endif
                            <button class="btn btn-sm btn-primary">{{ __('Save points') }}</button>
                        </div>
                    </div>
                    <div class="bd">
                        <small class="text-muted d-block">{{ __('Change what any activity is worth. Deductions are typed as a plain number and stay deductions. New points use the new value straight away; points already earned keep what they were worth at the time.') }}
                        @if(count($changed)) <strong>{{ trans_choice('{1}:count rule is different from the programme default.|[2,*]:count rules are different from the programme default.', count($changed), ['count' => count($changed)]) }}</strong> @endif
                        </small>
                    </div>
                </div>

                <div class="grid2">
                    @foreach(['provider' => __('Huduma Pro League — providers'), 'client' => __('Huduma Client League — clients')] as $lg => $lgTitle)
                        <div class="box">
                            <div class="hd"><h3>{{ $lgTitle }}</h3></div>
                            <div class="bd" style="padding:0;overflow-x:auto">
                                <table>
                                    <thead><tr><th>{{ __('Activity') }}</th><th style="width:120px;text-align:right">{{ __('HP') }}</th></tr></thead>
                                    <tbody>
                                    @foreach(($groups[$lg] ?? []) as $groupTitle => $keys)
                                        @if(count($keys))
                                            <tr><td class="grp" colspan="2">{{ __($groupTitle) }}</td></tr>
                                            @foreach($keys as $key)
                                                @php $r = $rules[$key]; $isMinus = $r['hp'] < 0; @endphp
                                                <tr class="rule-row {{ in_array($key, $changed, true) ? 'edited' : '' }} {{ $isMinus ? 'minus' : '' }}">
                                                    <td>
                                                        {{ __($r['label']) }}
                                                        <div class="meta">{{ $limitText($r) }}</div>
                                                    </td>
                                                    <td style="text-align:right;white-space:nowrap">
                                                        @if($isMinus)<span style="color:#b91c1c !important;font-weight:700">−</span>@endif
                                                        <input name="hp[{{ $key }}]" type="number" min="0" step="5"
                                                               value="{{ abs((int) $r['hp']) }}"
                                                               title="{{ __('Programme default') }}: {{ abs((int) \App\Services\ChampionsService::RULES[$key]['hp']) }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="box">
                    <div class="hd"><h3>{{ __('Monthly caps') }}</h3></div>
                    <div class="bd">
                        <small class="text-muted d-block" style="margin-bottom:12px">{{ __('The most HP anyone can earn in a month from each group of activities, so nobody can farm points by repeating one cheap action.') }}</small>
                        <div class="form-row">
                            @foreach([
                                'response' => __('Replying to clients'),
                                'referral' => __('Referrals'),
                                'save'     => __('Saving providers'),
                                'compare'  => __('Comparing providers'),
                            ] as $group => $label)
                                <label style="font-size:12px">{{ $label }}
                                    <input name="caps[{{ $group }}]" type="number" min="0" step="25" value="{{ (int) ($caps[$group] ?? 0) }}" style="width:110px"
                                           title="{{ __('Programme default') }}: {{ \App\Services\ChampionsService::CAPS[$group] }}">
                                </label>
                            @endforeach
                        </div>
                        <div style="margin-top:14px"><button class="btn btn-sm btn-primary">{{ __('Save points') }}</button></div>
                    </div>
                </div>
            </form>

            <form method="post" action="{{ route('admin.champions.points.reset') }}"
                  onsubmit="return confirm('{{ __('Put every point value and cap back to the programme defaults?') }}')">
                @csrf
                <button class="btn btn-sm btn-secondary">{{ __('Reset everything to the programme defaults') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
