<!DOCTYPE html>
@php
    $brand     = get_static_option('site_color') ?: '#ff6b2c';
    $siteTitle = get_static_option('site_title') ?: 'Huduma Portal';
    $headline  = get_static_option('coming_soon_headline') ?: __('Tanzania\'s services marketplace is almost here');
    $subline   = get_static_option('coming_soon_subheadline') ?: __('Connect with trusted local providers for anything you need — book services, hire professionals, and get work done, all in one place.');
    $note      = get_static_option('coming_soon_note') ?: __('Register today and we\'ll notify you the moment we launch.');
    $logoId    = get_static_option('site_logo');
    $logoUrl   = null;
    if (!empty($logoId)) { try { $a = \App\MediaUpload::find($logoId); if ($a && !empty($a->path)) $logoUrl = asset('assets/uploads/media-uploader/' . $a->path); } catch (\Throwable $e) {} }
    $features = [
        ['🔧', __('Book Local Services'), __('Find and book verified providers for home repairs, cleaning, beauty, tech and more.')],
        ['💼', __('Hire Professionals'), __('Post a job and receive proposals from skilled freelancers across Tanzania.')],
        ['🛡️', __('Verified & Secure'), __('Identity-checked providers, secure payments and authentic reviews you can trust.')],
        ['🏆', __('Rewards & Champions'), __('Earn points, climb the leaderboard and win monthly rewards as you use the platform.')],
    ];
@endphp
<html lang="{{ get_default_language() }}" dir="{{ get_user_lang_direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }} — {{ __('Coming Soon') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
        body{background:#0f1535;color:#e8ebf5;min-height:100vh;}
        .cs-top{background:radial-gradient(1200px 500px at 50% -10%, rgba(255,107,60,.25), transparent), #0f1535;padding:46px 20px 60px;text-align:center;}
        .cs-logo img{max-height:52px;width:auto;}
        .cs-logo .word{font-size:26px;font-weight:800;color:#fff;letter-spacing:.4px;}
        .cs-badge{display:inline-block;margin-top:34px;background:rgba(255,138,84,.15);color:#ffb892;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:8px 18px;border-radius:999px;}
        h1{margin:22px auto 0;max-width:760px;font-size:40px;line-height:1.2;font-weight:800;color:#fff;}
        .sub{margin:18px auto 0;max-width:620px;font-size:17px;line-height:1.7;color:#aeb6d6;}
        .cta{display:inline-flex;align-items:center;gap:8px;margin-top:30px;background:{{ $brand }};color:#fff;font-size:16px;font-weight:700;text-decoration:none;padding:15px 34px;border-radius:12px;box-shadow:0 10px 30px rgba(255,107,60,.35);transition:.2s;}
        .cta:hover{transform:translateY(-2px);}
        .cta-note{margin-top:14px;font-size:13px;color:#8a93bd;}
        .login-link{color:#ffb892;text-decoration:none;font-weight:600;}
        .cs-features{max-width:980px;margin:-30px auto 0;padding:0 20px 60px;display:grid;grid-template-columns:repeat(4,1fr);gap:18px;}
        .fcard{background:#171d42;border:1px solid #242c58;border-radius:16px;padding:26px 20px;text-align:center;}
        .fcard .ic{font-size:34px;}
        .fcard h3{margin:14px 0 8px;font-size:16px;color:#fff;font-weight:700;}
        .fcard p{font-size:13px;line-height:1.6;color:#9aa3c9;}
        .cs-foot{text-align:center;padding:26px 20px 40px;color:#6f789e;font-size:13px;line-height:1.8;border-top:1px solid #1c2348;}
        .cs-foot a{color:#aeb6d6;text-decoration:none;}
        @media(max-width:860px){.cs-features{grid-template-columns:repeat(2,1fr);} h1{font-size:30px;}}
        @media(max-width:480px){.cs-features{grid-template-columns:1fr;} h1{font-size:26px;} .sub{font-size:15px;}}
    </style>
</head>
<body>
    <div class="cs-top">
        <div class="cs-logo">
            @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $siteTitle }}">@else<span class="word">{{ $siteTitle }}</span>@endif
        </div>
        <div class="cs-badge">🚀 {{ __('Launching Soon') }}</div>
        <h1>{{ $headline }}</h1>
        <p class="sub">{{ $subline }}</p>
        <a href="{{ route('user.register') }}" class="cta">{{ __('Register & Join the Waitlist') }} →</a>
        <div class="cta-note">{{ $note }}<br>
            {{ __('Already registered?') }} <a href="{{ route('user.login') }}" class="login-link">{{ __('Sign in') }}</a>
        </div>
    </div>

    <div class="cs-features">
        @foreach($features as [$icon, $title, $text])
            <div class="fcard">
                <div class="ic">{{ $icon }}</div>
                <h3>{{ $title }}</h3>
                <p>{{ $text }}</p>
            </div>
        @endforeach
    </div>

    <div class="cs-foot">
        {{ $siteTitle }} · Morogoro, Tanzania<br>
        © {{ date('Y') }} {{ $siteTitle }}. {{ __('All rights reserved.') }}
    </div>
</body>
</html>
