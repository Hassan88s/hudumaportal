@extends('frontend.ride.master')

@section('title', __('Trip finished'))
@section('back', route('rider.dashboard'))

@section('style')
<style>
    .paid { text-align: center; padding: 28px 18px; }
    .paid .n { font-size: 38px; font-weight: 800; letter-spacing: -1px; color: var(--accent); }
    .paid .l { color: var(--muted); font-size: 14px; }

    .stars { display: flex; gap: 8px; justify-content: center; margin: 6px 0 16px; }
    .stars button {
        background: transparent; border: 0; font-size: 34px; line-height: 1;
        color: var(--line); cursor: pointer; padding: 0;
    }
    .stars button.on { color: var(--warn); }

    .pax { text-align: center; margin-bottom: 4px; font-weight: 700; }
    .pax .sub { font-weight: 400; color: var(--muted); font-size: 13px; }
</style>
@endsection

@section('content')

    <div class="card paid">
        <div class="n">+ {{ $currency }} {{ number_format($ride->rider_earning, 0) }}</div>
        <div class="l">{{ __('Paid into your wallet') }}</div>
        <div class="muted" style="margin-top:10px">
            {{ __('Fare :fare · our commission :fee', [
                'fare' => $currency . ' ' . number_format($ride->fare, 0),
                'fee'  => $currency . ' ' . number_format($ride->commission, 0),
            ]) }}
        </div>
    </div>

    @if ($ride->passenger_rating === null)
        <div class="card" id="rateBox">
            <h2>{{ __('How was the passenger?') }}</h2>

            <div class="pax">
                {{ optional($ride->passenger)->name ?: __('Passenger') }}
                <div class="sub">
                    @if ($standing['count'])
                        {{ __(':avg from :count ratings', [
                            'avg'   => number_format($standing['average'], 1),
                            'count' => $standing['count'],
                        ]) }}
                    @else
                        {{ __('Never been rated before') }}
                    @endif
                </div>
            </div>

            <div class="stars" id="stars">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" data-star="{{ $i }}" aria-label="{{ $i }}">★</button>
                @endfor
            </div>

            <textarea id="rateComment" rows="3"
                      placeholder="{{ __('Anything other riders should know? (optional)') }}"></textarea>

            <button type="button" class="btn" id="rateBtn" disabled>{{ __('Send rating') }}</button>

            <p class="muted" style="margin:12px 0 0">
                {{ __('The passenger never sees this. It is for the next rider who is offered their trip.') }}
            </p>
        </div>
    @else
        <div class="card">
            <h2>{{ __('You rated this passenger') }}</h2>
            <p style="color:var(--warn); letter-spacing:2px; margin:0 0 6px">
                {{ str_repeat('★', $ride->passenger_rating) }}
            </p>
            @if (filled($ride->passenger_rating_comment))
                <p class="muted" style="margin:0">{{ $ride->passenger_rating_comment }}</p>
            @endif
        </div>
    @endif

    <a href="{{ route('rider.dashboard') }}" class="btn btn-ghost" id="backBtn">
        {{ __('Back to work') }}
    </a>

    <div id="msg" class="alert alert-error" style="display:none; margin-top:16px"></div>

@endsection

@section('script')
<script>
(function () {
    'use strict';

    var starsBox = document.getElementById('stars');
    if (!starsBox) { return; }

    var T = {
        sending: @json(__('Sending…')),
        failed:  @json(__('Something went wrong. Try again.')),
        thanks:  @json(__('Thanks for the rating.'))
    };

    var url     = @json(route('rider.trip.rate.passenger', $ride->id));
    var rateBtn = document.getElementById('rateBtn');
    var msg     = document.getElementById('msg');
    var chosen  = 0;

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

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.RIDER.csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                rating: chosen,
                comment: document.getElementById('rateComment').value || null
            })
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
            if (!res.ok || !res.d.ok) { throw new Error(res.d.message || T.failed); }
            document.getElementById('rateBox').innerHTML =
                '<p class="muted" style="margin:0">' + T.thanks + '</p>';
        })
        .catch(function (e) {
            msg.style.display = '';
            msg.textContent = e.message || T.failed;
            rateBtn.disabled = false;
            rateBtn.textContent = @json(__('Send rating'));
        });
    });
})();
</script>
@endsection
