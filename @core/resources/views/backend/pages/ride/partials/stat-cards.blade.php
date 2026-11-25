{{--
    Coloured totals for the ride screens.

    Both the ride dashboard and the trips list showed their figures as
    identical grey boxes, which made six numbers that mean six different
    things look like one block of noise. Colour here is not decoration: a
    tone is tied to what the figure means, and the same tone means the same
    thing on every ride screen.

        green   something finished well        amber   somebody is waiting
        blue    money in                       teal    something in motion
        purple  money we keep                  red     money going back out
        slate   a plain count, no verdict

    Expects $cards as a list of ['value', 'label', 'tone', 'icon'], and
    optionally $cardCol for the grid class when the default six-up is wrong.

    The styles are emitted once per request however many times this is
    included. They are deliberately scoped to .ride-stat: the admin stylesheet
    repaints .card for dark mode after @yield('style') has already run, so
    anything built on .card here would be overridden further down the page.
--}}
@once
    @php $rideStatDark = get_static_option('site_admin_dark_mode') == 'on'; @endphp
    <style>
        .ride-stat {
            display: flex;
            align-items: center;
            gap: 14px;
            height: 100%;
            padding: 18px 16px;
            border-radius: 12px;
            /* Tint and edge are both derived from --tone, so a new tone is one
               custom property rather than a new rule. */
            background: rgba(var(--tone), .10);
            border: 1px solid rgba(var(--tone), .22);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .ride-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(var(--tone), .18);
        }
        .ride-stat__icon {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgb(var(--tone));
            color: #fff;
            font-size: 22px;
            line-height: 1;
        }
        .ride-stat__icon i { display: block; }
        .ride-stat__text { min-width: 0; }
        .ride-stat__value {
            margin: 0;
            font-weight: 700;
            line-height: 1.15;
            color: rgb(var(--tone));
            /* Figures sit in a row and get compared down the column, so they
               need to line up rather than wander with the digits. */
            font-variant-numeric: tabular-nums;
            /* A wrapped figure reads as two numbers: 1,842,500 broken after
               the comma looked like 1,842,50 above a stray 0. It shrinks to
               fit the column instead, and the unit lives in the label. */
            white-space: nowrap;
            font-size: clamp(16px, 1.45vw, 22px);
        }
        .ride-stat__label {
            font-size: 12px;
            line-height: 1.3;
            color: {{ $rideStatDark ? 'rgba(255,255,255,.66)' : '#6b7280' }};
        }

        @media (max-width: 575.98px) {
            .ride-stat { padding: 14px 12px; gap: 10px; }
            .ride-stat__icon { width: 36px; height: 36px; font-size: 18px; }
            .ride-stat__value { font-size: 17px; }
        }

        .ride-stat--slate  { --tone: 100, 116, 139; }
        .ride-stat--green  { --tone:  29, 191, 115; }
        .ride-stat--blue   { --tone:  59, 130, 246; }
        .ride-stat--purple { --tone: 139,  92, 246; }
        .ride-stat--amber  { --tone: 245, 158,  11; }
        .ride-stat--red    { --tone: 239,  68,  68; }
        .ride-stat--teal   { --tone:  20, 184, 166; }
    </style>
@endonce

<div class="row">
    @foreach ($cards as $card)
        <div class="{{ $cardCol ?? 'col-xl-2 col-md-4 col-6' }} mb-3">
            <div class="ride-stat ride-stat--{{ $card['tone'] ?? 'slate' }}">
                <div class="ride-stat__icon">
                    <i class="las la-{{ $card['icon'] ?? 'circle' }}"></i>
                </div>
                <div class="ride-stat__text">
                    <div class="ride-stat__value">{{ $card['value'] }}</div>
                    <div class="ride-stat__label">{{ $card['label'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
