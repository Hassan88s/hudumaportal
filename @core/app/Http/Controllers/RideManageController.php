<?php

namespace App\Http\Controllers;

use App\Helpers\FlashMsg;
use App\Ride;
use App\Rider;
use App\Services\RideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin side of ride booking: who may carry passengers, and what a ride costs.
 */
class RideManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ride-dashboard',     ['only' => ['dashboard']]);
        $this->middleware('permission:ride-rider-list',    ['only' => ['riders', 'riderDetails']]);
        $this->middleware('permission:ride-rider-approve', ['only' => ['approve', 'reject', 'suspend', 'restore']]);
        $this->middleware('permission:ride-settings',      ['only' => ['settings', 'settingsUpdate']]);
        $this->middleware('permission:ride-trip-list',     ['only' => ['trips', 'tripDetails']]);
        $this->middleware('permission:ride-trip-resolve',  ['only' => ['resolveTrip']]);
    }

    /* ------------------------------------------------------------ dashboard */

    /**
     * One screen that answers "how is ride booking doing".
     *
     * Everything here is a figure somebody acts on: money taken, riders
     * waiting to be approved, trips running right now. The lists are short on
     * purpose — the Riders and Trips screens are where the work gets done.
     */
    public function dashboard()
    {
        $rides = app(RideService::class);

        // A trip nobody accepted has to be timed out before it is counted, or
        // "running now" includes trips that are already over.
        $rides->expireStale();

        return view('backend.pages.ride.dashboard', [
            'enabled'   => get_static_option('ride_enabled') == '1',
            'currency'  => $rides->currency(),
            'periods'   => $this->moneyByPeriod(),
            'now'       => $this->liveCounts(),
            'attention' => $this->needsAttention($rides),
            'top'       => $this->topRiders(),
            'recent'    => Ride::with(['passenger', 'rider.user'])
                ->orderByDesc('id')->limit(8)->get(),
            'labels'    => $this->statusLabels(),
            'fares'     => [
                'commission' => (float) get_static_option('ride_commission_percent'),
                'base'       => (float) get_static_option('ride_base_fare'),
                'per_km'     => (float) get_static_option('ride_price_per_km'),
                'per_min'    => (float) get_static_option('ride_price_per_minute'),
                'radius'     => (float) get_static_option('ride_search_radius_km'),
            ],
        ]);
    }

    /**
     * Trips and money over today, the last week and the last month.
     *
     * Money is only ever counted from completed trips, because a fare still
     * being held is not revenue yet.
     */
    protected function moneyByPeriod(): array
    {
        $windows = [
            __('Today')        => now()->startOfDay(),
            __('Last 7 days')  => now()->subDays(7),
            __('Last 30 days') => now()->subDays(30),
            __('All time')     => null,
        ];

        $out = [];

        foreach ($windows as $label => $since) {
            $base = Ride::query()->when($since, fn ($q) => $q->where('created_at', '>=', $since));
            $done = (clone $base)->where('status', Ride::COMPLETED);

            $out[$label] = [
                'trips'      => (clone $base)->count(),
                'completed'  => (clone $done)->count(),
                'fares'      => (float) (clone $done)->sum('fare'),
                'commission' => (float) (clone $done)->sum('commission'),
                'earnings'   => (float) (clone $done)->sum('rider_earning'),
                'refunded'   => (float) (clone $base)->sum('refunded_amount'),
            ];
        }

        return $out;
    }

    /** What is happening on the road this minute. */
    protected function liveCounts(): array
    {
        return [
            'searching' => Ride::where('status', Ride::SEARCHING)->count(),
            'heading'   => Ride::whereIn('status', [Ride::ACCEPTED, Ride::ARRIVED])->count(),
            'ongoing'   => Ride::where('status', Ride::ONGOING)->count(),
            'online'    => Rider::where('status', Rider::APPROVED)->where('is_online', 1)->count(),
            'approved'  => Rider::where('status', Rider::APPROVED)->count(),
        ];
    }

    /**
     * The things waiting on an admin.
     *
     * Each one is a count with somewhere to go, so a zero means there is
     * nothing to do rather than nothing to see.
     */
    protected function needsAttention(RideService $rides): array
    {
        $threshold = $rides->lowRatingThreshold();

        return [
            [
                'label' => __('Applications waiting'),
                'count' => Rider::where('status', Rider::PENDING)->count(),
                'url'   => route('admin.ride.riders', ['status' => Rider::PENDING]),
            ],
            [
                'label' => __('Poorly rated riders'),
                'count' => Rider::where('rating_count', '>=', 5)
                    ->whereRaw('(rating_sum / rating_count) < ?', [$threshold])
                    ->count(),
                'url'   => route('admin.ride.riders', ['poor' => 1]),
            ],
            [
                'label' => __('Nobody accepted, last 7 days'),
                'count' => Ride::where('status', Ride::EXPIRED)
                    ->where('created_at', '>=', now()->subDays(7))->count(),
                'url'   => route('admin.ride.trips', ['status' => Ride::EXPIRED]),
            ],
            [
                // A trip open far longer than any trip should be is the one
                // case support has to step into.
                'label' => __('Open over 6 hours'),
                'count' => Ride::whereIn('status', Ride::LIVE)
                    ->where('requested_at', '<', now()->subHours(6))->count(),
                'url'   => route('admin.ride.trips'),
            ],
        ];
    }

    /** Busiest riders over the last 30 days, by trips carried. */
    protected function topRiders()
    {
        return Ride::query()
            ->selectRaw('rider_id, COUNT(*) AS trips, SUM(rider_earning) AS earned')
            ->where('status', Ride::COMPLETED)
            ->where('completed_at', '>=', now()->subDays(30))
            ->whereNotNull('rider_id')
            ->groupBy('rider_id')
            ->orderByDesc('trips')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $rider = Rider::with('user')->find($row->rider_id);

                return [
                    'id'     => (int) $row->rider_id,
                    'name'   => $rider ? ($rider->full_name ?: optional($rider->user)->name) : __('Removed'),
                    'trips'  => (int) $row->trips,
                    'earned' => (float) $row->earned,
                    'rating' => $rider ? $rider->averageRating() : null,
                ];
            });
    }

    /* --------------------------------------------------------------- riders */

    public function riders(Request $request)
    {
        $status = $request->query('status');

        $threshold = app(RideService::class)->lowRatingThreshold();

        /*
         * "poor" is its own filter rather than a status, because a badly rated
         * rider is still an approved one — the point is to find them, not to
         * change what they are.
         */
        $poor = $request->query('poor') == '1';

        $riders = Rider::with('user')
            ->when($status !== null && $status !== '', fn ($q) => $q->where('status', (int) $status))
            ->when($poor, fn ($q) => $q->where('rating_count', '>=', 5)
                ->whereRaw('(rating_sum / rating_count) < ?', [$threshold]))
            ->when($poor, fn ($q) => $q->orderByRaw('(rating_sum / rating_count) ASC'))
            ->when(! $poor, fn ($q) => $q
                ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Rider::PENDING])  // waiting first
                ->orderByDesc('submitted_at'))
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'pending'   => Rider::where('status', Rider::PENDING)->count(),
            'approved'  => Rider::where('status', Rider::APPROVED)->count(),
            'rejected'  => Rider::where('status', Rider::REJECTED)->count(),
            'suspended' => Rider::where('status', Rider::SUSPENDED)->count(),
            'online'    => Rider::where('status', Rider::APPROVED)->where('is_online', 1)->count(),
        ];

        $counts['poor'] = Rider::where('rating_count', '>=', 5)
            ->whereRaw('(rating_sum / rating_count) < ?', [$threshold])
            ->count();

        return view('backend.pages.ride.riders',
            compact('riders', 'counts', 'status', 'poor', 'threshold'));
    }

    public function riderDetails($id)
    {
        $rider = Rider::with('user')->findOrFail($id);
        $rides = app(RideService::class);

        return view('backend.pages.ride.rider-details', [
            'rider'     => $rider,
            'breakdown' => $rides->ratingBreakdown($rider),
            'ratings'   => $rides->recentRatings($rider),
            'threshold' => $rides->lowRatingThreshold(),
        ]);
    }

    public function approve($id)
    {
        $rider = Rider::findOrFail($id);

        // Approving an incomplete application would put a rider on the road
        // without the papers the approval is supposed to confirm.
        $missing = $rider->missingDocuments();
        if ($missing) {
            return redirect()->back()->with(FlashMsg::item_delete(
                __('This application is missing documents: ') . implode(', ', $missing)
            ));
        }

        $rider->update([
            'status'           => Rider::APPROVED,
            'rejection_reason' => null,
            'approved_at'      => now(),
            'approved_by'      => Auth::guard('admin')->id(),
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider approved.')));
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => __('Say why, so the rider knows what to fix.'),
        ]);

        $rider = Rider::findOrFail($id);

        $rider->update([
            'status'           => Rider::REJECTED,
            'rejection_reason' => $request->rejection_reason,
            'is_online'        => 0,   // a rejected rider must leave the map at once
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider rejected.')));
    }

    public function suspend(Request $request, $id)
    {
        $rider = Rider::findOrFail($id);

        $rider->update([
            'status'           => Rider::SUSPENDED,
            'rejection_reason' => $request->input('rejection_reason'),
            'is_online'        => 0,
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider suspended.')));
    }

    public function restore($id)
    {
        $rider = Rider::findOrFail($id);

        $rider->update([
            'status'           => Rider::APPROVED,
            'rejection_reason' => null,
            'approved_at'      => $rider->approved_at ?? now(),
            'approved_by'      => $rider->approved_by ?? Auth::guard('admin')->id(),
        ]);

        return redirect()->back()->with(FlashMsg::item_new(__('Rider restored.')));
    }

    /* ------------------------------------------------------------- settings */

    /** Option name => validation rule. The list is the schema. */
    protected function settingFields(): array
    {
        return [
            'ride_enabled'            => 'nullable|boolean',
            'ride_base_fare'          => 'required|numeric|min:0',
            'ride_price_per_km'       => 'required|numeric|min:0',
            'ride_price_per_minute'   => 'required|numeric|min:0',
            'ride_minimum_fare'       => 'required|numeric|min:0',
            'ride_commission_percent' => 'required|numeric|min:0|max:100',
            'ride_search_radius_km'   => 'required|numeric|min:0.5|max:100',
            'ride_request_timeout'    => 'required|integer|min:5|max:300',
            'ride_cancellation_fee'   => 'required|numeric|min:0',
            'ride_road_factor'        => 'required|numeric|min:1|max:3',
            'ride_avg_speed_kmh'      => 'required|numeric|min:5|max:120',
            'ride_fare_rounding'      => 'required|numeric|min:1|max:10000',
            'ride_low_rating_threshold' => 'required|numeric|min:1|max:5',
        ];
    }

    public function settings()
    {
        $settings = [];
        foreach (array_keys($this->settingFields()) as $name) {
            $settings[$name] = get_static_option($name);
        }

        return view('backend.pages.ride.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $request->validate($this->settingFields(), [
            'ride_commission_percent.max' => __('Commission cannot be more than the whole fare.'),
        ]);

        foreach (array_keys($this->settingFields()) as $name) {
            // An unticked checkbox sends nothing, which has to mean off rather
            // than "leave it as it was".
            $value = $name === 'ride_enabled'
                ? ($request->boolean($name) ? '1' : '0')
                : $request->input($name);

            update_static_option($name, $value);
        }

        return redirect()->back()->with(FlashMsg::item_new(__('Ride settings saved.')));
    }

    /* ---------------------------------------------------------------- trips */

    public function trips(Request $request)
    {
        app(RideService::class)->expireStale();

        $status = $request->query('status');
        $from   = $request->query('from');
        $to     = $request->query('to');

        $base = Ride::query()
            ->when($status !== null && $status !== '', fn ($q) => $q->where('status', (int) $status))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to));

        $trips = (clone $base)
            ->with(['passenger', 'rider.user'])
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        // Totals follow the same filter as the list, so the figures always
        // describe exactly what is on screen.
        $settled = (clone $base)->where('status', Ride::COMPLETED);

        $totals = [
            'trips'      => (clone $base)->count(),
            'completed'  => (clone $settled)->count(),
            'fares'      => (float) (clone $settled)->sum('fare'),
            'commission' => (float) (clone $settled)->sum('commission'),
            'earnings'   => (float) (clone $settled)->sum('rider_earning'),
            'refunded'   => (float) (clone $base)->sum('refunded_amount'),
            'live'       => Ride::live()->count(),
        ];

        return view('backend.pages.ride.trips', [
            'trips'    => $trips,
            'totals'   => $totals,
            'status'   => $status,
            'from'     => $from,
            'to'       => $to,
            'currency' => app(RideService::class)->currency(),
            'labels'   => $this->statusLabels(),
        ]);
    }

    public function tripDetails($id)
    {
        $ride = Ride::with(['passenger', 'rider.user'])->findOrFail($id);

        $rides = app(RideService::class);

        return view('backend.pages.ride.trip-details', [
            'ride'      => $ride,
            'standing'  => $rides->passengerStanding((int) $ride->passenger_id),
            'threshold' => $rides->lowRatingThreshold(),
            'currency'  => $rides->currency(),
        ]);
    }

    /**
     * End a trip that has stalled.
     *
     * Only ever reached for a trip that is still open: once a trip has an
     * ending, support has nothing left to decide.
     */
    public function resolveTrip(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        $ride = Ride::findOrFail($id);

        try {
            $ride = app(RideService::class)->forceClose($ride, $request->input('reason'));
        } catch (\App\Exceptions\RideException $e) {
            return redirect()->back()->with(FlashMsg::item_delete($e->getMessage()));
        }

        return redirect()->back()->with(FlashMsg::item_new(__('Trip closed and :amount returned to the passenger.', [
            'amount' => app(RideService::class)->currency() . ' ' . number_format($ride->refunded_amount, 0),
        ])));
    }

    /** Trip statuses as the filter tabs need them. */
    protected function statusLabels(): array
    {
        return [
            Ride::SEARCHING           => __('Looking for a rider'),
            Ride::ACCEPTED            => __('Rider on the way'),
            Ride::ARRIVED             => __('Rider waiting'),
            Ride::ONGOING             => __('On the way'),
            Ride::COMPLETED           => __('Completed'),
            Ride::CANCELLED_PASSENGER => __('Cancelled by passenger'),
            Ride::CANCELLED_RIDER     => __('Cancelled by rider'),
            Ride::EXPIRED             => __('No rider available'),
        ];
    }
}
