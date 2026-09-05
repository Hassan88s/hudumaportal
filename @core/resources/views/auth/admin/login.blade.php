@extends('layouts.login-screens')

@section('content')
    <div class="login-area">
        <div class="container">
            <div class="login-box ptb--100">
                <form id="adminLoginForm" method="POST" action="{{ route('admin.login') }}">
                    @csrf

                    <div class="login-form-head">
                        <div class="logo-wrapper" style="margin-bottom: 40px;">
                            {!! render_image_markup_by_attachment_id(get_static_option('site_logo')) !!}
                        </div>
                        <h4>{{ __('Admin Login') }}</h4>
                        <p>{{ __('Hello there, Sign in and start managing your website') }}</p>
                    </div>

                    @include('backend.partials.message')

                    <div class="error-message"></div>

                    <div class="login-form-body">
                        <div class="form-gp">
                            <label for="username">{{ __('Username or Email') }}</label>
                            <input type="text" id="username" name="username" autocomplete="username">
                            <i class="ti-email"></i>
                        </div>

                        <div class="form-gp">
                            <label for="password">{{ __('Password') }}</label>
                            <input type="password" id="password" name="password" autocomplete="current-password">
                            <i class="ti-lock"></i>
                        </div>

                        {{-- OTP Area --}}
                        <div class="form-gp otp-area" style="display:none;">
                            <label for="otp">{{ __('OTP Code') }}</label>
                            <input type="text" id="otp" name="otp" maxlength="6" autocomplete="one-time-code" inputmode="numeric">
                            <i class="ti-key"></i>
                            <small class="d-block mt-2">
                                <a href="#" id="resendOtp">{{ __('Resend OTP') }}</a>
                            </small>
                        </div>

                        {{-- IMPORTANT: Hidden admin_id --}}
                        <input type="hidden" id="admin_id" name="admin_id" value="">

                        <div class="row mb-4 rmber-area">
                            <div class="col-6">
                                <div class="custom-control custom-checkbox mr-sm-2">
                                    <input type="checkbox" name="remember" class="custom-control-input" id="remember" value="1">
                                    <label class="custom-control-label" for="remember">{{ __('Remember Me') }}</label>
                                </div>
                            </div>
                            <div class="col-6 text-right">
                                <a href="{{ route('admin.forget.password') }}">{{ __('Forgot Password?') }}</a>
                            </div>
                        </div>

                        <div class="submit-btn-area">
                            <button id="form_submit" type="submit">
                                {{ __('Login') }} <i class="ti-arrow-right"></i>
                            </button>
                        </div>

                       
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
<script>
(function($){
    "use strict";

    $(document).ready(function () {

        let otpMode = false;

        function showMsg(msg, type = 'danger') {
            $(".error-message").html('<div class="alert alert-'+type+'">'+msg+'</div>');
        }

        function setButton(btn, text, disabled) {
            btn.prop('disabled', disabled);
            btn.text(text);
        }

        // Prevent normal submit (Enter key) and always run AJAX
        $(document).on('submit', '#adminLoginForm', function(e){
            e.preventDefault();
            $('#form_submit').trigger('click');
        });

        // Auto login (demo table)
        $(document).on('click', '#autoLogin', function(){
            let username = $('#td_username').text().trim();
            let password = $('#td_password').text().trim();
            $('#username').val(username);
            $('#password').val(password);
            $('#form_submit').trigger('click');
        });

        $(document).on('click', '#form_submit', function (e) {
            e.preventDefault();

            const el = $(this);
            const rememberVal = $('#remember').is(':checked') ? 1 : 0;

            $(".error-message").html('');
            setButton(el, '{{ __("Please Wait..") }}', true);

            // If you do NOT have named routes, replace route(...) with url(...)
            // step1Url = "{{ url('/login/admin') }}";
            const step1Url = "{{ route('admin.login') }}";
            const verifyUrl = "{{ route('admin.login.otp.verify') }}";
            const resendUrl = "{{ route('admin.login.otp.resend') }}";

            if (!otpMode) {
                // STEP 1: Username/Password -> Send OTP
                $.ajax({
                    url: step1Url,
                    type: "POST",
                    dataType: "json",
                    headers: { 'Accept': 'application/json' },
                    data: {
                        _token: "{{ csrf_token() }}",
                        username: $('#username').val(),
                        password: $('#password').val(),
                        remember: rememberVal,
                    },
                    success: function (data) {
                        if (data.status === 'otp_required') {
                            otpMode = true;

                            // store admin id for verify/resend
                            $('#admin_id').val(data.admin_id);

                            // show otp input
                            $('.otp-area').slideDown();
                            showMsg(data.msg || '{{ __("OTP sent to your email.") }}', data.type || 'success');

                            // lock username/password after OTP sent (optional)
                            $('#username, #password').prop('readonly', true);

                            setButton(el, '{{ __("Verify OTP") }}', false);
                            $('#otp').focus();
                            return;
                        }

                        if (data.status === 'ok') {
                            showMsg(data.msg || '{{ __("Login Success Redirecting") }}', data.type || 'success');
                            setButton(el, '{{ __("Redirecting") }}..', true);
                            location.reload();
                            return;
                        }

                        // not_ok
                        showMsg(data.msg || '{{ __("Login failed") }}', data.type || 'danger');
                        setButton(el, '{{ __("Login") }}', false);
                    },
                    error: function (xhr) {
                        // Debug: show real server error in console
                        console.log('STATUS:', xhr.status);
                        console.log('RESPONSE:', xhr.responseText);

                        let errors = xhr.responseJSON;

                        if (errors && errors.errors) {
                            let html = '<div class="alert alert-danger">';
                            $.each(errors.errors, function(_, value){
                                html += '<p>'+value+'</p>';
                            });
                            html += '</div>';
                            $(".error-message").html(html);
                        } else {
                            // 404/419/500 etc.
                            showMsg('{{ __("Server error:") }} ' + xhr.status);
                        }

                        setButton(el, '{{ __("Login") }}', false);
                    }
                });

            } else {
                // STEP 2: Verify OTP -> Login
                $.ajax({
                    url: verifyUrl,
                    type: "POST",
                    dataType: "json",
                    headers: { 'Accept': 'application/json' },
                    data: {
                        _token: "{{ csrf_token() }}",
                        admin_id: $('#admin_id').val(),
                        otp: $('#otp').val(),
                        remember: rememberVal,
                    },
                    success: function (data) {
                        if (data.status === 'ok') {
                            showMsg(data.msg || '{{ __("Login Success Redirecting") }}', data.type || 'success');
                            setButton(el, '{{ __("Redirecting") }}..', true);
                            location.reload();
                            return;
                        }

                        showMsg(data.msg || '{{ __("Invalid OTP") }}', data.type || 'danger');
                        setButton(el, '{{ __("Verify OTP") }}', false);
                    },
                    error: function (xhr) {
                        console.log('STATUS:', xhr.status);
                        console.log('RESPONSE:', xhr.responseText);

                        let errors = xhr.responseJSON;

                        if (errors && errors.errors) {
                            let html = '<div class="alert alert-danger">';
                            $.each(errors.errors, function(_, value){
                                html += '<p>'+value+'</p>';
                            });
                            html += '</div>';
                            $(".error-message").html(html);
                        } else {
                            showMsg('{{ __("Server error:") }} ' + xhr.status);
                        }

                        setButton(el, '{{ __("Verify OTP") }}', false);
                    }
                });
            }
        });

        // Resend OTP
        $(document).on('click', '#resendOtp', function(e){
            e.preventDefault();

            const adminId = $('#admin_id').val();
            if (!adminId) {
                showMsg('{{ __("Please login first to request OTP.") }}', 'warning');
                return;
            }

            // If you do NOT have named routes, replace resendUrl with url('/login/admin/otp/resend')
            const resendUrl = "{{ route('admin.login.otp.resend') }}";

            $.ajax({
                url: resendUrl,
                type: "POST",
                dataType: "json",
                headers: { 'Accept': 'application/json' },
                data: {
                    _token: "{{ csrf_token() }}",
                    admin_id: adminId
                },
                success: function(data){
                    showMsg(data.msg || '{{ __("OTP resent.") }}', data.type || 'success');
                },
                error: function(xhr){
                    console.log('STATUS:', xhr.status);
                    console.log('RESPONSE:', xhr.responseText);
                    showMsg('{{ __("Could not resend OTP. Server error:") }} ' + xhr.status);
                }
            });
        });

    });

})(jQuery);
</script>
@endsection
