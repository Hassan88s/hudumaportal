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
            @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif
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

            @if(!method_exists(auth()->guard('admin')->user(), 'hasRole') || auth()->guard('admin')->user()->hasRole('Super Admin'))
                <div class="box" style="border-color:#fca5a5">
                    <div class="hd" style="background:#fef2f2;border-bottom-color:#fca5a5">
                        <h3 style="color:#991b1b !important">{{ __('Start fresh — wipe testing data') }}</h3>
                    </div>
                    <div class="bd">
                        <p style="margin:0 0 12px;font-size:13px">{{ __('This deletes points, winners, badges, mission progress and disqualifications — permanently, with no undo. Use it while testing, before the programme goes live to real users. Missions you created are kept, and service credit already paid onto a wallet stays on that wallet.') }}</p>

                        <div class="grid2">
                            <div>
                                <h5 style="font-size:13px;margin:0 0 8px">{{ __('Wipe one season') }}</h5>
                                <form method="post" action="{{ route('admin.champions.wipe') }}"
                                      onsubmit="return confirm('{{ __('Permanently delete all Champions data for this season?') }}')">
                                    @csrf
                                    <input type="hidden" name="scope" value="season">
                                    <div class="form-row">
                                        <label style="font-size:12px">{{ __('Season') }}
                                            <input name="season_key" value="{{ $season }}" required style="width:110px">
                                        </label>
                                        <label style="font-size:12px">{{ __('Type the season to confirm') }}
                                            <input name="confirm" placeholder="{{ $season }}" required style="width:140px" autocomplete="off">
                                        </label>
                                        <button class="btn btn-sm btn-danger" style="align-self:flex-end">{{ __('Wipe season') }}</button>
                                    </div>
                                </form>
                            </div>

                            <div>
                                <h5 style="font-size:13px;margin:0 0 8px">{{ __('Wipe everything, every season') }}</h5>
                                <form method="post" action="{{ route('admin.champions.wipe') }}"
                                      onsubmit="return confirm('{{ __('Permanently delete ALL Champions data for every season? This cannot be undone.') }}')">
                                    @csrf
                                    <input type="hidden" name="scope" value="all">
                                    <div class="form-row">
                                        <label style="font-size:12px">{{ __('Type WIPE ALL to confirm') }}
                                            <input name="confirm" placeholder="WIPE ALL" required style="width:160px" autocomplete="off">
                                        </label>
                                        <button class="btn btn-sm btn-danger" style="align-self:flex-end">{{ __('Wipe everything') }}</button>
                                    </div>
                                </form>
                                <small class="text-muted d-block" style="margin-top:8px">{{ __('Everyone starts from zero HP with no badges and no history. Do this once when testing is finished and before real users start earning.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
