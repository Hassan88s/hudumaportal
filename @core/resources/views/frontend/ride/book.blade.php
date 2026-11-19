@extends('frontend.ride.master')

@section('title', __('Book a ride'))

@section('topright')
    <a href="{{ route('ride.history') }}" class="link">{{ __('My trips') }}</a>
@endsection

@section('style')
<style>
    #map { height: 260px; border-radius: var(--radius); border: 1px solid var(--line); margin-bottom: 16px; background: #0d1420; }
    #map.hidden { display: none; }

    .point { position: relative; }
    .point .ico {
        position: absolute; left: 13px; top: 13px; font-size: 18px;
    }
    .point input { padding-left: 40px; }
    .ico-pick { color: var(--accent); }
    .ico-drop { color: var(--danger); }

    .locate {
        background: transparent; border: 1px solid var(--line); color: var(--muted);
        border-radius: 10px; padding: 9px 12px; font-size: 13px; cursor: pointer;
        margin: -8px 0 16px;
    }

    .quote .fare { font-size: 30px; font-weight: 800; letter-spacing: -.5px; }
    .quote .meta { color: var(--muted); font-size: 14px; margin-top: 2px; }

    .spin { color: var(--muted); font-size: 14px; }

    .trip-chip {
        display: flex; justify-content: space-between; gap: 10px;
        padding: 11px 0; border-bottom: 1px solid var(--line); font-size: 14px;
    }
    .trip-chip:last-child { border-bottom: 0; }
</style>
@endsection

@section('content')

    @if (! $settings['enabled'])
        <div class="alert alert-warn">{{ __('Ride booking is switched off at the moment.') }}</div>
    @endif

    <div id="map" class="hidden"></div>

    <div class="card">
        <h2>{{ __('Where to?') }}</h2>

        <div class="point">
            <i class="las la-dot-circle ico ico-pick"></i>
            <label for="pickup" class="sr-only" style="display:none">{{ __('Pick-up') }}</label>
            <input type="text" id="pickup" placeholder="{{ __('Pick-up point') }}" autocomplete="off">
        </div>

        <button type="button" class="locate" id="locateBtn">
            <i class="las la-crosshairs"></i> {{ __('Use my current location') }}
        </button>

        <div class="point">
            <i class="las la-map-marker ico ico-drop"></i>
            <input type="text" id="dropoff" placeholder="{{ __('Where are you going?') }}" autocomplete="off">
        </div>

        {{-- Shown only when the map could not load, so the page still works
             on a phone with no map tiles and a passenger can still be found. --}}
        <div id="manual" style="display:none">
            <div class="row">
                <div><label>{{ __('Pick-up latitude') }}</label><input type="number" step="any" id="plat"></div>
                <div><label>{{ __('Pick-up longitude') }}</label><input type="number" step="any" id="plng"></div>
            </div>
            <div class="row">
                <div><label>{{ __('Drop-off latitude') }}</label><input type="number" step="any" id="dlat"></div>
                <div><label>{{ __('Drop-off longitude') }}</label><input type="number" step="any" id="dlng"></div>
            </div>
        </div>

        <div id="hint" class="muted">{{ __('Set both points to see the price.') }}</div>
    </div>

    <div class="card quote" id="quoteCard" style="display:none">
        <h2>{{ __('Your fare') }}</h2>
        <div class="fare" id="fareText">—</div>
        <div class="meta" id="fareMeta"></div>

        <div style="margin-top:14px">
            <div class="kv">
                <span class="k">{{ __('Wallet balance') }}</span>
                <span class="v" id="balText">{{ $currency }} {{ number_format($balance, 0) }}</span>
            </div>
        </div>

        <div id="shortMsg" class="alert alert-error" style="display:none; margin-top:14px"></div>

        <button type="button" class="btn" id="bookBtn" style="margin-top:16px" disabled>
            {{ __('Book this ride') }}
        </button>

        <p class="muted" style="margin:12px 0 0">
            {{ __('The fare is taken from your wallet now and held until the trip ends. If no rider accepts, it comes straight back.') }}
        </p>
    </div>

    <div class="card">
        <h2>{{ __('How the price is worked out') }}</h2>
        <div class="kv"><span class="k">{{ __('Base fare') }}</span><span class="v">{{ $currency }} {{ number_format($settings['base']) }}</span></div>
        <div class="kv"><span class="k">{{ __('Per km') }}</span><span class="v">{{ $currency }} {{ number_format($settings['per_km']) }}</span></div>
        <div class="kv"><span class="k">{{ __('Per minute') }}</span><span class="v">{{ $currency }} {{ number_format($settings['per_minute']) }}</span></div>
        <div class="kv"><span class="k">{{ __('Minimum fare') }}</span><span class="v">{{ $currency }} {{ number_format($settings['minimum']) }}</span></div>
        @if ($settings['cancel_fee'] > 0)
            <div class="kv"><span class="k">{{ __('Cancelling after a rider accepts') }}</span><span class="v">{{ $currency }} {{ number_format($settings['cancel_fee']) }}</span></div>
        @endif
    </div>

    @if (count($recent))
        <div class="card">
            <h2>{{ __('Recent trips') }}</h2>
            @foreach ($recent as $old)
                <div class="trip-chip">
                    <span class="muted">{{ \Illuminate\Support\Str::limit($old->dropoff_address ?: __('Trip #:id', ['id' => $old->id]), 28) }}</span>
                    <span>{{ $currency }} {{ number_format($old->fare, 0) }}</span>
                </div>
            @endforeach
            <a href="{{ route('ride.history') }}" class="btn btn-ghost btn-sm" style="margin-top:14px">{{ __('See all trips') }}</a>
        </div>
    @endif

@endsection

@section('script')
<script>
(function () {
    'use strict';

    var T = {
        both:      @json(__('Set both points to see the price.')),
        pricing:   @json(__('Working out the price…')),
        locating:  @json(__('Finding your location…')),
        denied:    @json(__('We could not get your location. Type the pick-up point instead.')),
        here:      @json(__('My location')),
        booking:   @json(__('Booking…')),
        book:      @json(__('Book this ride')),
        failed:    @json(__('Something went wrong. Try again.')),
        short:     @json(__('You need :amount more in your wallet.')),
        topup:     @json(__('Top up your wallet')),
        same:      @json(__('Pick-up and drop-off are the same place.'))
    };

    var urls = {
        quote:  @json(route('ride.quote')),
        book:   @json(route('ride.book')),
        wallet: @json($walletUrl)
    };

    var state  = { pickup: null, dropoff: null, quote: null };
    var els = {
        pickup:  document.getElementById('pickup'),
        dropoff: document.getElementById('dropoff'),
        hint:    document.getElementById('hint'),
        card:    document.getElementById('quoteCard'),
        fare:    document.getElementById('fareText'),
        meta:    document.getElementById('fareMeta'),
        bal:     document.getElementById('balText'),
        short:   document.getElementById('shortMsg'),
        book:    document.getElementById('bookBtn'),
        locate:  document.getElementById('locateBtn'),
        map:     document.getElementById('map'),
        manual:  document.getElementById('manual')
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
        }).then(function (r) {
            return r.json().then(function (d) { return { ok: r.ok, d: d }; });
        });
    }

    /* ------------------------------------------------------------- quoting */

    var quoteTimer = null;

    function scheduleQuote() {
        clearTimeout(quoteTimer);
        // A dragged marker fires constantly; one request after things settle
        // is both kinder to the server and less flickery to read.
        quoteTimer = setTimeout(getQuote, 350);
    }

    function getQuote() {
        if (!state.pickup || !state.dropoff) {
            els.card.style.display = 'none';
            els.hint.textContent = T.both;
            return;
        }

        els.hint.textContent = T.pricing;
        els.card.style.display = '';
        els.book.disabled = true;

        post(urls.quote, {
            pickup_lat:  state.pickup.lat,
            pickup_lng:  state.pickup.lng,
            dropoff_lat: state.dropoff.lat,
            dropoff_lng: state.dropoff.lng
        }).then(function (res) {
            if (!res.ok || !res.d.ok) { throw new Error(T.failed); }

            state.quote = res.d.quote;
            els.hint.textContent = '';

            els.fare.textContent = res.d.quote.currency + ' '
                + Number(res.d.quote.fare).toLocaleString();
            els.meta.textContent = res.d.quote.distance_km + ' km · '
                + res.d.quote.duration_min + ' min';
            els.bal.textContent  = res.d.quote.currency + ' '
                + Number(res.d.balance).toLocaleString();

            if (res.d.affords) {
                els.short.style.display = 'none';
                els.book.disabled = false;
            } else {
                els.short.style.display = '';
                els.short.innerHTML = T.short.replace(':amount',
                    res.d.quote.currency + ' ' + Number(res.d.shortfall).toLocaleString())
                    + ' <a href="' + urls.wallet + '" style="color:inherit;text-decoration:underline">'
                    + T.topup + '</a>';
                els.book.disabled = true;
            }
        }).catch(function (e) {
            els.hint.textContent = e.message || T.failed;
            els.card.style.display = 'none';
        });
    }

    /* ------------------------------------------------------------- booking */

    els.book.addEventListener('click', function () {
        if (!state.pickup || !state.dropoff) { return; }

        els.book.disabled = true;
        els.book.textContent = T.booking;

        post(urls.book, {
            pickup_lat:      state.pickup.lat,
            pickup_lng:      state.pickup.lng,
            pickup_address:  els.pickup.value || null,
            dropoff_lat:     state.dropoff.lat,
            dropoff_lng:     state.dropoff.lng,
            dropoff_address: els.dropoff.value || null
        }).then(function (res) {
            if (!res.ok || !res.d.ok) { throw new Error(res.d.message || T.failed); }
            window.location = res.d.redirect;
        }).catch(function (e) {
            els.short.style.display = '';
            els.short.textContent = e.message || T.failed;
            els.book.disabled = false;
            els.book.textContent = T.book;
        });
    });

    /* ------------------------------------------------------------ location */

    function currentPosition() {
        return new Promise(function (resolve, reject) {
            if (!navigator.geolocation) { reject(new Error('unsupported')); return; }
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true, timeout: 15000, maximumAge: 30000
            });
        });
    }

    els.locate.addEventListener('click', function () {
        els.hint.textContent = T.locating;

        currentPosition().then(function (p) {
            setPickup(p.coords.latitude, p.coords.longitude, T.here, true);
        }).catch(function () {
            els.hint.textContent = T.denied;
        });
    });

    /* ----------------------------------------------------------- the map   */

    var map = null, markers = {}, geocoder = null;

    function setPickup(lat, lng, label, recentre) {
        state.pickup = { lat: lat, lng: lng };
        if (label) { els.pickup.value = label; }
        if (document.getElementById('plat')) {
            document.getElementById('plat').value = lat;
            document.getElementById('plng').value = lng;
        }
        placeMarker('pickup', lat, lng, recentre);
        scheduleQuote();
    }

    function setDropoff(lat, lng, label, recentre) {
        state.dropoff = { lat: lat, lng: lng };
        if (label) { els.dropoff.value = label; }
        if (document.getElementById('dlat')) {
            document.getElementById('dlat').value = lat;
            document.getElementById('dlng').value = lng;
        }
        placeMarker('dropoff', lat, lng, recentre);
        scheduleQuote();
    }

    function placeMarker(which, lat, lng, recentre) {
        if (!map) { return; }

        var pos = { lat: Number(lat), lng: Number(lng) };

        if (markers[which]) {
            markers[which].setPosition(pos);
        } else {
            markers[which] = new google.maps.Marker({
                position: pos,
                map: map,
                draggable: true,
                label: which === 'pickup' ? 'A' : 'B'
            });
            markers[which].addListener('dragend', function (e) {
                var fn = which === 'pickup' ? setPickup : setDropoff;
                fn(e.latLng.lat(), e.latLng.lng(), null, false);
            });
        }

        if (recentre) { map.panTo(pos); }

        if (markers.pickup && markers.dropoff) {
            var b = new google.maps.LatLngBounds();
            b.extend(markers.pickup.getPosition());
            b.extend(markers.dropoff.getPosition());
            map.fitBounds(b, 60);
        }
    }

    function initMap() {
        els.map.classList.remove('hidden');

        map = new google.maps.Map(els.map, {
            center: { lat: -6.7924, lng: 39.2083 },   // a sensible first view
            zoom: 12,
            disableDefaultUI: true,
            zoomControl: true,
            gestureHandling: 'greedy'
        });

        geocoder = new google.maps.Geocoder();

        // Tapping the map fills whichever point is still empty, then moves on
        // to the drop-off — the order people actually enter them in.
        map.addListener('click', function (e) {
            var lat = e.latLng.lat(), lng = e.latLng.lng();
            if (!state.pickup) { setPickup(lat, lng, null, false); }
            else { setDropoff(lat, lng, null, false); }
            describe(lat, lng, !state.dropoff ? els.pickup : els.dropoff);
        });

        [['pickup', els.pickup, setPickup], ['dropoff', els.dropoff, setDropoff]]
            .forEach(function (pair) {
                var ac = new google.maps.places.Autocomplete(pair[1], { types: ['geocode'] });
                ac.addListener('place_changed', function () {
                    var place = ac.getPlace();
                    if (!place || !place.geometry) { return; }
                    pair[2](
                        place.geometry.location.lat(),
                        place.geometry.location.lng(),
                        place.formatted_address || place.name,
                        true
                    );
                });
            });

        currentPosition().then(function (p) {
            map.setCenter({ lat: p.coords.latitude, lng: p.coords.longitude });
            map.setZoom(15);
        }).catch(function () { /* the default view will do */ });
    }

    /** Turn a tapped point into words, so the rider gets an address too. */
    function describe(lat, lng, input) {
        if (!geocoder || !input || input.value) { return; }
        geocoder.geocode({ location: { lat: lat, lng: lng } }, function (res, status) {
            if (status === 'OK' && res && res[0]) { input.value = res[0].formatted_address; }
        });
    }

    /**
     * No map is not a dead end.
     *
     * If the Maps script cannot load — no key, no network, a blocked referrer —
     * the plain coordinate boxes appear instead and everything else on the page
     * carries on working.
     */
    function noMap() {
        els.manual.style.display = '';
        els.pickup.placeholder  = @json(__('Pick-up point (name it for the rider)'));
        els.dropoff.placeholder = @json(__('Drop-off point (name it for the rider)'));

        ['plat', 'plng', 'dlat', 'dlng'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', function () {
                var pa = parseFloat(document.getElementById('plat').value);
                var po = parseFloat(document.getElementById('plng').value);
                var da = parseFloat(document.getElementById('dlat').value);
                var dob = parseFloat(document.getElementById('dlng').value);

                state.pickup  = (!isNaN(pa) && !isNaN(po)) ? { lat: pa, lng: po } : null;
                state.dropoff = (!isNaN(da) && !isNaN(dob)) ? { lat: da, lng: dob } : null;
                scheduleQuote();
            });
        });
    }

    window.__rideMapReady  = function () { try { initMap(); } catch (e) { noMap(); } };
    window.__rideMapFailed = noMap;
})();
</script>

@php $mapKey = get_static_option('service_google_map_api_key'); @endphp
@if ($mapKey)
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapKey }}&libraries=places"
            onload="window.__rideMapReady()" onerror="window.__rideMapFailed()" async defer></script>
@else
    <script>window.__rideMapFailed();</script>
@endif
@endsection
