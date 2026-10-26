<!DOCTYPE html>
@php
    $brand     = get_static_option('site_color') ?: '#ff6b2c';
    $siteTitle = get_static_option('site_title') ?: 'Huduma Portal';
    $logoId    = get_static_option('site_logo');
    $logoUrl   = null;
    if (!empty($logoId)) { try { $a = \App\MediaUpload::find($logoId); if ($a && !empty($a->path)) $logoUrl = asset('assets/uploads/media-uploader/' . $a->path); } catch (\Throwable $e) {} }
@endphp
<html lang="{{ get_default_language() }}" dir="{{ get_user_lang_direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteTitle }} — {{ __($settings['title']) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{ --brand: {{ $brand }}; }
        *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
        body{background:radial-gradient(1200px 600px at 50% -10%, #1b2250 0%, #0f1535 55%, #0b1029 100%);color:#e8ebf5;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:24px 16px 60px;}
        .pw-top{width:100%;max-width:560px;display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;}
        .pw-back{color:#aeb6d6;text-decoration:none;font-size:14px;}
        .pw-logo img{max-height:40px;width:auto;}
        .pw-logo .word{font-size:20px;font-weight:800;color:#fff;}
        h1{font-size:30px;font-weight:800;color:#fff;text-align:center;margin:14px 0 4px;}
        .pw-sub{color:#aeb6d6;font-size:15px;text-align:center;max-width:460px;margin:0 auto 22px;line-height:1.6;}

        .wheel-stage{position:relative;width:min(88vw,440px);height:min(88vw,440px);margin:0 auto;}
        .pw-pointer{position:absolute;top:-6px;left:50%;transform:translateX(-50%);z-index:5;width:0;height:0;border-left:18px solid transparent;border-right:18px solid transparent;border-top:30px solid var(--brand);filter:drop-shadow(0 4px 6px rgba(0,0,0,.4));}
        .pw-ring{position:absolute;inset:0;border-radius:50%;padding:10px;background:conic-gradient(from 0deg,#ffd27a,#ff8a54,#ffd27a,#ff8a54,#ffd27a,#ff8a54,#ffd27a,#ff8a54,#ffd27a);box-shadow:0 20px 50px rgba(0,0,0,.45), inset 0 0 0 2px rgba(255,255,255,.1);}
        canvas#wheel{display:block;width:100%;height:100%;border-radius:50%;background:#10152f;transition:transform 5s cubic-bezier(.17,.67,.14,1);}
        .pw-hub{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:4;width:86px;height:86px;border-radius:50%;background:linear-gradient(145deg,#fff,#e9edf8);border:5px solid var(--brand);display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 24px rgba(0,0,0,.4);transition:transform .15s ease;}
        .pw-hub:hover{transform:translate(-50%,-50%) scale(1.05);}
        .pw-hub:active{transform:translate(-50%,-50%) scale(.96);}
        .pw-hub.disabled{cursor:not-allowed;filter:grayscale(.4) opacity(.85);}
        .pw-hub span{font-size:15px;font-weight:800;color:var(--brand);text-transform:uppercase;}

        .pw-actions{margin-top:28px;text-align:center;}
        .pw-spins{color:#aeb6d6;font-size:14px;margin-bottom:12px;}
        .pw-spins b{color:#fff;}
        .pw-btn{display:inline-block;background:var(--brand);color:#fff;border:none;font-weight:700;font-size:16px;padding:14px 40px;border-radius:12px;cursor:pointer;box-shadow:0 10px 24px rgba(255,107,44,.35);transition:transform .15s ease,opacity .2s;}
        .pw-btn:hover{transform:translateY(-2px);}
        .pw-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}

        .pw-modal{position:fixed;inset:0;background:rgba(6,10,26,.78);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:50;padding:20px;}
        .pw-modal.show{display:flex;}
        .pw-card{background:#171d42;border:1px solid #2a3366;border-radius:22px;max-width:400px;width:100%;padding:36px 30px;text-align:center;box-shadow:0 30px 80px rgba(0,0,0,.55);animation:pop .35s cubic-bezier(.2,1.2,.3,1);}
        @keyframes pop{from{transform:scale(.8);opacity:0;}to{transform:scale(1);opacity:1;}}
        .pw-emoji{font-size:52px;line-height:1;margin-bottom:6px;}
        .pw-card h2{color:#fff;font-size:23px;font-weight:800;margin-bottom:6px;}
        .pw-prize{display:inline-block;margin:10px 0 6px;background:rgba(255,138,84,.16);color:#ffc29e;font-size:20px;font-weight:800;padding:10px 22px;border-radius:12px;}
        .pw-code{margin-top:12px;font-size:13px;color:#aeb6d6;}
        .pw-code b{display:inline-block;margin-top:4px;color:#fff;font-size:17px;letter-spacing:1px;background:rgba(255,255,255,.08);border:1px dashed #49518a;border-radius:8px;padding:6px 14px;}
        .pw-card p{color:#aeb6d6;font-size:14px;margin:12px 0 22px;line-height:1.6;}
        .pw-card .pw-btn.ghost{background:transparent;border:1px solid #39416e;color:#cdd3ee;box-shadow:none;}

        .confetti{position:fixed;top:-10px;width:9px;height:14px;z-index:60;opacity:.9;will-change:transform;pointer-events:none;}
        @keyframes fall{to{transform:translateY(105vh) rotate(680deg);opacity:1;}}
        .pw-foot{margin-top:38px;color:#6f789e;font-size:12px;text-align:center;}
    </style>
</head>
<body>
    <div class="pw-top">
        <a href="{{ url('/') }}" class="pw-back">← {{ __('Back to site') }}</a>
        <div class="pw-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" onerror="this.style.display='none';document.getElementById('pw-word').style.display='inline';">
                <span id="pw-word" class="word" style="display:none;">{{ $siteTitle }}</span>
            @else
                <span class="word">{{ $siteTitle }}</span>
            @endif
        </div>
    </div>

    <h1>🎡 {{ __($settings['title']) }}</h1>
    <p class="pw-sub">{{ __($settings['subtitle']) }}</p>

    <div class="wheel-stage">
        <div class="pw-pointer"></div>
        <div class="pw-ring">
            <canvas id="wheel" width="440" height="440"></canvas>
        </div>
        <div class="pw-hub" id="hub"><span>{{ __('Spin') }}</span></div>
    </div>

    <div class="pw-actions">
        <div class="pw-spins">{{ __('Spins left') }}: <b id="spinsLeft">{{ $triesLeft }}</b></div>
        <button class="pw-btn" id="spinBtn" {{ $triesLeft <= 0 ? 'disabled' : '' }}>{{ $triesLeft > 0 ? __('Spin the Wheel') : __('No spins left') }}</button>
    </div>

    <div class="pw-foot">© {{ date('Y') }} {{ $siteTitle }}</div>

    <div class="pw-modal" id="resultModal">
        <div class="pw-card">
            <div class="pw-emoji" id="resEmoji">🎉</div>
            <h2 id="resTitle">{{ __('Congratulations!') }}</h2>
            <div class="pw-prize" id="resPrize"></div>
            <div class="pw-code" id="resCodeWrap" style="display:none;">{{ __('Your code') }}:<br><b id="resCode"></b></div>
            <p id="resNote"></p>
            <button class="pw-btn" id="againBtn">{{ __('Spin Again') }}</button>
            <button class="pw-btn ghost" id="closeBtn">{{ __('Close') }}</button>
        </div>
    </div>

    <script>
    (function(){
        "use strict";
        var BRAND = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#ff6b2c';
        var SPIN_URL = @json(route('spin.wheel.spin'));
        var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var segments = @json($segments);
        var triesLeft = {{ (int) $triesLeft }};

        var T = {
            win: @json(__('Congratulations!')),
            lose: @json(__('So close!')),
            winNote: @json(__('Use the code below on your next order. (Checkout support is coming soon — your win is saved to your account.)')),
            loseNote: @json(__('No prize this time.')),
            loseNoteAgain: @json(__('No prize this time — you have another spin!')),
            noTries: @json(__('No spins left')),
            spinAgain: @json(__('Spin Again')),
            done: @json(__('Done')),
            error: @json(__('Something went wrong. Please try again.'))
        };

        var canvas = document.getElementById('wheel'), ctx = canvas.getContext('2d');
        var N = segments.length, seg = (2*Math.PI)/N, R = canvas.width/2;

        function drawWheel(){
            ctx.clearRect(0,0,canvas.width,canvas.height);
            for (var i=0;i<N;i++){
                var start = -Math.PI/2 + i*seg, end = start+seg;
                ctx.beginPath(); ctx.moveTo(R,R); ctx.arc(R,R,R-4,start,end); ctx.closePath();
                ctx.fillStyle = segments[i].color || '#5a6488'; ctx.fill();
                ctx.strokeStyle='rgba(255,255,255,.18)'; ctx.lineWidth=2; ctx.stroke();
                var mid = start+seg/2, deg=((mid*180/Math.PI)%360+360)%360, flip=(deg>90&&deg<270);
                ctx.save(); ctx.translate(R,R); ctx.rotate(mid); if(flip) ctx.rotate(Math.PI);
                ctx.textAlign = flip?'left':'right'; ctx.fillStyle='#10152f'; ctx.font='700 17px Poppins, sans-serif';
                var lx = flip?-(R-24):(R-24), ex = flip?-(R-92):(R-92);
                ctx.fillText(segments[i].label, lx, 6);
                ctx.font='20px sans-serif'; ctx.fillText(segments[i].emoji||'', ex, 8);
                ctx.restore();
            }
            ctx.beginPath(); ctx.arc(R,R,54,0,2*Math.PI); ctx.fillStyle='#10152f'; ctx.fill();
            ctx.strokeStyle=BRAND; ctx.lineWidth=4; ctx.stroke();
        }
        drawWheel();

        var spinning=false, currentRotation=0;
        var spinBtn=document.getElementById('spinBtn'), hub=document.getElementById('hub'), spinsLeftEl=document.getElementById('spinsLeft');
        var modal=document.getElementById('resultModal');

        function setDisabled(d){ spinBtn.disabled=d; hub.classList.toggle('disabled',d); }

        function spin(){
            if (spinning) return;
            if (triesLeft<=0){ return; }
            spinning=true; setDisabled(true);

            fetch(SPIN_URL, {
                method:'POST',
                headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json','Content-Type':'application/json'},
                credentials:'same-origin'
            }).then(function(r){ return r.json(); }).then(function(res){
                if (!res || !res.ok){
                    spinning=false;
                    if (res && res.reason==='no_tries'){ triesLeft=0; updateSpinsUi(); }
                    else { setDisabled(triesLeft<=0); alert(T.error); }
                    return;
                }
                var idx = res.index, segDeg = 360/N;
                var jitter = (Math.random()-0.5)*segDeg*0.6;
                currentRotation += 360*6 - (idx+0.5)*segDeg + jitter;
                canvas.style.transform = 'rotate(' + currentRotation + 'deg)';
                setTimeout(function(){
                    spinning=false;
                    triesLeft = res.tries_left;
                    updateSpinsUi();
                    showResult(res);
                }, 5200);
            }).catch(function(){ spinning=false; setDisabled(triesLeft<=0); alert(T.error); });
        }

        function updateSpinsUi(){
            spinsLeftEl.textContent = triesLeft;
            if (triesLeft<=0){ setDisabled(true); spinBtn.textContent = T.noTries; }
        }

        function showResult(res){
            document.getElementById('resEmoji').textContent = res.is_win ? (res.emoji||'🎉') : '😅';
            document.getElementById('resTitle').textContent = res.is_win ? T.win : T.lose;
            document.getElementById('resPrize').textContent = res.label;
            var codeWrap = document.getElementById('resCodeWrap');
            if (res.is_win && res.code){ document.getElementById('resCode').textContent = res.code; codeWrap.style.display='block'; }
            else { codeWrap.style.display='none'; }
            document.getElementById('resNote').textContent = res.is_win ? T.winNote : (triesLeft>0 ? T.loseNoteAgain : T.loseNote);
            var again = document.getElementById('againBtn');
            if (triesLeft>0){ again.style.display=''; again.textContent=T.spinAgain; }
            else { again.style.display='none'; }
            document.getElementById('closeBtn').textContent = triesLeft>0 ? @json(__('Close')) : T.done;
            if (res.is_win) burstConfetti();
            modal.classList.add('show');
        }
        function closeModal(){ modal.classList.remove('show'); }

        function burstConfetti(){
            var colors=['#ffcf4d','#ff8a54','#39d0c9','#a66bff','#4c8dff','#ff5d7a'];
            for (var i=0;i<80;i++){
                var c=document.createElement('div'); c.className='confetti';
                c.style.left=Math.random()*100+'vw'; c.style.background=colors[i%colors.length];
                c.style.animation='fall '+(2.4+Math.random()*1.8)+'s linear '+(Math.random()*0.4)+'s forwards';
                c.style.transform='rotate('+(Math.random()*360)+'deg)';
                document.body.appendChild(c);
                (function(el){ setTimeout(function(){ el.remove(); },4800); })(c);
            }
        }

        spinBtn.addEventListener('click', spin);
        hub.addEventListener('click', spin);
        document.getElementById('closeBtn').addEventListener('click', closeModal);
        document.getElementById('againBtn').addEventListener('click', function(){ closeModal(); setTimeout(spin,250); });
        modal.addEventListener('click', function(e){ if(e.target===modal) closeModal(); });
        updateSpinsUi();
    })();
    </script>
</body>
</html>
