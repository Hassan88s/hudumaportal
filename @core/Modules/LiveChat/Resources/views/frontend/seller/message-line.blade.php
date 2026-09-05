@if(request()->is('buyer/load-latest-messages'))
    @include('livechat::frontend.seller.partials.message-line-two')
@else
    @include('livechat::frontend.seller.partials.message-line-one')
@endif