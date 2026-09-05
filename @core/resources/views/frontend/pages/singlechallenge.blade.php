@extends('frontend.frontend-page-master')
@section('inner-title')
    {{ $project->name }}
@endsection
@section('style')

<style>
    .social-post {
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        background-color: #fff;
    }
    .post-header img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .media-preview img, .media-preview video {
        border-radius: 10px;
        width: 100%;
        height: auto;
    }
    .post-footer i {
        margin-right: 5px;
    }
</style>
<style>
.carousel-indicators [data-bs-target] {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #666;
    margin: 0 4px;
    opacity: 0.5;
    transition: opacity 0.3s, background-color 0.3s;
}

.carousel-indicators .active {
    opacity: 1;
    background-color: #000;
}
</style>
<style>
.bg-custom-primary {
  background-color: rgb(207, 225, 251) !important;
  color: #000 !important;
}
.dropdown:hover .dropdown-menu {
    display: block;
    margin-top: 0; /* remove the delay */
}
</style>
@endsection

@section('content')
<section class="py-5 bg-light">
  
</section>

<section class="py-5">
   
    <div class="container pt-5">
      

   
                <div class="social-post mb-5 border p-3 rounded">
                    <!-- Post Header -->
                    @php
                        $user = \App\User::find($project->freelancer_id);
                        $profileImage = get_attachment_image_by_id(optional($user)->image, null, false);
                         $shortDesc = Str::limit(strip_tags($project->description), 150, '');
                          $fullDesc = Str::after(strip_tags($project->description), $shortDesc);
                    @endphp
                    <a href="{{ route('about.seller.profile', $user->username) }}">
                        <div class="d-flex align-items-center mb-3">
                            @if($profileImage)
                                <img src="{{ $profileImage['img_url'] }}" alt="User Image" class="rounded-circle me-2" style="width:50px; height:50px; object-fit:cover;">
                            @endif
                            <div>
                                <strong>{{ $user->username ?? 'Unknown User' }}</strong><br>
                            </div>
                        </div>
                    </a>

                    <!-- Project Title -->
                    <div class="mb-2">
                        <strong><a href="{{ route('seller.project.single', $project->slug) }}">{{ $project->name }}</a></strong>

                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <p>
                            {!! $project->description !!}
                           
                        </p>
                    </div>

                    <!-- Media Carousel -->
                    <div id="mediaCarousel{{ $project->id }}" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                            @foreach($project->images as $index => $image)
                                <div class="carousel-item @if($index == 0) active @endif">
                                    <img src="{{ asset('@core/public/' . $image->image) }}" class="d-block w-100" style="height: 300px; object-fit: cover;" alt="Project Image">
                                </div>
                            @endforeach
                            @if($project->video)
                                <div class="carousel-item @if(count($project->images) == 0) active @endif">
                                    <video controls class="d-block w-100" style="height: 300px; object-fit: cover;">
                                        <source src="{{ asset('@core/public/' . $project->video) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            @endif
                        </div>

                        <!-- Dots only -->
                        @php
                            $mediaCount = count($project->images) + ($project->video ? 1 : 0);
                        @endphp
                        @if($mediaCount > 1)
                            <div class="carousel-indicators mt-2">
                                @for ($i = 0; $i < $mediaCount; $i++)
                                    <button type="button" data-bs-target="#mediaCarousel{{ $project->id }}" data-bs-slide-to="{{ $i }}" @if($i == 0) class="active" @endif></button>
                                @endfor
                            </div>
                        @endif
                    </div>

                    <!-- Post Footer -->
                  <div class="d-flex justify-content-around mt-4 text-muted">
                    @guest
                        <!-- Guest user: show button that redirects on click -->
                       <button class="btn vote-btn" onclick="window.location.href = '/login'">
                        <i class="fas fa-vote-yea"></i> <span class="vote-count">{{ $project->votes->count() }}</span> {{ __('Vote') }}
                    </button>
                    @else
                    <audio id="voteSound" src="https://www.myinstants.com/media/sounds/audiocutter_facebook-like-sound-effect2.mp3" preload="auto"></audio>
                        <!-- Authenticated user: interactive vote button -->
                        <button class="btn vote-btn" data-id="{{ $project->id }}">
                            <i class="fas fa-vote-yea {{ $project->isVotedBy(auth()->user()) ? 'text-success' : '' }}"></i>
                            <span class="vote-count">{{ $project->votes->count() }}</span> {{ __('Vote') }}
                        </button>
                    @endguest




                            <div>
                @guest
                    <i class="fas fa-comment pt-2" onclick="window.location.href='/login'" style="cursor: pointer;"></i>  {{ __('Comments') }} 
                @else
                 <span
   style="cursor: pointer;"
   data-bs-toggle="modal"
   data-bs-target="#commentsModal"
   onclick="openCommentModal({{ $project->id }})"
>
    <i class="fas fa-comment pt-2"></i>
    <span id="commentCount_{{ $project->id }}">{{ $project->comments->count() }}</span> {{ __('Comments') }} 
</span>

                    
                    
                @endguest
</div>

  <!--social-->
               <div class="dropdown pt-1">
    <strong><i class="fas fa-share-alt " style="cursor: pointer;" data-bs-toggle="dropdown"></i> {{ __('Share') }}</strong>

    <ul class="dropdown-menu p-2" style="min-width: 180px;">
        <li>
            <a class="dropdown-item" target="_blank" 
               href="https://facebook.com/sharer/sharer.php?u={{ urlencode(route('seller.project.single', $project->slug)) }}">
                <i class="fab fa-facebook"></i> Facebook
            </a>
        </li>
        <li>
            <a class="dropdown-item" target="_blank"
               href="https://twitter.com/intent/tweet?url={{ urlencode(route('seller.project.single', $project->slug)) }}">
                <i class="fab fa-twitter"></i> Twitter
            </a>
        </li>
        <li>
            <a class="dropdown-item" target="_blank"
               href="https://api.whatsapp.com/send?text={{ urlencode($project->name . ' ' . route('seller.project.single', $project->slug)) }}">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
        </li>
        <li>
            <a class="dropdown-item disabled" href="#">
                <i class="fab fa-instagram"></i> Instagram
            </a>
        </li>
    </ul>
</div>
                </div>

                </div>
         
    </div>
</section>
<!-- Modal Structure -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Comments') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="commentsContainer">{{ __('Loading comments') }}...</div>

        <form id="commentForm">
          @csrf
             <input type="hidden" name="project_id" id="commentProjectId">
          <textarea name="content" class="form-control mt-3" placeholder="Write a new comment..." required></textarea>
          <button type="submit" class="btn btn-primary mt-2">{{ __('Post Comment') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).on('click', '.toggle-replies', function () {
    var commentId = $(this).data('comment-id');
    var container = $('#replies-' + commentId);
    
    container.slideToggle();

    var link = $(this);
    if (link.text().includes('Load')) {
        link.text('Hide Replies');
    } else {
        link.text(`Load Replies (${container.find('.reply-form').length ? container.find('.reply-form').siblings().length : 0})`);
    }
});


let currentProjectId = null;

function openCommentModal(projectId) {
    currentProjectId = projectId; // store the current project id
    
    $('#commentProjectId').val(projectId); // set in the form
    loadComments(projectId);
}

function loadComments(projectId) {
    $.get(`/projects/${projectId}/comments`, function(response) {
        $('#commentsContainer').html(response.html);
        $('#commentCount_' + projectId).text(response.count);
    });
}

$(document).on('submit', '#commentForm', function(e) {
    e.preventDefault();
    $.ajax({
        url: `/projects/${currentProjectId}/comments`,
        method: 'POST',
        data: $(this).serialize(),
        success: function() {
            loadComments(currentProjectId);
            $('#commentForm')[0].reset();
        }
    });
});

$(document).on('submit', '.reply-form', function(e) {
    e.preventDefault();
    let form = $(this);
    let projectId = currentProjectId || form.find('input[name="project_id"]').val();

    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize() + '&project_id=' + projectId,
        success: function() {
            loadComments(projectId);
        }
    });
});
</script>

 <script>
document.querySelectorAll('.vote-btn').forEach(button => {
    button.addEventListener('click', function () {
        const projectId = this.getAttribute('data-id');
        fetch(`/project/vote/${projectId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            const icon = this.querySelector('i');
            const countSpan = this.querySelector('.vote-count');
            if (data.status === 'voted') {
                icon.classList.add('text-success');
                  document.getElementById('voteSound').play();
            } else {
                icon.classList.remove('text-success');
            }
            countSpan.textContent = data.count;
        });
    });
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.read-more-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-target');
                document.getElementById(`more-desc-${id}`).style.display = 'inline';
                this.style.display = 'none';
                document.querySelector(`.read-less-btn[data-target="${id}"]`).style.display = 'inline';
            });
        });

        document.querySelectorAll('.read-less-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-target');
                document.getElementById(`more-desc-${id}`).style.display = 'none';
                this.style.display = 'none';
                document.querySelector(`.read-more-btn[data-target="${id}"]`).style.display = 'inline';
            });
        });
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Step 1: Initialize all carousels manually
    document.querySelectorAll('.carousel').forEach(carousel => {
        new bootstrap.Carousel(carousel, {
            interval: false,
            ride: false,
            touch: true,
            pause: false,
            wrap: true
        });
    });

    // Step 2: Enable manual swipe/drag navigation
    document.querySelectorAll('.carousel').forEach(carousel => {
        let startX = 0;
        let isDown = false;

        carousel.addEventListener('mousedown', e => {
            isDown = true;
            startX = e.clientX;
        });

        carousel.addEventListener('mouseup', e => {
            if (!isDown) return;
            isDown = false;
            let diff = e.clientX - startX;

            const instance = bootstrap.Carousel.getInstance(carousel);
            if (diff > 50) {
                instance.prev();
            } else if (diff < -50) {
                instance.next();
            }
        });

        // Touch support
        let touchStartX = 0;
        carousel.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
        });

        carousel.addEventListener('touchend', e => {
            let touchEndX = e.changedTouches[0].clientX;
            let diff = touchEndX - touchStartX;

            const instance = bootstrap.Carousel.getInstance(carousel);
            if (diff > 50) {
                instance.prev();
            } else if (diff < -50) {
                instance.next();
            }
        });
    });
});
</script>
@endsection
