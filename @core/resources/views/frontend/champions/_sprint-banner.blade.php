{{--
    PDF §33 — final-week visibility. Small floating card in the last 7 days
    of a season ("7 Days Left", "Final 72 Hours"). Dismissible for the day.
    Renders nothing if the public Champions pages are hidden or the program is off.
--}}
@php
    $hcDays = null;
    try {
        if (Route::has('champions.board')) {
            $hcSvc = app(\App\Services\ChampionsService::class);
            if ($hcSvc->enabled()) $hcDays = $hcSvc->daysRemaining();
        }
    } catch (\Throwable $e) { $hcDays = null; }
@endphp
@if(!is_null($hcDays) && $hcDays <= 7)
    @php
        $hcText = $hcDays <= 3
            ? __('Final 72 hours of this month\'s Huduma Champions!')
            : trans_choice(':n day left in Huduma Champions|:n days left in Huduma Champions', $hcDays, ['n' => $hcDays]);
    @endphp
    <div id="hc-sprint" style="display:none;position:fixed;left:16px;bottom:16px;z-index:999;max-width:320px;background:linear-gradient(135deg,#1f2733,#2d3748);color:#fff;border-radius:14px;padding:12px 38px 12px 14px;box-shadow:0 8px 24px rgba(0,0,0,.25);font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif">
        <button type="button" aria-label="{{ __('Close') }}" onclick="hcSprintClose()" style="position:absolute;top:6px;right:8px;background:none;border:0;color:rgba(255,255,255,.7);font-size:18px;line-height:1;cursor:pointer">×</button>
        <div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#fbbf24">🏆 {{ $hcDays <= 3 ? __('Final sprint') : __('Final week') }}</div>
        <div style="font-size:14px;font-weight:700;margin:3px 0 8px">{{ $hcText }}</div>
        <a href="{{ route('champions.board') }}" style="display:inline-block;background:#ff6b3d;color:#fff;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none">{{ __('See the leaderboard') }} →</a>
    </div>
    <script>
        (function () {
            var key = 'hc_sprint_closed_{{ now()->format('Ymd') }}';
            var closed = false;
            try { closed = localStorage.getItem(key) === '1'; } catch (e) {}
            if (!closed) document.getElementById('hc-sprint').style.display = 'block';
            window.hcSprintClose = function () {
                document.getElementById('hc-sprint').style.display = 'none';
                try { localStorage.setItem(key, '1'); } catch (e) {}
            };
        })();
    </script>
@endif
