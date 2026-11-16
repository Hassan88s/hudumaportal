{{--
    Server error page.

    The handler has always pointed at frontend.pages.500, but the file was
    never there — so a 500 failed again while trying to render the page that
    explains the 500, and the visitor got a blank or raw error instead.

    Deliberately self-contained, like 404.blade.php: no master layout, and
    nothing here touches the database, because whatever just failed may well
    be the database.
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('Something went wrong') }}</title>
    <meta name="robots" content="noindex">

    <link rel="stylesheet" href="{{ asset('assets/frontend/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/style.css') }}">

    <style>
        .hp-error { min-height: 70vh; display: flex; align-items: center; }
        .hp-error__icon { font-size: 64px; color: #ff8a54; line-height: 1; }
        .hp-error__title { font-size: 28px; font-weight: 700; margin: 18px 0 8px; }
        .hp-error__text { color: #6b7280; max-width: 520px; margin: 0 auto 28px; }
    </style>
</head>

<body>
    <div class="hp-error">
        <div class="container text-center">
            <div class="hp-error__icon"><i class="las la-exclamation-triangle"></i></div>
            <h1 class="hp-error__title">{{ __('Something went wrong on our side') }}</h1>
            <p class="hp-error__text">
                {{ __('This one is not your fault. The page could not be loaded. Please try again in a moment.') }}
            </p>
            <div>
                <a href="{{ url('/') }}" class="cmn-btn btn-bg-1">{{ __('Back to home') }}</a>
            </div>
        </div>
    </div>
</body>
</html>
