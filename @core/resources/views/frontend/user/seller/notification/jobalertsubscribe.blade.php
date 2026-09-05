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
            <h3 class="text-xl font-semibold mb-4">{{ __('Job Alert Subscription') }}</h3>
<div class="container">
    
    <p>{{ __('Select the categories you want to receive job alerts for') }}.</p>

    @if(session('success'))
        <div style="padding:10px; background:#d4edda; color:#155724; border-radius:5px; margin-bottom:10px;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('job-alert.store') }}" method="POST">
        @csrf

        <div style="display:flex; flex-wrap:wrap; gap:15px;">
            @foreach($categories as $category)
                <label style="display:flex; align-items:center; gap:8px; border:1px solid #ccc; padding:10px; border-radius:5px; min-width:200px;">
                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                        {{ in_array($category->id, $subscribed) ? 'checked' : '' }}>
                    {{ __($category->name) }}
                </label>
            @endforeach
        </div>

        <br>
        <button type="submit" style="background:#007bff; color:white; padding:10px 20px; border:none; border-radius:5px;">
            {{ __('Save Subscription') }}
        </button>
    </form>
</div>
           
        </div>
    </div>
</div>


       
        @endsection
        @section('scripts')
  
@endsection