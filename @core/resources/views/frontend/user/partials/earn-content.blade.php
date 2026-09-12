{{--
    Shared Earn (Rafiki Rewards) content — used by BOTH seller & buyer pages.
    Callers must pass:
      $stats            — array from ReferralService::statsForUser()
      $referrals        — Illuminate\Pagination\LengthAwarePaginator of Referral
      $rewards          — Collection of ReferralReward
      $shareUrl         — full URL for the referral (e.g. http://.../r/HPCODE)
      $shareCode        — the referral code
      $transferRoute    — named route to POST the transfer to (seller.earn.transfer | buyer.earn.transfer)
      $walletRoute      — named route for the "Open Main Wallet" link (seller.wallet.history | buyer.wallet.history)
--}}

<style>
.earn-wrap{padding:0;color:#1f2733;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
.earn-wrap .earn-hd{margin:0 0 20px}
.earn-wrap .earn-hd h1{font-size:26px;font-weight:700;margin:0 0 6px;color:#1f2733}
.earn-wrap .earn-hd p{margin:0;color:#6b7280;font-size:14px}

/* ═══ Quick stats row (5 cards) ═══ */
.earn-wrap .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:20px}
.earn-wrap .stat{background:#fff;border:1px solid #eef0f3;border-radius:12px;padding:16px}
.earn-wrap .stat .label{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#8892a0;margin-bottom:6px;font-weight:600}
.earn-wrap .stat .value{font-size:20px;font-weight:700;color:#1f2733}
.earn-wrap .stat .value small{font-size:12px;color:#8892a0;font-weight:500;margin-left:4px}
.earn-wrap .stat.available{background:linear-gradient(135deg,#ff8a54 0%,#ff6b3d 100%);border-color:transparent;color:#fff}
.earn-wrap .stat.available .label{color:rgba(255,255,255,.85)}
.earn-wrap .stat.available .value{color:#fff}
.earn-wrap .stat.available .value small{color:rgba(255,255,255,.85)}

/* ═══ Two-wallet grid ═══ */
.earn-wrap .wallet-grid{display:grid;grid-template-columns:1.5fr 1fr;gap:16px;margin-bottom:22px}
.earn-wrap .wallet-card{background:#fff;border:1px solid #eef0f3;border-radius:14px;padding:20px;display:flex;flex-direction:column}
.earn-wrap .wallet-hd{display:flex;flex-direction:column;margin-bottom:14px}
.earn-wrap .wallet-tag{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#8892a0;font-weight:700}
.earn-wrap .wallet-sub{font-size:12px;color:#6b7280;margin-top:2px}
.earn-wrap .wallet-ref{background:linear-gradient(135deg,#fff7ed 0%,#fff 60%);border-color:#fed7aa}
.earn-wrap .wallet-ref .wallet-tag{color:#c2410c}
.earn-wrap .wallet-rows{display:flex;flex-direction:column;gap:8px;margin-bottom:14px}
.earn-wrap .wallet-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;color:#1f2733;padding:6px 0;border-bottom:1px dashed #f2f4f7}
.earn-wrap .wallet-row:last-child{border-bottom:none}
.earn-wrap .wallet-row strong{font-weight:700;color:#ff6b3d;font-size:15px}
.earn-wrap .btn-transfer{background:#ff8a54;color:#fff;border:none;border-radius:8px;padding:11px 18px;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;justify-content:center;transition:background .15s}
.earn-wrap .btn-transfer:hover:not(:disabled){background:#ff6b3d}
.earn-wrap .btn-transfer:disabled{background:#e4e7ec;color:#9ca3af;cursor:not-allowed}
.earn-wrap .wallet-hint{font-size:11px;color:#6b7280;margin:8px 0 0;line-height:1.5}
.earn-wrap .wallet-main{background:#f8f9fb}
.earn-wrap .wallet-big{font-size:32px;font-weight:800;color:#1f2733;margin:8px 0 14px}
.earn-wrap .wallet-big small{font-size:14px;font-weight:600;color:#8892a0;margin-left:4px}
.earn-wrap .btn-outline{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;background:#fff;color:#1f2733;border:1px solid #d1d5db;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;align-self:flex-start;transition:all .15s}
.earn-wrap .btn-outline:hover{background:#1f2733;color:#fff;border-color:#1f2733}

/* Rafiki Level card */
.earn-wrap .level-card{background:#fff;border:1px solid #eef0f3;border-radius:14px;padding:20px 22px;margin-bottom:22px;position:relative;overflow:hidden}
.earn-wrap .level-card.level-rafiki{border-color:#e4e7ec}
.earn-wrap .level-card.level-balozi{background:linear-gradient(135deg,#fef3c7 0%,#fff 60%);border-color:#fde68a}
.earn-wrap .level-card.level-super{background:linear-gradient(135deg,#e0e7ff 0%,#fff 60%);border-color:#c7d2fe}
.earn-wrap .level-card.level-champion{background:linear-gradient(135deg,#ff8a54 0%,#ff6b3d 100%);border-color:transparent;color:#fff}
.earn-wrap .level-card.level-champion .level-tag,.earn-wrap .level-card.level-champion .level-hint,.earn-wrap .level-card.level-champion .level-count-l{color:rgba(255,255,255,.9)}
.earn-wrap .level-hd{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.earn-wrap .level-tag{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#8892a0;font-weight:700;margin-bottom:4px}
.earn-wrap .level-name{font-size:22px;font-weight:800;color:#1f2733;letter-spacing:-.3px}
.earn-wrap .level-card.level-champion .level-name{color:#fff}
.earn-wrap .level-count{text-align:right}
.earn-wrap .level-count-n{font-size:26px;font-weight:800;color:#1f2733;line-height:1}
.earn-wrap .level-card.level-champion .level-count-n{color:#fff}
.earn-wrap .level-count-l{font-size:11px;color:#8892a0;text-transform:uppercase;letter-spacing:.4px;margin-top:2px}
.earn-wrap .level-bar-wrap{height:10px;background:rgba(0,0,0,.06);border-radius:999px;overflow:hidden;margin-bottom:10px}
.earn-wrap .level-card.level-champion .level-bar-wrap{background:rgba(255,255,255,.25)}
.earn-wrap .level-bar{height:100%;background:linear-gradient(90deg,#ff8a54,#ff6b3d);border-radius:999px;transition:width .4s}
.earn-wrap .level-card.level-champion .level-bar{background:#fff}
.earn-wrap .level-hint{font-size:13px;color:#6b7280;font-weight:600}

/* Flash messages */
.earn-wrap .flash{padding:12px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:16px}
.earn-wrap .flash-ok{background:#d1fae5;color:#065f46;border:1px solid #10b981}
.earn-wrap .flash-err{background:#fee2e2;color:#991b1b;border:1px solid #ef4444}

.earn-wrap .card-box{background:#fff;border:1px solid #eef0f3;border-radius:12px;padding:22px;margin-bottom:22px}
.earn-wrap .card-box h3{font-size:16px;font-weight:700;margin:0 0 14px;color:#1f2733}

.earn-wrap .share-row{display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center;margin-bottom:14px}
.earn-wrap .share-row input{width:100%;padding:11px 14px;border:1px solid #e4e7ec;border-radius:8px;font-size:13px;color:#1f2733;background:#f8f9fb;font-family:monospace}
.earn-wrap .share-row .copy-btn{padding:11px 18px;background:#1f2733;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:13px;cursor:pointer;white-space:nowrap;transition:background .15s}
.earn-wrap .share-row .copy-btn:hover{background:#0f1520}
.earn-wrap .share-row .copy-btn.copied{background:#10b981}

/* Message picker (PDF §15 — Swahili + English variants) */
.earn-wrap .msg-picker{margin-bottom:14px}
.earn-wrap .msg-picker-label{font-size:12px;font-weight:600;color:#6b7280;margin-bottom:8px;text-transform:uppercase;letter-spacing:.4px}
.earn-wrap .msg-picker-tabs{display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap}
.earn-wrap .msg-tab{padding:6px 14px;font-size:12px;font-weight:600;background:#fff;border:1px solid #d1d5db;color:#374151;border-radius:999px;cursor:pointer;transition:all .15s}
.earn-wrap .msg-tab:hover{border-color:#ff8a54;color:#ff6b3d}
.earn-wrap .msg-tab.active{background:#ff8a54;color:#fff;border-color:#ff8a54}
.earn-wrap .msg-preview{width:100%;min-height:80px;padding:10px 12px;font-size:12px;font-family:inherit;line-height:1.5;color:#374151;background:#f8f9fb;border:1px solid #e4e7ec;border-radius:8px;resize:vertical}
.earn-wrap .msg-preview:focus{outline:none;border-color:#ff8a54;background:#fff}

.earn-wrap .share-chips{display:flex;flex-wrap:wrap;gap:8px}
.earn-wrap .share-chips a{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;text-decoration:none;font-size:13px;font-weight:600;border:1px solid #e4e7ec;color:#1f2733;background:#fff;transition:all .15s}
.earn-wrap .share-chips a:hover{background:#f8f9fb;border-color:#1f2733}
.earn-wrap .share-chips a.wa{background:#25d366;color:#fff;border-color:#25d366}
.earn-wrap .share-chips a.wa:hover{background:#1cb257;border-color:#1cb257}
.earn-wrap .share-chips a.fb{background:#1877f2;color:#fff;border-color:#1877f2}
.earn-wrap .share-chips a.fb:hover{background:#0d5fc8;border-color:#0d5fc8}
.earn-wrap .share-chips a.qr{background:#1f2733;color:#fff;border-color:#1f2733}

.earn-wrap .qr-box{display:none;text-align:center;margin-top:14px;padding:16px;background:#f8f9fb;border-radius:10px}
.earn-wrap .qr-box img{max-width:220px;background:#fff;padding:12px;border-radius:8px}
.earn-wrap .qr-box p{margin:10px 0 0;font-size:12px;color:#6b7280}

.earn-wrap .tbl{width:100%;border-collapse:collapse}
.earn-wrap .tbl th{text-align:left;padding:10px 12px;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#8892a0;font-weight:600;background:#f8f9fb;border-bottom:1px solid #eef0f3}
.earn-wrap .tbl td{padding:12px;border-bottom:1px solid #f2f4f7;font-size:13px;color:#1f2733;vertical-align:middle}
.earn-wrap .tbl tr:last-child td{border-bottom:none}
.earn-wrap .tbl .empty{text-align:center;padding:30px;color:#8892a0;font-size:14px}

.earn-wrap .milestone{display:inline-flex;gap:4px}
.earn-wrap .milestone .dot{width:10px;height:10px;border-radius:50%;background:#e4e7ec;border:1px solid #d1d5db}
.earn-wrap .milestone .dot.done{background:#10b981;border-color:#10b981}

.earn-wrap .badge-pill{display:inline-block;padding:3px 9px;font-size:11px;font-weight:600;border-radius:999px;text-transform:uppercase;letter-spacing:.4px}
.earn-wrap .badge-pill.pending{background:#fef3c7;color:#92400e}
.earn-wrap .badge-pill.approved{background:#d1fae5;color:#065f46}
.earn-wrap .badge-pill.paid{background:#dbeafe;color:#1e40af}
.earn-wrap .badge-pill.qualifying{background:#e0e7ff;color:#3730a3}
.earn-wrap .badge-pill.rejected{background:#fee2e2;color:#991b1b}
.earn-wrap .badge-pill.blocked{background:#f3f4f6;color:#374151}

.earn-wrap .tip{margin-top:8px;padding:10px 12px;background:#fff7ed;border-left:3px solid #ff8a54;border-radius:6px;font-size:12px;color:#78350f}

/* ═══ How You Earn — reward schedule ═══ */
.earn-wrap .how-earn-sub{font-size:13px;color:#6b7280;margin:-4px 0 16px}
.earn-wrap .tracks{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
@media (max-width: 1100px){ .earn-wrap .tracks{grid-template-columns:1fr 1fr} }
.earn-wrap .track{background:#f8f9fb;border:1px solid #e6e9ef;border-radius:10px;padding:16px}
.earn-wrap .track-provider{background:linear-gradient(135deg,#fff7ed 0%,#fff 100%);border-color:#fed7aa}
.earn-wrap .track-client{background:linear-gradient(135deg,#eff6ff 0%,#fff 100%);border-color:#bfdbfe}
.earn-wrap .track-business{background:linear-gradient(135deg,#f5f3ff 0%,#fff 100%);border-color:#ddd6fe}
.earn-wrap .track-business .track-tag{color:#6d28d9}
.earn-wrap .track-business .track-total strong{color:#6d28d9}
.earn-wrap .track-business .stage-num{background:#8b5cf6;color:#fff;border-color:#8b5cf6}
.earn-wrap .track-hd{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:10px;border-bottom:1px dashed #e4e7ec}
.earn-wrap .track-tag{font-size:13px;font-weight:700;color:#1f2733}
.earn-wrap .track-total{font-size:11px;color:#8892a0;text-transform:uppercase;letter-spacing:.4px}
.earn-wrap .track-total strong{color:#ff6b3d;font-size:14px;text-transform:none;letter-spacing:0}
.earn-wrap .track-client .track-total strong{color:#1d4ed8}
.earn-wrap .stage{display:flex;gap:12px;padding:10px 0;border-bottom:1px dashed #f2f4f7}
.earn-wrap .stage:last-child{border-bottom:none}
.earn-wrap .stage-num{width:26px;height:26px;border-radius:50%;background:#fff;border:1px solid #d1d5db;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#1f2733;flex-shrink:0}
.earn-wrap .track-provider .stage-num{background:#ff8a54;color:#fff;border-color:#ff8a54}
.earn-wrap .track-client .stage-num{background:#3b82f6;color:#fff;border-color:#3b82f6}
.earn-wrap .stage-num-star{background:#8b5cf6!important;border-color:#8b5cf6!important;font-size:14px}
.earn-wrap .stage-title{font-size:13px;color:#1f2733;font-weight:600;margin-bottom:2px}
.earn-wrap .stage-amt{font-size:12px;color:#10b981;font-weight:700}
.earn-wrap .stage-amt-muted{color:#8892a0;font-weight:500}
.earn-wrap .stage-note{color:#6b7280;font-weight:400}
.earn-wrap .how-earn-foot{margin:16px 0 0;padding:10px 12px;background:#f8f9fb;border-radius:6px;font-size:12px;color:#6b7280;line-height:1.6}
.earn-wrap .how-earn-foot strong{color:#1f2733}
@media (max-width: 720px){ .earn-wrap .tracks{grid-template-columns:1fr} }

@media (max-width: 900px){
    .earn-wrap .wallet-grid{grid-template-columns:1fr}
}
@media (max-width: 640px){
    .earn-wrap .share-row{grid-template-columns:1fr}
    .earn-wrap .card-box{padding:16px}
    .earn-wrap .wallet-big{font-size:26px}
}
</style>

<div class="earn-wrap">

    <div class="earn-hd">
        <h1>{{ __('Earn — Refer & Rewards') }}</h1>
        <p>{{ __('Invite friends to Huduma Portal and earn rewards when they join and use the platform.') }}</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flash flash-ok">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-err">{{ session('error') }}</div>
    @endif

    {{-- ═══ QUICK STATS (5 cards) ═══ --}}
    <div class="stats-row">
        <div class="stat available">
            <div class="label">{{ __('Available to transfer') }}</div>
            <div class="value">{{ number_format($stats['available'], 0) }} <small>TZS</small></div>
        </div>
        <div class="stat">
            <div class="label">{{ __('Pending') }}</div>
            <div class="value">{{ number_format($stats['pending'], 0) }} <small>TZS</small></div>
        </div>
        <div class="stat">
            <div class="label">{{ __('This month') }}</div>
            <div class="value">{{ number_format($stats['thisMonth'], 0) }} <small>TZS</small></div>
        </div>
        <div class="stat">
            <div class="label">{{ __('Lifetime earned') }}</div>
            <div class="value">{{ number_format($stats['lifetime'], 0) }} <small>TZS</small></div>
        </div>
        <div class="stat">
            <div class="label">{{ __('People referred') }}</div>
            <div class="value">{{ $stats['referred'] }}</div>
        </div>
    </div>

    {{-- ═══ RAFIKI LEVEL + NEXT MILESTONE (PDF §11, §12) ═══ --}}
    @php
        $refCount = (int) $stats['referred'];
        $levels = [
            ['name' => 'RAFIKI',          'min' => 0,  'max' => 4,  'class' => 'rafiki'],
            ['name' => 'BALOZI',          'min' => 5,  'max' => 14, 'class' => 'balozi'],
            ['name' => 'SUPER BALOZI',    'min' => 15, 'max' => 49, 'class' => 'super'],
            ['name' => 'HUDUMA CHAMPION', 'min' => 50, 'max' => null,'class' => 'champion'],
        ];
        $currentLevel = collect($levels)->first(fn ($l) => $refCount >= $l['min'] && ($l['max'] === null || $refCount <= $l['max']));
        $nextLevel    = collect($levels)->first(fn ($l) => $l['min'] > $refCount);
        $needed       = $nextLevel ? $nextLevel['min'] - $refCount : 0;
        $progressPct  = $nextLevel ? min(100, ($refCount / $nextLevel['min']) * 100) : 100;
    @endphp
    <div class="level-card level-{{ $currentLevel['class'] }}">
        <div class="level-hd">
            <div>
                <div class="level-tag">{{ __('Your Rafiki Level') }}</div>
                <div class="level-name">{{ $currentLevel['name'] }}</div>
            </div>
            <div class="level-count">
                <div class="level-count-n">{{ $refCount }}</div>
                <div class="level-count-l">{{ __('referrals') }}</div>
            </div>
        </div>
        <div class="level-bar-wrap">
            <div class="level-bar" style="width:{{ $progressPct }}%"></div>
        </div>
        <div class="level-hint">
            @if($nextLevel)
                {{ __(':needed more :count to become :level', ['needed' => $needed, 'count' => $needed === 1 ? __('referral') : __('referrals'), 'level' => $nextLevel['name']]) }}
            @else
                {{ __('You have reached the top level! Keep referring to stay at the top.') }}
            @endif
        </div>
    </div>

    {{-- ═══ TWO-WALLET SUMMARY ═══ --}}
    <div class="wallet-grid">
        <div class="wallet-card wallet-ref">
            <div class="wallet-hd">
                <span class="wallet-tag">{{ __('Referral Wallet') }}</span>
                <span class="wallet-sub">{{ __('Earnings from referrals') }}</span>
            </div>
            <div class="wallet-rows">
                <div class="wallet-row"><span>{{ __('Available to transfer') }}</span><strong>{{ number_format($stats['available'], 0) }} TZS</strong></div>
                <div class="wallet-row"><span>{{ __('Pending (protection window)') }}</span><span>{{ number_format($stats['pending'], 0) }} TZS</span></div>
                <div class="wallet-row"><span>{{ __('This month') }}</span><span>{{ number_format($stats['thisMonth'], 0) }} TZS</span></div>
                <div class="wallet-row"><span>{{ __('Lifetime earned') }}</span><span>{{ number_format($stats['lifetime'], 0) }} TZS</span></div>
                <div class="wallet-row"><span>{{ __('People referred') }}</span><span>{{ $stats['referred'] }}</span></div>
            </div>
            <form method="post" action="{{ route($transferRoute) }}" class="wallet-action">
                @csrf
                <button type="submit" class="btn-transfer" @if($stats['available'] < $stats['minWithdraw']) disabled @endif>
                    <i class="las la-exchange-alt"></i>
                    {{ __('Transfer to Main Wallet') }}
                </button>
                <p class="wallet-hint">
                    {{ __('Minimum transfer:') }} <strong>{{ number_format($stats['minWithdraw'], 0) }} TZS</strong>
                    · {{ __('protection period') }}: {{ (int) (\App\StaticOption::where('option_name','referral_protection_days')->value('option_value') ?? 14) }} {{ __('days') }}
                </p>
            </form>
        </div>

        <div class="wallet-card wallet-main">
            <div class="wallet-hd">
                <span class="wallet-tag">{{ __('Main Wallet') }}</span>
                <span class="wallet-sub">{{ __('Where you withdraw from') }}</span>
            </div>
            <div class="wallet-big">
                {{ number_format($stats['mainWallet'], 0) }} <small>TZS</small>
            </div>
            <a href="{{ route($walletRoute) }}" class="btn-outline">
                <i class="las la-wallet"></i> {{ __('Open Main Wallet') }}
            </a>
        </div>
    </div>

    {{-- Share box --}}
    <div class="card-box">
        <h3>{{ __('Your referral link') }}</h3>
        <div class="share-row">
            <input type="text" id="referralLink" value="{{ $shareUrl }}" readonly onclick="this.select()">
            <button type="button" class="copy-btn" data-copy="{{ $shareUrl }}">{{ __('Copy link') }}</button>
        </div>
        <div class="share-row">
            <input type="text" id="referralCode" value="{{ $shareCode }}" readonly onclick="this.select()">
            <button type="button" class="copy-btn" data-copy="{{ $shareCode }}">{{ __('Copy code') }}</button>
        </div>

        {{-- ═══ Message variants (PDF §15) — pick one, all share pre-populate the WhatsApp text ═══ --}}
        @php
            $welcomeAmt = number_format((float) (\App\StaticOption::where('option_name','referral_client_welcome_credit')->value('option_value') ?? 1000), 0);
            $providerMax = number_format((float) (\App\StaticOption::where('option_name','referral_stage1_provider_amount')->value('option_value') ?? 500) + (float) (\App\StaticOption::where('option_name','referral_stage2_provider_cash')->value('option_value') ?? 1000) + (float) (\App\StaticOption::where('option_name','referral_stage3_provider_amount')->value('option_value') ?? 1500), 0);
            $messages = [
                'general' => [
                    'label' => __('General'),
                    'text'  => __('Nimeanza kutumia HudumaPortal kupata huduma na fursa za kazi. Jiunge kupitia link yangu na upate faida za kuanza. / I use Huduma Portal to find services and job opportunities. Join through my link and get welcome benefits: :url', ['url' => $shareUrl]),
                ],
                'provider' => [
                    'label' => __('For freelancers'),
                    'text'  => __('Una ujuzi au biashara ya huduma? Jiunge HudumaPortal, tangaza huduma zako na pata wateja. Tumia link yangu: :url / Have skills or a service business? Join Huduma Portal, list your services, and get customers. Use my link: :url', ['url' => $shareUrl]),
                ],
                'client' => [
                    'label' => __('For clients'),
                    'text'  => __('Unatafuta fundi au mtoa huduma? Angalia HudumaPortal kupitia link yangu na upate :amt TZS welcome credit: :url / Looking for a professional? Check out Huduma Portal via my link and get :amt TZS welcome credit: :url', ['amt' => $welcomeAmt, 'url' => $shareUrl]),
                ],
            ];
            $defaultMsg = $messages['general']['text'];
        @endphp
        <div class="msg-picker">
            <div class="msg-picker-label">{{ __('Pick a message:') }}</div>
            <div class="msg-picker-tabs">
                @foreach($messages as $key => $m)
                    <button type="button" class="msg-tab {{ $key === 'general' ? 'active' : '' }}" data-msg-key="{{ $key }}">{{ $m['label'] }}</button>
                @endforeach
            </div>
            <textarea class="msg-preview" id="shareMsgText" readonly onclick="this.select()">{{ $defaultMsg }}</textarea>
        </div>

        <div class="share-chips">
            <a class="wa" id="shareWa" target="_blank" rel="noopener"
               href="https://wa.me/?text={{ urlencode($defaultMsg) }}">
                <i class="la la-whatsapp"></i> {{ __('WhatsApp') }}
            </a>
            <a class="fb" target="_blank" rel="noopener"
               href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}">
                <i class="la la-facebook"></i> {{ __('Facebook') }}
            </a>
            <a id="shareTw" target="_blank" rel="noopener"
               href="https://twitter.com/intent/tweet?text={{ urlencode($defaultMsg) }}">
                <i class="la la-twitter"></i> X / Twitter
            </a>
            <a id="shareMail" target="_blank" rel="noopener"
               href="mailto:?subject={{ urlencode(__('Join Huduma Portal')) }}&body={{ urlencode($defaultMsg) }}">
                <i class="la la-envelope"></i> {{ __('Email') }}
            </a>
            <a class="qr" href="#" onclick="event.preventDefault(); document.getElementById('qrBox').style.display='block';">
                <i class="la la-qrcode"></i> {{ __('QR Code') }}
            </a>
        </div>

        {{-- Inline JS to swap the active message + rebuild share URLs when a tab is clicked --}}
        <script>
        (function(){
            var messages = {!! json_encode(array_map(fn ($m) => $m['text'], $messages)) !!};
            var textarea = document.getElementById('shareMsgText');
            var subject = @json(__('Join Huduma Portal'));
            document.querySelectorAll('.msg-tab').forEach(function(btn){
                btn.addEventListener('click', function(){
                    document.querySelectorAll('.msg-tab').forEach(function(b){ b.classList.remove('active'); });
                    btn.classList.add('active');
                    var key = btn.getAttribute('data-msg-key');
                    var text = messages[key];
                    textarea.value = text;
                    document.getElementById('shareWa').href   = 'https://wa.me/?text=' + encodeURIComponent(text);
                    document.getElementById('shareTw').href   = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text);
                    document.getElementById('shareMail').href = 'mailto:?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(text);
                });
            });
        })();
        </script>

        <div class="qr-box" id="qrBox">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($shareUrl) }}" alt="QR Code">
            <p>{{ __('Save or print this QR code — anyone who scans it lands on your referral page.') }}</p>
        </div>

        <div class="tip">
            {{ __('Tip: share your link on WhatsApp status, business cards, or after a completed job. New users get :amt TZS welcome credit.', ['amt' => number_format((float) (\App\StaticOption::where('option_name','referral_client_welcome_credit')->value('option_value') ?? 1000), 0)]) }}
        </div>
    </div>

    {{-- ═══ HOW YOU EARN — reward schedule breakdown ═══ --}}
    @php
        $r_p1 = (float) (\App\StaticOption::where('option_name','referral_stage1_provider_amount')->value('option_value') ?? 500);
        $r_p2 = (float) (\App\StaticOption::where('option_name','referral_stage2_provider_cash')->value('option_value') ?? 1000);
        $r_p2c = (float) (\App\StaticOption::where('option_name','referral_stage2_provider_credit')->value('option_value') ?? 1000);
        $r_p3 = (float) (\App\StaticOption::where('option_name','referral_stage3_provider_amount')->value('option_value') ?? 1500);
        $r_c_wel = (float) (\App\StaticOption::where('option_name','referral_client_welcome_credit')->value('option_value') ?? 1000);
        $r_c1 = (float) (\App\StaticOption::where('option_name','referral_client_first_booking')->value('option_value') ?? 750);
        $r_c2 = (float) (\App\StaticOption::where('option_name','referral_client_second_booking')->value('option_value') ?? 750);
        $r_b1 = (float) (\App\StaticOption::where('option_name','referral_stage1_business_amount')->value('option_value') ?? 1000);
        $r_b2 = (float) (\App\StaticOption::where('option_name','referral_stage2_business_amount')->value('option_value') ?? 4000);
        $r_b3 = (float) (\App\StaticOption::where('option_name','referral_stage3_business_amount')->value('option_value') ?? 5000);
        $r_b_thresh = (float) (\App\StaticOption::where('option_name','referral_business_spend_threshold')->value('option_value') ?? 250000);
        $r_b_days = (int) (\App\StaticOption::where('option_name','referral_business_spend_days')->value('option_value') ?? 90);
        $providerTotal = $r_p1 + $r_p2 + $r_p3;
        $clientTotal   = $r_c1 + $r_c2;
        $businessTotal = $r_b1 + $r_b2 + $r_b3;
    @endphp

    <div class="card-box how-earn">
        <h3>{{ __('How You Earn') }}</h3>
        <p class="how-earn-sub">{{ __('Every time someone signs up via your link and reaches a milestone, you earn. Here is the full reward schedule:') }}</p>

        <div class="tracks">
            {{-- Provider track --}}
            <div class="track track-provider">
                <div class="track-hd">
                    <span class="track-tag">{{ __('Refer a Freelancer') }}</span>
                    <span class="track-total">{{ __('Up to') }} <strong>{{ number_format($providerTotal, 0) }} TZS</strong></span>
                </div>
                <div class="stage">
                    <span class="stage-num">1</span>
                    <div>
                        <div class="stage-title">{{ __('Profile + first service published') }}</div>
                        <div class="stage-amt">+ {{ number_format($r_p1, 0) }} TZS</div>
                    </div>
                </div>
                <div class="stage">
                    <span class="stage-num">2</span>
                    <div>
                        <div class="stage-title">{{ __('First paid order received') }}</div>
                        <div class="stage-amt">+ {{ number_format($r_p2, 0) }} TZS <span class="stage-note">({{ __('friend also gets') }} {{ number_format($r_p2c, 0) }} {{ __('TZS credit') }})</span></div>
                    </div>
                </div>
                <div class="stage">
                    <span class="stage-num">3</span>
                    <div>
                        <div class="stage-title">{{ __('Second order or paid subscription') }}</div>
                        <div class="stage-amt">+ {{ number_format($r_p3, 0) }} TZS</div>
                    </div>
                </div>
            </div>

            {{-- Client track --}}
            <div class="track track-client">
                <div class="track-hd">
                    <span class="track-tag">{{ __('Refer a Client') }}</span>
                    <span class="track-total">{{ __('Up to') }} <strong>{{ number_format($clientTotal, 0) }} TZS</strong></span>
                </div>
                <div class="stage">
                    <span class="stage-num stage-num-star">★</span>
                    <div>
                        <div class="stage-title">{{ __('Friend signs up via your link') }}</div>
                        <div class="stage-amt stage-amt-muted">{{ __('Friend gets') }} {{ number_format($r_c_wel, 0) }} TZS {{ __('welcome credit') }}</div>
                    </div>
                </div>
                <div class="stage">
                    <span class="stage-num">1</span>
                    <div>
                        <div class="stage-title">{{ __('First booking placed') }}</div>
                        <div class="stage-amt">+ {{ number_format($r_c1, 0) }} TZS</div>
                    </div>
                </div>
                <div class="stage">
                    <span class="stage-num">2</span>
                    <div>
                        <div class="stage-title">{{ __('Second booking within 60 days') }}</div>
                        <div class="stage-amt">+ {{ number_format($r_c2, 0) }} TZS</div>
                    </div>
                </div>
            </div>

            {{-- Business track --}}
            <div class="track track-business">
                <div class="track-hd">
                    <span class="track-tag">{{ __('Refer a Business') }}</span>
                    <span class="track-total">{{ __('Up to') }} <strong>{{ number_format($businessTotal, 0) }} TZS</strong></span>
                </div>
                <div class="stage">
                    <span class="stage-num">1</span>
                    <div>
                        <div class="stage-title">{{ __('Business gets verified') }}</div>
                        <div class="stage-amt">+ {{ number_format($r_b1, 0) }} TZS</div>
                    </div>
                </div>
                <div class="stage">
                    <span class="stage-num">2</span>
                    <div>
                        <div class="stage-title">{{ __('First completed booking') }}</div>
                        <div class="stage-amt">+ {{ number_format($r_b2, 0) }} TZS</div>
                    </div>
                </div>
                <div class="stage">
                    <span class="stage-num">3</span>
                    <div>
                        <div class="stage-title">{{ __('Spend :amt TZS within :days days', ['amt' => number_format($r_b_thresh, 0), 'days' => $r_b_days]) }}</div>
                        <div class="stage-amt">+ {{ number_format($r_b3, 0) }} TZS</div>
                    </div>
                </div>
            </div>
        </div>

        <p class="how-earn-foot">
            {{ __('All rewards start as Pending and become Available after the') }}
            <strong>{{ (int) (\App\StaticOption::where('option_name','referral_protection_days')->value('option_value') ?? 14) }}-{{ __('day protection window') }}</strong>.
            {{ __('Transfer to your Main Wallet at') }}
            <strong>{{ number_format((float) (\App\StaticOption::where('option_name','referral_min_withdrawal')->value('option_value') ?? 5000), 0) }} TZS</strong>
            {{ __('or more.') }}
        </p>
    </div>

    {{-- Referred users --}}
    <div class="card-box">
        <h3>{{ __('People you referred') }} ({{ $referrals->total() }})</h3>
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Joined') }}</th>
                    <th>{{ __('Progress') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrals as $r)
                    <tr>
                        <td>{{ optional($r->referredUser)->name ?? '—' }}</td>
                        <td><span class="badge-pill qualifying">{{ ucfirst($r->track) }}</span></td>
                        <td>{{ optional($r->created_at)->format('d M Y') }}</td>
                        <td>
                            <span class="milestone" title="{{ __('Stage 1 → 2 → 3') }}">
                                <span class="dot {{ $r->stage1_at ? 'done' : '' }}"></span>
                                <span class="dot {{ $r->stage2_at ? 'done' : '' }}"></span>
                                <span class="dot {{ $r->stage3_at ? 'done' : '' }}"></span>
                            </span>
                        </td>
                        <td><span class="badge-pill {{ $r->status }}">{{ $r->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">{{ __('No referrals yet. Share your link above to get started!') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($referrals->hasPages())
            <div style="margin-top:14px">{{ $referrals->links() }}</div>
        @endif
    </div>

    {{-- Rewards history — all earnings sit in the Referral Wallet until user clicks Transfer.
         No "type" column: to the referrer it's all just referral earnings, one bucket. --}}
    <div class="card-box">
        <h3>{{ __('Recent rewards') }}</h3>
        <table class="tbl">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Event') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rewards as $rw)
                    <tr>
                        <td>{{ optional($rw->created_at)->format('d M Y') }}</td>
                        <td>{{ $rw->reason ?? $rw->event }}</td>
                        <td><strong>{{ number_format($rw->amount, 0) }}</strong> {{ $rw->currency }}</td>
                        <td><span class="badge-pill {{ $rw->status }}">{{ $rw->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">{{ __('No rewards yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($rewards->hasPages())
            <div style="margin-top:14px">{{ $rewards->links() }}</div>
        @endif
    </div>

</div>

<script>
document.querySelectorAll('.copy-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
        var text = this.getAttribute('data-copy');
        var original = this.textContent;
        var self = this;
        if(navigator.clipboard){
            navigator.clipboard.writeText(text).then(function(){
                self.textContent = '✓ {{ __("Copied!") }}';
                self.classList.add('copied');
                setTimeout(function(){ self.textContent = original; self.classList.remove('copied'); }, 1800);
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); } catch(e){}
            document.body.removeChild(ta);
            self.textContent = '✓ {{ __("Copied!") }}';
            self.classList.add('copied');
            setTimeout(function(){ self.textContent = original; self.classList.remove('copied'); }, 1800);
        }
    });
});
</script>
