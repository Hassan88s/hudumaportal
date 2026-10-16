@extends('backend.admin-master')
@section('site-title')
    {{ __('Coming Soon') }}
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                @include('backend.partials.message')
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{ __('Coming Soon / Pre-launch Mode') }}</h4>
                        @if($errors->any())
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger">{{ $error }}</div>
                            @endforeach
                        @endif

                        @php $enabled = (int) get_static_option('coming_soon_enabled') === 1; @endphp
                        <p class="text-muted">
                            {{ __('When ON, the public site is replaced by a coming-soon page. Registration stays open and new users wait on a queue page until you turn it off. Admins keep full access.') }}
                        </p>

                        <form action="{{ route('admin.general.coming.soon.update') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="coming_soon_enabled"><strong>{{ __('Coming Soon Mode') }}</strong></label>
                                <select name="coming_soon_enabled" id="coming_soon_enabled" class="form-control">
                                    <option value="0" @if(!$enabled) selected @endif>{{ __('OFF — site works normally') }}</option>
                                    <option value="1" @if($enabled) selected @endif>{{ __('ON — show coming-soon page') }}</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="coming_soon_headline">{{ __('Headline') }}</label>
                                <input type="text" name="coming_soon_headline" id="coming_soon_headline" class="form-control"
                                       value="{{ get_static_option('coming_soon_headline') }}"
                                       placeholder="{{ __("Tanzania's services marketplace is almost here") }}">
                            </div>

                            <div class="form-group">
                                <label for="coming_soon_subheadline">{{ __('Sub-headline') }}</label>
                                <textarea name="coming_soon_subheadline" id="coming_soon_subheadline" class="form-control" rows="3"
                                          placeholder="{{ __('Connect with trusted local providers — book services, hire professionals, and get work done, all in one place.') }}">{{ get_static_option('coming_soon_subheadline') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="coming_soon_note">{{ __('Call-to-action note') }}</label>
                                <input type="text" name="coming_soon_note" id="coming_soon_note" class="form-control"
                                       value="{{ get_static_option('coming_soon_note') }}"
                                       placeholder="{{ __("Register today and we'll notify you the moment we launch.") }}">
                            </div>

                            <button id="update" type="submit" class="btn btn-primary mt-4 pr-4 pl-4">{{ __('Update Changes') }}</button>
                            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary mt-4 pr-4 pl-4">{{ __('Preview Site') }}</a>
                            <small class="d-block text-muted mt-2">{{ __('Tip: preview in an incognito window — admins bypass the coming-soon page.') }}</small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        <x-btn.update/>
    </script>
@endsection
