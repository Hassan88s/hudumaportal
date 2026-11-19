<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    {{--
        Ride pages — passenger and rider alike — are used one-handed,
        outdoors, on a phone. They deliberately do not extend the dashboard
        shell: no sidebar, no desktop chrome, and a viewport that cannot be
        pinched away from the controls.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#111827">
    <title>@yield('title', __('Ride')) — {{ get_static_option('site_title') ?? 'Huduma' }}</title>

    <link rel="stylesheet" href="{{ asset('assets/frontend/css/line-awesome.min.css') }}">

    <style>
        :root {
            --bg:      #0f1623;
            --card:    #182334;
            --line:    #243149;
            --text:    #f1f5f9;
            --muted:   #94a3b8;
            --accent:  #22c55e;
            --warn:    #f59e0b;
            --danger:  #ef4444;
            --radius:  14px;
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .rider-wrap { max-width: 560px; margin: 0 auto; padding: 16px 16px 40px; }

        .rider-top {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px;
            background: var(--card);
            border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 20;
        }
        .rider-top h1 { font-size: 17px; margin: 0; font-weight: 600; flex: 1; }
        .rider-top a { color: var(--muted); text-decoration: none; font-size: 22px; line-height: 1; }
        .rider-top .link { font-size: 14px; font-weight: 600; color: var(--accent); }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 18px;
            margin-bottom: 16px;
        }
        .card h2 { font-size: 15px; margin: 0 0 12px; font-weight: 600; }

        label { display: block; font-size: 14px; color: var(--muted); margin: 0 0 6px; }

        input[type=text], input[type=date], input[type=tel], input[type=number],
        input[type=file], select, textarea {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 16px;
            background: #0d1420;
            border: 1px solid var(--line);
            border-radius: 10px;
            color: var(--text);
            font-size: 16px;   /* 16px stops iOS zooming the page on focus */
            font-family: inherit;
        }
        input[type=file] { padding: 10px; font-size: 14px; }
        input:focus, select:focus, textarea:focus { outline: 2px solid var(--accent); outline-offset: 1px; }

        .btn {
            display: block; width: 100%;
            padding: 15px;
            border: 0; border-radius: 12px;
            background: var(--accent); color: #04140a;
            font-size: 16px; font-weight: 700;
            cursor: pointer;
            text-align: center; text-decoration: none;
        }
        .btn[disabled] { opacity: .55; cursor: not-allowed; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-warn   { background: var(--warn); color: #231703; }
        .btn-ghost  { background: transparent; color: var(--muted); border: 1px solid var(--line); }
        .btn-sm     { padding: 11px; font-size: 14px; }

        .pill {
            display: inline-block; padding: 5px 12px; border-radius: 999px;
            font-size: 13px; font-weight: 600;
        }
        .pill-pending   { background: rgba(245,158,11,.15); color: var(--warn); }
        .pill-approved  { background: rgba(34,197,94,.15);  color: var(--accent); }
        .pill-rejected  { background: rgba(239,68,68,.15);  color: var(--danger); }
        .pill-suspended { background: rgba(148,163,184,.15); color: var(--muted); }

        .muted { color: var(--muted); font-size: 14px; }
        .err   { color: var(--danger); font-size: 13px; margin: -12px 0 14px; }

        .alert {
            padding: 13px 15px; border-radius: 10px; margin-bottom: 16px;
            font-size: 14px; border: 1px solid transparent;
        }
        .alert-error { background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.35); color: #fecaca; }
        .alert-info  { background: rgba(34,197,94,.08); border-color: rgba(34,197,94,.3);  color: #bbf7d0; }
        .alert-warn  { background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.35); color: #fde68a; }

        .row { display: flex; gap: 12px; }
        .row > * { flex: 1; }

        .have { font-size: 13px; color: var(--accent); margin: -12px 0 14px; }

        .kv { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid var(--line); }
        .kv:last-child { border-bottom: 0; }
        .kv .k { color: var(--muted); font-size: 14px; }
        .kv .v { font-weight: 600; }
    </style>

    @yield('style')
</head>
<body>

<div class="rider-top">
    <a href="@yield('back', url('/'))" aria-label="{{ __('Back') }}"><i class="las la-arrow-left"></i></a>
    <h1>@yield('title', __('Ride'))</h1>
    @yield('topright')
</div>

<div class="rider-wrap">
    {{-- isset(): $errors is shared by session middleware, which is absent when
         this view is rendered outside a normal request. --}}
    @if (isset($errors) && $errors->any())
        <div class="alert alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- These pages have no toastr script, so the flashes the controllers set
         with toastr_success()/toastr_error() are drawn here as plain text
         instead of being silently dropped. --}}
    @php $flashes = session('toastr::notifications', []); @endphp
    @foreach ($flashes as $flash)
        <div class="alert {{ ($flash['type'] ?? '') === 'error' ? 'alert-error' : 'alert-info' }}">
            {{ $flash['message'] ?? '' }}
        </div>
    @endforeach

    @yield('content')
</div>

<script>
    window.RIDER = {
        csrf: document.querySelector('meta[name=csrf-token]').getAttribute('content')
    };
</script>
@yield('script')

</body>
</html>
