@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Order Details') }}
@endsection
@section('style')
    <style>
        .line-top-contents{
            margin-top: 20px;
        }
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
      <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
  <div class="dashboard__body">
    <div class="dashboard__inner">
        <!-- Report section start-->
        <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
            <h3 class="text-xl font-semibold mb-4">Notification</h3>

            <div class="text-base text-gray-800 mb-2">
                {{ $notification->data['message'] ?? 'No message found.' }}
            </div>

            @if(isset($notification->data['data']['details']))
                <div class="text-sm text-gray-600">
                    {{ $notification->data['data']['details'] }}
                </div>
            @endif

         {{--   @if(isset($notification->data['data']['service_id']))
                <div class="text-sm text-gray-600 mt-2">
                    <strong>Service ID:</strong> {{ $notification->data['data']['service_id'] }}
                </div>
            @endif --}}
        </div>
    </div>
</div>


       
        @endsection
        @section('scripts')
  
@endsection