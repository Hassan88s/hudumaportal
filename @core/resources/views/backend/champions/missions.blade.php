@extends('backend.admin-master')
@section('site-title'){{ __('Champions Missions') }}@endsection

@section('style')
@include('backend.champions._style')
@endsection

@section('content')
@php $keys = ['completed_services','completed_bookings','repeat_bookings','reviews','portfolio_items','fast_responses','proposals_sent','new_categories','requests_created','demand']; @endphp
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 hc-adm">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @include('backend.champions._nav')

            <div class="box">
                <div class="hd"><h3>{{ __('Create a mission or demand bonus') }}</h3></div>
                <div class="bd">
                    <form method="post" action="{{ route('admin.champions.mission.store') }}" class="form-row">
                        @csrf
                        <select name="league"><option value="provider">{{ __('Provider') }}</option><option value="client">{{ __('Client') }}</option></select>
                        <select name="type"><option value="mission">{{ __('Mission') }}</option><option value="demand_bonus">{{ __('Demand bonus') }}</option></select>
                        <select name="mission_key">
                            @foreach($keys as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
                        </select>
                        <input name="title" placeholder="{{ __('Title') }}" required>
                        <input name="description" placeholder="{{ __('Description') }}">
                        <input name="target" type="number" placeholder="{{ __('Target') }}" style="width:80px">
                        <input name="reward_hp" type="number" placeholder="{{ __('Reward HP') }}" style="width:100px">
                        <input name="bonus_percent" type="number" placeholder="{{ __('Bonus %') }}" style="width:90px">
                        <input name="city_id" type="number" placeholder="{{ __('City ID') }}" style="width:80px">
                        <input name="category_id" type="number" placeholder="{{ __('Category ID') }}" style="width:100px">
                        <input name="season_key" placeholder="YYYY-MM ({{ __('blank = every month') }})" style="width:170px">
                        <button class="btn btn-sm btn-primary">{{ __('Create') }}</button>
                    </form>
                    <small class="text-muted d-block" style="margin-top:10px">{{ __('A mission pays its reward HP once the counter reaches the target. A demand bonus adds its percentage on top of the points for work in that city or category.') }}</small>
                </div>
            </div>

            <div class="box">
                <div class="hd"><h3>{{ __('Missions & demand bonuses') }}</h3></div>
                <div class="bd" style="padding:0 18px">
                    <div style="overflow-x:auto">
                        <table>
                            <thead><tr><th>{{ __('League') }}</th><th>{{ __('Type') }}</th><th>{{ __('Title, counter, target & reward') }}</th><th>{{ __('Active') }}</th></tr></thead>
                            <tbody>
                            @forelse($missions as $m)
                                <tr>
                                    <td>{{ $m->league }}</td>
                                    <td>{{ $m->type }}</td>
                                    <td>
                                        <form method="post" action="{{ route('admin.champions.mission.update', $m->id) }}" class="form-row" style="gap:6px">
                                            @csrf
                                            <input name="title" value="{{ $m->title }}" required style="min-width:150px" title="{{ __('Title') }}">
                                            <input name="description" value="{{ $m->description }}" placeholder="{{ __('Description') }}" style="min-width:150px">
                                            <select name="mission_key" title="{{ __('Counter') }}">
                                                @foreach($keys as $k)<option value="{{ $k }}" @selected($m->mission_key === $k)>{{ $k }}</option>@endforeach
                                            </select>
                                            <input name="target" type="number" min="1" value="{{ $m->target }}" style="width:70px" title="{{ __('Target') }}">
                                            <input name="reward_hp" type="number" min="0" value="{{ $m->reward_hp }}" style="width:85px" title="{{ __('Reward HP') }}">
                                            <input name="bonus_percent" type="number" min="1" value="{{ $m->bonus_percent }}" placeholder="%" style="width:65px" title="{{ __('Bonus %') }}">
                                            <input name="city_id" type="number" value="{{ $m->city_id }}" placeholder="{{ __('City') }}" style="width:70px">
                                            <input name="category_id" type="number" value="{{ $m->category_id }}" placeholder="{{ __('Cat') }}" style="width:70px">
                                            <input name="season_key" value="{{ $m->season_key }}" placeholder="{{ __('every') }}" style="width:90px" title="{{ __('Season, blank = every month') }}">
                                            <button class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                        </form>
                                    </td>
                                    <td style="white-space:nowrap">
                                        <form method="post" action="{{ route('admin.champions.mission.toggle', $m->id) }}" style="display:inline">@csrf
                                            <button class="btn btn-sm {{ $m->is_active ? 'btn-success' : 'btn-outline-secondary' }}">{{ $m->is_active ? __('On') : __('Off') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.champions.mission.delete', $m->id) }}" style="display:inline"
                                              onsubmit="return confirm('{{ __('Delete this mission? Points already earned from it are kept.') }}')">@csrf
                                            <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted" style="padding:16px">{{ __('No missions yet.') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        @if($missions->hasPages())<div style="padding:8px 0">{{ $missions->links() }}</div>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
