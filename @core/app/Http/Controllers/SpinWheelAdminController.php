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
        return view('backend.general-settings.spin-wheel', compact('settings', 'segments', 'recentWins', 'stats'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'spin_wheel_max_tries' => 'required|integer|min:1|max:20',
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
        update_static_option('spin_wheel_force_first_lose', (int) $request->spin_wheel_force_first_lose === 1 ? 1 : 0);
        update_static_option('spin_wheel_title', $request->spin_wheel_title ?: 'Spin & Win');
        update_static_option('spin_wheel_subtitle', $request->spin_wheel_subtitle ?: '');

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
