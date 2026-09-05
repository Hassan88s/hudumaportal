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