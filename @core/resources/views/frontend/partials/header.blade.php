<!DOCTYPE html>
<html lang="{{get_user_lang()}}" dir="{{get_user_lang_direction()}}">
<head>
   @if(!empty(get_static_option('site_google_analytics')))
        {!! get_static_option('site_google_analytics') !!}
    @endif
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {!! render_favicon_by_id(get_static_option('site_favicon')) !!}
       @php
        $custom_body_font_get = \App\CustomFontImport::select('status','file','path')->where('status', 1)->latest()->first();
        $custom_heading_font_get = \App\CustomFontImport::select('status','file','path')->where('status', 2)->latest()->first();
         $adsense_code = \App\StaticOption::select('option_value')->where('option_name', 'google_adsense_code')->first();
       @endphp
       <style>
           .seller-verified{
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 20px;
            width: 20px;
            background-color: var(--main-color-one);
            color: #fff;
            border-radius: 50%;
        }
        .profile-flex-content .profile-contents .title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Tooltip container */
        .tooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: black;
            color: #fff;
            text-align: center;
            padding: 5px 0;
            border-radius: 6px;
            position: absolute;
            z-index: 1;
        }
        .tooltip:hover .tooltiptext {
            visibility: visible;
        }

       </style>
       <style>
  /* Hide Google Translate bar */
  .goog-te-banner-frame {
    display: none !important;
  }
  .goog-te-menu-frame {
    display: none !important;
  }
  .goog-te-gadget-icon {
    display: none !important;
  }
  /* Hide the iframe used for Google Translate */
  iframe {
    display: none !important;
  }
 body {
    margin-top: 0 !important; /* Adjust if necessary */
  }
</style>

       @if(!empty($custom_body_font_get) || !empty($custom_heading_font_get))
           <style>
               /*heading font*/
               @font-face {
                   font-family: {{optional($custom_heading_font_get)->file}};
                   src: url('{{optional($custom_heading_font_get)->path}}') format('woff');
                   font-weight: normal;
                   font-style: normal;
                   font-display: swap;
               }
               /*body font*/
               @font-face {
                   font-family: {{optional($custom_body_font_get)->file}};
                   src: url('{{optional($custom_body_font_get)->path}}') format('woff');
                   font-weight: normal;
                   font-style: normal;
                   font-display: swap;
               }
               :root {
                   --heading-font: '{{optional($custom_heading_font_get)->file}}', sans-serif !important;
                   --body-font: '{{optional($custom_body_font_get)->file}}', sans-serif !important;
               }
               #all_search_result {
                   position: absolute; /* or "fixed" depending on your requirement */
                   top: 0;
                   left: 0;
                   background-color: white; /* Optional: Set a background color to distinguish the data */
                   padding: 10px; /* Optional: Add some padding for better visibility */
                   z-index: 9999; /* A higher value to bring it above other elements */
               }
                  
           </style>
       @else
        {!! load_google_fonts() !!}
       @endif

</script>
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>

@auth
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "c1ba95cf-fa2e-4b7d-8be3-de039b9cc7f0",
    });

    const externalId = "{{ Auth::user()->id }}";

   
   await OneSignal.login(externalId);
   
  });
</script>
@endauth

           <!--new css load -->
           <link rel=icon href="favicons.ico" sizes="16x16" type="icon/ico">
       <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/animate.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/bootstrap.min.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/fontawesome.min.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/flaticon.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/slick.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/line-awesome.min.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/select2.min.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/flatpickr.min.css')}}">
           {{--  // booststrap 4--}}
           <link rel="stylesheet" href="{{asset('assets/frontend/css/nice-select.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/css/jquery.ihavecookies.css')}}">
           <link rel="stylesheet" href="{{asset('assets/common/css/toastr.min.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/css/helpers.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/css/style.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/css/dynamic-style.css')}}">
           <link rel="stylesheet" href="{{asset('assets/frontend/theme-two/css/02_style.css')}}">
           <!--adsense code-->
         <?= $adsense_code->option_value; ?>
    @if( get_user_lang_direction() === 'rtl')
    <link rel="stylesheet" href="{{asset('assets/common/css/rtl.css')}}">
    @endif
    <link rel="canonical" href="{{canonical_url()}}" />
    @php
    $page_post = isset($page_post) ? $page_post : [];
    $page_type = isset($page_type) ? $page_post : [];
    @endphp
    @include('frontend.partials.root-style')
    @yield('style')
    @if(request()->routeIs('homepage'))
           {!! render_site_meta() !!}
     @elseif( request()->routeIs('frontend.dynamic.page') && $page_type === 'page' )
           {!! render_site_title(optional($page_post)->title ) !!}
           {!! render_site_meta() !!}
    @else
        @yield('page-meta-data')
    @endif
 @if(!empty( get_static_option('site_third_party_tracking_code')))
 {!! get_static_option('site_third_party_tracking_code') !!}
 @endif

</head>
<body class="__qixer">
{{-- DEV environment banner — only visible when APP_ENV != production --}}
@if(!app()->environment('production'))
    <div style="position:fixed;bottom:12px;left:12px;z-index:9999;background:#ff8a54;color:#fff;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:700;font-family:system-ui,sans-serif;letter-spacing:.5px;box-shadow:0 4px 12px rgba(0,0,0,.15);pointer-events:none;">
        DEV
    </div>
@endif
@php
    $notice = \App\AdminNotice::where('status', 1)->where('expire_date', '>', now())->latest()->where('notice_for', 1)->first();
@endphp
@if($notice)
    <div class="notice_main_section">
        <div class="col-12">
            <div class="alert
         @if($notice->notice_type === 1) alert-danger
         @elseif($notice->notice_type === 2) alert-warning
         @elseif($notice->notice_type === 3) alert-success
         @elseif($notice->notice_type === 4) alert-info
         @endif d-flex  notice_for_frontend m-0 justify-content-center">
                <p> <strong class="text-dark">{{ __($notice->title) }}</strong>
                
              
<strong>{{ __($notice->description) }}</strong>
                </p>
            </div>
        </div>
    </div>
@endif
@include('frontend.partials.preloader')
@include('frontend.partials.navbar',$page_post)



