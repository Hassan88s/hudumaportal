@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{ __('Live Chat') }}
@endsection

@section('style')
  <style>
      /*new theme chat css*/
      .base_sent.msg_container {
          display: flex;
          align-items: flex-start;
          gap: 10px;
          justify-content: flex-end;
          text-align: right;
      }

      .conversation-message-contents {
          flex-grow: 1;
      }

      .conversation-bg-thumb {
          max-width: 40px;
          border-radius: 50%;
      }

      .conversation-bg-thumb img {
          border-radius: 50%;
      }

      .conversation-message-contents > p {}

      .conversation-message-contents > p {
          background-color: #1dbf7329;
          padding: 10px 20px;
          border-radius: 0 10px 10px 10px;
          color: #000;
          font-size: 16px;
          font-weight: 500;
          display: inline-block;
      }

      .conversation-message-contents p {
          background-color: #F5F6F9;
          padding: 10px 20px;
          border-radius: 10px 0px 10px 10px;
          color: #000;
          font-size: 16px;
          font-weight: 500;
          display: table;
          margin-left: auto;
      }

      .conversation-message-contents time {
          font-size: 16px;
          color: var(--paragraph-color);
          font-weight: 500;
          display: block;
          font-size: 12px;
      }
      .conversation-message-contents .msg_sent img {
          max-width: 250px;
          background-color: #F5F6F9;
          padding: 10px 20px;
          border-radius: 10px 0px 10px 10px;
          color: #000;
          font-size: 16px;
          font-weight: 500;
          display: table;
          margin-left: auto;
      }

  </style>
@endsection
@section('content')
<x-frontend.seller-buyer-preloader/>
@include('frontend.user.buyer.partials.sidebar-two')
<div class="dashboard__right">
    @include('frontend.user.buyer.header.buyer-header')
    <div class="dashboard__body">
        <div class="dashboard__inner">
            <div class="chat_wrapper">
                <div class="chat_wrapper__flex" id="app">
                    <div class="chat_sidebar d-lg-none">
                        <i class="fas fa-bars"></i>
                    </div>
                    <div class="chat_wrapper__contact">
                        <div class="chat_wrapper__contact__close">
                        <!-- Chat section start -->
                        <div id="app">
                            <input type="hidden" id="current_user" value="{{ \Auth::user()->id }}" />
                            <h5 class="panel-title mb-3">{{__('All Contacts')}}</h5>
                                @if($users->count() > 0)
                                    <ul class="chat_wrapper__contact__list" id="users">    
                                    @foreach($users as $user)
                                    
                                    <li class="chat_wrapper__contact__list__item chat_item">
                                        <div class="chat_wrapper__contact__list__flex">
                                            <div class="chat_wrapper__contact__list__thumb">
                                                {!! render_image_markup_by_attachment_id($user->sellerList->image) !!}

                                                     @if(\Illuminate\Support\Facades\Cache::has('user-is-online-' . $user->sellerList->id))
                                                         <div class="notification__dots active"></div>
                                                     @else
                                                         <div class="notification__dots"></div>
                                                     @endif
                                            </div>
                                            <div class="chat_wrapper__contact__list__contents">
                                                <div class="chat_wrapper__contact__list__contents__details">
                                                    <h4 class="chat_wrapper__contact__list__contents__title">
                                                        @php $user_image = get_attachment_image_by_id($user->sellerList->image); @endphp
                                                        <a href="javascript:void(0)" class="chat-toggle"
                                                           data-id="{{ $user->sellerList->id }}"
                                                           data-user="{{ $user->sellerList->username }}"
                                                           data-user_image="{{ !empty($user_image) ? $user_image["img_url"] :  asset('assets/frontend/img/user-no-image.png') }}"
                                                           data-user-status="{{ \Illuminate\Support\Facades\Cache::has('user-is-online-' . $user->sellerList->id) ? __('Active Now') : __('Offline') }}"
                                                        >{{ $user->sellerList->username }}</a>
                                                    </h4>
                                                    <p class="chat_wrapper__contact__list__contents__para">
                                                        @if(\Illuminate\Support\Facades\Cache::has('user-is-online-' . $user->sellerList->id))
                                                            <span class="text-success">{{ __('Online') }}</span>
                                                        @else
                                                            <span class="text-secondary">{{ __('Offline') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <span class="chat_wrapper__contact__list__time">
                                                     @if(\Illuminate\Support\Facades\Cache::has('user-is-online-' . $user->sellerList->id))@else
                                                        {{ \Carbon\Carbon::parse($user->last_seen)->diffForHumans() }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </li>   
                                    @endforeach
                                </ul>
                                @else
                                    <div class="chat_wrapper__details__inner__chat__contents">
                                        <p class="no_data_found_for_buyer_seller_panel">
                                            {{ __('No Contacts Yet')}}
                                        </p>                              
                                    </div>
                                @endif
                            <!-- Chat section End -->    
                        </div>   

                     </div>       
                 </div>
                <div id="chat-overlay" class="chat_wrapper__details"></div>
                @include('livechat::frontend.buyer.partials.chat-box-two')
             </div>
           </div>

        </div>
    </div>    
 @endsection
 @section('scripts')
  @if(moduleExists("LiveChat"))
   <x-livechat.widget-js />
   @endif

      <script>
          $(document).ready(function(){

              $(document).on('change', '.new_image_add', function (event) {
                  $(this).next('#uploadImage').html(event.target.files[0].name);
              });

              $(document).on('click', '.chat_send_message_paper_button_new_design', function (event) {
                  setTimeout(function() {
                      $(".new_image_add").val(''); // clear image input value
                      $("#uploadImage").text("{{ __('Attach Files') }}"); // file name remove
                  }, 1000);
              });
          });
      </script>
 @endsection

