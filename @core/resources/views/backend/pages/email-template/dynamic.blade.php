@extends('backend.admin-master')
@section('site-title')
    {{ $title ?? __('Email Template') }}
@endsection

@section('style')
    <x-media.css/>
    <x-summernote.css/>
@endsection

@section('content')
<div class="col-lg-12 col-ml-12 padding-bottom-30">
    <div class="row">
        <div class="col-lg-12">
            <div class="margin-top-40"></div>
            <x-msg.success/>
            <x-msg.error/>
        </div>
        <div class="col-lg-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <div class="header-wrapp d-flex justify-content-between">
                        <h4 class="header-title">{{ $title ?? __('Email Template') }}</h4>
                        <a class="btn btn-info" href="{{ route('admin.email.template.all') }}">{{__('All Email Templates')}}</a>
                    </div>
                    <form action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="tab-content margin-top-30">
                            <div class="form-group">
                                <label for="email_subject">{{__('Email Subject')}}</label>
                                <input
                                    type="text"
                                    name="{{ $subject_field }}"
                                    class="form-control"
                                    value="{{ get_static_option($subject_field) ?? __('New Message') }}"
                                >
                            </div>
                            <div class="form-group">
                                <label for="email_message">{{ __('Email Message') }}</label>
                                <textarea
                                    class="form-control summernote"
                                    name="{{ $message_field }}"
                                >{!! get_static_option($message_field) ?? '' !!}</textarea>
                            </div>

                            <small class="form-text text-muted text-danger">
                                <code>@name</code> {{__('will be replaced dynamically with the name.')}}
                            </small>
                            <small class="form-text text-muted text-danger">
                                <code>@clientname</code> {{__('will be replaced dynamically with the client name.')}}
                            </small>
                            @if(!empty($notes))
                                <small class="form-text text-muted">
                                    <strong>{{__('Note:')}}</strong> {{ $notes }}
                                </small>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <x-media.js/>
    <x-summernote.js/>
    <script>
        $(document).ready(function () {
            // You can add custom JS here if needed
        });
    </script>
@endsection
