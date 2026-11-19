@extends('frontend.user.rider.rider-master')

@section('title', __('Rider'))

@section('style')
<style>
    .duty {
        text-align: center;
        padding: 26px 18px;
        border-radius: var(--radius);
        border: 1px solid var(--line);
        background: var(--card);
        margin-bottom: 16px;
    }
    .duty.on  { border-color: rgba(34,197,94,.55); background: linear-gradient(180deg, rgba(34,197,94,.10), var(--card)); }
    .duty .state { font-size: 21px; font-weight: 700; margin-bottom: 4px; }
    .duty .sub   { color: var(--muted); font-size: 14px; margin-bottom: 20px; }

    .gps {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        font-size: 13px; color: var(--muted); margin-top: 14px;
    }
    .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--muted); }
    .dot.ok   { background: var(--accent); }
    .dot.bad  { background: var(--danger); }

    .stats { display: flex; gap: 12px; margin-bottom: 16px; }
    .stat {
        flex: 1; background: var(--card); border: 1px solid var(--line);
        border-radius: var(--radius); padding: 15px; text-align: center;
    }
    .stat .n { font-size: 22px; font-weight: 700; }
    .stat .l { font-size: 12px; color: var(--muted); }

    .fare-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid var(--line); }
    .fare-row:last-child { border-bottom: 0; }
</style>
@endsection

@section('content')

    <div class="duty {{ $rider->is_online ? 'on' : '' }}" id="duty">
        <div class="state" id="dutyState">
            {{ $rider->is_online ? __('You are online') : __('You are offline') }}
        </div>
        <div class="sub" id="dutySub">
            {{ $rider->is_online
                ? __('Trip requests will come to this screen.')
                : __('Go online to start receiving trips.') }}
        </div>

        <button type="button" id="dutyBtn"
                class="btn {{ $rider->is_online ? 'btn-danger' : '' }}">
            {{ $rider->is_online ? __('Go offline') : __('Go online') }}
        </button>

        <div class="gps">
            <span class="dot" id="gpsDot"></span>
            <span id="gpsText">{{ __('Location not started') }}</span>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="n">{{ $rider->total_trips }}</div>
            <div class="l">{{ __('Trips') }}</div>
        </div>
        <div class="stat">
            <div class="n">{{ $rider->rating_count ? number_format($rider->averageRating(), 1) : '—' }}</div>
            <div class="l">{{ __('Rating') }}</div>
        </div>
    </div>

    <div class="card">
        <h2>{{ __("Today's fares") }}</h2>
        @php $cur = get_static_option('currency_symbol') ?: 'TZS'; @endphp
        <div class="fare-row"><span class="muted">{{ __('Base fare') }}</span><span>{{ $cur }} {{ number_format($fare['base']) }}</span></div>
        <div class="fare-row"><span class="muted">{{ __('Per km') }}</span><span>{{ $cur }} {{ number_format($fare['per_km']) }}</span></div>
        <div class="fare-row"><span class="muted">{{ __('Per minute') }}</span><span>{{ $cur }} {{ number_format($fare['per_minute']) }}</span></div>
        <div class="fare-row"><span class="muted">{{ __('Minimum fare') }}</span><span>{{ $cur }} {{ number_format($fare['minimum']) }}</span></div>
        <div class="fare-row"><span class="muted">{{ __('Our commission') }}</span><span>{{ rtrim(rtrim(number_format($fare['commission'], 2, '.', ''), '0'), '.') }}%</span></div>
    </div>

    <div class="card">
        <h2>{{ __('Trip requests') }}</h2>
        <p class="muted" style="margin:0">
            {{ __('Nothing yet. Requests will appear here while you are online.') }}
        </p>
    </div>

@endsection

@section('script')
<script>
(function () {
    'use strict';

    var btn     = document.getElementById('dutyBtn');
    var duty    = document.getElementById('duty');
    var state   = document.getElementById('dutyState');
    var sub     = document.getElementById('dutySub');
    var gpsDot  = document.getElementById('gpsDot');
    var gpsText = document.getElementById('gpsText');

    var online   = {{ $rider->is_online ? 'true' : 'false' }};
    var watchId  = null;
    var lastFix  = null;
    var pingTimer = null;

    var T = {
        on:        @json(__('You are online')),
        off:       @json(__('You are offline')),
        onSub:     @json(__('Trip requests will come to this screen.')),
        offSub:    @json(__('Go online to start receiving trips.')),
        goOn:      @json(__('Go online')),
        goOff:     @json(__('Go offline')),
        locating:  @json(__('Finding your location…')),
        located:   @json(__('Location sharing on')),
        locOff:    @json(__('Location not started')),
        denied:    @json(__('Location is blocked. Allow it to go online.')),
        noGps:     @json(__('This phone cannot share location.')),
        failed:    @json(__('Could not change your status. Try again.'))
    };

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.RIDER.csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify(body || {})
        });
    }

    function paint() {
        duty.classList.toggle('on', online);
        state.textContent = online ? T.on : T.off;
        sub.textContent   = online ? T.onSub : T.offSub;
        btn.textContent   = online ? T.goOff : T.goOn;
        btn.classList.toggle('btn-danger', online);
    }

    function gps(cls, text) {
        gpsDot.className = 'dot' + (cls ? ' ' + cls : '');
        gpsText.textContent = text;
    }

    /*
     * Going online needs a position first: a rider on the map with no location
     * is worse than one who is simply offline, because the dispatcher would
     * offer them trips it cannot route.
     */
    function currentPosition() {
        return new Promise(function (resolve, reject) {
            if (!navigator.geolocation) { reject(new Error('unsupported')); return; }
            navigator.geolocation.getCurrentPosition(
                function (p) { resolve(p); },
                function (e) { reject(e); },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
            );
        });
    }

    function startWatching() {
        if (!navigator.geolocation || watchId !== null) { return; }

        watchId = navigator.geolocation.watchPosition(
            function (p) {
                lastFix = p.coords;
                gps('ok', T.located);
            },
            function () { gps('bad', T.denied); },
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 5000 }
        );

        // The browser fires watchPosition as often as the hardware allows, which
        // would be far more traffic than the dispatcher needs. The server is told
        // on a fixed interval instead, from whatever the latest fix happens to be.
        pingTimer = setInterval(function () {
            if (!online || !lastFix) { return; }
            post(@json(route('rider.ping')), {
                lat: lastFix.latitude,
                lng: lastFix.longitude
            }).catch(function () { /* a dropped ping is not worth bothering the rider about */ });
        }, 10000);
    }

    function stopWatching() {
        if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null; }
        if (pingTimer) { clearInterval(pingTimer); pingTimer = null; }
        gps('', T.locOff);
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        var going = !online;

        var step = going
            ? (gps('', T.locating), currentPosition())
            : Promise.resolve(null);

        step.then(function (pos) {
            return post(@json(route('rider.online')), {
                online: going ? 1 : 0,
                lat: pos ? pos.coords.latitude  : null,
                lng: pos ? pos.coords.longitude : null
            });
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
            if (!res.ok || !res.d.ok) { throw new Error(res.d.message || T.failed); }
            online = res.d.online;
            paint();
            if (online) { startWatching(); } else { stopWatching(); }
        })
        .catch(function (e) {
            // A refused location permission is the common case and deserves its
            // own wording rather than a generic failure.
            if (e && typeof e.code !== 'undefined') {
                gps('bad', e.code === 1 ? T.denied : T.noGps);
            } else {
                gps('bad', e.message || T.failed);
            }
        })
        .then(function () { btn.disabled = false; });
    });

    paint();
    if (online) { startWatching(); }
})();
</script>
@endsection
