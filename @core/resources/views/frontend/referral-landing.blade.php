@extends('frontend.frontend-master')
@section('site-title'){{ __('Rafiki Rewards — Earn With Every Friend You Bring') }}@endsection

@section('style')
<style>
    .rl-page{background:#fafbfc;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#1f2733;padding:0;margin:0}
    .rl-page .container{max-width:1180px;margin:0 auto;padding:0 20px}

    /* ═══ HERO ═══ */
    .rl-hero{background:linear-gradient(135deg,#ff8a54 0%,#ff6b3d 100%);color:#fff;padding:70px 0 90px;position:relative;overflow:hidden}
    .rl-hero::before{content:'';position:absolute;top:-100px;right:-100px;width:400px;height:400px;background:rgba(255,255,255,.08);border-radius:50%;pointer-events:none}
    .rl-hero::after{content:'';position:absolute;bottom:-150px;left:-150px;width:500px;height:500px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none}
    .rl-hero .container{position:relative;z-index:1}
    .rl-hero .kicker{display:inline-block;background:rgba(255,255,255,.2);color:#fff;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;margin-bottom:20px;backdrop-filter:blur(10px)}
    .rl-hero h1{font-size:52px;font-weight:800;line-height:1.1;margin:0 0 20px;letter-spacing:-1px}
    .rl-hero h1 span{color:#fff7ed;position:relative;display:inline-block}
    .rl-hero h1 span::after{content:'';position:absolute;bottom:6px;left:0;right:0;height:12px;background:rgba(255,255,255,.25);z-index:-1;transform:skewX(-8deg)}
    .rl-hero .sub{font-size:19px;font-weight:400;line-height:1.5;max-width:640px;margin:0 0 32px;color:rgba(255,255,255,.92)}
    .rl-hero .cta-row{display:flex;flex-wrap:wrap;gap:12px;align-items:center}
    .rl-hero .btn-primary{background:#fff;color:#ff6b3d;padding:14px 28px;border-radius:10px;font-weight:700;font-size:15px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:transform .15s,box-shadow .15s;border:none;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.15)}
    .rl-hero .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);color:#ff6b3d}
    .rl-hero .btn-secondary{background:rgba(255,255,255,.15);color:#fff;padding:14px 28px;border-radius:10px;font-weight:600;font-size:15px;text-decoration:none;border:2px solid rgba(255,255,255,.4);transition:all .15s;display:inline-flex;align-items:center;gap:8px}
    .rl-hero .btn-secondary:hover{background:rgba(255,255,255,.25);border-color:rgba(255,255,255,.6);color:#fff}
    .rl-hero .trust-strip{display:flex;flex-wrap:wrap;gap:36px;margin-top:44px;padding-top:28px;border-top:1px solid rgba(255,255,255,.2)}
    .rl-hero .trust-strip .item .num{font-size:28px;font-weight:800;color:#fff;line-height:1}
    .rl-hero .trust-strip .item .lbl{font-size:12px;color:rgba(255,255,255,.85);letter-spacing:.4px;text-transform:uppercase;margin-top:6px}

    /* ═══ Section common ═══ */
    .rl-section{padding:70px 0}
    .rl-section h2{font-size:34px;font-weight:800;color:#1f2733;text-align:center;margin:0 0 12px;letter-spacing:-.5px}
    .rl-section .lead{font-size:16px;color:#6b7280;text-align:center;max-width:640px;margin:0 auto 46px;line-height:1.6}

    /* ═══ HOW IT WORKS ═══ */
    .rl-steps{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
    .rl-steps .step{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:32px 26px;position:relative;transition:transform .2s,box-shadow .2s}
    .rl-steps .step:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.08)}
    .rl-steps .step .num{width:44px;height:44px;background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:20px;margin-bottom:18px}
    .rl-steps .step h3{font-size:19px;font-weight:700;color:#1f2733;margin:0 0 10px}
    .rl-steps .step p{font-size:14px;color:#6b7280;line-height:1.6;margin:0}

    /* ═══ REWARDS ═══ */
    .rl-rewards{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    .rl-rewards .track{background:#fff;border-radius:16px;padding:32px;position:relative;overflow:hidden}
    .rl-rewards .track-provider{background:linear-gradient(135deg,#fff7ed 0%,#fff 60%);border:1px solid #fed7aa}
    .rl-rewards .track-client{background:linear-gradient(135deg,#eff6ff 0%,#fff 60%);border:1px solid #bfdbfe}
    .rl-rewards .track-hd{margin-bottom:24px}
    .rl-rewards .track-hd .tag{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px}
    .rl-rewards .track-provider .tag{color:#c2410c}
    .rl-rewards .track-client .tag{color:#1d4ed8}
    .rl-rewards .track-hd h3{font-size:24px;font-weight:800;color:#1f2733;margin:0 0 4px}
    .rl-rewards .track-hd .total{font-size:14px;color:#6b7280}
    .rl-rewards .track-hd .total strong{font-size:20px;font-weight:800}
    .rl-rewards .track-provider .track-hd .total strong{color:#ff6b3d}
    .rl-rewards .track-client .track-hd .total strong{color:#1d4ed8}
    .rl-rewards .stage{display:flex;gap:14px;padding:16px 0;border-top:1px dashed #e4e7ec}
    .rl-rewards .stage-num{width:32px;height:32px;border-radius:50%;background:#fff;color:#1f2733;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0;border:2px solid #e4e7ec}
    .rl-rewards .track-provider .stage-num{background:#ff8a54;color:#fff;border-color:#ff8a54}
    .rl-rewards .track-client .stage-num{background:#3b82f6;color:#fff;border-color:#3b82f6}
    .rl-rewards .stage-num.star{background:#8b5cf6!important;border-color:#8b5cf6!important;font-size:16px}
    .rl-rewards .stage-body h4{font-size:15px;font-weight:700;color:#1f2733;margin:0 0 4px}
    .rl-rewards .stage-body p{font-size:13px;color:#6b7280;margin:0 0 6px;line-height:1.5}
    .rl-rewards .stage-body .amt{font-size:14px;color:#10b981;font-weight:800}
    .rl-rewards .stage-body .amt.muted{color:#8b5cf6}
    .rl-rewards .stage-body .amt small{color:#6b7280;font-weight:500}

    /* ═══ QUALIFICATION ═══ */
    .rl-qual{display:grid;grid-template-columns:1fr 1fr;gap:24px;max-width:960px;margin:0 auto}
    .rl-qual .col{background:#fff;border-radius:14px;padding:28px}
    .rl-qual .col.yes{border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5 0%,#fff 100%)}
    .rl-qual .col.no{border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2 0%,#fff 100%)}
    .rl-qual h3{font-size:18px;font-weight:800;margin:0 0 16px;display:flex;align-items:center;gap:8px}
    .rl-qual .yes h3{color:#065f46}
    .rl-qual .no h3{color:#991b1b}
    .rl-qual ul{list-style:none;padding:0;margin:0}
    .rl-qual li{padding:10px 0;font-size:14px;color:#1f2733;display:flex;gap:10px;align-items:flex-start;border-top:1px dashed #e4e7ec}
    .rl-qual li:first-child{border-top:none}
    .rl-qual li .ic{width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;font-weight:800;margin-top:2px}
    .rl-qual .yes .ic{background:#10b981;color:#fff}
    .rl-qual .no .ic{background:#ef4444;color:#fff}

    /* ═══ PAYOUT FLOW ═══ */
    .rl-payout{background:#fff;border:1px solid #eef0f3;border-radius:16px;padding:40px;max-width:960px;margin:0 auto}
    .rl-payout .flow{display:grid;grid-template-columns:1fr 40px 1fr 40px 1fr 40px 1fr;gap:12px;align-items:center;margin-top:24px}
    .rl-payout .box{background:#f8f9fb;border-radius:12px;padding:20px;text-align:center;border:1px solid #eef0f3}
    .rl-payout .box .n{width:36px;height:36px;background:#ff8a54;color:#fff;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;margin-bottom:10px}
    .rl-payout .box h4{font-size:14px;font-weight:700;margin:0 0 6px}
    .rl-payout .box p{font-size:12px;color:#6b7280;margin:0;line-height:1.4}
    .rl-payout .arrow{text-align:center;color:#ff8a54;font-size:24px;font-weight:800}

    /* ═══ FAQ ═══ */
    .rl-faq{max-width:820px;margin:0 auto}
    .rl-faq details{background:#fff;border:1px solid #eef0f3;border-radius:10px;margin-bottom:10px;overflow:hidden;transition:all .2s}
    .rl-faq details[open]{border-color:#ff8a54;box-shadow:0 4px 12px rgba(255,138,84,.1)}
    .rl-faq summary{padding:18px 22px;font-size:15px;font-weight:700;color:#1f2733;cursor:pointer;display:flex;justify-content:space-between;align-items:center;list-style:none}
    .rl-faq summary::-webkit-details-marker{display:none}
    .rl-faq summary::after{content:'+';font-size:22px;color:#ff8a54;font-weight:400;transition:transform .2s}
    .rl-faq details[open] summary::after{transform:rotate(45deg)}
    .rl-faq .answer{padding:0 22px 20px;font-size:14px;color:#6b7280;line-height:1.7}

    /* ═══ FINAL CTA ═══ */
    .rl-cta-final{background:#1f2733;color:#fff;padding:60px 20px;text-align:center;border-radius:20px;max-width:1080px;margin:0 auto 60px;position:relative;overflow:hidden}
    .rl-cta-final::before{content:'';position:absolute;top:-80px;right:-80px;width:300px;height:300px;background:rgba(255,138,84,.15);border-radius:50%}
    .rl-cta-final h2{color:#fff;font-size:32px;font-weight:800;margin:0 0 12px;position:relative}
    .rl-cta-final p{font-size:16px;color:rgba(255,255,255,.8);margin:0 0 26px;position:relative}
    .rl-cta-final .btn{background:#ff8a54;color:#fff;padding:16px 34px;border-radius:10px;font-weight:700;font-size:15px;text-decoration:none;display:inline-block;transition:transform .15s;position:relative}
    .rl-cta-final .btn:hover{transform:translateY(-2px);background:#ff6b3d;color:#fff}

    /* ═══ Responsive ═══ */
    @media (max-width: 900px){
        .rl-hero h1{font-size:36px}
        .rl-hero .sub{font-size:16px}
        .rl-steps{grid-template-columns:1fr}
        .rl-rewards{grid-template-columns:1fr}
        .rl-qual{grid-template-columns:1fr}
        .rl-payout .flow{grid-template-columns:1fr;gap:6px}
        .rl-payout .arrow{transform:rotate(90deg);padding:8px 0}
        .rl-section h2{font-size:26px}
        .rl-cta-final h2{font-size:24px}
    }
</style>
@endsection

@section('content')
<div class="rl-page">
    {{-- ═══ HERO ═══ --}}
    <section class="rl-hero">
        <div class="container">
            <span class="kicker">{{ __('Rafiki Rewards Program') }}</span>
            <h1>
                {{ __('Bring a friend, earn') }}
                <span>{{ number_format($rewards['provider_total'], 0) }} TZS</span>
                {{ __('per referral.') }}
            </h1>
            <p class="sub">{{ __('Share your link, invite freelancers and clients, and earn real cash when they use Huduma Portal. No downloads, no gimmicks — just verified activity.') }}</p>

            <div class="cta-row">
                @auth
                    <a href="{{ route('buyer.earn') }}" class="btn-primary">
                        <i class="las la-gift"></i> {{ __('Open My Earn Dashboard') }}
                    </a>
                @else
                    <a href="{{ url('/register') }}" class="btn-primary">
                        <i class="las la-user-plus"></i> {{ __('Get Started — Sign Up Free') }}
                    </a>
                    <a href="{{ url('/login') }}" class="btn-secondary">{{ __('Already a member? Log in') }}</a>
                @endauth
            </div>

            <div class="trust-strip">
                <div class="item"><div class="num">{{ number_format($stats['total_users']) }}+</div><div class="lbl">{{ __('Active Users') }}</div></div>
                <div class="item"><div class="num">{{ number_format($stats['total_referrals']) }}</div><div class="lbl">{{ __('Referrals So Far') }}</div></div>
                <div class="item"><div class="num">{{ number_format($stats['total_paid'], 0) }} TZS</div><div class="lbl">{{ __('Rewards Earned') }}</div></div>
                <div class="item"><div class="num">{{ $rewards['prot_days'] }}-{{ __('day') }}</div><div class="lbl">{{ __('Protection Window') }}</div></div>
            </div>
        </div>
    </section>

    {{-- ═══ HOW IT WORKS ═══ --}}
    <section class="rl-section">
        <div class="container">
            <h2>{{ __('How It Works') }}</h2>
            <p class="lead">{{ __('Three simple steps — you earn only when your friend takes real action on the platform.') }}</p>

            <div class="rl-steps">
                <div class="step">
                    <div class="num">1</div>
                    <h3>{{ __('Get Your Link') }}</h3>
                    <p>{{ __('Sign up (or log in) and grab your unique referral link from the Earn dashboard. Each link has a code that tracks every friend you bring.') }}</p>
                </div>
                <div class="step">
                    <div class="num">2</div>
                    <h3>{{ __('Share It') }}</h3>
                    <p>{{ __('WhatsApp, Facebook, X, LinkedIn, or a personal QR code. Anywhere real people are — as long as it follows our rules and Tanzanian laws.') }}</p>
                </div>
                <div class="step">
                    <div class="num">3</div>
                    <h3>{{ __('They Take Action') }}</h3>
                    <p>{{ __('Once your friend signs up, verifies, and completes real activity, you earn rewards at each milestone. Downloads alone do not count.') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ REWARDS ═══ --}}
    <section class="rl-section" style="background:#fff">
        <div class="container">
            <h2>{{ __('What You Earn') }}</h2>
            <p class="lead">{{ __('Every stage your friend completes adds to your balance. Full reward schedule below — nothing hidden.') }}</p>

            <div class="rl-rewards">
                {{-- Provider track --}}
                <div class="track track-provider">
                    <div class="track-hd">
                        <div class="tag">{{ __('Refer a Freelancer') }}</div>
                        <h3>{{ __('Provider Track') }}</h3>
                        <div class="total">{{ __('Earn up to') }} <strong>{{ number_format($rewards['provider_total'], 0) }} TZS</strong> {{ __('per referral') }}</div>
                    </div>
                    <div class="stage">
                        <div class="stage-num">1</div>
                        <div class="stage-body">
                            <h4>{{ __('Freelancer publishes first service') }}</h4>
                            <p>{{ __('Your friend joins, completes their profile, and lists one service.') }}</p>
                            <span class="amt">+ {{ number_format($rewards['p1'], 0) }} TZS</span>
                        </div>
                    </div>
                    <div class="stage">
                        <div class="stage-num">2</div>
                        <div class="stage-body">
                            <h4>{{ __('First paid order received') }}</h4>
                            <p>{{ __('A buyer books their service. Your friend also gets') }} {{ number_format($rewards['p2c'], 0) }} TZS {{ __('promo credit.') }}</p>
                            <span class="amt">+ {{ number_format($rewards['p2'], 0) }} TZS</span>
                        </div>
                    </div>
                    <div class="stage">
                        <div class="stage-num">3</div>
                        <div class="stage-body">
                            <h4>{{ __('Second order or paid subscription') }}</h4>
                            <p>{{ __('They keep going — earn a second order or upgrade their plan.') }}</p>
                            <span class="amt">+ {{ number_format($rewards['p3'], 0) }} TZS</span>
                        </div>
                    </div>
                </div>

                {{-- Client track --}}
                <div class="track track-client">
                    <div class="track-hd">
                        <div class="tag">{{ __('Refer a Client') }}</div>
                        <h3>{{ __('Client Track') }}</h3>
                        <div class="total">{{ __('Earn up to') }} <strong>{{ number_format($rewards['client_total'], 0) }} TZS</strong> {{ __('per referral') }}</div>
                    </div>
                    <div class="stage">
                        <div class="stage-num star">★</div>
                        <div class="stage-body">
                            <h4>{{ __('Friend signs up via your link') }}</h4>
                            <p>{{ __('Their welcome bonus, not yours — a spendable credit for their first booking.') }}</p>
                            <span class="amt muted">{{ __('Friend gets') }} {{ number_format($rewards['c_welcome'], 0) }} TZS <small>{{ __('credit') }}</small></span>
                        </div>
                    </div>
                    <div class="stage">
                        <div class="stage-num">1</div>
                        <div class="stage-body">
                            <h4>{{ __('Their first paid booking') }}</h4>
                            <p>{{ __('When they book and pay for their first service.') }}</p>
                            <span class="amt">+ {{ number_format($rewards['c1'], 0) }} TZS</span>
                        </div>
                    </div>
                    <div class="stage">
                        <div class="stage-num">2</div>
                        <div class="stage-body">
                            <h4>{{ __('Second booking within 60 days') }}</h4>
                            <p>{{ __('They come back for more within two months.') }}</p>
                            <span class="amt">+ {{ number_format($rewards['c2'], 0) }} TZS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ WHAT QUALIFIES / DOESN'T ═══ --}}
    <section class="rl-section">
        <div class="container">
            <h2>{{ __('What Qualifies (and What Doesn\'t)') }}</h2>
            <p class="lead">{{ __('We reward real activity from real people. Everything else gets rejected.') }}</p>

            <div class="rl-qual">
                <div class="col yes">
                    <h3><span class="ic">✓</span> {{ __('Qualifies for rewards') }}</h3>
                    <ul>
                        <li><span class="ic">✓</span> {{ __('New users who have never registered on Huduma Portal before') }}</li>
                        <li><span class="ic">✓</span> {{ __('Sign-ups where phone or email is verified') }}</li>
                        <li><span class="ic">✓</span> {{ __('Real paid activity: first service published, first paid order, subscription upgrade') }}</li>
                        <li><span class="ic">✓</span> {{ __('Referral used within 24 hours of visiting your link') }}</li>
                    </ul>
                </div>
                <div class="col no">
                    <h3><span class="ic">×</span> {{ __('Does NOT qualify') }}</h3>
                    <ul>
                        <li><span class="ic">×</span> {{ __('App or website downloads alone') }}</li>
                        <li><span class="ic">×</span> {{ __('Unverified or fake accounts') }}</li>
                        <li><span class="ic">×</span> {{ __('Free service posts with no paid activity') }}</li>
                        <li><span class="ic">×</span> {{ __('Self-referrals or duplicate accounts (same phone / email / IP / device / payment)') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ PAYOUT FLOW ═══ --}}
    <section class="rl-section" style="background:#fff">
        <div class="container">
            <h2>{{ __('How You Get Paid') }}</h2>
            <p class="lead">{{ __('Referral earnings flow through a two-wallet system so we can protect against refunds while paying you fairly.') }}</p>

            <div class="rl-payout">
                <div class="flow">
                    <div class="box"><div class="n">1</div><h4>{{ __('Reward Earned') }}</h4><p>{{ __('Lands as PENDING in Referral Wallet') }}</p></div>
                    <div class="arrow">→</div>
                    <div class="box"><div class="n">2</div><h4>{{ __('Protection Window') }}</h4><p>{{ $rewards['prot_days'] }} {{ __('days to catch refunds') }}</p></div>
                    <div class="arrow">→</div>
                    <div class="box"><div class="n">3</div><h4>{{ __('Available') }}</h4><p>{{ __('Transfer to Main Wallet (min') }} {{ number_format($rewards['min_wd'], 0) }} TZS)</p></div>
                    <div class="arrow">→</div>
                    <div class="box"><div class="n">4</div><h4>{{ __('Withdraw') }}</h4><p>{{ __('M-Pesa, Airtel, HaloPesa, NMB, CRDB, and more') }}</p></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ FAQ ═══ --}}
    <section class="rl-section">
        <div class="container">
            <h2>{{ __('Frequently Asked Questions') }}</h2>
            <p class="lead">{{ __('Everything you need to know before you start sharing.') }}</p>

            <div class="rl-faq">
                <details>
                    <summary>{{ __('Who qualifies as a "referred user"?') }}</summary>
                    <div class="answer">{{ __('Only NEW users who have never registered on Huduma Portal before, who use your referral link OR enter your code within 24 hours of signing up, AND who verify their phone or email.') }}</div>
                </details>
                <details>
                    <summary>{{ __('When do my rewards become available?') }}</summary>
                    <div class="answer">{{ __('Rewards start as PENDING and become AVAILABLE after a :days-day protection window. This gives us time to handle any refunds that might reverse the reward.', ['days' => $rewards['prot_days']]) }}</div>
                </details>
                <details>
                    <summary>{{ __('What is the minimum I can transfer to my Main Wallet?') }}</summary>
                    <div class="answer">{{ __(':amt TZS. Below this, rewards stay in your Referral Wallet until you accumulate enough.', ['amt' => number_format($rewards['min_wd'], 0)]) }}</div>
                </details>
                <details>
                    <summary>{{ __('Can I refer myself with a different phone number?') }}</summary>
                    <div class="answer">{{ __('No. Self-referrals are automatically blocked. Duplicate accounts (same phone prefix, email pattern, payment account, or device) are flagged and rejected.') }}</div>
                </details>
                <details>
                    <summary>{{ __('Are there per-user caps on rewards?') }}</summary>
                    <div class="answer">{{ __('Yes. Freelancer referrals max out at :p TZS. Client referrals max out at :c TZS. Business referrals will be introduced in a later phase.', ['p' => number_format($rewards['provider_total'], 0), 'c' => number_format($rewards['client_total'], 0)]) }}</div>
                </details>
                <details>
                    <summary>{{ __('What happens if my referred user gets a refund?') }}</summary>
                    <div class="answer">{{ __('If a refund happens within the protection window, the reward is reversed and marked "rejected". After the window closes, rewards are safe.') }}</div>
                </details>
                <details>
                    <summary>{{ __('Where do I see my referrals and earnings?') }}</summary>
                    <div class="answer">{{ __('In the Earn (Refer & Rewards) tab in your dashboard — includes your link, referred users, milestone progress, reward events, and the Transfer button.') }}</div>
                </details>
                <details>
                    <summary>{{ __('How long does my referral link stay active?') }}</summary>
                    <div class="answer">{{ __('Your link never expires. Attribution for each new visitor lasts :days days from the day they first click your link.', ['days' => $rewards['attr_days']]) }}</div>
                </details>
            </div>
        </div>
    </section>

    {{-- ═══ FINAL CTA ═══ --}}
    <div class="container" style="padding:0 20px 60px">
        <div class="rl-cta-final">
            <h2>{{ __('Ready to start earning?') }}</h2>
            <p>{{ __('Grab your link in seconds and start referring friends today.') }}</p>
            @auth
                <a href="{{ route('buyer.earn') }}" class="btn">{{ __('Open My Earn Dashboard') }} →</a>
            @else
                <a href="{{ url('/register') }}" class="btn">{{ __('Sign Up Free') }} →</a>
            @endauth
        </div>
    </div>
</div>
@endsection
