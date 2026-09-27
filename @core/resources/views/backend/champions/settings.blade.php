@extends('backend.admin-master')
@section('site-title'){{ __('Champions Program Settings') }}@endsection

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
                <div class="hd"><h3>{{ __('Program settings') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.settings') }}">
                        @csrf
                        <table style="max-width:760px">
                            <tbody>
                                <tr>
                                    <td style="width:260px"><strong>{{ __('Programme is running') }}</strong><br>
                                        <small class="text-muted">{{ __('Turn off to stop all point scoring. Points already earned are kept.') }}</small></td>
                                    <td>
                                        <select name="champions_enabled">
                                            <option value="1" @selected((string) $settings['champions_enabled'] === '1')>{{ __('On') }}</option>
                                            <option value="0" @selected((string) $settings['champions_enabled'] === '0')>{{ __('Off') }}</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Minimum order (TZS)') }}</strong><br>
                                        <small class="text-muted">{{ __('Completed orders below this total earn no points, so small fake orders cannot farm HP. 0 turns the check off.') }}</small></td>
                                    <td><input name="champions_min_order_tzs" type="number" min="0" step="1000" value="{{ $settings['champions_min_order_tzs'] }}" style="width:140px"></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Full-points orders per pair each month') }}</strong><br>
                                        <small class="text-muted">{{ __('How many times the same buyer and seller can score full points together in one month.') }}</small></td>
                                    <td><input name="champions_pair_txn_cap" type="number" min="1" max="50" value="{{ $settings['champions_pair_txn_cap'] }}" style="width:90px"></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Pending hold (days)') }}</strong><br>
                                        <small class="text-muted">{{ __('Order points are held this long before they count, or until the 3rd of next month — whichever comes first — so they settle before the audit.') }}</small></td>
                                    <td><input name="champions_pending_hold_days" type="number" min="0" max="60" value="{{ $settings['champions_pending_hold_days'] }}" style="width:90px"></td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Last month\'s #1 cannot take #1 again') }}</strong><br>
                                        <small class="text-muted">{{ __('They drop to #2 so the same person does not win every month.') }}</small></td>
                                    <td>
                                        <select name="champions_block_repeat_winner">
                                            <option value="1" @selected((string) $settings['champions_block_repeat_winner'] === '1')>{{ __('Yes') }}</option>
                                            <option value="0" @selected((string) $settings['champions_block_repeat_winner'] === '0')>{{ __('No') }}</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="margin-top:14px"><button class="btn btn-sm btn-primary">{{ __('Save settings') }}</button></div>
                    </form>
                </div>
            </div>

            <div class="box">
                <div class="hd"><h3>{{ __('Run a job now') }}</h3></div>
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
                    </form>
                    <small class="text-muted d-block" style="margin-top:8px">{{ __('These normally run by themselves every few minutes. If you always have to press Run, the server cron for the scheduler is not set up.') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
