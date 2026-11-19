<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    {{--
        Rider pages are used one-handed, outdoors, on a phone. They deliberately
        do not extend the dashboard shell: no sidebar, no desktop chrome, and a
        viewport that cannot be pinched away from the controls.
    --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#111827">
    <title>@yield('title', __('Rider')) — {{ get_static_option('site_title') ?? 'Huduma' }}</title>

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
        .rider-top h1 { font-size: 17px; margin: 0; font-weight: 600; }
        .rider-top a { color: var(--muted); text-decoration: none; font-size: 22px; line-height: 1; }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 18px;
            margin-bottom: 16px;
        }
        .card h2 { font-size: 15px; margin: 0 0 12px; font-weight: 600; }

        label { display: block; font-size: 14px; color: var(--muted); margin: 0 0 6px; }

        input[type=text], input[type=date], input[type=tel], input[type=file], select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 16px;
            background: #0d1420;
            border: 1px solid var(--line);
            border-radius: 10px;
            color: var(--text);
            font-size: 16px;   /* 16px stops iOS zooming the page on focus */
        }
        input[type=file] { padding: 10px; font-size: 14px; }
        input:focus, select:focus { outline: 2px solid var(--accent); outline-offset: 1px; }

        .btn {
            display: block; width: 100%;
            padding: 15px;
            border: 0; border-radius: 12px;
            background: var(--accent); color: #04140a;
            font-size: 16px; font-weight: 700;
            cursor: pointer;
        }
        .btn[disabled] { opacity: .55; cursor: not-allowed; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-ghost  { background: transparent; color: var(--muted); border: 1px solid var(--line); }

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

        .row { display: flex; gap: 12px; }
        .row > * { flex: 1; }

        .have { font-size: 13px; color: var(--accent); margin: -12px 0 14px; }
    </style>

    @yield('style')
</head>
<body>

<div class="rider-top">
    <a href="{{ url('/') }}" aria-label="{{ __('Back to site') }}"><i class="las la-arrow-left"></i></a>
    <h1>@yield('title', __('Rider'))</h1>
</div>

<div class="rider-wrap">
    {{-- isset(): $errors is shared by session middleware, which is absent when
         this view is rendered outside a normal request. --}}
    @if (isset($errors) && $errors->any())
        <div class="alert alert-error">
            {{ __('Please check the form below.') }}
        </div>
    @endif

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
