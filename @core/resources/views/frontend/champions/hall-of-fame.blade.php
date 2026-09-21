@extends('frontend.frontend-master')
@section('site-title'){{ __('Huduma Champions — Hall of Fame') }}@endsection

@section('style')
    @include('frontend.champions._style')
@endsection

@section('content')
<div class="hc-page">
    <section class="hc-hero">
        <div class="container">
            <span class="kicker">🏆 {{ __('Huduma Champions') }}</span>
            <h1>{{ __('Hall of') }} <span>{{ __('Fame') }}</span></h1>
            <p class="sub">{{ __('Every month one provider and one client are crowned Huduma Champion. Their names stay here permanently.') }}</p>
            @include('frontend.champions._tabs', ['active' => 'hall'])
        </div>
    </section>

    <section class="hc-body">
        <div class="container">
            <div class="hc-card" style="padding:0">
                <div class="hc-scroll">
                    <table class="hc-table">
                        <thead><tr><th>{{ __('Month') }}</th><th>{{ __('Provider Champion') }}</th><th>{{ __('Client Champion') }}</th></tr></thead>
                        <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td><strong>{{ $row->season }}</strong></td>
                                <td>{{ $row->provider ?? '—' }}</td>
                                <td>{{ $row->client ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="hc-empty">{{ __('The first Huduma Champions will be announced after the first season closes.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
