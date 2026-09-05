@extends('frontend.frontend-page-master')
@section('page-meta-data')
    <title>{{ $portfolio->name  }}</title>
@endsection
@section('page-title')
    <?php
    $page_info = request()->url();
    $str = explode("/",request()->url());
    $page_info = $str[count($str)-2];
    ?>
    {{ __(ucwords(str_replace("-", " ", $page_info))) }}
@endsection
@section('inner-title')
    {{ $portfolio->name }}
@endsection
<!-- Swiper CSS -->
<!--<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">-->
<!--<style>-->
<!--    .swiper-slide img {-->
<!--    width: 100%; -->
<!--    height: 100%; -->
<!--    object-fit: cover;-->
<!--}-->

<!--.swiper-button-next, .swiper-button-prev {-->
<!--    display: none;-->
<!--}-->


<!--@media (max-width: 768px) {-->
<!--    .swiper-button-next, .swiper-button-prev {-->
<!--        display: block; -->
<!--    }-->
<!--}-->

</style>
@section('content')
   <div class="new_service_details_area padding-top-100 padding-bottom-100">
    <div class="container">

        <div class="new_stepForm">
            <div class="row g-4 mt-1">
                <div class="col-xl-9 col-lg-8">

                    <!-- Service Details (Title + Description) -->
                    <div class="new_serviceDetails radius-10">
                        <div class="new_serviceDetails__flex">
                            <div class="new_serviceDetails__author">
                                <div class="new_serviceDetails__author__flex">
    <div class="new_serviceDetails__author__contents">
        <h4 class="new_serviceDetails__author__title">
            {{ $portfolio->name }}
        </h4>
       

        <!-- Project Cost & Duration -->
        <div class="new_serviceDetails__projectInfo">
            <div class="d-flex justify-content-between mt-3">
                <div>
                    <small class="text-muted">Project Cost</small>
                    <p class="fw-bold mb-0">
                        ${{ $portfolio->project_cost ?? '' }}
                    </p>
                </div>
                <div>
                    <small class="text-muted">Project Duration</small>
                    <p class="fw-bold mb-0">
                        {{ $portfolio->timeline ?? '' }}
                    </p>
                </div>
            </div>
        </div>
         <p class="new_serviceDetails__author__para">
            {!! $portfolio->description !!}
        </p>
    </div>
</div>

                            </div>
                        </div>
                    </div>

                    <!-- Video Section (if available) -->
                    @if($portfolio->video)
                        <div class="portfolio-video mt-4">
                            <video controls loop playsinline  style="width:100%; max-height:400px;">
                                <source src="{{ asset('@core/public/' . $portfolio->video) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    @endif

@if($portfolio->images->count() > 0)
    <div class="portfolio-images mt-4">
        <div class="row">
            @foreach($portfolio->images as $image)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <a href="{{ asset('@core/public/' . $image->image) }}" target="_blank">
                            <img src="{{ asset('@core/public/' . $image->image) }}" class="card-img-top img-fluid" style="height:250px; object-fit:cover;" alt="Portfolio Image">
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif


            



 </div>
                </div>
            </div>
        </div>

    </div>

<!-- Swiper JS -->
<!--<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>-->

<!--<script>-->

<!--var swiper = new Swiper('.swiper-container', {-->
<!--    slidesPerView: 1,-->
<!--    spaceBetween: 10, -->
<!--    loop: true, -->
<!--    autoplay: {-->
<!--        delay: 3000, -->
<!--        disableOnInteraction: false,-->
<!--    },-->
    <!--// navigation: {-->
    <!--//     nextEl: '.swiper-button-next',-->
    <!--//     prevEl: '.swiper-button-prev',-->
    <!--// },-->
<!--    pagination: {-->
<!--        el: '.swiper-pagination',-->
<!--        clickable: true, -->
<!--    }-->
<!--});-->


<!--</script>-->
@endsection

