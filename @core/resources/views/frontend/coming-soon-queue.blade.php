<!DOCTYPE html>
@php
    $brand     = get_static_option('site_color') ?: '#ff6b2c';
    $siteTitle = get_static_option('site_title') ?: 'Huduma Portal';
    $logoId    = get_static_option('site_logo');
    $logoUrl   = null;
    if (!empty($logoId)) { try { $a = \App\MediaUpload::find($logoId); if ($a && !empty($a->path)) $logoUrl = asset('assets/uploads/media-uploader/' . $a->path); } catch (\Throwable $e) {} }
    $user = auth()->guard('web')->user();
    $name = $user ? ($user->username ?: $user->name) : '';
    $isSeller = $user && (int) $user->user_type === 0;
@endphp
<html lang="{{ get_default_language() }}" dir="{{ get_user_lang_direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }} — {{ __('You are on the list') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
        body{background:#0f1535;color:#e8ebf5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
        .q-card{background:#171d42;border:1px solid #242c58;border-radius:20px;max-width:540px;width:100%;padding:44px 36px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35);}
        .q-logo img{max-height:46px;width:auto;margin-bottom:8px;}
        .q-logo .word{font-size:22px;font-weight:800;color:#fff;}
        .q-check{width:76px;height:76px;margin:22px auto 6px;border-radius:50%;background:rgba(22,163,74,.15);display:flex;align-items:center;justify-content:center;font-size:40px;}
        h1{margin:18px 0 10px;font-size:26px;color:#fff;font-weight:800;}
        p{font-size:15px;line-height:1.7;color:#aeb6d6;margin:0 auto 14px;max-width:420px;}
        .q-pill{display:inline-block;margin-top:6px;background:rgba(255,138,84,.15);color:#ffb892;font-size:13px;font-weight:700;padding:7px 16px;border-radius:999px;}
        .q-actions{margin-top:26px;}
        .q-btn{display:inline-block;background:{{ $brand }};color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 24px;border-radius:10px;margin:4px;}
        .q-btn.ghost{background:transparent;border:1px solid #39416e;color:#aeb6d6;}
        .q-foot{margin-top:26px;font-size:12px;color:#6f789e;}
    </style>
</head>
<body>
    <div class="q-card">
        <div class="q-logo">
            @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $siteTitle }}">@else<span class="word">{{ $siteTitle }}</span>@endif
        </div>
        <div class="q-check">✅</div>
        <h1>{{ __('You\'re on the list') }}@if($name), {{ $name }}@endif!</h1>
        <div class="q-pill">🚀 {{ __('Launching soon') }}</div>
        <p style="margin-top:16px;">
            {{ __('Thank you for registering as a') }} <strong style="color:#fff;">{{ $isSeller ? __('Service Provider') : __('Buyer') }}</strong>.
            {{ __('Your account is ready, and') }} {{ $siteTitle }} {{ __('is putting the final touches in place.') }}
        </p>
        <p>{{ __('We\'ll email you the moment we go live so you can start right away — no need to register again.') }}</p>
        <div class="q-actions">
            @php $logoutRoute = $isSeller ? (Route::has('seller.logout') ? route('seller.logout') : null) : (Route::has('buyer.logout') ? route('buyer.logout') : null); @endphp
            @if($logoutRoute)
                <a href="{{ $logoutRoute }}" class="q-btn ghost">{{ __('Sign out') }}</a>
            @endif
        </div>
        <div class="q-foot">© {{ date('Y') }} {{ $siteTitle }} · {{ __('We can\'t wait to have you on board.') }}</div>
    </div>
</body>
</html>
