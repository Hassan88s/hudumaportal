<?php

namespace App\Services;

use App\SpinWheelSpin;
use Illuminate\Support\Str;

/**
 * All Spin-&-Win rules live here so the frontend, the spin endpoint and the
 * admin page share one source of truth. The outcome is decided SERVER-SIDE
 * (never trusted from the browser), which is what makes "the first try always
 * lands on Try Again, the second try can win" reliable and un-cheatable.
 */
class SpinWheelService
{
    /** Shipped defaults, used until an admin saves their own config. */
    const DEFAULT_SEGMENTS = [
        ['label' => 'Try Again', 'type' => 'nothing',  'value' => 0,  'weight' => 28, 'color' => '#5a6488', 'emoji' => '🔁'],
        ['label' => '5% OFF',    'type' => 'discount', 'value' => 5,  'weight' => 24, 'color' => '#39d0c9', 'emoji' => '🏷️'],
        ['label' => '10% OFF',   'type' => 'discount', 'value' => 10, 'weight' => 18, 'color' => '#4c8dff', 'emoji' => '🔖'],
        ['label' => '15% OFF',   'type' => 'discount', 'value' => 15, 'weight' => 12, 'color' => '#a66bff', 'emoji' => '💎'],
        ['label' => 'Try Again', 'type' => 'nothing',  'value' => 0,  'weight' => 20, 'color' => '#6b7390', 'emoji' => '😅'],
        ['label' => '20% OFF',   'type' => 'discount', 'value' => 20, 'weight' => 8,  'color' => '#ff5d7a', 'emoji' => '🔥'],
        ['label' => '25% OFF',   'type' => 'discount', 'value' => 25, 'weight' => 4,  'color' => '#ffcf4d', 'emoji' => '🏆'],
        ['label' => '50% OFF',   'type' => 'discount', 'value' => 50, 'weight' => 2,  'color' => '#ff8a54', 'emoji' => '🎉'],
    ];

    public function settings(): array
    {
        return [
            'enabled'          => (int) get_static_option('spin_wheel_enabled') === 1,
            'max_tries'        => max(1, (int) (get_static_option('spin_wheel_max_tries') ?: 2)),
            'force_first_lose' => get_static_option('spin_wheel_force_first_lose') === '' ? true : ((int) get_static_option('spin_wheel_force_first_lose') === 1),
            'title'            => get_static_option('spin_wheel_title') ?: 'Spin & Win',
            'subtitle'         => get_static_option('spin_wheel_subtitle') ?: 'Give the wheel a spin for a chance to win a discount on your next order.',
        ];
    }

    /** Normalised segment list (always has the keys the wheel needs). */
    public function segments(): array
    {
        $raw = json_decode((string) get_static_option('spin_wheel_segments'), true);
        if (! is_array($raw) || count($raw) < 2) {
            $raw = self::DEFAULT_SEGMENTS;
        }

        $out = [];
        foreach ($raw as $s) {
            $type  = $s['type'] ?? 'nothing';
            $value = (float) ($s['value'] ?? 0);
            $out[] = [
                'label'  => (string) ($s['label'] ?? 'Reward'),
                'type'   => $type,
                'value'  => $value,
                'weight' => max(0, (float) ($s['weight'] ?? 1)),
                'color'  => $s['color'] ?? '#5a6488',
                'emoji'  => $s['emoji'] ?? ($type === 'discount' ? '🏷️' : '🔁'),
                'is_win' => $type !== 'nothing' && $value > 0,
            ];
        }
        return $out;
    }

    public function triesUsed(int $userId): int
    {
        return SpinWheelSpin::where('user_id', $userId)->count();
    }

    public function triesLeft(int $userId): int
    {
        return max(0, $this->settings()['max_tries'] - $this->triesUsed($userId));
    }

    /**
     * Run one spin for a user. Returns a structured result; never throws for
     * the ordinary "no tries left / disabled" cases.
     */
    public function spin(int $userId): array
    {
        $settings = $this->settings();
        $segments = $this->segments();

        if (! $settings['enabled']) {
            return ['ok' => false, 'reason' => 'disabled', 'tries_left' => 0];
        }

        $used = $this->triesUsed($userId);
        if ($used >= $settings['max_tries']) {
            return ['ok' => false, 'reason' => 'no_tries', 'tries_left' => 0];
        }

        $tryNumber = $used + 1;

        // First try is forced to a losing ("Try Again") segment when enabled.
        $forceLose = $settings['force_first_lose'] && $tryNumber === 1;
        $index     = $forceLose ? $this->pickLosingIndex($segments) : $this->pickWeightedIndex($segments);
        $segment   = $segments[$index];
        $isWin     = (bool) $segment['is_win'];

        $code = null;
        if ($isWin && $segment['type'] === 'discount') {
            $code = 'SPIN-' . strtoupper(Str::random(6));
        }

        SpinWheelSpin::create([
            'user_id'       => $userId,
            'try_number'    => $tryNumber,
            'segment_label' => $segment['label'],
            'result_type'   => $segment['type'],
            'result_value'  => $segment['value'],
            'is_win'        => $isWin,
            'code'          => $code,
        ]);

        return [
            'ok'         => true,
            'index'      => $index,
            'label'      => $segment['label'],
            'emoji'      => $segment['emoji'],
            'is_win'     => $isWin,
            'type'       => $segment['type'],
            'value'      => $segment['value'],
            'code'       => $code,
            'tries_left' => max(0, $settings['max_tries'] - $tryNumber),
        ];
    }

    /** Weighted random index across all segments. */
    protected function pickWeightedIndex(array $segments): int
    {
        $total = array_sum(array_map(fn ($s) => $s['weight'], $segments));
        if ($total <= 0) {
            return array_rand($segments);
        }
        $r = mt_rand(1, (int) round($total * 1000)) / 1000;
        foreach ($segments as $i => $s) {
            $r -= $s['weight'];
            if ($r <= 0) {
                return $i;
            }
        }
        return count($segments) - 1;
    }

    /** Weighted random index among the losing ("Try Again") segments only. */
    protected function pickLosingIndex(array $segments): int
    {
        $losing = array_values(array_filter(array_keys($segments), fn ($i) => ! $segments[$i]['is_win']));
        if (empty($losing)) {
            // No losing segment configured — fall back to a normal weighted pick.
            return $this->pickWeightedIndex($segments);
        }
        $subset = array_map(fn ($i) => $segments[$i], $losing);
        $pick   = $this->pickWeightedIndex($subset);
        return $losing[$pick];
    }
}
