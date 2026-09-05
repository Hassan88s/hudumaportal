<!-- Jobs area starts -->

<section class="new_jobs_area padding-top-50 padding-bottom-50"
         data-padding-top="{{$padding_top}}" data-padding-bottom="{{$padding_bottom}}" style="background-color:{{$section_bg}}">
    <div class="container">
        <div class="new_sectionTitle text-left title_flex">
            <h2 class="title">{{ __($section_title) }}</h2>
            <!--<a href="{{ $explore_link }}" class="new_exploreBtn"> {{ $explore_text }} <i class="fa-solid fa-angle-right"></i></a>-->
        </div>
        <div class="row g-4 mt-4">
        @if($all_jobs->count() > 0)
       {{-- @foreach ($all_jobs->reject(fn($job) => $job->job_request->where('is_hired', 1)->count() > 0) as $job)--}}
           @foreach ($all_jobs->reject(function ($job) {
                $hiredCount = $job->job_request->where('is_hired', 1)->count();
                return $job->no_of_hiring !== null && $hiredCount >= $job->no_of_hiring;
            }) as $job)
                 <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="new_jobs__single">
                        <div class="new_jobs__single__thumb">
                            <a href="{{ route('job.post.details',$job->slug) }}">
                                 @if($job->buyer->is_company== '1')
                               <div >  <span class="badge bg-warning text-dark">{{ __('Enterprise') }}</span></div>
                             @endif
                            @if(!empty($job->image))
                                @php  $media_image_get = \App\MediaUpload::select('id','path')->find($job->image);  @endphp
                                @if (file_exists('assets/uploads/media-uploader/' . $media_image_get->path))
                                {!! render_image_markup_by_attachment_id($job->image,'','','thumb') !!}
                                @else
                                    <img src="{{ asset('assets/frontend/img/no-image-one.jpg') }}">
                                @endif
                            @else
                                <img src="{{ asset('assets/frontend/img/no-image-one.jpg') }}">
                            @endif
                            </a>
                         
                           
                             @if($job->promoted) @if($job->package_id == 3)
                                <div class="award_icons"> <span class="badge bg-danger">{{ __('Urgent') }}</span></div>
                            @elseif($job->package_id == 2)
                               <div class="award_icons">  <span class="badge bg-warning text-dark">{{ __('Featured') }}</span></div>
                            @endif @endif</span>
                        </div>
                        <div class="new_jobs__single__contents">
                            <span class="new_jobs__single__contents__location mb-2">
                              <i class="fa-solid fa-location-dot"></i>
                                @if($job->is_job_online == 1)
                                   {{  __('Online') }}
                                @else
                                   {{ optional($job->area)->service_area }}, {{ optional($job->city)->service_city }}
                                @endif
                               
                            </span>

                            <h5 class="new_jobs__single__contents__title">
                                <a href="{{ route('job.post.details',$job->slug) }}">{{ $job->title }}</a></h5>
                           
                            <div class="new_jobs__single__price mt-4">
                                <span class="new_jobs__single__price__starting">Budget
                                <h5 class="new_jobs__single__price__title mt-1"> {{ amount_with_currency_symbol($job->price) }}</h5>
                               
                            </div>
                            <div class="author_tag border_top">
                                <a href="{{ route('about.buyer.profile',optional($job->buyer)->username) }}" class="single_authors">
                                    <div class="single_authors__thumb">
                                        {!! render_image_markup_by_attachment_id(optional($job->buyer)->image,'','','thumb') !!}
                                        <span class="notification-dot"></span>
                                    </div>
                                    <span class="single_authors__title">{{ getEnterpriseNamewithoutAuth($job->buyer)}}</span>
                                 
                                </a>
                               
                                @php
                                    $service_rating = \App\Review::where('buyer_id', $job->buyer_id)->where('type', 0)->avg('rating');
                                    $service_reviews = \App\Review::where('buyer_id', $job->id)->where('type', 0)->get();
                                @endphp

                                @if($service_rating >=1)
                                <div class="author_tag__review radius-5">
                                    <a href="javascript:void(0)" class="author_tag__review__star"> {!! ratting_star(round($service_rating, 1)) !!} </a>
                                    <a href="javascript:void(0)" class="author_tag__review__para"> ({{ $service_reviews->count() }}) </a>
                                </div>
                                @endif

                            </div>
                             @if($job->buyer->is_company== '0')
                             
                             <div class="btn-wrapper border_top">
                                @php $is_job_hired = $job->job_request->where('is_hired',1)->count() ?? 0;   @endphp
                                  @if($is_job_hired >= 1 &&  auth()->guard("web")->check())
                                     <a href="javascript:void(0)" class="cmn-btn canceled w-100 radius-5" disabled>{{ __('Already Hired') }}</a>
                                   @else
                                    <a href="{{ route('job.post.details',$job->slug) }}" class="cmn-btn btn-outline-border w-100 radius-5">{{ __($book_now_text) }}</a>
                                @endif
                            </div> @endif
                            
                            
                         
                                @if($job->buyer->is_company== '1')
                            <div class="btn-wrapper border_top">
                                @php 
                                    $is_job_hired = $job->job_request->where('is_hired',1)->count() ?? 0;  
                                @endphp
                            
                                @if($is_job_hired >= $job->no_of_hiring && auth()->guard("web")->check())
                                    <a href="javascript:void(0)" class="cmn-btn canceled w-100 radius-5" disabled>{{ __('Already Hired') }}</a>
                                @else
                                    <a href="{{ route('job.post.details', $job->slug) }}" class="cmn-btn btn-outline-border w-100 radius-5">{{ __($book_now_text) }}</a>
                                @endif
                            </div>> @endif

                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-lg-12 margin-top-30">
                    <h5 class="common-title text-center text-danger"> {{ __('No Jobs Found') }}</h5>
                </div>
            @endif
        </div>
    </div>
     <div class="row margin-top-20 ">
             
                <!--<div class="explore-btn">-->
                    <div class="text-center">
                        <!--<a href="{$explore_more_link}" class="cmn-btn {$explore_btn_color}" style="color:{$title_text_color}"> {$btn_text} </a>-->
                         <a href="{{ $explore_link }}" class="cmn-btn btn-outline-1" style="color:rgb(29, 191, 115)">{{ __('Explore More')}}</a>
                   </div>
                <!--</div>-->
            </div>
</section>
<!-- Jobs area end -->