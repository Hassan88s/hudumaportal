{{-- Shared sub-navigation for the Champions admin pages --}}
@php
    $hcNav = [
        'admin.champions.index'            => __('Seasons & Winners'),
        'admin.champions.points'           => __('Points & Rules'),
        'admin.champions.rewards'          => __('Prizes & Budget'),
        'admin.champions.missions'         => __('Missions & Bonuses'),
        'admin.champions.adjust.page'      => __('Manual HP'),
        'admin.champions.penalties'        => __('Penalties'),
        'admin.champions.disqualifications'=> __('Disqualifications'),
        'admin.champions.settings.page'    => __('Program Settings'),
        'admin.champions.analytics'        => __('Analytics'),
    ];
    $hcCurrent = Route::currentRouteName();
@endphp
<div class="hc-nav">
    @foreach($hcNav as $route => $label)
        <a href="{{ route($route, ['season' => $season ?? null]) }}" class="{{ $hcCurrent === $route ? 'on' : '' }}">{{ $label }}</a>
    @endforeach
</div>
