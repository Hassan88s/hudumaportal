@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Edit Seller Profile')}}
@endsection
@section('style')
    <x-media.css/>
    <style>
        .pe-wrap{max-width:1050px}
        .pe-card{background:#fff;border:1px solid #eef0f3;border-radius:14px;padding:22px;margin-bottom:18px;box-shadow:0 2px 10px rgba(0,0,0,.03)}
        .pe-card h3{font-size:15px;font-weight:800;margin:0 0 4px;color:#1f2733;text-transform:uppercase;letter-spacing:.4px}
        .pe-card .hint{font-size:13px;color:#8892a0;margin:0 0 16px}
        .pe-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px 20px}
        .pe-field{display:flex;flex-direction:column}
        .pe-field.full{grid-column:1 / -1}
        .pe-field label{font-size:13px;font-weight:600;color:#1f2733;margin-bottom:6px}
        .pe-field .req{color:#e11d48}
        .pe-field .form--control,.pe-field select{width:100%}
        .pe-field .note{font-size:12px;color:#8892a0;margin-top:5px}
        .pe-media{display:flex;gap:26px;flex-wrap:wrap}
        .pe-media .img-wrap img{max-width:170px;height:auto;border-radius:12px;border:1px solid #eef0f3;display:block}
        .pe-media .media-upload-btn-wrapper{display:flex;flex-direction:column;align-items:flex-start;gap:10px}
        .pe-actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
        .pe-progress{display:flex;align-items:center;gap:10px;font-size:13px;color:#4b5563}
        .pe-progress .bar{width:150px;height:8px;border-radius:999px;background:#f1f3f5;overflow:hidden}
        .pe-progress .bar span{display:block;height:100%;background:linear-gradient(90deg,#ff8a54,#ff6b3d)}
        @media (max-width:768px){.pe-grid{grid-template-columns:1fr}}
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @include('frontend.user.seller.partials.sidebar-two')

    @php
        $me  = Auth::guard('web')->user();
        $pct = app(\App\Services\ChampionsService::class)->providerProfilePercent($me);
        // Social links are only editable on the plan that allows a website
        $sellerSub = \Modules\Subscription\Entities\SellerSubscription::where(['seller_id' => $me->id, 'status' => '1'])->first();
        $planId    = optional($sellerSub)->subscription_id
            ? optional(\Modules\Subscription\Entities\Subscription::find($sellerSub->subscription_id))->id
            : null;
        $showSocial = (string) $planId === '6';
    @endphp

    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <div class="pe-wrap">

                    <div class="pe-card" style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap">
                        <div>
                            <h3 style="margin-bottom:6px">{{ __('Edit Profile') }}</h3>
                            <p class="hint" style="margin:0">{{ __('A complete profile wins more bookings — and earns Huduma Champions points.') }}</p>
                        </div>
                        <div class="pe-progress">
                            <span>{{ __('Profile') }} <strong>{{ $pct }}%</strong></span>
                            <span class="bar"><span style="width:{{ $pct }}%"></span></span>
                            <span>{{ $pct >= 100 ? __('Complete') : __('80% = +50 HP · 100% = +100 HP') }}</span>
                        </div>
                    </div>

                    <div class="mt-2"> <x-msg.error/> </div>

                    <form action="{{ route('seller.profile.edit') }}" method="post">
                        @csrf

                        {{-- Photos --}}
                        <div class="pe-card">
                            <h3>{{ __('Photos') }}</h3>
                            <p class="hint">{{ __('A clear profile photo and a cover image make your page look professional.') }}</p>
                            <div class="pe-media">
                                <div>
                                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:8px">{{ __('Profile image') }}</label>
                                    <div class="media-upload-btn-wrapper">
                                        <div class="img-wrap">{!! render_image_markup_by_attachment_id($me->image, '', 'thumb') !!}</div>
                                        <input type="hidden" id="image" name="image" value="{{ $me->image }}">
                                        <button type="button" class="dashboard_table__title__btn btn-bg-1 radius-5 media_upload_form_btn"
                                                data-btntitle="{{ __('Select Image') }}" data-modaltitle="{{ __('Upload Image') }}"
                                                data-toggle="modal" data-target="#media_upload_modal">{{ __('Upload Profile Image') }}</button>
                                        <span class="note">{{ __('jpg, jpeg, png · recommended 500x443') }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:8px">{{ __('Cover image') }}</label>
                                    <div class="media-upload-btn-wrapper">
                                        <div class="img-wrap">{!! render_image_markup_by_attachment_id($me->profile_background) !!}</div>
                                        <input type="hidden" id="profile_background" name="profile_background" value="{{ $me->profile_background }}">
                                        <button type="button" class="dashboard_table__title__btn btn-bg-1 radius-5 media_upload_form_btn"
                                                data-btntitle="{{ __('Select Image') }}" data-modaltitle="{{ __('Upload Image') }}"
                                                data-toggle="modal" data-target="#media_upload_modal">{{ __('Upload Cover Image') }}</button>
                                        <span class="note">{{ __('jpg, jpeg, png · recommended 1394x315') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Basic details --}}
                        <div class="pe-card">
                            <h3>{{ __('Your details') }}</h3>
                            <p class="hint">{{ __('Clients see your name and can contact you on these details.') }}</p>
                            <div class="pe-grid">
                                <div class="pe-field">
                                    <label>{{ __('Full name') }} <span class="req">*</span></label>
                                    <input class="form--control" type="text" name="name" value="{{ old('name', $me->name) }}" placeholder="{{ __('Type Your Name') }}">
                                </div>
                                <div class="pe-field">
                                    <label>{{ __('Username') }} <span class="req">*</span></label>
                                    <input class="form--control" type="text" name="username" value="{{ old('username', $me->username) }}" placeholder="{{ __('Type Your Username') }}">
                                    <span class="note">{{ __('Your public profile link') }}: {{ url('/') }}/{{ $me->username }}</span>
                                </div>
                                <div class="pe-field">
                                    <label>{{ __('Email') }} <span class="req">*</span></label>
                                    <input class="form--control" type="email" name="email" value="{{ old('email', $me->email) }}" placeholder="{{ __('Type Your Email') }}">
                                </div>
                                <div class="pe-field">
                                    <label>{{ __('Phone number') }} <span class="req">*</span></label>
                                    <input class="form--control" type="text" name="phone" value="{{ old('phone', $me->phone) }}" placeholder="{{ __('Type Your Number') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="pe-card">
                            <h3>{{ __('Where you work') }}</h3>
                            <p class="hint">{{ __('Clients search by city and area, so keep these accurate.') }}</p>
                            <div class="pe-grid">
                                <div class="pe-field country-wrapper">
                                    <label>{{ __('Country') }} <span class="req">*</span></label>
                                    <select name="country_id" id="country">
                                        @if(!empty($countries))
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}" @selected($country->id == $me->country_id)>{{ $country->country }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="pe-field service_city_wrapper">
                                    <label>{{ __('Service city') }} <span class="req">*</span></label>
                                    <select name="service_city" id="service_city">
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" @selected($me->service_city == $city->id)>{{ $city->service_city }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="pe-field service_area_wrapper">
                                    <label>{{ __('Service area') }} <span class="req">*</span></label>
                                    <select name="service_area" id="service_area" class="get_service_city">
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" @selected($me->service_area == $area->id)>{{ $area->service_area }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="pe-field">
                                    <label>{{ __('Post code') }} <span class="req">*</span></label>
                                    <input class="form--control" type="text" name="post_code" value="{{ old('post_code', $me->post_code) }}" placeholder="{{ __('Type Post Code') }}">
                                </div>
                                <div class="pe-field full">
                                    <label>{{ __('Address') }} <span class="req">*</span></label>
                                    <input class="form--control" type="text" name="address" value="{{ old('address', $me->address) }}" placeholder="{{ __('Type Your Address') }}">
                                </div>
                            </div>
                        </div>

                        {{-- About --}}
                        <div class="pe-card">
                            <h3>{{ __('About you') }}</h3>
                            <p class="hint">{{ __('Tell clients what you do, your experience and the areas you cover.') }}</p>
                            <div class="pe-grid">
                                <div class="pe-field full">
                                    <label>{{ __('About') }} <span class="req">*</span></label>
                                    <textarea class="form--control textarea--form" name="about" rows="5" placeholder="{{ __('Example: Licensed electrician with 6 years experience in Arusha. Wiring, repairs and installations.') }}">{{ old('about', $me->about) }}</textarea>
                                </div>
                                <div class="pe-field">
                                    <label>{{ __('Tax number') }}</label>
                                    <input class="form--control" type="text" name="tax_number" value="{{ old('tax_number', $me->tax_number) }}" placeholder="{{ __('Type Tax Number') }}">
                                    <span class="note">{{ __('Optional') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Social links (plan feature) --}}
                        @if($showSocial)
                            <div class="pe-card">
                                <h3>{{ __('Website & social links') }}</h3>
                                <p class="hint">{{ __('Shown on your public profile.') }}</p>
                                <div class="pe-grid">
                                    <div class="pe-field">
                                        <label>{{ __('Website') }}</label>
                                        <input class="form--control" type="text" name="website_url" value="{{ old('website_url', $me->website_url) }}" placeholder="{{ __('Type Your Website Link') }}">
                                    </div>
                                    <div class="pe-field">
                                        <label>{{ __('Facebook') }}</label>
                                        <input class="form--control" type="text" name="fb_url" value="{{ old('fb_url', $me->fb_url) }}" placeholder="{{ __('Type Your Facebook Link') }}">
                                    </div>
                                    <div class="pe-field">
                                        <label>{{ __('Twitter / X') }}</label>
                                        <input class="form--control" type="text" name="tw_url" value="{{ old('tw_url', $me->tw_url) }}" placeholder="{{ __('Type Your Twitter Link') }}">
                                    </div>
                                    <div class="pe-field">
                                        <label>{{ __('LinkedIn') }}</label>
                                        <input class="form--control" type="text" name="li_url" value="{{ old('li_url', $me->li_url) }}" placeholder="{{ __('Type Your LinkedIn Link') }}">
                                    </div>
                                    <div class="pe-field">
                                        <label>{{ __('Instagram') }}</label>
                                        <input class="form--control" type="text" name="in_url" value="{{ old('in_url', $me->in_url) }}" placeholder="{{ __('Type Your Instagram Link') }}">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="pe-card pe-actions">
                            <button type="submit" class="dashboard_table__title__btn btn-bg-1 radius-5">{{ __('Save Changes') }}</button>
                            <a href="{{ route('seller.profile') }}" class="dashboard_table__title__btn radius-5" style="border:1px solid #d1d5db;color:#1f2733">{{ __('Cancel') }}</a>
                            <span class="note" style="color:#8892a0;font-size:12px">{{ __('Fields marked * are required.') }}</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <x-media.markup :type="'web'"/>
@endsection

@section('scripts')
    <x-media.js :type="'web'"/>
    <script type="text/javascript">
        (function() {
            "use strict";
            $(document).ready(function() {
                $('.select_activation').select2();

                // change country and get city
                $(document).on('change','#country' ,function() {
                    let country_id = $("#country").val();
                    $.ajax({
                        method: 'post',
                        url: "{{ route('user.country.city') }}",
                        data: {
                            country_id: country_id
                        },
                        success: function(res) {
                            if (res.status == 'success') {
                                var alloptions = "<option value=''>{{__('Select City')}}</option>";
                                var allList = "<li class='option' data-value=''>{{__('Select City')}}</li>";
                                var allCity = res.cities;
                                $.each(allCity, function(index, value) {
                                    alloptions += "<option value='" + value.id +
                                        "'>" + value.service_city + "</option>";
                                    allList += "<li class='option' data-value='" + value.id +
                                        "'>" + value.service_city + "</li>";
                                });
                                $("#service_city").html(alloptions);
                                $("#service_city").parent().find(".current").html("{{__('Select City')}}");
                                $("#service_city").parent().find(".list").html(allList);
                                $(".service_area_wrapper").find(".current").html("{{__('Select Area')}}");
                                $(".service_area_wrapper .list").html("");
                            }
                        }
                    })
                })

                $('#service_city').select2({
                  placeholder: `{{__('search city')}}`,
                  ajax: {
                    type: 'get',
                    url: "{{route('user.country.city.ajax.search')}}",
                    dataType: 'json',
                    data: function (params) {
                        let country_id = $("#country").val();
                        return {
                            q: params.term, // search term
                            country_id: country_id,
                        };
                    },
                    delay: 250,
                    processResults: function (response) {
                      return {
                        results:  $.map(response, function (item) {
                              return {
                                  text: item.service_city,
                                  id: item.id
                              }
                          })
                      };
                    },
                    cache: true
                  }
                });

                // select city and area
                $(document).on('change','#service_city', function() {
                    var city_id = $("#service_city").val();
                    $.ajax({
                        method: 'post',
                        url: "{{ route('user.city.area') }}",
                        data: {
                            city_id: city_id
                        },
                        success: function(res) {
                            if (res.status == 'success') {
                                var alloptions = "<option value=''>{{__('Select Area')}}</option>";
                                var allList = "<li data-value='' class='option'>{{__('Select Area')}}</li>";
                                var allArea = res.areas;
                                $.each(allArea, function(index, value) {
                                    alloptions += "<option value='" + value.id +
                                        "'>" + value.service_area + "</option>";
                                    allList += "<li class='option' data-value='" + value.id +
                                        "'>" + value.service_area + "</li>";
                                });

                                $("#service_area").html(alloptions);
                                $(".service_area_wrapper ul.list").html(allList);
                                $(".service_area_wrapper").find(".current").html("{{__('Select Area')}}");
                            }
                        }
                    })
                })

            });
        })(jQuery);
    </script>
@endsection
