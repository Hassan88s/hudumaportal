{{--
    Dashboard links into ride booking.

    Included from both the buyer and the seller sidebars, in both themes, so
    the wording and the rules live in one place. $listClass is whatever the
    surrounding theme calls a sidebar item.

    Nothing shows while ride booking is off: the pages are not there, and a
    link to a 404 is worse than no link. The rider link shows only on seller
    accounts, because only a seller may apply to carry passengers.
--}}
@if (get_static_option('ride_enabled') == '1' && auth('web')->check())
    @php
        $rideListClass = $listClass ?? 'list';
        $isSeller = (int) auth('web')->user()->user_type === 0;
        $myRider  = $isSeller ? \App\Rider::where('user_id', auth('web')->id())->first() : null;
    @endphp

    <li class="{{ $rideListClass }} @if(request()->is('ride') || request()->is('ride/*')) active @endif">
        <a href="{{ route('ride.index') }}"><i class="las la-motorcycle"></i> {{ __('Book a ride') }}</a>
    </li>

    @if ($isSeller)
        <li class="{{ $rideListClass }} @if(request()->is('rider*')) active @endif">
            <a href="{{ $myRider && $myRider->isApproved() ? route('rider.dashboard') : ($myRider ? route('rider.status') : route('rider.apply')) }}">
                <i class="las la-id-card"></i>
                {{ $myRider ? __('Rider account') : __('Earn as a rider') }}
            </a>
        </li>
    @endif
@endif
