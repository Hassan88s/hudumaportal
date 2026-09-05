@if(request()->is('buyer/live-chat'))
    @include('livechat::frontend.buyer.partials.message-line-two')
@else
    @include('livechat::frontend.buyer.partials.message-line-one')
@endif