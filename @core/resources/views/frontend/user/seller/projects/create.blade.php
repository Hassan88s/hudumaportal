@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Manage Projects')}}
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
                            <h4 class="dashboards-title"><strong>{{__('Add Project')}}</strong></h4>
                           
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
            
                    <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
                       <form action="{{ route('seller.project.store') }}" method="POST" enctype="multipart/form-data" class="p-3 border rounded">
    @csrf

    <div class="mb-3">
        <label class="form-label">{{__('Project Name')}}:</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">{{__('Category')}}:</label>
      
       <select class="form-control" name="cate_id"  required>
             @foreach($categories as $category)
           <option value="{{$category->id}}">{{__($category->name)}}</option>
           @endforeach
       </select>
    </div>

    <div class="mb-3">
        <label class="form-label">{{__('Description')}}:</label>
        <textarea name="description" id="classic_editor" class="form-control"></textarea>
    </div>

   <div class="mb-3">
    <label class="form-label">{{__('Images')}}: <span class="text text-danger">{{ __('(Max file Size 5 MB only jpg, jpeg, png files allowed, Max 10 files)') }}</span></label>
    <input type="file" name="images[]" class="form-control" multiple accept="image/*" id="images" onchange="validateFiles()">
    <span id="image-error" class="text-danger" style="display:none;"></span>
</div>

    <div class="mb-3">
        <label class="form-label">{{__('Video')}}: <span class="text text-danger">{{ __('(Max file Size 30 MB only mp4,mov,avi,wmv file is allowed)') }}</span></label>
        <input type="file" name="video" class="form-control" accept="video/*" id="video">
           <span id="video-error" class="text-danger" style="display:none;"></span>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> {{__('Save Project')}}
        </button>
    </div>
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#classic_editor'))
        .catch(error => console.error(error));
        
        
</script>
<script>
    
    // document.getElementById('images').addEventListener('change', function(event) {
    //     var errorElement = document.getElementById('image-error');
    //     errorElement.style.display = 'none'; 
    //     var files = event.target.files;
    //     var maxFileSize = 5 * 1024 * 1024; 

    //     for (var i = 0; i < files.length; i++) {
    //         if (files[i].size > maxFileSize) {
    //             errorElement.textContent = "File size exceeds 5 MB!";
    //             errorElement.style.display = 'block'; 
    //             event.target.value = ''; 
    //             return; 
    //         }
    //     }
    // });
 
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


    // Video file size validation (Max 30 MB)
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
  


@endsection
