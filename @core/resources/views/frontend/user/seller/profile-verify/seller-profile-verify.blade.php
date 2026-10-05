@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Seller Profile Verify')}}
@endsection
@section('style')
    <style>
        .single-dashboard-input .attachment-preview {
            width: 500px;
            height: 500px;
        }
        .notice-board {
            display: block;
            background-color: #fff;
            box-shadow: 0 0 20px #f2f2f2;
            border-left: 5px solid #dc3545;
            padding: 10px;
            border-radius: 10px;
            margin-top: 30px;
        }
    </style>
    <x-media.css/>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">

            <div class="dashboard__inner mt-3">
                <div class="dashboard_table__wrapper dashboard_border  padding-20 radius-10 bg-white">
                    <div class="row">
                        <div class="col-lg-12 margin-top-40">
                            <div class="edit-profile">
                                <div class="profile-info-dashboard mt-3">
                                    <h4 class="dashboards-title"> {{__('Profile Verify')}} </h4>
                                    @if(!is_null($seller_verify_info) && $seller_verify_info->status === 1)
                                        <div class="alert alert-success mt-3 mx-2" style="width: 170px">  <i class="las la-check-circle mx-2" style="font-size: 25px"></i>{{__('Profile Verified')}}
                                        </div>
                                    @else

                                        <div class="notice-board mt-3">
                                            <p class="text-danger">{{ __('Submit your original documents so that the admin can verify you. Once verified a badge will show in your profile that increase your order possibility') }}</p>
                                        </div>

                                        <div class="dashboard-profile-flex">
                                            <div class="dashboard-address-details">

                                                <div class="mt-5"> <x-msg.error/> </div>

                                                @php
                                                    $verifyDoc = function ($val) {
                                                        if (empty($val)) return null;
                                                        if (is_numeric($val)) return ['type' => 'media', 'val' => $val];
                                                        $isPdf = \Illuminate\Support\Str::endsWith(strtolower($val), '.pdf');
                                                        return ['type' => $isPdf ? 'pdf' : 'image', 'val' => $val];
                                                    };
                                                    $nid  = $verifyDoc(optional($seller_verify_info)->national_id);
                                                    $addr = $verifyDoc(optional($seller_verify_info)->address);
                                                @endphp
                                                <form action="{{route('seller.profile.verify')}}" method="post" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="single-dashboard-input">
                                                        <div class="row">
                                                            <div class="col-xxl-6 col-lg-6">
                                                                <div class="single-info-input margin-top-30">
                                                                    <div class="form-group">
                                                                        <label class="form-label">{{ __('National ID') }}</label>
                                                                        @if($nid)
                                                                            <div class="img-wrap mb-2">
                                                                                @if($nid['type'] === 'media') {!! render_image_markup_by_attachment_id($nid['val'],'','large') !!}
                                                                                @elseif($nid['type'] === 'pdf') <a href="{{ asset($nid['val']) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('View uploaded National ID (PDF)') }}</a>
                                                                                @else <img src="{{ asset($nid['val']) }}" alt="National ID" style="max-width:100%;height:auto;border-radius:8px"> @endif
                                                                            </div>
                                                                        @endif
                                                                        <input type="file" name="national_id" accept=".jpg,.jpeg,.png,.pdf" class="form-control">
                                                                        <small class="form-text text-muted">{{__('Upload your National ID — JPG, PNG or PDF (max 5 MB).')}}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-6 col-lg-6">
                                                                <div class="single-info-input margin-top-30">
                                                                    <div class="form-group">
                                                                        <label class="form-label">{{ __('Address Document') }}</label>
                                                                        @if($addr)
                                                                            <div class="img-wrap mb-2">
                                                                                @if($addr['type'] === 'media') {!! render_image_markup_by_attachment_id($addr['val'],'','large') !!}
                                                                                @elseif($addr['type'] === 'pdf') <a href="{{ asset($addr['val']) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('View uploaded Address Document (PDF)') }}</a>
                                                                                @else <img src="{{ asset($addr['val']) }}" alt="Address document" style="max-width:100%;height:auto;border-radius:8px"> @endif
                                                                            </div>
                                                                        @endif
                                                                        <input type="file" name="address" accept=".jpg,.jpeg,.png,.pdf" class="form-control">
                                                                        <small class="form-text text-muted">{{__('Upload a utility bill or similar address proof — JPG, PNG or PDF (max 5 MB).')}}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="dashboard__headerGlobal__btn">
                                                        <div class="btn-wrapper">
                                                            <button href="#" class="dashboard_table__title__btn btn-bg-1 radius-5" type="submit"> {{ __('Send Verify Request') }}</button>
                                                        </div>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-media.markup :type="'web'"/>
    <!-- Dashboard area end -->
@endsection
@section('scripts')
    <x-media.js :type="'web'"/>
@endsection