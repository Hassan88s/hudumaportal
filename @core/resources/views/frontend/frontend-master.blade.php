@include('frontend.partials.header')
@yield('content')

{{-- Rafiki Rewards — Share Prompt Modal (PDF §16, §18). Triggered by
     session flash 'refer_prompt' set from happy-moment controllers like
     order_payment_success. Only renders for logged-in users with a
     referral_code. --}}
@auth
    @include('frontend.partials.referral-share-prompt')
@endauth

@include('frontend.partials.footer')
