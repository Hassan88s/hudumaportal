@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('All Jobs') }}
@endsection
@section('style')
    <style>
        .budget_style{
            font-size: 18px;
            font-weight: 400;
        }
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>

    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
                <x-msg.error/>
                <x-msg.success/>

                <!-- search section start-->
                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard__wallet">
                        <form action="{{ route('seller.new.jobs') }}" method="GET">
                            <div class="dashboard__headerGlobal__flex">
                                <div class="dashboard__headerGlobal__content">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <h4 class="dashboard_table__title">{{ __('Search New Jobs Module') }}</h4> <i class="las la-angle-down search_by_all"></i>
                                    </button>
                                </div>
                                <div class="dashboard__headerGlobal__btn">
                                    <div class="btn-wrapper">
                                        <button href="#" class="dashboard_table__title__btn btn-bg-1 radius-5" type="submit">
                                            <i class="fa-solid fa-magnifying-glass"></i> {{ __('Search') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div id="collapseOne" class="accordion-collapse collapse
                                @if(request()->get('job_date')) show
                                @elseif(request()->get('job_budget')) show
                                @elseif(request()->get('job_type')) show
                                @elseif(request()->get('job_title')) show
                                @endif
                             " aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="single-settings">
                                                    <div class="single-dashboard-input">
                                                        <div class="row g-4 mt-3">
                                                            <div class="col-lg-6 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="job_title" class="info-title"> {{__('Job Title')}} </label>
                                                                    <input class="form--control" name="job_title" value="{{ request()->get('job_title') }}" type="text" placeholder="{{ __('Job Title') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-2 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="job_budget" class="info-title"> {{__('Job Budget')}} </label>
                                                                    <input class="form--control" name="job_budget" value="{{ request()->get('job_budget') }}" type="text" placeholder="{{ __('Job Budget') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-2 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="status" class="info-title"> {{__('Job Type')}} </label>
                                                                    <select name="job_type">
                                                                        <option value="">{{__('Select Job Type')}}</option>
                                                                        <option value="online" @if(request()->get('job_type') == 'online') selected @endif>{{ __('Online') }}</option>
                                                                        <option value="offline" @if(request()->get('job_type') == 'offline') selected @endif>{{  __('Offline') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-2 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="job_date" class="info-title"> {{__('Created Date Range')}} </label>
                                                                    <input class="form--control flatpickr_input" value="{{ request()->get('job_date') }}" name="job_date" type="text" value="{{ request()->get('job_date') }}" placeholder="{{ __('Created Date Range') }}">
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--search section end-->

                <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboard__headerContents__title">{{ __('All New Jobs') }}</h4>
                            <p class="text-secondary mt-2">{{ __('All new jobs are listed bellow') }}</p>
                        </div>
                    </div>
                </div>

                @if($jobs->count() > 0)
                    <div class="row g-4">
                        @foreach($jobs as $data)
                            <div class="col-xxl-6 col-lg-12">
                                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                                    <div class="dashboard_jobPost">
                                        <div class="dashboard_jobPost__flex">
                                            <div class="dashboard_jobPost__author">
                                                <div class="dashboard_jobPost__author__thumb">
                                                    @if(!empty($data->image))
                                                        {!! render_image_markup_by_attachment_id($data->image,'','thumb') !!}
                                                    @endif
                                                </div>
                                                <div class="dashboard_jobPost__author__contents">
                                                    <h4 class="dashboard_jobPost__author__title"><a target="_blank" href="{{route('job.post.details',$data->slug)}}">{{ $data->title }}</a></h4>
                                                    <h6 class="dashboard_jobPost__author__price mt-2">
                                                        <span class="text-dark budget_style">{{ __('Budget:') }}</span>{{amount_with_currency_symbol($data->price)}}
                                                    </h6>
                                                    <div class="dashboard_jobPost__views mt-2">
                                                        <div class="dashboard_jobPost__views__item">
                                                            <span class="dashboard_jobPost__views__para"><i class="fa-regular fa-eye"></i> {{ __('Views') }}</span>
                                                            <span class="dashboard_jobPost__views__count">{{ $data->view }}</span>
                                                        </div>


                                                        <div class="dashboard_jobPost__views__item">
                                                            <span class="dashboard_jobPost__views__para"> {{ __('Status:') }}</span>
                                                            @if($data->status == 1)
                                                                <strong class="replaceText"> {{ __('Active') }}</strong>
                                                            @else
                                                                <strong class="replaceText text-danger">{{ __('Inactive') }}</strong>
                                                            @endif
                                                        </div>

                                                        <div class="dashboard_jobPost__views__item">
                                                            <span class="dashboard_jobPost__views__para"> {{ __('Job Type:') }}</span>
                                                            @if($data->is_job_online == 1)
                                                                <strong class="replaceText text-success"> {{ __('Online') }}</strong>
                                                            @else
                                                                <strong class="replaceText text-dark">{{ __('Offline') }}</strong>
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="dashboard_table__main__actions">
                                                <a href="{{ route('job.post.details',$data->slug) }}" title="{{ __('View Job Post') }}" class="icon"><i class="fa-regular fa-eye"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="blog-pagination margin-top-55">
                            <div class="custom-pagination mt-4 mt-lg-5">
                                {!! $jobs->links() !!}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="chat_wrapper__details__inner__chat__contents">
                        <h2 class="chat_wrapper__details__inner__chat__contents__para">{{ __('New Job Created')}}</h2>
                    </div>
                @endif
            </div>
        </div>
@endsection
@section('scripts')
    <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
<script>
    (function($){
        "use strict";
        $(document).ready(function(){
            // for date range
            $('.flatpickr_input').flatpickr({
                altFormat: "invisible",
                altInput: false,
                mode: "range",
            });

            $(document).on('click','.swal_delete_button',function(e){
                e.preventDefault();
                Swal.fire({
                    title: '{{__("Are you sure?")}}',
                    text: '{{__("You would not be able to revert this item!")}}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{__('Yes, delete it!')}}",
                    cancelButtonText: "{{__('Cancel')}}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).next().find('.swal_form_submit_btn').trigger('click');
                    }
                });
            });

        });

    })(jQuery);
</script>
@endsection