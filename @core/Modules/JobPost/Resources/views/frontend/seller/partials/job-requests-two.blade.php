@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Job Requests') }}
@endsection
@section('style')
    <style>
        .table-td-padding {
            border-collapse: separate;
            border-spacing: 10px 20px;
        }
        .dashboard-right {
            width: 100%;
            box-shadow: 0 0 40px #ebebeb;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
    <link rel="stylesheet" href="{{asset('assets/common/css/themify-icons.css')}}">
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>

    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">

                <!-- search section start-->
                <div class="dashboard__inner__item dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard__wallet">
                        <form action="{{ route('seller.all.jobs.request') }}" method="GET">
                            <div class="dashboard__headerGlobal__flex">
                                <div class="dashboard__headerGlobal__content">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <h4 class="dashboard_table__title">{{ __('Search Jobs Request Module') }}</h4> <i class="las la-angle-down search_by_all"></i>
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
                                @if(request()->get('job_id'))  show
                                @elseif(request()->get('job_offer_id')) show
                                @elseif(request()->get('job_request_date')) show
                                @elseif(request()->get('job_type')) show
                                @elseif(request()->get('job_title')) show
                                @elseif(request()->get('buyer_name')) show
                                @endif
                             " aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="single-settings">
                                                    <div class="single-dashboard-input">
                                                        <div class="row g-4 mt-3">

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="job_offer_id" class="info-title"> {{__('Job Offer ID')}} </label>
                                                                    <input class="form--control" name="job_offer_id" value="{{ request()->get('job_offer_id') }}" type="text" placeholder="{{ __('Job Offer ID') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="job_id" class="info-title"> {{__('Job ID')}} </label>
                                                                    <input class="form--control" name="job_id" value="{{ request()->get('job_id') }}" type="text" placeholder="{{ __('Job ID') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="status" class="info-title"> {{__('Job Type')}} </label>
                                                                    <select name="job_type">
                                                                        <option value="">{{__('Select Job Type')}}</option>
                                                                        <option value="online" @if(request()->get('job_type') == 'online') selected @endif>{{ __('Online') }}</option>
                                                                        <option value="offline" @if(request()->get('job_type') == 'offline') selected @endif>{{  __('Offline') }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="job_title" class="info-title"> {{__('Job Title')}} </label>
                                                                    <input class="form--control" name="job_title" value="{{ request()->get('job_title') }}" type="text" placeholder="{{ __('Job Title') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="buyer_name" class="info-title"> {{__('Buyer Name')}} </label>
                                                                    <input class="form--control" name="buyer_name" value="{{ request()->get('buyer_name') }}" type="text" placeholder="{{ __('Buyer Name') }}">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-4 col-sm-6">
                                                                <div class="single-info-input">
                                                                    <label for="job_request_date" class="info-title"> {{__('Created Date Range')}} </label>
                                                                    <input class="form--control flatpickr_input" value="{{ request()->get('job_request_date') }}" name="job_request_date" type="text" placeholder="{{ __('Created Date Range') }}">
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
                            <h4 class="dashboards-title">{{ __('All Request') }}</h4>
                            <p class="text-success mt-3">{{ __('These are all job applied list that you have apply') }}</p>
                        </div>
                    </div>
                </div>

                @if($all_job_requests->count() >= 1)
                <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
                    <div class="dashboard_table__main custom--table mt-4">
                        <table>
                            <thead>
                            <tr>
                                <th> {{ __('Job Offer ID') }} </th>
                                <th> {{ __('Job ID') }} </th>
                                <th> {{ __('Job Title') }} </th>
                                <th> {{ __('Buyer Name') }} </th>
                                <th> {{ __('Your Offer') }} </th>
                                <th> {{ __('Buyer Offer') }} </th>
                                <th> {{ __('Action') }} </th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($all_job_requests as $job_req)
                                <tr>
                                    <td data-label="{{ __('Job Offer ID') }}"> {{ $job_req->id }} </td>
                                    <td data-label="{{ __('Job ID') }}"> {{ optional($job_req->job)->id }} </td>
                                    <td data-label="{{ __('Job Title') }}"> {{ Str::limit(optional($job_req->job)->title,50) }} </td>
                                    <td data-label="{{ __('Buyer Name') }}">
                                        {{ optional(optional($job_req->job)->buyer)->name }}
                                        @if(optional($job_req->jobRequestTicket)->is_hired == 1)
                                            <span class="btn btn-info">{{ __('Hired') }}</span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('Your Offer') }}">{{ float_amount_with_currency_symbol($job_req->expected_salary) }}</td>
                                    <td data-label="{{ __('Buyer Offer') }}">{{ float_amount_with_currency_symbol(optional($job_req->job)->price) }}</td>
                                    <td data-label="{{ __('Action') }}">
                                        @if(!empty(optional($job_req->job)->slug))
                                            <a href="{{ route('job.post.details', optional($job_req->job)->slug) }}" target="_blank">
                                                <span class="btn btn-info btn-sm">{{__('View Job Post')}}</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('seller.job.request.conversation', $job_req->id) }}">
                                            <span class="btn btn-success btn-sm">{{ __('Conversation') }}</span>
                                        </a>
                                        @if($job_req->is_hired == 1)
                                            <span class="btn btn-danger btn-sm mt-2">{{ __('Hired') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>

                    <div class="blog-pagination margin-top-55">
                        <div class="custom-pagination mt-4 mt-lg-5">
                            {!! $all_job_requests->links() !!}
                        </div>
                    </div>

                </div>
                @else
                    <div class="chat_wrapper__details__inner__chat__contents">
                        <h2 class="chat_wrapper__details__inner__chat__contents__para">{{ __('No Job Request Found') }}</h2>
                    </div>
                @endif
            </div>
        </div>
        @endsection

@section('scripts')
  <script src="{{ asset('assets/backend/js/sweetalert2.js') }}"></script>
            <script>
                (function($) {
                    "use strict";

                    $(document).ready(function() {
                        // for date range
                        $('.flatpickr_input').flatpickr({
                            altFormat: "invisible",
                            altInput: false,
                            mode: "range",
                        });

                        //order complete status approve
                        $(document).on('click','.swal_status_change',function(e){
                            e.preventDefault();
                            Swal.fire({
                                title: '{{__("Are you sure to change status? Once you done you can not revert this !!")}}',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes, change it!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $(this).next().find('.swal_form_submit_btn').trigger('click');
                                }
                            });
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
                                confirmButtonText: 'Yes, delete it!'
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
