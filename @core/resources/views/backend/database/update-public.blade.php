<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Database Update') }} — {{ get_static_option('site_title') ?? 'Huduma Portal' }}</title>
    <style>
        body{margin:0;background:#f4f6f8;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#1f2733}
        .wrap{max-width:760px;margin:40px auto;padding:0 16px}
        .card{background:#fff;border:1px solid #e6e9ef;border-radius:12px;margin-bottom:18px;overflow:hidden}
        .card .hd{padding:14px 18px;background:#f8f9fb;border-bottom:1px solid #e6e9ef;font-weight:700;text-transform:uppercase;letter-spacing:.4px;font-size:13px}
        .card .bd{padding:18px}
        h1{font-size:22px;margin:0 0 4px}
        .sub{color:#6b7280;font-size:13px;margin:0 0 22px}
        ul{margin:0;padding-left:18px}
        li{font-family:monospace;font-size:13px;margin-bottom:5px}
        button{background:#ff6b3d;color:#fff;border:0;padding:11px 20px;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer}
        button:hover{background:#1f2733}
        pre{background:#1f2733;color:#e5e7eb;padding:14px;border-radius:8px;font-size:12px;overflow:auto;max-height:320px}
        .ok{color:#15803d;font-weight:700}
        .msg{padding:12px 16px;border-radius:9px;margin-bottom:16px;font-size:14px}
        .msg.good{background:#dcfce7;color:#166534}
        .msg.bad{background:#fee2e2;color:#991b1b}
        .note{font-size:12px;color:#8892a0;margin-top:10px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>{{ __('Database Update') }}</h1>
    <p class="sub">{{ __('Applies database changes that came with newly deployed code. Existing tables and data are left alone.') }}</p>

    @if(session('success'))<div class="msg good">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="msg bad">{{ session('warning') }}</div>@endif

    <div class="card">
        <div class="hd">{{ __('Waiting changes') }}</div>
        <div class="bd">
            @if(count($pending))
                <ul>@foreach($pending as $m)<li>{{ $m }}</li>@endforeach</ul>
                <form method="post" action="{{ route('database.update.key.run', $publicKey) }}" style="margin-top:18px"
                      onsubmit="return confirm('{{ __('Apply these database changes now?') }}')">
                    @csrf
                    <button type="submit">{{ __('Run database update') }}</button>
                </form>
            @else
                <p class="ok" style="margin:0">✓ {{ __('Database is up to date. Nothing to run.') }}</p>
            @endif
            <p class="note">{{ __('This link works without logging in, so keep it private. Every run is written to the server log.') }}</p>
        </div>
    </div>

    @if(session('db_update_output'))
        <div class="card">
            <div class="hd">{{ __('What ran') }}</div>
            <div class="bd"><pre>{{ session('db_update_output') }}</pre></div>
        </div>
    @endif
</div>
</body>
</html>
