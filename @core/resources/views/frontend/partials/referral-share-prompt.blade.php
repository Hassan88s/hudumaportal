{{--
    Rafiki Rewards — Share Prompt Modal (PDF §16, §18)

    Renders a small celebration modal that offers a one-tap WhatsApp share
    right after a happy moment. Triggered by session flash:

        session()->flash('refer_prompt', [
            'title'  => __('Hongera! Umepata booking.'),
            'body'   => __('Unamjua mtu mwingine mwenye ujuzi? Mwalike na upate hadi TZS 3,000.'),
            'context'=> 'first_booking',   // used as dismissal cookie key
        ]);

    OR set explicitly via JS by dispatching:
        window.dispatchEvent(new CustomEvent('rf:show-share-prompt', {detail: {title,body,context}}));

    Dismissal is remembered via a 30-day cookie so it never nags the same
    user twice for the same context.
--}}

@php
    $u = auth('web')->user() ?? auth('api')->user();
    $refCode = $u?->referral_code;
    if (!$refCode) return;

    // Only render if we have flash OR the layout wants it always available for JS.
    $flash = session('refer_prompt');
    $shareUrl = url('/r/' . $refCode);
    $welcomeAmt = number_format((float) (\App\StaticOption::where('option_name','referral_client_welcome_credit')->value('option_value') ?? 1000), 0);
    $defaultMsg = __('Nimeanza kutumia HudumaPortal kupata huduma na fursa za kazi. Jiunge kupitia link yangu: :url / Try Huduma Portal — use my link to sign up: :url', ['url' => $shareUrl]);
@endphp

<style>
    #rfShareModal{position:fixed;inset:0;background:rgba(15,20,32,.55);z-index:99999;display:none;align-items:center;justify-content:center;padding:20px}
    #rfShareModal.open{display:flex;animation:rf-fade .18s ease-out}
    @keyframes rf-fade{from{opacity:0}to{opacity:1}}
    #rfShareModal .rf-card{background:#fff;border-radius:18px;max-width:440px;width:100%;padding:0;overflow:hidden;box-shadow:0 24px 48px rgba(0,0,0,.25);animation:rf-pop .25s cubic-bezier(.32,1.4,.5,1)}
    @keyframes rf-pop{from{transform:translateY(20px) scale(.94);opacity:0}to{transform:translateY(0) scale(1);opacity:1}}
    #rfShareModal .rf-head{background:linear-gradient(135deg,#ff8a54,#ff6b3d);color:#fff;padding:28px 24px 22px;text-align:center;position:relative}
    #rfShareModal .rf-head .close{position:absolute;top:14px;right:16px;background:rgba(255,255,255,.2);border:none;color:#fff;width:32px;height:32px;border-radius:50%;font-size:18px;cursor:pointer;line-height:1;font-family:system-ui}
    #rfShareModal .rf-head .close:hover{background:rgba(255,255,255,.35)}
    #rfShareModal .rf-emoji{font-size:44px;line-height:1;margin-bottom:8px}
    #rfShareModal .rf-title{font-size:20px;font-weight:800;margin:0 0 6px;color:#fff;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
    #rfShareModal .rf-body{font-size:14px;color:rgba(255,255,255,.92);margin:0;line-height:1.5}
    #rfShareModal .rf-content{padding:22px 24px 24px}
    #rfShareModal .rf-msg{width:100%;min-height:80px;padding:12px 14px;font-size:12.5px;color:#374151;background:#f8f9fb;border:1px solid #e4e7ec;border-radius:10px;resize:vertical;line-height:1.55;margin-bottom:14px;font-family:inherit}
    #rfShareModal .rf-msg:focus{outline:none;border-color:#ff8a54;background:#fff}
    #rfShareModal .rf-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    #rfShareModal .rf-btn{padding:12px 18px;border-radius:10px;font-weight:700;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:transform .1s,opacity .1s;cursor:pointer;border:none;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
    #rfShareModal .rf-btn:hover{transform:translateY(-1px)}
    #rfShareModal .rf-btn-wa{background:#25d366;color:#fff}
    #rfShareModal .rf-btn-copy{background:#1f2733;color:#fff}
    #rfShareModal .rf-btn-copy.copied{background:#10b981}
    #rfShareModal .rf-footnote{font-size:11px;color:#8892a0;text-align:center;margin:10px 0 0}
    #rfShareModal .rf-footnote a{color:#ff6b3d;font-weight:700;text-decoration:none}
</style>

<div id="rfShareModal" role="dialog" aria-labelledby="rfShareTitle">
    <div class="rf-card">
        <div class="rf-head">
            <button type="button" class="close" onclick="rfCloseSharePrompt()" aria-label="Close">×</button>
            <div class="rf-emoji" id="rfShareEmoji">🎉</div>
            <h3 class="rf-title" id="rfShareTitle">{{ $flash['title'] ?? __('Share Huduma Portal with a friend') }}</h3>
            <p class="rf-body" id="rfShareBody">{{ $flash['body'] ?? __('Send them your link — you earn when they sign up and complete their first booking.') }}</p>
        </div>
        <div class="rf-content">
            <textarea class="rf-msg" id="rfShareMsg" readonly onclick="this.select()">{{ $defaultMsg }}</textarea>
            <div class="rf-actions">
                <button type="button" class="rf-btn rf-btn-copy" id="rfCopyBtn" onclick="rfCopyLink()">
                    <i class="las la-copy"></i> {{ __('Copy Link') }}
                </button>
                <a class="rf-btn rf-btn-wa" id="rfWaBtn" target="_blank" rel="noopener"
                   href="https://wa.me/?text={{ urlencode($defaultMsg) }}"
                   onclick="rfCloseSharePrompt(true)">
                    <i class="lab la-whatsapp"></i> {{ __('Share on WhatsApp') }}
                </a>
            </div>
            <p class="rf-footnote">
                <a href="#" onclick="rfCloseSharePrompt();return false">{{ __('Maybe later') }}</a>
            </p>
        </div>
    </div>
</div>

<script>
(function(){
    var modal = document.getElementById('rfShareModal');
    var shareUrl = @json(url('/r/' . $refCode));

    // Dismissal cookie helpers (30-day per context)
    function ctxKey(c){ return 'rf_share_dismissed_' + (c || 'default'); }
    function isDismissed(c){ return document.cookie.split('; ').some(function(x){ return x.startsWith(ctxKey(c) + '='); }); }
    function markDismissed(c){
        var d = new Date(); d.setDate(d.getDate() + 30);
        document.cookie = ctxKey(c) + '=1; expires=' + d.toUTCString() + '; path=/';
    }

    window.rfOpenSharePrompt = function(opts){
        opts = opts || {};
        if (isDismissed(opts.context)) return;
        if (opts.title) document.getElementById('rfShareTitle').textContent = opts.title;
        if (opts.body)  document.getElementById('rfShareBody').textContent = opts.body;
        if (opts.emoji) document.getElementById('rfShareEmoji').textContent = opts.emoji;
        modal.dataset.context = opts.context || 'default';
        modal.classList.add('open');
    };

    window.rfCloseSharePrompt = function(fromShare){
        markDismissed(modal.dataset.context);
        modal.classList.remove('open');
    };

    window.rfCopyLink = function(){
        var btn = document.getElementById('rfCopyBtn');
        var original = btn.innerHTML;
        var text = document.getElementById('rfShareMsg').value;
        var done = function(){
            btn.classList.add('copied');
            btn.innerHTML = '<i class="las la-check"></i> {{ __("Copied!") }}';
            setTimeout(function(){ btn.classList.remove('copied'); btn.innerHTML = original; }, 1800);
        };
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(done).catch(function(){});
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); done(); } catch(e){}
            document.body.removeChild(ta);
        }
    };

    // Fire from session flash
    @if($flash)
        setTimeout(function(){
            window.rfOpenSharePrompt({
                title:  @json($flash['title'] ?? null),
                body:   @json($flash['body'] ?? null),
                emoji:  @json($flash['emoji'] ?? null),
                context:@json($flash['context'] ?? 'default')
            });
        }, 600);
    @endif

    // Allow external code to trigger via CustomEvent
    window.addEventListener('rf:show-share-prompt', function(e){
        window.rfOpenSharePrompt(e.detail || {});
    });

    // Close on backdrop click
    modal.addEventListener('click', function(e){
        if (e.target === modal) window.rfCloseSharePrompt();
    });
})();
</script>
