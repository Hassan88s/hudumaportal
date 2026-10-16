@extends('backend.admin-master')
@section('site-title'){{ __('Coming Soon') }}@endsection

@section('style')
<style>
    .cs-settings .section-card{background:#fff;border:1px solid #e6e9ef;border-radius:10px;padding:22px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.03)}
    .cs-settings .section-hd{font-size:16px;font-weight:700;color:#1f2733;margin:0 0 6px}
    .cs-settings .section-sub{font-size:13px;color:#6b7280;margin:0 0 16px}
    .cs-settings .form-group{margin-bottom:16px}
    .cs-settings .form-group label{font-weight:600;font-size:13px;color:#1f2733;display:block;margin-bottom:6px}
    .cs-settings .form-group .hint{font-weight:400;color:#8892a0;font-size:12px;margin-top:5px;display:block}
    .cs-settings .form-control{border:1px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:14px;width:100%}
    .cs-settings .toggle-row{display:flex;align-items:center;gap:12px;background:#f8f9fb;border:1px solid #e6e9ef;border-radius:8px;padding:16px 18px}
    .cs-settings .toggle-row .t-label{margin:0;font-weight:700;font-size:14px;flex:1;cursor:pointer;color:#1f2733}
    .cs-settings .switch{position:relative;display:inline-block;width:52px;height:28px;flex:0 0 auto}
    .cs-settings .switch input{opacity:0;width:0;height:0}
    .cs-settings .slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:28px;transition:.2s}
    .cs-settings .slider:before{position:absolute;content:'';height:22px;width:22px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}
    .cs-settings .switch input:checked + .slider{background:#16a34a}
    .cs-settings .switch input:checked + .slider:before{transform:translateX(24px)}
    .cs-settings .state{display:inline-block;font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px}
    .cs-settings .state.on{background:#dcfce7;color:#166534}
    .cs-settings .state.off{background:#f3f4f6;color:#6b7280}
</style>
@endsection

@section('content')
@php $enabled = (int) get_static_option('coming_soon_enabled') === 1; @endphp
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-lg-8 col-12 mt-5 cs-settings">
            @include('backend.partials.message')

            <form action="{{ route('admin.general.coming.soon.update') }}" method="post">
                @csrf

                {{-- Master switch --}}
                <div class="section-card">
                    <h4 class="section-hd">{{ __('Coming Soon / Pre-launch Mode') }}
                        <span class="state {{ $enabled ? 'on' : 'off' }}" id="cs-state">{{ $enabled ? __('ON') : __('OFF') }}</span>
                    </h4>
                    <p class="section-sub">{{ __('When ON, the public site is replaced by a coming-soon page. Registration stays open and new users wait on a queue page until you turn it off. Admins keep full access.') }}</p>

                    <div class="toggle-row">
                        <span class="t-label">{{ __('Enable Coming Soon mode') }}</span>
                        <label class="switch">
                            <input type="checkbox" name="coming_soon_enabled" value="1" id="cs-toggle" {{ $enabled ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                {{-- Page content --}}
                <div class="section-card">
                    <h4 class="section-hd">{{ __('Coming Soon page content') }}</h4>
                    <p class="section-sub">{{ __('Shown on the public coming-soon page. Leave blank to use the default text.') }}</p>

                    <div class="form-group">
                        <label>{{ __('Headline') }}</label>
                        <input type="text" name="coming_soon_headline" class="form-control" value="{{ get_static_option('coming_soon_headline') }}" placeholder="{{ __("Tanzania's services marketplace is almost here") }}">
                        <span class="hint">{{ __('The big title at the top of the page.') }}</span>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Sub-headline') }}</label>
                        <textarea name="coming_soon_subheadline" class="form-control" rows="3" placeholder="{{ __('Connect with trusted local providers for anything you need — book services, hire professionals, and get work done, all in one place.') }}">{{ get_static_option('coming_soon_subheadline') }}</textarea>
                        <span class="hint">{{ __('A short sentence under the headline describing the platform.') }}</span>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Call-to-action note') }}</label>
                        <input type="text" name="coming_soon_note" class="form-control" value="{{ get_static_option('coming_soon_note') }}" placeholder="{{ __("Register today and we'll notify you the moment we launch.") }}">
                        <span class="hint">{{ __('Small text shown under the Register button.') }}</span>
                    </div>
                </div>

                <div class="d-flex" style="gap:10px;align-items:center">
                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary">{{ __('Preview the site') }}</a>
                    <small class="text-muted">{{ __('Tip: preview in an incognito window — admins bypass the coming-soon page.') }}</small>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        var t = document.getElementById('cs-toggle'), s = document.getElementById('cs-state');
        if (t && s) {
            t.addEventListener('change', function () {
                s.textContent = t.checked ? '{{ __('ON') }}' : '{{ __('OFF') }}';
                s.className = 'state ' + (t.checked ? 'on' : 'off');
            });
        }
    })();
</script>
@endsection
