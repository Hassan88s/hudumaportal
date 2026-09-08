@extends('frontend.user.buyer.buyer-master')
@section('site-title'){{ __('Earn — Refer & Rewards') }}@endsection

@section('content')
    <x-frontend.seller-buyer-preloader/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('frontend.user.buyer.partials.sidebar-two')

    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                @include('frontend.user.partials.earn-content', [
                    'transferRoute' => 'buyer.earn.transfer',
                    'walletRoute'   => 'buyer.wallet.history',
                ])
            </div>
        </div>
    </div>
@endsection
