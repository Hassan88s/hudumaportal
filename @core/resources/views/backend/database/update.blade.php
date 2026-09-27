@extends('backend.admin-master')
@section('site-title'){{ __('Database Update') }}@endsection

@section('style')
<style>
    .dbu .box{background:#fff;border:1px solid #e6e9ef;border-radius:10px;margin-bottom:18px;overflow:hidden;color:#1f2733 !important}
    .dbu .box h3,.dbu .box li,.dbu .box p,.dbu .box label,.dbu .box strong{color:#1f2733 !important}
    .dbu .box small,.dbu .box .text-muted{color:#6b7280 !important}
    .dbu .box .hd{padding:14px 18px;background:#f8f9fb;border-bottom:1px solid #e6e9ef}
    .dbu .box .hd h3{font-size:14px;font-weight:700;margin:0;text-transform:uppercase;letter-spacing:.4px}
    .dbu .box .bd{padding:16px 18px}
    .dbu ul{margin:0;padding-left:18px}
    .dbu li{font-family:monospace;font-size:13px;margin-bottom:4px}
    .dbu pre{background:#1f2733;color:#e5e7eb;padding:14px;border-radius:8px;font-size:12px;overflow:auto;max-height:320px}
    .dbu .ok{color:#15803d;font-weight:700}
    .dbu input[type=password]{padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff !important;color:#1f2733 !important}
</style>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5 dbu">
            @include('backend.partials.message')
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="alert alert-danger">{{ session('warning') }}</div>@endif

            <div class="box">
                <div class="hd"><h3>{{ __('Database update') }}</h3></div>
                <div class="bd">
                    @if(count($pending))
                        <p><strong>{{ trans_choice(':n change waiting|:n changes waiting', count($pending), ['n' => count($pending)]) }}</strong></p>
                        <ul>
                            @foreach($pending as $m)<li>{{ $m }}</li>@endforeach
                        </ul>

                        <form method="post" action="{{ route('admin.database.update.run') }}" style="margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap"
                              onsubmit="return confirm('{{ __('Apply these database changes now?') }}')">
                            @csrf
                            @if($keyNeeded)
                                <label style="font-size:13px;font-weight:600">{{ __('Update key') }}
                                    <input type="password" name="key" required autocomplete="off" placeholder="{{ __('from .env') }}">
                                </label>
                            @endif
                            <button class="btn btn-primary">{{ __('Run database update') }}</button>
                            <small class="text-muted">{{ __('Only adds what is missing. Existing tables and data are left alone.') }}</small>
                        </form>
                    @else
                        <p class="ok">✓ {{ __('Database is up to date. Nothing to run.') }}</p>
                    @endif
                </div>
            </div>

            @if(session('db_update_output'))
                <div class="box">
                    <div class="hd"><h3>{{ __('What ran') }}</h3></div>
                    <div class="bd"><pre>{{ session('db_update_output') }}</pre></div>
                </div>
            @endif

            <div class="box">
                <div class="hd"><h3>{{ __('How this works') }}</h3></div>
                <div class="bd">
                    <p style="margin:0 0 8px">{{ __('After new code is deployed, open this page and press the button. It applies any new database changes that came with the code.') }}</p>
                    <p style="margin:0;font-size:13px" class="text-muted">
                        {{ __('Only a Super Admin can open this page. Every run is written to the server log with your admin id. To require a key as well, add DB_UPDATE_KEY to the .env file.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
