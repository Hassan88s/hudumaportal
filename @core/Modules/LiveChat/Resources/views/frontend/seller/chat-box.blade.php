@if(request()->is('seller/live-chat'))
    @include('livechat::frontend.seller.partials.chat-box-two')
@else
    @include('livechat::frontend.seller.partials.chat-box-one')
@endif
