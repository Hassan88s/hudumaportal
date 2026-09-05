@if(request()->is('seller/live-chat'))
    @include('livechat::frontend.seller.partials.livechat-two')
@else
    @include('livechat::frontend.seller.partials.livechat-one')
@endif