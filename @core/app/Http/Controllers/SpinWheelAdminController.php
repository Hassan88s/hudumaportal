<?php

namespace App\Http\Controllers;

use App\Helpers\FlashMsg;
use App\Services\SpinWheelService;
use App\SpinWheelSpin;
use Illuminate\Http\Request;

class SpinWheelAdminController extends Controller
{
    public function index(SpinWheelService $svc)
    {
        $settings = $svc->settings();
        $segments = $svc->segments();
        $recentWins = SpinWheelSpin::with('user')->where('is_win', 1)->latest()->limit(20)->get();
        $stats = [
            'spins' => SpinWheelSpin::count(),
            'wins'  => SpinWheelSpin::where('is_win', 1)->count(),
            'players' => SpinWheelSpin::distinct('user_id')->count('user_id'),
        ];
        $maxDiscountPercent = $svc->maxDiscountPercent();

        return view('backend.general-settings.spin-wheel', compact('settings', 'segments', 'recentWins', 'stats', 'maxDiscountPercent'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'spin_wheel_max_tries' => 'required|integer|min:1|max:20',
            'spin_wheel_code_expiry_days' => 'nullable|integer|min:0|max:365',
            'label'  => 'required|array|min:2',
            'label.*' => 'required|string|max:60',
            'type'   => 'required|array',
            'value'  => 'required|array',
            'weight' => 'required|array',
        ], [
            'label.min' => __('A wheel needs at least 2 segments.'),
        ]);

        // Settings
        update_static_option('spin_wheel_enabled', (int) $request->spin_wheel_enabled === 1 ? 1 : 0);
        update_static_option('spin_wheel_max_tries', (int) $request->spin_wheel_max_tries);
        update_static_option('spin_wheel_code_expiry_days', max(0, (int) $request->spin_wheel_code_expiry_days));
        update_static_option('spin_wheel_force_first_lose', (int) $request->spin_wheel_force_first_lose === 1 ? 1 : 0);
        update_static_option('spin_wheel_title', $request->spin_wheel_title ?: 'Spin & Win');
        update_static_option('spin_wheel_subtitle', $request->spin_wheel_subtitle ?: '');

        // Spin rewards are funded out of the admin commission, so a segment
        // worth more than the commission cannot be honoured. Refuse to save it
        // rather than let the wheel advertise a prize the checkout will trim.
        $maxPct = app(SpinWheelService::class)->maxDiscountPercent();
        if ($maxPct !== null) {
            $tooBig = [];
            foreach (($request->label ?? []) as $i => $label) {
                $type  = ($request->type[$i] ?? 'nothing');
                $value = (float) ($request->value[$i] ?? 0);
                if ($type === 'discount' && $value > $maxPct) {
                    $tooBig[] = trim($label) . ' (' . rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%)';
                }
            }

            if (! empty($tooBig)) {
                return back()->withInput()->with([
                    'msg'  => __('These rewards are above the :max% admin commission, which is what funds them: :list. Lower them, or raise the commission first.', [
                        'max'  => rtrim(rtrim(number_format($maxPct, 2, '.', ''), '0'), '.'),
                        'list' => implode(', ', $tooBig),
                    ]),
                    'type' => 'danger',
                ]);
            }
        }

        // Segments (parallel arrays from the repeatable rows)
        $segments = [];
        foreach ($request->label as $i => $label) {
            if (trim($label) === '') {
                continue;
            }
            $type = ($request->type[$i] ?? 'nothing') === 'discount' ? 'discount' : 'nothing';
            $segments[] = [
                'label'  => trim($label),
                'type'   => $type,
                'value'  => $type === 'discount' ? (float) ($request->value[$i] ?? 0) : 0,
                'weight' => max(0, (float) ($request->weight[$i] ?? 1)),
                'color'  => $request->color[$i] ?? '#5a6488',
                'emoji'  => $request->emoji[$i] ?? '',
            ];
        }
        update_static_option('spin_wheel_segments', json_encode(array_values($segments)));

        return back()->with(FlashMsg::settings_update('Spin & Win settings saved'));
    }
}
