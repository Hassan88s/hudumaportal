@if(request()->is('buyer/live-chat'))
    @include('livechat::frontend.buyer.partials.livechat-two')
@else
    @include('livechat::frontend.buyer.partials.livechat-one')
@endif