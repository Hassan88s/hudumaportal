@extends('backend.admin-master')
@section('site-title')
    {{__('Job Settings')}}
@endsection
@section('content')
    <div class="col-lg-6 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40"></div>
                <x-msg.success/>
                <x-msg.error/>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <div class="left-content">
                                <h4 class="header-title">{{__('Job Create Settings')}} </h4>
                                <p class="mb-3 text-info">{{ __('You can set the buyer create job status auto active/Inactive from here.') }}</p>
                            </div>
                        </div>
                        <form action="{{route('admin.job.create.settings.update')}}" method="post">
                            @csrf
                            <div class="tab-content margin-top-10">
                                <div class="form-group">
                                    <label for="job_create_settings">{{__('Select')}}</label>
                                    <select type="text" class="form-control" name="job_create_settings" id="job_create_settings">
                                        <option value="">{{ __('Select Status') }}</option>
                                        <option value="active" {{ get_static_option('job_create_settings')=='active' ? 'selected' : '' }} >{{ __('Active') }}</option>
                                        <option value="inactive" {{ get_static_option('job_create_settings')=='inactive' ? 'selected' : '' }} >{{ __('Inactive') }}</option>
                                    </select>
                                </div>




                                <div class="mt-5">
                                    <h4 class="header-title">{{__('Job Details Page Settings')}} </h4>
                                    <div class="form-group">
                                        <label for="job_overview_title">{{__('Job Overview Title')}}</label>
                                        <input type="text" class="form-control" name="job_overview_title" value="{{ get_static_option('job_overview_title') }}" placeholder="Job Overview Title">
                                    </div>

                                    <div class="form-group">
                                        <label for="job_create_settings">{{__('Starting at Price Title')}}</label>
                                        <input type="text" class="form-control" name="job_starting_at_price_title" value="{{ get_static_option('job_starting_at_price_title') }}" placeholder="Starting at price title">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3 submit_btn">{{__('Submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection