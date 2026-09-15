<footer class="footer-area color-two new-section-bg-2">
    <div class="footer-top padding-top-100 padding-bottom-70">
        <div class="container container-two">
            <div class="row">
                {!! render_frontend_sidebar('footer_two') !!}
            </div>

            {{-- Rafiki Rewards + Community Pages — quick access links --}}
            <div class="row" style="border-top:1px solid rgba(255,255,255,.1);padding-top:24px;margin-top:14px">
                <div class="col-12">
                    <div style="display:flex;flex-wrap:wrap;gap:16px 32px;align-items:center;justify-content:center">
                        {{-- Top 100 Sellers — hidden for now
                        <a href="{{ url('/top-sellers') }}"
                           style="color:rgba(255,255,255,.85);text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px;transition:color .15s"
                           onmouseover="this.style.color='#ff8a54'"
                           onmouseout="this.style.color='rgba(255,255,255,.85)'">
                            <i class="las la-crown"></i> {{ __('Top 100 Sellers') }}
                        </a>
                        --}}
                        <a href="{{ url('/champions') }}"
                           style="color:rgba(255,255,255,.85);text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px;transition:color .15s"
                           onmouseover="this.style.color='#ff8a54'"
                           onmouseout="this.style.color='rgba(255,255,255,.85)'">
                            <i class="las la-medal"></i> {{ __('Huduma Champions') }}
                        </a>
                        <a href="{{ url('/leaderboard') }}"
                           style="color:rgba(255,255,255,.85);text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px;transition:color .15s"
                           onmouseover="this.style.color='#ff8a54'"
                           onmouseout="this.style.color='rgba(255,255,255,.85)'">
                            <i class="las la-trophy"></i> {{ __('Rafiki Leaderboard') }}
                        </a>
                        <a href="{{ url('/referral') }}"
                           style="color:rgba(255,255,255,.85);text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:6px;transition:color .15s"
                           onmouseover="this.style.color='#ff8a54'"
                           onmouseout="this.style.color='rgba(255,255,255,.85)'">
                            <i class="las la-gift"></i> {{ __('Refer & Earn') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright-area style-02 copyright-border">
        <div class="container container-two">
            <div class="row align-items-center">
                {!! render_frontend_sidebar('copyright') !!}
            </div>
        </div>
    </div>
</footer>