@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Spin & Win Rewards')}}
@endsection
@section('style')
    @include('frontend.user.spin-rewards._style')
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        @include('frontend.user.spin-rewards._body')
    </div>
@endsection
@section('scripts')
    @include('frontend.user.spin-rewards._script')
@endsection
