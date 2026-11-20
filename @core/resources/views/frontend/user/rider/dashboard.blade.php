@extends('frontend.ride.master')

@section('title', __('Rider'))

@section('topright')
    <a href="{{ route('rider.earnings') }}" class="link">{{ __('Earnings') }}</a>
@endsection

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

    .req {
        border: 1px solid var(--line); border-radius: 12px;
        padding: 14px; margin-bottom: 12px; background: #0d1420;
    }
    .req .money { font-size: 20px; font-weight: 800; }
    .req .away  { color: var(--muted); font-size: 13px; }
    .req .leg   { font-size: 14px; margin-top: 8px; }
    .req .leg b { color: var(--muted); font-weight: 600; font-size: 12px; text-transform: uppercase; }
    .req button { margin-top: 12px; }
    .req .pax { font-size: 14px; margin-top: 10px; font-weight: 600; }
    .req .pax .st  { color: var(--warn); }
    .req .pax .new { color: var(--muted); font-weight: 400; font-size: 13px; }
</style>
@endsection

@section('content')

    @if ($active)
        <div class="card" style="border-color: rgba(245,158,11,.5)">
            <h2>{{ __('You are on a trip') }}</h2>
            <p class="muted">{{ $active->pickup_address ?: __('Point on the map') }}
                &rarr; {{ $active->dropoff_address ?: __('Point on the map') }}</p>
            <a href="{{ route('rider.trip') }}" class="btn">{{ __('Open the trip') }}</a>
        </div>
    @endif

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
        <a class="stat" href="{{ route('rider.ratings') }}" style="text-decoration:none;color:inherit">
            <div class="n">{{ $rider->rating_count ? number_format($rider->averageRating(), 1) : '—' }}</div>
            <div class="l">{{ __('Rating') }} &rsaquo;</div>
        </a>
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

    <div class="card" id="reqCard">
        <h2>{{ __('Trip requests') }}</h2>
        <div id="reqList"></div>
        <p class="muted" id="reqEmpty" style="margin:0{{ $waiting->isNotEmpty() ? ';display:none' : '' }}">
            {{ $rider->is_online
                ? __('Nothing yet. New requests appear here on their own.')
                : __('Go online to start receiving trips.') }}
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
            if (online) { startWatching(); startPolling(); }
            else { stopWatching(); stopPolling(); }
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

    /* ---------------------------------------------------------- trip work */

    var reqList  = document.getElementById('reqList');
    var reqEmpty = document.getElementById('reqEmpty');
    var hadTrip  = {{ $active ? 'true' : 'false' }};
    var reqTimer = null;
    // Rendered by the server with the page, so the first paint is not empty.
    var waiting  = @json($waiting);

    var R = {
        none:    @json(__('Nothing yet. New requests appear here on their own.')),
        offline: @json(__('Go online to start receiving trips.')),
        take:    @json(__('Take this trip')),
        taking:  @json(__('Taking…')),
        gone:    @json(__('That trip has already been taken.')),
        away:    @json(__(':km km away')),
        newPax:  @json(__('first ride'))
    };

    function card(r) {
        var el = document.createElement('div');
        el.className = 'req';

        var head = document.createElement('div');
        head.innerHTML = '<div class="money">' + r.earning + '</div>'
            + '<div class="away">' + R.away.replace(':km', r.away_km)
            + ' · ' + r.trip_km + ' km · ' + r.minutes + ' min</div>';

        // A rider deciding whether to take a trip should know who is in it.
        var who = document.createElement('div');
        who.className = 'pax';
        who.innerHTML = escapeHtml(r.passenger || '')
            + (r.standing && r.standing.count
                ? ' <span class="st">\u2605</span> ' + Number(r.standing.average).toFixed(1)
                : ' <span class="new">' + R.newPax + '</span>');

        var legs = document.createElement('div');
        legs.innerHTML = '<div class="leg"><b>' + @json(__('Pick up')) + '</b><br>'
            + escapeHtml(r.pickup) + '</div>'
            + '<div class="leg"><b>' + @json(__('Drop off')) + '</b><br>'
            + escapeHtml(r.dropoff) + '</div>';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn';
        btn.textContent = R.take;
        btn.addEventListener('click', function () {
            btn.disabled = true;
            btn.textContent = R.taking;

            post(r.accept_url, {})
                .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, d: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.d.ok) { throw new Error(res.d.message || R.gone); }
                    window.location = res.d.redirect;
                })
                .catch(function (e) {
                    btn.textContent = e.message || R.gone;
                    // The trip is gone, so take it off the screen rather than
                    // leaving a button that cannot work.
                    setTimeout(function () { el.remove(); pollRequests(); }, 1200);
                });
        });

        el.appendChild(head);
        el.appendChild(who);
        el.appendChild(legs);
        el.appendChild(btn);

        return el;
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function paintRequests(list, isOnline) {
        reqList.innerHTML = '';

        if (!list || !list.length) {
            reqEmpty.style.display = '';
            reqEmpty.textContent = isOnline ? R.none : R.offline;
            return;
        }

        reqEmpty.style.display = 'none';
        list.forEach(function (r) { reqList.appendChild(card(r)); });
    }

    function pollRequests() {
        fetch(@json(route('rider.requests')), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (!d || !d.ok) { return; }

            // A trip taken on another device should pull this screen along
            // with it, but a rider who already knew is left where they are.
            if (d.active && !hadTrip) { window.location = d.redirect; return; }

            paintRequests(d.requests, d.online);
        })
        .catch(function () { /* a missed poll just means the next one */ });
    }

    function startPolling() {
        if (reqTimer) { return; }
        pollRequests();
        reqTimer = setInterval(pollRequests, 8000);
    }

    function stopPolling() {
        if (reqTimer) { clearInterval(reqTimer); reqTimer = null; }
        reqList.innerHTML = '';
        reqEmpty.style.display = '';
        reqEmpty.textContent = R.offline;
    }

    paint();
    if (waiting.length) { paintRequests(waiting, true); }
    if (online) { startWatching(); startPolling(); }
})();
</script>
@endsection
