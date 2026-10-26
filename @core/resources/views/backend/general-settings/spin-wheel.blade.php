@extends('backend.admin-master')
@section('site-title'){{ __('Spin & Win') }}@endsection
@section('style')
    <style>
        .sw-seg-table{width:100%;}
        .sw-seg-table th{font-size:12px;text-transform:uppercase;opacity:.7;padding:6px 8px;}
        .sw-seg-table td{padding:6px 8px;vertical-align:middle;}
        .sw-seg-table input,.sw-seg-table select{min-width:60px;}
        .sw-color{width:46px;height:38px;padding:2px;}
        .sw-emoji{width:60px;text-align:center;}
        .sw-stat{border:1px solid rgba(135,138,160,.28);border-radius:10px;padding:14px 18px;text-align:center;}
        .sw-stat b{display:block;font-size:24px;}
        .sw-stat span{font-size:12px;opacity:.7;}
    </style>
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                @include('backend.partials.message')

                <form action="{{ route('admin.spin.wheel.update') }}" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">{{ __('Spin & Win — Game Settings') }}</h4>
                            @if($errors->any())
                                @foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach
                            @endif
                            <p class="text-muted">{{ __('A logged-in buyer or seller gets a limited number of spins. Wins are saved to their account; discount checkout support comes later.') }}</p>

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label><strong>{{ __('Game status') }}</strong></label>
                                    <select name="spin_wheel_enabled" class="form-control">
                                        <option value="0" @if(!$settings['enabled']) selected @endif>{{ __('OFF — hidden') }}</option>
                                        <option value="1" @if($settings['enabled']) selected @endif>{{ __('ON — players can spin') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label><strong>{{ __('Tries per user') }}</strong></label>
                                    <input type="number" name="spin_wheel_max_tries" class="form-control" min="1" max="20" value="{{ $settings['max_tries'] }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label><strong>{{ __('First try always "Try Again"') }}</strong></label>
                                    <select name="spin_wheel_force_first_lose" class="form-control">
                                        <option value="1" @if($settings['force_first_lose']) selected @endif>{{ __('Yes — first spin never wins') }}</option>
                                        <option value="0" @if(!$settings['force_first_lose']) selected @endif>{{ __('No — any spin can win') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ __('Headline') }}</label>
                                    <input type="text" name="spin_wheel_title" class="form-control" value="{{ $settings['title'] }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ __('Sub-headline') }}</label>
                                    <input type="text" name="spin_wheel_subtitle" class="form-control" value="{{ $settings['subtitle'] }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 class="header-title mb-0">{{ __('Wheel Segments') }}</h4>
                                <button type="button" class="btn btn-sm btn-info" id="addSeg">+ {{ __('Add segment') }}</button>
                            </div>
                            <p class="text-muted">{{ __('Type "Discount" with a % value is a win. Type "Try Again" is a loss. Weight = relative chance on the second spin (bigger = more likely).') }}</p>
                            <div class="table-responsive">
                                <table class="sw-seg-table" id="segTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Label') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Discount %') }}</th>
                                            <th>{{ __('Weight') }}</th>
                                            <th>{{ __('Colour') }}</th>
                                            <th>{{ __('Emoji') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="segBody">
                                        @foreach($segments as $s)
                                            <tr class="seg-row">
                                                <td><input type="text" name="label[]" class="form-control" value="{{ $s['label'] }}"></td>
                                                <td>
                                                    <select name="type[]" class="form-control">
                                                        <option value="discount" @if($s['type']==='discount') selected @endif>{{ __('Discount') }}</option>
                                                        <option value="nothing" @if($s['type']!=='discount') selected @endif>{{ __('Try Again') }}</option>
                                                    </select>
                                                </td>
                                                <td><input type="number" name="value[]" class="form-control" min="0" step="1" value="{{ (int) $s['value'] }}"></td>
                                                <td><input type="number" name="weight[]" class="form-control" min="0" step="1" value="{{ (int) $s['weight'] }}"></td>
                                                <td><input type="color" name="color[]" class="form-control sw-color" value="{{ $s['color'] }}"></td>
                                                <td><input type="text" name="emoji[]" class="form-control sw-emoji" value="{{ $s['emoji'] }}"></td>
                                                <td><button type="button" class="btn btn-sm btn-danger seg-del">&times;</button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-2 mb-4 pr-4 pl-4">{{ __('Save Changes') }}</button>
                    <a href="{{ url('/spin-wheel') }}" target="_blank" class="btn btn-outline-secondary mt-2 mb-4">{{ __('Open game') }}</a>
                </form>

                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{ __('Activity') }}</h4>
                        <div class="row mb-3">
                            <div class="col-4"><div class="sw-stat"><b>{{ $stats['spins'] }}</b><span>{{ __('Total spins') }}</span></div></div>
                            <div class="col-4"><div class="sw-stat"><b>{{ $stats['wins'] }}</b><span>{{ __('Wins') }}</span></div></div>
                            <div class="col-4"><div class="sw-stat"><b>{{ $stats['players'] }}</b><span>{{ __('Players') }}</span></div></div>
                        </div>
                        <h5 class="mb-2">{{ __('Recent winners') }}</h5>
                        <div class="table-responsive">
                            <table class="sw-seg-table">
                                <thead><tr><th>{{ __('User') }}</th><th>{{ __('Prize') }}</th><th>{{ __('Code') }}</th><th>{{ __('When') }}</th></tr></thead>
                                <tbody>
                                    @forelse($recentWins as $w)
                                        <tr>
                                            <td>{{ optional($w->user)->username ?? optional($w->user)->name ?? ('#'.$w->user_id) }}</td>
                                            <td>{{ $w->segment_label }}</td>
                                            <td><code>{{ $w->code ?: '—' }}</code></td>
                                            <td>{{ $w->created_at ? $w->created_at->diffForHumans() : '' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-muted">{{ __('No wins yet.') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            "use strict";
            var rowTpl = '<tr class="seg-row">' +
                '<td><input type="text" name="label[]" class="form-control" value=""></td>' +
                '<td><select name="type[]" class="form-control"><option value="discount">{{ __('Discount') }}</option><option value="nothing">{{ __('Try Again') }}</option></select></td>' +
                '<td><input type="number" name="value[]" class="form-control" min="0" step="1" value="0"></td>' +
                '<td><input type="number" name="weight[]" class="form-control" min="0" step="1" value="10"></td>' +
                '<td><input type="color" name="color[]" class="form-control sw-color" value="#4c8dff"></td>' +
                '<td><input type="text" name="emoji[]" class="form-control sw-emoji" value="🏷️"></td>' +
                '<td><button type="button" class="btn btn-sm btn-danger seg-del">&times;</button></td></tr>';
            $('#addSeg').on('click', function () { $('#segBody').append(rowTpl); });
            $('#segBody').on('click', '.seg-del', function () {
                if ($('#segBody .seg-row').length <= 2) { alert('{{ __('A wheel needs at least 2 segments.') }}'); return; }
                $(this).closest('tr').remove();
            });
        });
    </script>
@endsection
