@extends('backend.admin-master')

@section('site-title')
    {{ __('Riders') }}
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <x-msg.success/>
                        <x-msg.error/>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="header-title mb-0">{{ __('Riders') }}</h4>
                            @can('ride-settings')
                                <a href="{{ route('admin.ride.settings') }}" class="btn btn-primary">
                                    {{ __('Ride Settings') }}
                                </a>
                            @endcan
                        </div>

                        @php
                            $tabs = [
                                ''  => __('All'),
                                '0' => __('Waiting') . ' (' . $counts['pending'] . ')',
                                '1' => __('Approved') . ' (' . $counts['approved'] . ')',
                                '2' => __('Rejected') . ' (' . $counts['rejected'] . ')',
                                '3' => __('Suspended') . ' (' . $counts['suspended'] . ')',
                            ];
                        @endphp

                        <div class="mb-4">
                            @foreach ($tabs as $value => $label)
                                <a href="{{ route('admin.ride.riders') }}{{ $value === '' ? '' : '?status=' . $value }}"
                                   class="btn btn-sm {{ (string) $status === (string) $value ? 'btn-primary' : 'btn-outline-primary' }} mr-2 mb-2">
                                    {{ $label }}
                                </a>
                            @endforeach
                            <a href="{{ route('admin.ride.riders') }}?poor=1"
                               class="btn btn-sm {{ $poor ? 'btn-danger' : 'btn-outline-danger' }} mr-2 mb-2">
                                {{ __('Poorly rated') }} ({{ $counts['poor'] }})
                            </a>
                            <span class="badge badge-success ml-2">
                                {{ $counts['online'] }} {{ __('online now') }}
                            </span>
                        </div>

                        <div class="table-wrap table-responsive">
                            <table class="table text-center">
                                <thead class="text-capitalize">
                                <tr>
                                    <th>{{ __('Rider') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Plate') }}</th>
                                    <th>{{ __('Rating') }}</th>
                                    <th>{{ __('Applied') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($riders as $rider)
                                    <tr>
                                        <td>
                                            {{ $rider->full_name ?: optional($rider->user)->name }}
                                            <div class="text-muted small">{{ optional($rider->user)->email }}</div>
                                        </td>
                                        <td>{{ $rider->phone ?: '—' }}</td>
                                        <td>{{ $rider->bike_plate ?: '—' }}</td>
                                        <td>
                                            @if ($rider->rating_count)
                                                <span class="{{ $rider->isPoorlyRated($threshold) ? 'text-danger font-weight-bold' : '' }}">
                                                    {{ number_format($rider->averageRating(), 1) }}
                                                </span>
                                                <div class="text-muted small">{{ $rider->rating_count }}</div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $rider->submitted_at ? $rider->submitted_at->format('d/m/y') : '—' }}</td>
                                        <td>
                                            @php
                                                $class = [
                                                    \App\Rider::PENDING   => 'badge-warning',
                                                    \App\Rider::APPROVED  => 'badge-success',
                                                    \App\Rider::REJECTED  => 'badge-danger',
                                                    \App\Rider::SUSPENDED => 'badge-secondary',
                                                ][$rider->status] ?? 'badge-secondary';
                                            @endphp
                                            <span class="badge {{ $class }}">{{ $rider->statusLabel() }}</span>
                                            @if ($rider->isAvailable())
                                                <div class="small text-success mt-1">{{ __('online') }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ride.rider.details', $rider->id) }}"
                                               class="btn btn-sm btn-info">{{ __('Review') }}</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted py-4">
                                            {{ $poor ? __('No rider is rated that poorly.') : __('No riders yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $riders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
