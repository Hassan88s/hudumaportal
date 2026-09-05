@include('frontend.user.seller.partials.header-two')
<!--Dashboard Markup -->
<div class="body-overlay"></div>
<div class="dashboard__area @if(Auth::guard('web')->user()->user_type == 0) seller_look @endif">
    <div class="container-fluid p-0">
        <div class="dashboard__contents__wrapper">
            <div class="dashboard__icon">
                <div class="dashboard__icon__bars sidebar-icon">
                    <i class="fa-solid fa-bars"></i>
                </div>
            </div>
             @yield('content')
        </div>
    </div>
</div>
@include('frontend.user.seller.partials.footer-two')
<!-- Pusher and Laravel Echo (CDN version) -->
<!-- Load Laravel Echo and Pusher from CDN (without integrity) -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>

<audio id="voteSound" src="https://www.myinstants.com/media/sounds/audiocutter_facebook-like-sound-effect2.mp3" preload="auto"></audio>

<script>
    // Unlock sound on first interaction
    function unlockAudio() {
        const audio = document.getElementById('voteSound');
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
            document.removeEventListener('click', unlockAudio);
            document.removeEventListener('scroll', unlockAudio);
        }).catch(() => {});
    }
    document.addEventListener('click', unlockAudio);
    document.addEventListener('scroll', unlockAudio);

    const countEl = document.querySelector('.dashboard__header__notification__number');
    const list = document.querySelector('.dashboard__header__notification__wrap__list');

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ config("broadcasting.connections.pusher.key") }}',
        cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
        forceTLS: true,
    });

    Echo.private(`App.User.{{ Auth::id() }}`)
        .notification((notification) => {
           // console.log("🔔 New Notification Received:", notification);

            
            const audio = document.getElementById('voteSound');
            if (audio) audio.play().catch(() => {});

      
            countEl.textContent = parseInt(countEl.textContent || 0) + 1;

          
            let url = '#';
            let message = '';

            if (notification.seller_last_ticket_id) {
         
                url = `/seller/support-ticket/view/${notification.seller_last_ticket_id}`;
                message = notification.order_ticcket_message + ' #' + notification.seller_last_ticket_id;
            } else if (notification.order_id) {
       
                url = `/seller/orders-details/${notification.order_id}`;
                message = notification.order_message + ' #' + notification.order_id;
            } else if (notification.last_ticket_id) {
    
                url = `/buyer/support-ticket/view/${notification.last_ticket_id}`;
                message = notification.order_ticcket_message + ' #' + notification.last_ticket_id;
            }
                 else if (notification.message)  {
        
                    const notifId = notification.id;
                    message = notification.message;
                    url = `/seller/general-notification/view/${notifId}`; // Construct URL using ID
                } else {
                    message = 'You have a new notification.';
                }


        
            const html = `
                <li class="dashboard__header__notification__wrap__list__item">
                    <div class="dashboard__header__notification__wrap__list__flex">
                        <a href="${url}">
                            <div class="dashboard__header__notification__wrap__list__icon">
                                <i class="las la-bell"></i>
                            </div>
                            <div class="dashboard__header__notification__wrap__list__contents">
                                ${message}
                                <span class="dashboard__header__notification__wrap__list__contents__sub">${new Date().toLocaleString()}</span>
                            </div>
                        </a>
                    </div>
                </li>
            `;
            list.insertAdjacentHTML('afterbegin', html);
        });
</script>
