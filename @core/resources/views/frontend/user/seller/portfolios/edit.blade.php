@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Manage Portfolio')}}
@endsection
@section('style')
    <x-media.css/>
    <style>
        img.no-image {
            width: 119px;
        }
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php $default_lang = get_default_language(); @endphp
            <!-- Dashboard area Starts -->
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        <!-- buyer header -->
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
               
 <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboard__headerContents__title">
                        </div>
                    </div>
                </div>
                
  
                   <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboards-title"><strong>{{__('Create Portfolio')}}</strong></h4>
                           
                        </div>
                    </div>
                </div>
  @if ($errors->any())
    <div style="color: red;">
        Please fix the following errors:
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            
     <form action="{{ route('seller.portfolio.update', $portfolio->id) }}" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
    @csrf

    <div class="mb-3">
        <label class="form-label">{{ __('Project Name')}}:</label>
        <input type="text" name="name" value="{{ $portfolio->name }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">{{ __('Description')}}:</label>
        <textarea name="description" id="description" class="form-control" rows="4">{{ $portfolio->description }}</textarea>
    </div>
    
     <div class="mb-3">
        <label class="form-label">{{ __('Maximum Cost')}}:</label>
          <input type="text" name="cost" value="{{ $portfolio->project_cost }}" class="form-control" >
    </div>
    
    
    <div class="mb-3">
        <label class="form-label">{{ __('Duration')}}:</label>
        <input type="text" name="Duration" value="{{ $portfolio->timeline }}" class="form-control" >
    </div>


    <div class="mb-3">
    <label class="form-label">{{ __('Existing Images')}}:</label><br>
    @foreach($portfolio->images as $image)
         <div class="d-inline-block position-relative">
            <img src="{{ asset('@core/public/portfolios/images/' . basename($image->image)) }}" class="img-thumbnail me-2" width="100">
           
        </div>
        <button type="button" class="btn btn-danger btn-sm" onclick="deletePortfolioImage({{ $image->id }})">{{ __('Delete')}}</button>

    @endforeach
</div>

    <div class="mb-3">
        <label class="form-label">{{ __('New Images (optional)')}}: <span class="text text-danger">({{ __('Max file Size 5 MB only jpg, jpeg, png files allowed, Max 10 files') }})</span></label>
        <input type="file" name="images[]" class="form-control" multiple accept="image/*" id="images" onchange="validateFiles()">
         <span id="image-error" class="text-danger" style="display:none;"></span>
         
        
    </div>

    <div class="mb-3">
        <label class="form-label">{{ __('Existing Video')}}:</label><br>
        @if($portfolio->video)
            <video controls class="border rounded" width="200">
               <source src="{{ asset('@core/public/' . $portfolio->video) }}" type="video/mp4" >

            </video>
        @else
            <p class="text-muted">{{ __('No video uploaded')}}.</p>
        @endif
    </div>

    <div class="mb-3">
        <label class="form-label">{{ __('New Video (optional)')}}: <span class="text text-danger">({{ __('Max file Size 30 MB only mp4,mov,avi,wmv file is allowed') }})</span></label>
        <input type="file" name="video" class="form-control" accept="video/*" id="video">
           <span id="video-error" class="text-danger" style="display:none;"></span>
    </div>

    <button type="submit" class="btn btn-primary">{{ __('Update Portfolio')}}</button>
</form>


                    </div>
    
            </div>
        </div>
   
        </div>
    </div>
    </div>
    <!-- Buyer Profile Edit Modal End-->
    <x-media.markup :type="'web'"/>
@endsection
@section('scripts')
    <x-media.js :type="'web'"/>
    <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
  

<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#description'))
        .catch(error => console.error(error));


</script>
        <script>
    function deletePortfolioImage(imageId) {
        if (!confirm('Are you sure?')) return;

        fetch("{{ route('seller.portfolio.image.delete', ':id') }}".replace(':id', imageId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Image deleted successfully!');
                
                  location.reload();
              } else {
                  alert('Failed to delete image.');
              }
          }).catch(error => {
              console.error('Error:', error);
              alert('Something went wrong.');
          });
    }
</script>
<script>
    
        function validateFiles() {
        var fileInput = document.getElementById('images');
        var fileError = document.getElementById('image-error');
        
        // Check if more than 10 files are selected
        if (fileInput.files.length > 10) {
            fileError.style.display = 'block';
            fileError.textContent = 'You can only upload a maximum of 10 files.';
            fileInput.value = ''; // Clear the input
        } else {
            fileError.style.display = 'none';
        }
        
        // Check file size and type
        for (var i = 0; i < fileInput.files.length; i++) {
            var file = fileInput.files[i];
            if (file.size > 5 * 1024 * 1024) { // 5 MB
                fileError.style.display = 'block';
                fileError.textContent = 'Each file must be smaller than 5 MB.';
                fileInput.value = ''; // Clear the input
                break;
            } else if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                fileError.style.display = 'block';
                fileError.textContent = 'Only JPG, JPEG, or PNG files are allowed.';
                fileInput.value = ''; // Clear the input
                break;
            } else {
                fileError.style.display = 'none';
            }
        }
    }

   
    document.getElementById('video').addEventListener('change', function(event) {
        var errorElement = document.getElementById('video-error');
        errorElement.style.display = 'none'; 
        var file = event.target.files[0]; 
        var maxFileSize = 30 * 1024 * 1024; 

        if (file && file.size > maxFileSize) {
            errorElement.textContent = "File size exceeds 30 MB!";
            errorElement.style.display = 'block'; 
            event.target.value = ''; 
        }
    });
</script>
@endsection
