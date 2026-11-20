@extends('backend.admin-master')

@section('site-title')
    {{ __('Rider') }}
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
                            <h4 class="header-title mb-0">
                                {{ $rider->full_name ?: optional($rider->user)->name }}
                            </h4>
                            <a href="{{ route('admin.ride.riders') }}" class="btn btn-secondary">{{ __('Back') }}</a>
                        </div>

                        @if ($rider->isPoorlyRated($threshold))
                            <div class="alert alert-danger">
                                <strong>{{ __('Poorly rated.') }}</strong>
                                {{ __('This rider averages :avg from :count ratings, below the :limit you set.', [
                                    'avg'   => number_format($rider->averageRating(), 1),
                                    'count' => $rider->rating_count,
                                    'limit' => number_format($threshold, 1),
                                ]) }}
                            </div>
                        @endif

                        @php $missing = $rider->missingDocuments(); @endphp
                        @if ($missing)
                            <div class="alert alert-warning">
                                {{ __('This application is missing:') }} <strong>{{ implode(', ', $missing) }}</strong>.
                                {{ __('It cannot be approved until they are uploaded.') }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('Details') }}</h5>
                                <table class="table table-sm">
                                    @php
                                        $rows = [
                                            __('Account')        => optional($rider->user)->email,
                                            __('Full name')      => $rider->full_name,
                                            __('Phone')          => $rider->phone,
                                            __('National ID')    => $rider->national_id,
                                            __('Licence number') => $rider->licence_number,
                                            __('Licence expiry') => optional($rider->licence_expiry)->format('d/m/Y'),
                                            __('Plate')          => $rider->bike_plate,
                                            __('Bike')           => $rider->bike_model,
                                            __('Applied')        => optional($rider->submitted_at)->format('d/m/Y H:i'),
                                            __('Status')         => $rider->statusLabel(),
                                            __('Trips')          => $rider->total_trips,
                                            __('Rating')         => $rider->rating_count ? $rider->averageRating() : '—',
                                        ];
                                    @endphp
                                    @foreach ($rows as $label => $value)
                                        <tr>
                                            <th style="width:45%">{{ $label }}</th>
                                            <td>{{ $value !== null && $value !== '' ? $value : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </table>

                                @if ($rider->rejection_reason)
                                    <div class="alert alert-danger">
                                        <strong>{{ __('Reason on file:') }}</strong> {{ $rider->rejection_reason }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('Ratings') }}</h5>

                                @if ($rider->rating_count)
                                    <p class="mb-2">
                                        <span style="color:#f0ad4e">{{ str_repeat('★', (int) round($rider->averageRating())) }}</span>
                                        <strong>{{ number_format($rider->averageRating(), 1) }}</strong>
                                        <span class="text-muted">
                                            {{ __('from :count ratings', ['count' => $rider->rating_count]) }}
                                        </span>
                                    </p>

                                    {{-- The shape matters as much as the average: a 4.0 made of
                                         fours is a different rider from one made of fives and ones. --}}
                                    @php $most = max(1, max($breakdown)); @endphp
                                    <table class="table table-sm mb-3">
                                        @foreach ($breakdown as $stars => $count)
                                            <tr>
                                                <th style="width:48px">{{ $stars }} ★</th>
                                                <td>
                                                    <div class="progress" style="height:8px">
                                                        <div class="progress-bar bg-warning"
                                                             style="width: {{ round(($count / $most) * 100) }}%"></div>
                                                    </div>
                                                </td>
                                                <td style="width:48px" class="text-right text-muted">{{ $count }}</td>
                                            </tr>
                                        @endforeach
                                    </table>

                                    <h6 class="mb-2">{{ __('What passengers said') }}</h6>
                                    <ul class="list-group mb-4">
                                        @foreach ($ratings as $rated)
                                            <li class="list-group-item">
                                                <div class="d-flex justify-content-between">
                                                    <span style="color:#f0ad4e">{{ str_repeat('★', $rated->rating) }}</span>
                                                    <small class="text-muted">
                                                        {{ optional($rated->completed_at ?? $rated->created_at)->format('d/m/y') }}
                                                    </small>
                                                </div>
                                                @if (filled($rated->rating_comment))
                                                    <div>{{ $rated->rating_comment }}</div>
                                                @endif
                                                <small class="text-muted">
                                                    {{ optional($rated->passenger)->name ?: __('passenger') }}
                                                    @can('ride-trip-list')
                                                        · <a href="{{ route('admin.ride.trip.details', $rated->id) }}">{{ __('trip #:id', ['id' => $rated->id]) }}</a>
                                                    @endcan
                                                </small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">{{ __('No ratings yet.') }}</p>
                                @endif

                                <h5 class="mb-3">{{ __('Documents') }}</h5>
                                @php
                                    $docs = [
                                        'national_id_photo' => __('National ID'),
                                        'licence_photo'     => __('Driving licence'),
                                        'bike_reg_photo'    => __('Bike registration'),
                                        'bike_photo'        => __('Photo of the bike'),
                                        'insurance_photo'   => __('Insurance'),
                                    ];
                                @endphp
                                <ul class="list-group">
                                    @foreach ($docs as $field => $label)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $label }}
                                            @if ($rider->{$field})
                                                <a href="{{ asset($rider->{$field}) }}" target="_blank" rel="noopener"
                                                   class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                                            @else
                                                <span class="text-muted small">{{ __('not uploaded') }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        @can('ride-rider-approve')
                            <hr class="my-4">
                            <div class="row">
                                @if ($rider->status !== \App\Rider::APPROVED)
                                    <div class="col-md-4 mb-3">
                                        <form method="POST"
                                              action="{{ $rider->status === \App\Rider::SUSPENDED
                                                    ? route('admin.ride.rider.restore', $rider->id)
                                                    : route('admin.ride.rider.approve', $rider->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-block"
                                                    @if ($missing) disabled title="{{ __('Documents are missing') }}" @endif>
                                                {{ $rider->status === \App\Rider::SUSPENDED ? __('Restore rider') : __('Approve rider') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                <div class="col-md-8">
                                    <form method="POST"
                                          action="{{ $rider->status === \App\Rider::APPROVED
                                                ? route('admin.ride.rider.suspend', $rider->id)
                                                : route('admin.ride.rider.reject', $rider->id) }}">
                                        @csrf
                                        <label>
                                            {{ $rider->status === \App\Rider::APPROVED
                                                ? __('Why are you suspending this rider?')
                                                : __('Why is this application refused? The rider sees this.') }}
                                        </label>
                                        <textarea name="rejection_reason" rows="2" class="form-control mb-2"
                                                  @if ($rider->status !== \App\Rider::APPROVED) required @endif>{{ old('rejection_reason') }}</textarea>
                                        @error('rejection_reason')
                                            <div class="text-danger small mb-2">{{ $message }}</div>
                                        @enderror
                                        <button type="submit" class="btn btn-danger">
                                            {{ $rider->status === \App\Rider::APPROVED ? __('Suspend rider') : __('Reject application') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
