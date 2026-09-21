{{-- Shared Champions navigation (PDF §31: Leaderboard, Rewards, Rules, Previous Winners) --}}
@php $active = $active ?? ''; $lg = $lg ?? 'provider'; @endphp
<div class="hc-tabs">
    <a href="{{ route('champions.board', ['league' => 'provider']) }}" class="{{ $active === 'provider' ? 'active' : '' }}">{{ __('Pro League') }}</a>
    <a href="{{ route('champions.board', ['league' => 'client']) }}" class="{{ $active === 'client' ? 'active' : '' }}">{{ __('Client League') }}</a>
    <a href="{{ route('champions.winners') }}" class="{{ $active === 'winners' ? 'active' : '' }}">{{ __('Top Five') }}</a>
    <a href="{{ route('champions.hall') }}" class="{{ $active === 'hall' ? 'active' : '' }}">{{ __('Hall of Fame') }}</a>
    <a href="{{ route('champions.rewards') }}" class="{{ $active === 'rewards' ? 'active' : '' }}">{{ __('Rewards') }}</a>
    <a href="{{ route('champions.rules') }}" class="{{ $active === 'rules' ? 'active' : '' }}">{{ __('Rules') }}</a>
</div>
