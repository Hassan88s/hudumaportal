  <div class="chat_box" id="chat_box" style="display: none">
      <!-- Seller info-->
      <div class="chat_wrapper__details__header">
          <div class="chat_wrapper__details__header__flex">
              <div class="chat_wrapper__details__header__thumb">
                  <a href="javascript:void(0)" class="img-text">
                      <h3 class="panel-title">
                          <span class="glyphicon glyphicon-comment"></span>
                          <span class="img-text">
                            <img class="user-image-avater" src="{{ asset('assets/frontend/img/static/user_profile.png') }}" alt="img">
                        </span>
                      </h3>
                  </a>

                  <div class="notification__dots active"></div>
              </div>
              <div class="chat_wrapper__details__header__contents">
                  <div class="chat_wrapper__contact__list__contents__flex flex-between">
                      <h4 class="chat_wrapper__details__header__contents__title">
                          <a href="javascript:void(0)" class="chat-user"></a>
                      </h4>
                  </div>
                  <p class="chat_wrapper__details__header__contents__para user_online_offline_status"> </p>
              </div>
          </div>
      </div>

      <!-- load all chat buyer to seller & seller to buyer -->
      <div class="chat_wrapper__details__inner">
              <div class="panel-body chat-area"></div>
      </div>

      <!-- send message -->
      <div class="chat_wrapper__details__footer profile-border-top">
          <div class="chat_wrapper__details__footer__form custom-form">

              <!-- text message -->
              <div class="single-input">
                  <textarea class="form--control form-message input-sm chat_input" placeholder="{{__('Write your message here...')}}"></textarea>
              </div>

              <div class="hat-wrapper-details-footer-btn btn_flex justify-content-end mt-2">
                  <form method="post" enctype="multipart/form-data" class="upload-frm" data-to-user-prefix="buyer" id="dashbaord_chat_form">
                      <label class="dropMedia dashboard_table__title__btn btn-outline-border radius-5" for="inputTag">
                          <input type="file" name="image" class="new_image_add dropMedia__uploader image" accept="image/png, image/gif, image/jpeg" id="inputTag"  />
                          <span class="dropMedia__file" id="uploadImage"><i class="fa-solid fa-paperclip"></i> {{ __('Attach Files') }}</span>
                      </label>
                  </form>
                  <!--cusotm offer-->
<!-- Button trigger modal -->
<!--<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">-->
<!--  Custom Offer-->
<!--</button>-->

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Send Custom Offer To buyer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
       <form>
  <div class="form-group">
    <label for="exampleFormControlInput1">Job Title</label>
    <input type="email" class="form-control" id="title" name="title">
  </div>
  <div class="form-group">
    <label for="exampleFormControlSelect1">Job Price</label>
     <input type="text" name="Price" class="form-control" id="Price" name="Price">
  </div>
  
   <div class="form-group">
    <label for="exampleFormControlSelect1">Days</label>
     <input type="text" name="Days" class="form-control" id="Days" name="Days">
  </div>
 
  <div class="form-group">
    <label for="exampleFormControlTextarea1">Job Description</label>
    <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
  </div>
</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

                  <!-- send message btn -->
                  <button class="dashboard_table__title__btn btn-bg-1 radius-5 btn-chat chat_send_message_paper_button_new_design" type="button"
                          data-to-user="1" data-to-user-prefix="buyer" style="border: none">{{ __('Send Message') }}
                  </button>

              </div>
          </div>
      </div>
  </div>

  <input type="hidden" class="to_user_id" value="" />
  </div>
