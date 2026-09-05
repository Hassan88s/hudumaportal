<!doctype html>
@php
    $default_lang = get_default_language();
@endphp
<html lang="{{$default_lang}}" dir="{{ get_user_lang_direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('Admin Login OTP') }}</title>

    <style>
        .mail-container {
            max-width: 650px;
            margin: 0 auto;
            text-align: center;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .logo-wrapper {
            background-color: #111d5c;
            padding: 20px 0;
        }
        .content {
    /* NEW: more left/right padding */
    padding: 30px 28px;
    text-align: left;
    box-sizing: border-box;
}
        .otp-box{
            background: #f7f7f7;
            border: 1px dashed #ccc;
            padding: 18px;
            border-radius: 6px;
            margin: 15px 0;
            text-align: center;
        }
        .otp-code{
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 6px;
            color: #111d5c;
            margin: 10px 0;
        }
        .small-note{
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }
        footer {
            margin: 20px 0;
            font-size: 10px;
        }

        [dir="rtl"] .content { text-align:right !important; }
    </style>
</head>
<body>
<div class="mail-container">

    {{-- Logo --}}
    <div class="logo-wrapper">
        <a href="{{ url('/') }}">
            @php
                $site_logo = get_attachment_image_by_id(get_static_option('site_logo'), "full", false);
            @endphp

            @if (!empty($site_logo))
                <img src="{{ $site_logo['img_url'] }}" alt="{{ get_static_option('site_title') }}">
            @endif
        </a>
    </div>

    {{-- OTP Content --}}
    <div class="content">
        <h2 style="margin: 0 0 10px; pt-2"></h2>

        <p style="margin: 0 0 10px;">
            {{ __('Your Login OTP code is:') }}
        </p>

        <div class="otp-box">
            <div class="otp-code">{{ $data['otp'] ?? '' }}</div>
            <div class="small-note">
                {{ __('This code will expire in') }} {{ $data['minutes'] ?? 5 }} {{ __('minutes.') }}
            </div>
        </div>

        <p style="margin-top: 15px;">
            {{ __('If you did not request this, you can ignore this email.') }}
        </p>
    </div>

    {{-- Footer --}}
    <footer>
        {!! render_footer_copyright_text() !!}
    </footer>

</div>
</body>
</html>
