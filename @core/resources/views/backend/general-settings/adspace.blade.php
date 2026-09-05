@extends('backend.admin-master')
@section('site-title')
    {{__('Ads Space  Settings')}}
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                <x-msg.success/>
                <x-msg.error/>
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{__("Ad Spaces Settings")}}</h4>
                        <form action="{{route('admin.general.updateadsspace.settings')}}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="disable_user_otp_verify"><strong>{{__('Select Ad space')}}</strong></label>
                                <select name="parent_id" class="form-control" onchange="window.location.href = 'https://hudumaportal.co.tz/admin-home/general-settings/adspace'+'/'+this.value;">
                                   <?php foreach ($adsapce_places as $key => $value): ?>
                            <option value="<?=  $value->id; ?>" <?= $value->ads_space == $single_ad->ads_space ? 'selected' : ''; ?>><?= $value->ads_space; ?></option>
                        <?php endforeach; ?>
                                </select>
                               
                            </div>  
                            
                             <h4 class="mt-5">Setting for <?= $single_ad->ads_space;?></h4>
                            
                            <div class="col-lg-12 mt-5">
                                  <h4 class="header-title mb-4">{{__("Desktop Banner")}}</h4>
                                <div class="row">
                                <div class="col-lg-6">
                                   
                               <div class="form-group">
                                <label for="TWILIO_NUMBER"><strong>{{__('Width')}} <span class="text-danger"></span> </strong></label>
                                    <input type="number" class="form-control space_remove" name="d_width" value="<?= $single_ad->d_width;?>"
                                    placeholder="{{ __('Width')}}">  
                                    
                            </div>
                            </div>
                              <div class="col-lg-6">
                               <div class="form-group">
                                <label for="TWILIO_NUMBER"><strong>{{__('Height')}} <span class="text-danger"></span> </strong></label>
                                    <input type="number" class="form-control space_remove" name="d_height" value="<?= $single_ad->d_height;?>"
                                    placeholder="{{ __('Height')}}">  
                                    
                            </div>
                            </div>
                            </div>
                            </div>
                         <div class="form-group mt-3">
                                <label for="TWILIO_SID"><strong>{{__('Paste Ad Code')}} <span class="text-danger">*</span> </strong></label>
                                   <textarea name="d_google_adsense_code" class="form-control" placeholder="Paste Ads Code Here" style="min-height: 140px;"><?= $single_ad->ads_code_desktop;?></textarea>
                            </div>
                            <!--///mobile-->
                            <hr>
                            
                               <div class="col-lg-12 mt-5">
                                  <h2 class="header-title mb-4">{{__("Mobile Banner")}}</h2>
                                <div class="row">
                                <div class="col-lg-6">
                                   
                               <div class="form-group">
                                <label for="TWILIO_NUMBER"><strong>{{__('Width')}} <span class="text-danger"></span> </strong></label>
                                    <input type="number" class="form-control space_remove" name="m_width" value="<?= $single_ad->m_width;?>"
                                    placeholder="{{ __('Width')}}">  
                                    
                            </div>
                            </div>
                              <div class="col-lg-6">
                               <div class="form-group">
                                <label for="TWILIO_NUMBER"><strong>{{__('Height')}} <span class="text-danger"></span> </strong></label>
                                    <input type="number" class="form-control space_remove" name="m_height" value="<?= $single_ad->m_height;?>"
                                    placeholder="{{ __('Height')}}">  
                                    
                            </div>
                            </div>
                            </div>
                            </div>
                         <div class="form-group mt-3">
                                <label for="TWILIO_SID"><strong>{{__('Paste Ad Code')}} <span class="text-danger">*</span> </strong></label>
                                   <textarea name="m_google_adsense_code" class="form-control" placeholder="Paste Ads Code Here" style="min-height: 140px;"><?= $single_ad->ads_code_mobile;?></textarea>
                            </div>

                            <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                <x-msg.success/>
                <x-msg.error/>
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{__("Google Adsense Code")}}</h4>
                        <form action="{{route('admin.general.adsensecode')}}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!--Adsense env settings -->
            
                            <div class="form-group mt-3">
                                <label for="TWILIO_SID"><strong>{{__('Add Adsense Code Here')}} <span class="text-danger">*</span> </strong></label>
                                   <textarea name="google_adsense_code" class="form-control" placeholder="" style="min-height: 140px;"><?= $adesne_code->option_value; ?></textarea>
                            </div>

                        

                           
                            <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update')}}</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script>
        (function($){
            "use strict";
            $(document).ready(function (e) {

                // remove input space
                $('.space_remove').keypress(function( e ) {
                    if(e.which === 32)
                        return false;
                });

                // remove copy past text space
                $('.space_remove').on('paste', function(e) {
                    var inputElement = this;
                    setTimeout(function() {
                        var pastedText = $(inputElement).val();
                        var cleanedText = pastedText.replace(/\s+/g, '');
                        $(inputElement).val(cleanedText);
                    }, 0);
                });


            })
        })(jQuery);
    </script>
@endsection