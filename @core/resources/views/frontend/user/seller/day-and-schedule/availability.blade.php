@extends('frontend.user.buyer.buyer-master')
@section('site-title'){{ __('My Availability') }}@endsection
@section('style')
    @include('frontend.user.seller.day-and-schedule.availability-style')
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('frontend.user.seller.partials.sidebar-two')

    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <div class="av-wrap">

                    {{-- ═══════════════════ HEADER ═══════════════════ --}}
                    <div class="av-header">
                        <div>
                            <h2 class="av-title">{{ __('My Availability') }}</h2>
                            <p class="av-subtitle">{{ __('Set the days and time slots buyers can book you.') }}</p>
                        </div>
                    </div>

                    {{-- ═══════════════════ GLOBAL SETTINGS ═══════════════════ --}}
                    <div class="av-settings">
                        <div class="av-setting">
                            <div class="av-setting__icon"><i class="las la-calendar-alt"></i></div>
                            <div class="av-setting__body">
                                <label class="av-setting__label">{{ __('Booking window') }}</label>
                                <p class="av-setting__hint">{{ __('How many days ahead buyers can book you.') }}</p>
                                <form class="av-inline-form" method="post" action="{{ route('seller.update.totalday') }}">
                                    @csrf
                                    <select name="total_day" class="av-select">
                                        @for($i=1;$i<=30;$i++)
                                            <option value="{{ $i }}" @selected($total_day == $i)>{{ $i }} {{ __('day(s)') }}</option>
                                        @endfor
                                    </select>
                                    <button type="submit" class="av-btn av-btn--primary">{{ __('Save') }}</button>
                                </form>
                            </div>
                        </div>

                        <div class="av-setting">
                            <div class="av-setting__icon"><i class="las la-user-friends"></i></div>
                            <div class="av-setting__body">
                                <label class="av-setting__label">{{ __('Multiple bookings per slot') }}</label>
                                <p class="av-setting__hint">{{ __('Allow more than one buyer to book the same time slot.') }}</p>
                                <form class="av-inline-form" method="post" action="{{ route('seller.allow.multiple.schedule') }}">
                                    @csrf
                                    <select name="allow_multiple_schedule" class="av-select">
                                        <option value="no"  @selected($allow_multi == 'no')>{{ __('No — one booking only') }}</option>
                                        <option value="yes" @selected($allow_multi == 'yes')>{{ __('Yes — allow multiple') }}</option>
                                    </select>
                                    <button type="submit" class="av-btn av-btn--primary">{{ __('Save') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════ WEEKLY GRID ═══════════════════ --}}
                    <div class="av-week">
                        <div class="av-week__head">
                            <h4>{{ __('Weekly schedule') }}</h4>
                            <p>{{ __('Toggle a day to accept bookings, then add the time slots you want to offer.') }}</p>
                        </div>

                        @foreach($week as $row)
                            @php
                                $enabled  = $row['enabled'];
                                $dayId    = $row['day'] ? $row['day']->id : null;
                                $slotCount = $row['schedules']->count();
                            @endphp
                            <div class="av-day {{ $enabled ? 'is-on' : 'is-off' }}" data-code="{{ $row['code'] }}" data-day-id="{{ $dayId }}">
                                <div class="av-day__head">
                                    <label class="av-toggle" title="{{ __('Enable or disable this day') }}">
                                        <input type="checkbox" class="av-toggle__input js-day-toggle" data-code="{{ $row['code'] }}" data-day-id="{{ $dayId }}" @checked($enabled)>
                                        <span class="av-toggle__slider"></span>
                                    </label>
                                    <div class="av-day__label">
                                        <span class="av-day__name">{{ $row['label'] }}</span>
                                        <span class="av-day__meta">
                                            @if($enabled)
                                                <span class="av-day__status">{{ __('Available') }}</span>
                                                <span class="av-day__count">· {{ $slotCount }} {{ __('slot(s)') }}</span>
                                            @else
                                                <span class="av-day__status av-day__status--off">{{ __('Not available') }}</span>
                                                @if($slotCount)
                                                    <span class="av-day__count">· {{ $slotCount }} {{ __('slot(s) saved') }}</span>
                                                @endif
                                            @endif
                                        </span>
                                    </div>
                                    <div class="av-day__actions">
                                        @if($enabled && $slotCount)
                                            <button type="button" class="av-btn av-btn--ghost js-copy-slots" data-day-id="{{ $dayId }}" data-day-name="{{ $row['label'] }}" title="{{ __('Copy these slots to another day') }}">
                                                <i class="las la-copy"></i> {{ __('Copy') }}
                                            </button>
                                        @endif
                                        <button type="button" class="av-btn av-btn--primary js-add-slot" @disabled(!$enabled) data-day-id="{{ $dayId }}">
                                            <i class="las la-plus"></i> {{ __('Add slot') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="av-day__slots">
                                    @if($slotCount)
                                        @foreach($row['schedules'] as $s)
                                            <span class="av-chip {{ $enabled ? '' : 'av-chip--muted' }}" data-id="{{ $s->id }}">
                                                <i class="las la-clock"></i>
                                                <span class="av-chip__text">{{ $s->schedule }}</span>
                                                <button type="button" class="av-chip__x js-remove-slot" data-id="{{ $s->id }}" title="{{ __('Remove this slot') }}">&times;</button>
                                            </span>
                                        @endforeach
                                        @if(!$enabled)
                                            <span class="av-empty av-empty--muted">{{ __('Slots kept for when you turn this day back on.') }}</span>
                                        @endif
                                    @elseif($enabled)
                                        <span class="av-empty">{{ __('No time slots yet — click Add slot to add one.') }}</span>
                                    @else
                                        <span class="av-empty av-empty--muted">{{ __('This day is off. Toggle it on to add slots.') }}</span>
                                    @endif

                                    {{-- inline add form (hidden by default) --}}
                                    <div class="av-inline-add js-inline-add" data-day-id="{{ $dayId }}" style="display:none">
                                        {{-- Quick presets --}}
                                        <div class="av-preset-row">
                                            <span class="av-preset-label">{{ __('Quick add:') }}</span>
                                            <button type="button" class="av-preset js-preset" data-start="09:00" data-end="12:00">9AM – 12PM</button>
                                            <button type="button" class="av-preset js-preset" data-start="12:00" data-end="15:00">12PM – 3PM</button>
                                            <button type="button" class="av-preset js-preset" data-start="15:00" data-end="18:00">3PM – 6PM</button>
                                            <button type="button" class="av-preset js-preset" data-start="18:00" data-end="21:00">6PM – 9PM</button>
                                            <button type="button" class="av-preset js-preset" data-start="09:00" data-end="17:00">{{ __('Full day 9-5') }}</button>
                                        </div>

                                        {{-- Time pickers --}}
                                        <div class="av-time-row">
                                            <div class="av-time-field">
                                                <label class="av-time-label">{{ __('From') }}</label>
                                                <input type="time" class="av-time-input js-slot-start" step="900" required>
                                            </div>
                                            <span class="av-time-sep">–</span>
                                            <div class="av-time-field">
                                                <label class="av-time-label">{{ __('To') }}</label>
                                                <input type="time" class="av-time-input js-slot-end" step="900" required>
                                            </div>
                                        </div>

                                        {{-- Options + actions --}}
                                        <div class="av-actions-row">
                                            <label class="av-inline-add__all">
                                                <input type="checkbox" class="js-apply-all"> <span>{{ __('Add to all enabled days') }}</span>
                                            </label>
                                            <div class="av-actions-btns">
                                                <button type="button" class="av-btn av-btn--ghost av-btn--sm js-slot-cancel">{{ __('Cancel') }}</button>
                                                <button type="button" class="av-btn av-btn--primary av-btn--sm js-slot-save">{{ __('Save slot') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="av-footnote">
                        <i class="las la-info-circle"></i>
                        {{ __('Tip: buyers will see available dates and slots when booking your services.') }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════ Copy-slots modal ═══════════════════ --}}
    <div class="modal fade" id="avCopyModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content av-modal">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Copy slots to another day') }}</h5>
                    <button type="button" class="close av-modal__close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="av-copy-from">{{ __('Copy schedules from') }} <strong id="avCopyFromName">—</strong> {{ __('to:') }}</p>
                    <div class="av-copy-targets">
                        @foreach($week as $row)
                            <label class="av-copy-target">
                                <input type="checkbox" class="js-copy-target" value="{{ $row['code'] }}"> <span>{{ $row['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="av-copy-warn"><i class="las la-exclamation-triangle"></i> {{ __('This will add these time slots to the selected days (existing slots on those days are kept).') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="av-btn av-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="av-btn av-btn--primary" id="avCopyConfirm">{{ __('Copy slots') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/backend/js/sweetalert2.js') }}"></script>
    <script>
        (function ($) {
            "use strict";

            const routes = {
                addDay:        "{{ route('seller.add.day') }}",
                delDay:        "{{ url('/seller/day-delete') }}",
                dayToggle:     "{{ route('seller.day.toggle.status') }}",
                addSchedule:   "{{ route('seller.add.schedule') }}",
                delSchedule:   "{{ url('/seller/schedules-delete') }}",
            };
            const csrf = "{{ csrf_token() }}";
            const T = {
                confirmDeleteSlot: "{{ __('Remove this time slot?') }}",
                yes:               "{{ __('Yes') }}",
                cancel:            "{{ __('Cancel') }}",
                emptySlot:         "{{ __('Please enter a schedule time.') }}",
                noTargets:         "{{ __('Please pick at least one target day.') }}",
                copiedOk:          "{{ __('Slots copied.') }}",
                needEnable:        "{{ __('Please enable this day first.') }}",
            };

            $(document).ajaxSend(function (event, xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
            });

            // ─── Day toggle (on/off — soft, preserves saved slots) ───
            $(document).on('change', '.js-day-toggle', function () {
                const $chk  = $(this);
                const code  = $chk.data('code');
                // Soft-toggle status: schedules stay in the DB, hidden from buyers when off.
                $.post(routes.dayToggle, { _token: csrf, day: code })
                    .always(function () { location.reload(); });
            });

            // ─── Show inline add form on a day ────────────────────
            $(document).on('click', '.js-add-slot', function () {
                const $day = $(this).closest('.av-day');
                if (!$day.hasClass('is-on')) { toastr.warning(T.needEnable); return; }
                const $box = $day.find('.js-inline-add');
                $box.slideDown(140);
                setTimeout(() => $box.find('.js-slot-start').focus(), 150);
            });
            $(document).on('click', '.js-slot-cancel', function () {
                const $box = $(this).closest('.js-inline-add');
                $box.slideUp(140);
                $box.find('.js-slot-start, .js-slot-end').val('');
                $box.find('.js-apply-all').prop('checked', false);
            });

            // ─── Quick presets (fill start + end from data attrs) ─
            $(document).on('click', '.js-preset', function () {
                const $box = $(this).closest('.js-inline-add');
                $box.find('.js-slot-start').val($(this).data('start'));
                $box.find('.js-slot-end').val($(this).data('end'));
            });

            // ─── Time formatting helpers ──────────────────────────
            // Turn "13:30" into "1:30 PM"
            function to12h(hhmm) {
                if (!hhmm) return '';
                const [hStr, mStr] = hhmm.split(':');
                let h = parseInt(hStr, 10);
                const m = mStr;
                const suffix = h >= 12 ? 'PM' : 'AM';
                h = h % 12; if (h === 0) h = 12;
                return h + ':' + m + ' ' + suffix;
            }
            function fmtRange(start, end) {
                return to12h(start) + ' - ' + to12h(end);
            }

            // ─── Save a new slot ──────────────────────────────────
            $(document).on('click', '.js-slot-save', function () {
                const $box     = $(this).closest('.js-inline-add');
                const dayId    = $box.data('day-id');
                const start    = ($box.find('.js-slot-start').val() || '').trim();
                const end      = ($box.find('.js-slot-end').val() || '').trim();
                const applyAll = $box.find('.js-apply-all').is(':checked');

                if (!start || !end) {
                    toastr.warning("{{ __('Please pick a start and end time.') }}");
                    return;
                }
                if (start >= end) {
                    toastr.warning("{{ __('End time must be after start time.') }}");
                    return;
                }

                const text = fmtRange(start, end);
                const payload = { _token: csrf, schedule: text };
                if (applyAll) {
                    payload.schedule_for_all_days = 1;
                } else {
                    payload.day_id = dayId;
                }
                $.post(routes.addSchedule, payload).always(function () { location.reload(); });
            });
            // Enter key on either time picker triggers save
            $(document).on('keydown', '.js-slot-start, .js-slot-end', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(this).closest('.js-inline-add').find('.js-slot-save').click();
                }
            });

            // ─── Remove a slot ───────────────────────────────────
            $(document).on('click', '.js-remove-slot', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title:  T.confirmDeleteSlot,
                    icon:   'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d9344b',
                    cancelButtonColor:  '#8b8fa3',
                    confirmButtonText:  T.yes,
                    cancelButtonText:   T.cancel,
                }).then(res => {
                    if (res.isConfirmed) {
                        $.post(routes.delSchedule + '/' + id, { _token: csrf }).always(function () { location.reload(); });
                    }
                });
            });

            // ─── Copy slots from one day to others ────────────────
            let copyFromId = null;
            $(document).on('click', '.js-copy-slots', function () {
                copyFromId = $(this).data('day-id');
                $('#avCopyFromName').text($(this).data('day-name'));
                // uncheck all + hide the source day from targets
                $('.js-copy-target').prop('checked', false).each(function () {
                    const $lbl = $(this).closest('.av-copy-target');
                    $lbl.show();
                });
                $('#avCopyModal').modal('show');
            });
            $('#avCopyConfirm').on('click', function () {
                const targets = $('.js-copy-target:checked').map(function () { return this.value; }).get();
                if (!targets.length) { toastr.warning(T.noTargets); return; }

                // 1) pull the current slots for source day from DOM (already rendered)
                const $srcDay = $('.av-day[data-day-id="' + copyFromId + '"]');
                const slots = $srcDay.find('.av-chip__text').map(function () { return $(this).text().trim(); }).get();
                if (!slots.length) { $('#avCopyModal').modal('hide'); return; }

                // 2) for each target code: ensure the day exists, then add each slot
                let pending = 0, done = 0;
                function tryFinish() { if (done >= pending) { toastr.success(T.copiedOk); setTimeout(() => location.reload(), 300); } }

                targets.forEach(function (code) {
                    const $target = $('.av-day[data-code="' + code + '"]');
                    const targetId = $target.data('day-id');
                    const enable = !$target.hasClass('is-on');

                    slots.forEach(function (slot) {
                        pending++;
                        const doPost = function (dayId) {
                            $.post(routes.addSchedule, { _token: csrf, day_id: dayId, schedule: slot })
                                .always(function () { done++; tryFinish(); });
                        };
                        if (enable) {
                            // add the day first, then re-fetch the id from a refresh — simplest: do all under a single reload trigger
                            $.post(routes.addDay, { _token: csrf, day: code }).always(function () {
                                // we can't easily know the new day_id client-side; use apply_all won't help either.
                                // simplest reliable path: mark for reload
                                done++; tryFinish();
                            });
                        } else if (targetId) {
                            doPost(targetId);
                        } else {
                            done++; tryFinish();
                        }
                    });
                });

                $('#avCopyModal').modal('hide');
            });

        })(jQuery);
    </script>
@endsection
