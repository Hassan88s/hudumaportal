@extends('frontend.ride.master')

@section('title', __('Trip #:id', ['id' => $ride->id]))
@section('back', route('ride.index'))

@section('style')
<style>
    .stage { text-align: center; padding: 26px 18px; }
    .stage .big { font-size: 21px; font-weight: 700; margin-bottom: 6px; }
    .stage .sub { color: var(--muted); font-size: 14px; }

    .pulse {
        width: 46px; height: 46px; margin: 0 auto 16px;
        border-radius: 50%;
        border: 3px solid var(--line); border-top-color: var(--accent);
        animation: spin 1s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .pulse.stop { animation: none; border-top-color: var(--line); }

    .route { position: relative; padding-left: 26px; }
    .route .leg { padding: 6px 0; }
    .route .leg .where { font-size: 15px; }
    .route .leg .what  { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
    .route .bullet { position: absolute; left: 0; width: 12px; height: 12px; border-radius: 50%; }
    .route .b1 { top: 12px; background: var(--accent); }
    .route .b2 { bottom: 14px; background: var(--danger); }
    .route .line { position: absolute; left: 5px; top: 24px; bottom: 26px; width: 2px; background: var(--line); }

    .who { display: flex; align-items: center; gap: 14px; }
    .who .av {
        width: 48px; height: 48px; border-radius: 50%;
        background: var(--line); display: flex; align-items: center; justify-content: center;
        font-size: 22px; color: var(--muted);
    }
    .who .nm { font-weight: 700; }
    .who .dt { color: var(--muted); font-size: 13px; }
    .who .rated .st { color: var(--warn); letter-spacing: 1px; }

    .stars { display: flex; gap: 8px; justify-content: center; margin: 6px 0 16px; }
    .stars button {
        background: transparent; border: 0; font-size: 34px; line-height: 1;
        color: var(--line); cursor: pointer; padding: 0;
    }
    .stars button.on { color: var(--warn); }
</style>
@endsection

@section('content')

    <div class="card stage" id="stage">
        <div class="pulse {{ $ride->isLive() ? '' : 'stop' }}" id="pulse"></div>
        <div class="big" id="stageText">{{ $ride->statusLabel() }}</div>
        <div class="sub" id="stageSub">
            @if ($ride->isSearching())
                {{ __('We are asking riders near you.') }}
            @elseif ($ride->status === \App\Ride::COMPLETED)
                {{ __('Hope it was a good trip.') }}
            @else
                &nbsp;
            @endif
        </div>
    </div>

    <div class="card" id="riderCard" style="{{ $ride->rider ? '' : 'display:none' }}">
        <h2>{{ __('Your rider') }}</h2>
        <div class="who">
            <div class="av"><i class="las la-motorcycle"></i></div>
            <div style="flex:1">
                <div class="nm" id="rName">{{ $ride->rider ? ($ride->rider->full_name ?: optional($ride->rider->user)->name) : '' }}</div>
                <div class="dt" id="rMeta">
                    @if ($ride->rider)
                        {{ $ride->rider->bike_plate }}
                        @if ($ride->rider->bike_model) · {{ $ride->rider->bike_model }} @endif
                    @endif
                </div>
                {{-- A rider's standing is the one thing a passenger waiting at
                     the roadside actually wants to know about them. --}}
                <div class="dt rated" id="rStars">
                    @if ($ride->rider && $ride->rider->rating_count)
                        <span class="st">{{ str_repeat('★', (int) round($ride->rider->averageRating())) }}</span>
                        {{ number_format($ride->rider->averageRating(), 1) }}
                        · {{ trans_choice('{1} trip|[2,*] :count trips', $ride->rider->total_trips, ['count' => $ride->rider->total_trips]) }}
                    @elseif ($ride->rider)
                        {{ __('New rider') }}
                    @endif
                </div>
            </div>
        </div>
        <a class="btn btn-ghost btn-sm" id="rCall" style="margin-top:14px;{{ optional($ride->rider)->phone ? '' : 'display:none' }}"
           href="tel:{{ optional($ride->rider)->phone }}">
            <i class="las la-phone"></i> {{ __('Call the rider') }}
        </a>
    </div>

    <div class="card">
        <h2>{{ __('Your trip') }}</h2>
        <div class="route">
            <span class="bullet b1"></span>
            <span class="line"></span>
            <span class="bullet b2"></span>
            <div class="leg">
                <div class="what">{{ __('Pick-up') }}</div>
                <div class="where">{{ $ride->pickup_address ?: __('Point on the map') }}</div>
            </div>
            <div class="leg">
                <div class="what">{{ __('Drop-off') }}</div>
                <div class="where">{{ $ride->dropoff_address ?: __('Point on the map') }}</div>
            </div>
        </div>

        <div style="margin-top:16px">
            <div class="kv"><span class="k">{{ __('Fare') }}</span><span class="v">{{ $currency }} {{ number_format($ride->fare, 0) }}</span></div>
            <div class="kv"><span class="k">{{ __('Distance') }}</span><span class="v">{{ $ride->distance_km }} km</span></div>
            <div class="kv"><span class="k">{{ __('Estimated time') }}</span><span class="v">{{ $ride->duration_min }} {{ __('min') }}</span></div>
            @if ($ride->refunded_amount > 0)
                <div class="kv"><span class="k">{{ __('Returned to your wallet') }}</span><span class="v">{{ $currency }} {{ number_format($ride->refunded_amount, 0) }}</span></div>
            @endif
            @if ($ride->cancellation_fee > 0)
                <div class="kv"><span class="k">{{ __('Cancellation fee') }}</span><span class="v">{{ $currency }} {{ number_format($ride->cancellation_fee, 0) }}</span></div>
            @endif
        </div>
    </div>

    {{-- Cancelling --}}
    <div id="cancelBox" style="{{ in_array($ride->status, [\App\Ride::SEARCHING, \App\Ride::ACCEPTED, \App\Ride::ARRIVED]) ? '' : 'display:none' }}">
        <div class="card">
            <h2>{{ __('Need to cancel?') }}</h2>
            <p class="muted" id="cancelNote">
                @if ($ride->isFreeToCancel())
                    {{ __('Nobody has accepted yet, so this costs you nothing.') }}
                @elseif ($settings['cancel_fee'] > 0)
                    {{ __('A rider is already on the way. :fee will be kept as a cancellation fee.', ['fee' => $currency . ' ' . number_format($settings['cancel_fee'], 0)]) }}
                @else
                    {{ __('A rider is already on the way.') }}
                @endif
            </p>
            <button type="button" class="btn btn-danger btn-sm" id="cancelBtn">{{ __('Cancel this trip') }}</button>
        </div>
    </div>

    {{-- Rating, once the trip is done --}}
    @if ($ride->status === \App\Ride::COMPLETED && $ride->rating === null)
        <div class="card" id="rateBox">
            <h2>{{ __('How was the ride?') }}</h2>
            <div class="stars" id="stars">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" data-star="{{ $i }}" aria-label="{{ $i }}">★</button>
                @endfor
            </div>
            <textarea id="rateComment" rows="3" placeholder="{{ __('Anything you want to add? (optional)') }}"></textarea>
            <button type="button" class="btn" id="rateBtn" disabled>{{ __('Send rating') }}</button>
        </div>
    @endif

    @if ($ride->isClosed())
        <a href="{{ route('ride.index') }}" class="btn">{{ __('Book another ride') }}</a>
    @endif

    <div id="msg" class="alert alert-error" style="display:none; margin-top:16px"></div>

@endsection

@section('script')
<script>
(function () {
    'use strict';

    var T = {
        free:    @json(__('Nobody has accepted yet, so this costs you nothing.')),
        onWay:   @json(__('A rider is already on the way.')),
        failed:  @json(__('Something went wrong. Try again.')),
        sure:    @json(__('Cancel this trip?')),
        sending: @json(__('Sending…')),
        asking:  @json(__('We are asking riders near you.')),
        done:    @json(__('Hope it was a good trip.')),
        thanks:  @json(__('Thanks for the rating.')),
        trips:   @json(__(':count trips')),
        newRider: @json(__('New rider'))
    };

    var urls = {
        status: @json(route('ride.trip.status', $ride->id)),
        cancel: @json(route('ride.trip.cancel', $ride->id)),
        rate:   @json(route('ride.trip.rate', $ride->id))
    };

    var els = {
        pulse:  document.getElementById('pulse'),
        text:   document.getElementById('stageText'),
        sub:    document.getElementById('stageSub'),
        rider:  document.getElementById('riderCard'),
        rName:  document.getElementById('rName'),
        rMeta:  document.getElementById('rMeta'),
        rStars: document.getElementById('rStars'),
        rCall:  document.getElementById('rCall'),
        cancel: document.getElementById('cancelBox'),
        cNote:  document.getElementById('cancelNote'),
        cBtn:   document.getElementById('cancelBtn'),
        msg:    document.getElementById('msg')
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

    function say(text) {
        els.msg.style.display = text ? '' : 'none';
        els.msg.textContent = text || '';
    }

    /* --------------------------------------------------------- the waiting */

    var poll = null;
    var lastStatus = {{ (int) $ride->status }};

    function paint(d) {
        els.text.textContent = d.label;

        els.sub.innerHTML = d.status === 0 ? T.asking
            : (d.status === 4 ? T.done : '&nbsp;');

        els.pulse.classList.toggle('stop', !d.live);

        if (d.rider) {
            els.rider.style.display = '';
            els.rName.textContent = d.rider.name || '';
            els.rMeta.textContent = [d.rider.plate, d.rider.model].filter(Boolean).join(' · ');

            if (d.rider.rating) {
                els.rStars.innerHTML = '<span class="st">'
                    + '\u2605'.repeat(Math.round(d.rider.rating)) + '</span> '
                    + Number(d.rider.rating).toFixed(1)
                    + ' \u00b7 ' + T.trips.replace(':count', d.rider.trips);
            } else {
                els.rStars.textContent = T.newRider;
            }
            if (d.rider.phone) {
                els.rCall.style.display = '';
                els.rCall.setAttribute('href', 'tel:' + d.rider.phone);
            }
        }

        if (d.can_cancel) {
            els.cancel.style.display = '';
            els.cNote.textContent = d.free_cancel ? T.free : T.onWay;
        } else {
            els.cancel.style.display = 'none';
        }

        // Anything final changes the page enough that a reload is simpler and
        // more honest than patching every section by hand.
        if (d.status !== lastStatus && d.closed) {
            clearInterval(poll);
            window.location.reload();
        }

        lastStatus = d.status;
    }

    function check() {
        fetch(urls.status, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (d) { if (d && d.ok) { paint(d); } })
        .catch(function () { /* one missed poll is not worth a message */ });
    }

    @if ($ride->isLive())
        poll = setInterval(check, 5000);
        check();
    @endif

    /* ------------------------------------------------------------ actions  */

    if (els.cBtn) {
        els.cBtn.addEventListener('click', function () {
            if (!window.confirm(T.sure)) { return; }

            els.cBtn.disabled = true;
            post(urls.cancel, {}).then(function (res) {
                if (!res.ok || !res.d.ok) { throw new Error(res.d.message || T.failed); }
                window.location.reload();
            }).catch(function (e) {
                say(e.message || T.failed);
                els.cBtn.disabled = false;
            });
        });
    }

    var starsBox = document.getElementById('stars');
    if (starsBox) {
        var chosen = 0;
        var rateBtn = document.getElementById('rateBtn');

        starsBox.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-star]');
            if (!btn) { return; }

            chosen = parseInt(btn.getAttribute('data-star'), 10);
            Array.prototype.forEach.call(starsBox.children, function (b, i) {
                b.classList.toggle('on', i < chosen);
            });
            rateBtn.disabled = false;
        });

        rateBtn.addEventListener('click', function () {
            rateBtn.disabled = true;
            rateBtn.textContent = T.sending;

            post(urls.rate, {
                rating: chosen,
                comment: document.getElementById('rateComment').value || null
            }).then(function (res) {
                if (!res.ok || !res.d.ok) { throw new Error(res.d.message || T.failed); }
                document.getElementById('rateBox').innerHTML =
                    '<p class="muted" style="margin:0">' + T.thanks + '</p>';
            }).catch(function (e) {
                say(e.message || T.failed);
                rateBtn.disabled = false;
            });
        });
    }
})();
</script>
@endsection
