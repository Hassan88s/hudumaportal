@if(request()->is('buyer/live-chat'))
    @include('livechat::frontend.buyer.partials.chat-box-two')
@else
    @include('livechat::frontend.buyer.partials.chat-box-one')
@endif