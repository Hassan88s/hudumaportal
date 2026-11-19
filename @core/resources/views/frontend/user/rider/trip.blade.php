@extends('frontend.ride.master')

@section('title', __('Trip #:id', ['id' => $ride->id]))
@section('back', route('rider.dashboard'))

@section('style')
<style>
    .route { position: relative; padding-left: 26px; }
    .route .leg { padding: 6px 0; }
    .route .leg .where { font-size: 15px; }
    .route .leg .what  { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
    .route .bullet { position: absolute; left: 0; width: 12px; height: 12px; border-radius: 50%; }
    .route .b1 { top: 12px; background: var(--accent); }
    .route .b2 { bottom: 14px; background: var(--danger); }
    .route .line { position: absolute; left: 5px; top: 24px; bottom: 26px; width: 2px; background: var(--line); }

    .earn { text-align: center; padding: 22px 18px; }
    .earn .n { font-size: 30px; font-weight: 800; letter-spacing: -.5px; }
    .earn .l { color: var(--muted); font-size: 14px; }

    .state { text-align: center; font-size: 15px; font-weight: 600; margin-bottom: 14px; }
</style>
@endsection

@section('content')

    <div class="card earn">
        <div class="n">{{ $currency }} {{ number_format($earning, 0) }}</div>
        <div class="l">{{ __('Yours when the trip is finished') }}</div>
        <div class="muted" style="margin-top:8px">
            {{ __('Fare :fare · :km km · :min min', [
                'fare' => $currency . ' ' . number_format($ride->fare, 0),
                'km'   => $ride->distance_km,
                'min'  => $ride->duration_min,
            ]) }}
        </div>
    </div>

    <div class="card">
        <div class="state" id="stateText">{{ $ride->statusLabel() }}</div>

        <div class="route">
            <span class="bullet b1"></span>
            <span class="line"></span>
            <span class="bullet b2"></span>
            <div class="leg">
                <div class="what">{{ __('Pick up') }}</div>
                <div class="where">{{ $ride->pickup_address ?: __('Point on the map') }}</div>
            </div>
            <div class="leg">
                <div class="what">{{ __('Drop off') }}</div>
                <div class="where">{{ $ride->dropoff_address ?: __('Point on the map') }}</div>
            </div>
        </div>

        <div class="row" style="margin-top:16px">
            <a class="btn btn-ghost btn-sm" target="_blank" rel="noopener"
               href="https://www.google.com/maps/dir/?api=1&destination={{ $ride->pickup_lat }},{{ $ride->pickup_lng }}">
                <i class="las la-directions"></i> {{ __('To pick-up') }}
            </a>
            <a class="btn btn-ghost btn-sm" target="_blank" rel="noopener"
               href="https://www.google.com/maps/dir/?api=1&destination={{ $ride->dropoff_lat }},{{ $ride->dropoff_lng }}">
                <i class="las la-directions"></i> {{ __('To drop-off') }}
            </a>
        </div>
    </div>

    <div class="card">
        <h2>{{ __('Passenger') }}</h2>
        <div style="font-weight:700">{{ optional($ride->passenger)->name ?: __('Passenger') }}</div>
        @if (optional($ride->passenger)->phone)
            <a class="btn btn-ghost btn-sm" style="margin-top:14px" href="tel:{{ $ride->passenger->phone }}">
                <i class="las la-phone"></i> {{ __('Call the passenger') }}
            </a>
        @endif
    </div>

    <button type="button" class="btn" id="mainBtn" style="margin-bottom:12px"></button>
    <button type="button" class="btn btn-ghost btn-sm" id="dropBtn"></button>

    <div id="msg" class="alert alert-error" style="display:none; margin-top:16px"></div>

@endsection

@section('script')
<script>
(function () {
    'use strict';

    var T = {
        arrived:  @json(__("I'm at the pick-up")),
        start:    @json(__('Start the trip')),
        finish:   @json(__('Finish and get paid')),
        drop:     @json(__('Cancel this trip')),
        working:  @json(__('Please wait…')),
        failed:   @json(__('Something went wrong. Try again.')),
        sureDrop: @json(__('Drop this trip? The passenger gets their fare back.')),
        sureEnd:  @json(__('Finish the trip and take payment?'))
    };

    var base = @json(url('rider/trip/' . $ride->id));

    var main = document.getElementById('mainBtn');
    var drop = document.getElementById('dropBtn');
    var msg  = document.getElementById('msg');
    var txt  = document.getElementById('stateText');

    var status = {{ (int) $ride->status }};

    var STATUS = { ACCEPTED: 1, ARRIVED: 2, ONGOING: 3 };

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

    function say(text) {
        msg.style.display = text ? '' : 'none';
        msg.textContent = text || '';
    }

    /*
     * One button, three jobs. Which one it is comes from the trip's own
     * status, so a stale screen cannot offer a step that has already been
     * taken — the server would refuse it anyway, and this keeps the two
     * answers the same.
     */
    function paint() {
        if (status === STATUS.ACCEPTED) {
            main.textContent = T.arrived;
            main.dataset.action = 'arrived';
            main.className = 'btn';
        } else if (status === STATUS.ARRIVED) {
            main.textContent = T.start;
            main.dataset.action = 'start';
            main.className = 'btn';
        } else {
            main.textContent = T.finish;
            main.dataset.action = 'complete';
            main.className = 'btn btn-warn';
        }

        // Once the passenger is on the bike there is no dropping the trip:
        // the only honest ending is finishing it.
        drop.style.display = status === STATUS.ONGOING ? 'none' : '';
        drop.textContent = T.drop;
    }

    function act(action, confirmText) {
        if (confirmText && !window.confirm(confirmText)) { return; }

        main.disabled = drop.disabled = true;
        var label = main.textContent;
        main.textContent = T.working;

        post(base + '/' + action, {}).then(function (res) {
            if (!res.ok || !res.d.ok) { throw new Error(res.d.message || T.failed); }

            if (res.d.redirect) { window.location = res.d.redirect; return; }

            status = res.d.status;
            txt.textContent = res.d.label;
            say('');
            paint();
        }).catch(function (e) {
            say(e.message || T.failed);
            main.textContent = label;
        }).then(function () {
            main.disabled = drop.disabled = false;
        });
    }

    main.addEventListener('click', function () {
        var action = main.dataset.action;
        act(action, action === 'complete' ? T.sureEnd : null);
    });

    drop.addEventListener('click', function () { act('cancel', T.sureDrop); });

    paint();
})();
</script>
@endsection
