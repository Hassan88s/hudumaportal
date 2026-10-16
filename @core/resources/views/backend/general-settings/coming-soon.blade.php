@extends('backend.admin-master')
@section('site-title'){{ __('Coming Soon') }}@endsection
@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-12 mt-5">
            @include('backend.partials.message')
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">{{ __('Coming Soon / Pre-launch Mode') }}</h4>

                    @php $enabled = (int) get_static_option('coming_soon_enabled') === 1; @endphp
                    <div class="alert {{ $enabled ? 'alert-warning' : 'alert-secondary' }}">
                        {{ $enabled
                            ? __('Coming Soon mode is ON — visitors see the pre-launch page and new sign-ups land on the waitlist. Admins still have full access.')
                            : __('Coming Soon mode is OFF — the site works normally.') }}
                    </div>

                    <form action="{{ route('admin.general.coming.soon.update') }}" method="post">
                        @csrf

                        <div class="form-group mb-4">
                            <label class="d-block mb-2"><strong>{{ __('Enable Coming Soon mode') }}</strong></label>
                            <label class="switch" style="position:relative;display:inline-block;width:52px;height:28px;">
                                <input type="checkbox" name="coming_soon_enabled" value="1" {{ $enabled ? 'checked' : '' }} style="opacity:0;width:0;height:0;">
                                <span style="position:absolute;inset:0;background:{{ $enabled ? '#16a34a' : '#cbd5e1' }};border-radius:999px;cursor:pointer;"></span>
                            </label>
                            <small class="form-text text-muted">{{ __('When on, the public site is replaced by the coming-soon page. Registration stays open and new users wait on a queue page until you turn this off.') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ __('Headline') }}</label>
                            <input type="text" name="coming_soon_headline" class="form-control" value="{{ get_static_option('coming_soon_headline') }}" placeholder="{{ __("Tanzania's services marketplace is almost here") }}">
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ __('Sub-headline') }}</label>
                            <textarea name="coming_soon_subheadline" class="form-control" rows="2" placeholder="{{ __('Connect with trusted local providers…') }}">{{ get_static_option('coming_soon_subheadline') }}</textarea>
                        </div>

                        <div class="form-group mb-4">
                            <label>{{ __('Call-to-action note') }}</label>
                            <input type="text" name="coming_soon_note" class="form-control" value="{{ get_static_option('coming_soon_note') }}" placeholder="{{ __("Register today and we'll notify you the moment we launch.") }}">
                        </div>

                        <div class="d-flex" style="gap:10px;align-items:center">
                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary">{{ __('Preview the site') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
