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
    <title>{{ $siteTitle }} — {{ __('Spin & Win') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{ --brand: {{ $brand }}; }
        *{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
        body{background:radial-gradient(1200px 600px at 50% -10%, #1b2250 0%, #0f1535 55%, #0b1029 100%);color:#e8ebf5;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:28px 16px 60px;}
        .pw-badge{display:inline-block;background:rgba(255,138,84,.15);color:#ffb892;font-size:12px;font-weight:700;letter-spacing:.5px;padding:6px 14px;border-radius:999px;text-transform:uppercase;margin-bottom:14px;}
        .pw-logo img{max-height:42px;width:auto;margin-bottom:6px;}
        .pw-logo .word{font-size:22px;font-weight:800;color:#fff;}
        h1{font-size:30px;font-weight:800;color:#fff;text-align:center;margin:10px 0 4px;}
        .pw-sub{color:#aeb6d6;font-size:15px;text-align:center;max-width:460px;margin:0 auto 26px;line-height:1.6;}

        .wheel-stage{position:relative;width:min(88vw,460px);height:min(88vw,460px);margin:0 auto;}
        /* pointer */
        .pw-pointer{position:absolute;top:-6px;left:50%;transform:translateX(-50%);z-index:5;width:0;height:0;border-left:18px solid transparent;border-right:18px solid transparent;border-top:30px solid var(--brand);filter:drop-shadow(0 4px 6px rgba(0,0,0,.4));}
        .pw-ring{position:absolute;inset:0;border-radius:50%;padding:10px;background:conic-gradient(from 0deg,#ffd27a,#ff8a54,#ffd27a,#ff8a54,#ffd27a,#ff8a54,#ffd27a,#ff8a54,#ffd27a);box-shadow:0 20px 50px rgba(0,0,0,.45), inset 0 0 0 2px rgba(255,255,255,.1);}
        canvas#wheel{display:block;width:100%;height:100%;border-radius:50%;background:#10152f;transition:transform 5s cubic-bezier(.17,.67,.14,1);}
        /* center hub + spin */
        .pw-hub{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:4;width:92px;height:92px;border-radius:50%;background:linear-gradient(145deg,#fff,#e9edf8);border:5px solid var(--brand);display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 24px rgba(0,0,0,.4);transition:transform .15s ease;}
        .pw-hub:hover{transform:translate(-50%,-50%) scale(1.05);}
        .pw-hub:active{transform:translate(-50%,-50%) scale(.96);}
        .pw-hub.disabled{cursor:not-allowed;filter:grayscale(.4) opacity(.85);}
        .pw-hub span{font-size:16px;font-weight:800;color:var(--brand);text-transform:uppercase;letter-spacing:.5px;}

        .pw-actions{margin-top:30px;text-align:center;}
        .pw-spins{color:#aeb6d6;font-size:14px;margin-bottom:12px;}
        .pw-spins b{color:#fff;}
        .pw-btn{display:inline-block;background:var(--brand);color:#fff;border:none;font-weight:700;font-size:16px;padding:14px 40px;border-radius:12px;cursor:pointer;box-shadow:0 10px 24px rgba(255,107,44,.35);transition:transform .15s ease,opacity .2s;}
        .pw-btn:hover{transform:translateY(-2px);}
        .pw-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}

        /* result modal */
        .pw-modal{position:fixed;inset:0;background:rgba(6,10,26,.78);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:50;padding:20px;}
        .pw-modal.show{display:flex;}
        .pw-card{background:#171d42;border:1px solid #2a3366;border-radius:22px;max-width:400px;width:100%;padding:38px 30px;text-align:center;box-shadow:0 30px 80px rgba(0,0,0,.55);animation:pop .35s cubic-bezier(.2,1.2,.3,1);}
        @keyframes pop{from{transform:scale(.8);opacity:0;}to{transform:scale(1);opacity:1;}}
        .pw-emoji{font-size:54px;line-height:1;margin-bottom:8px;}
        .pw-card h2{color:#fff;font-size:24px;font-weight:800;margin-bottom:6px;}
        .pw-prize{display:inline-block;margin:10px 0 6px;background:rgba(255,138,84,.16);color:#ffc29e;font-size:20px;font-weight:800;padding:10px 22px;border-radius:12px;}
        .pw-card p{color:#aeb6d6;font-size:14px;margin:12px 0 22px;line-height:1.6;}
        .pw-card .pw-btn.ghost{background:transparent;border:1px solid #39416e;color:#cdd3ee;box-shadow:none;margin-left:8px;}

        /* confetti */
        .confetti{position:fixed;top:-10px;width:9px;height:14px;z-index:60;opacity:.9;will-change:transform;pointer-events:none;}
        @keyframes fall{to{transform:translateY(105vh) rotate(680deg);opacity:1;}}

        .pw-foot{margin-top:40px;color:#6f789e;font-size:12px;text-align:center;}
    </style>
</head>
<body>
    <div class="pw-badge">★ {{ __('Sample Preview') }}</div>

    <div class="pw-logo">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $siteTitle }}" onerror="this.style.display='none';document.getElementById('pw-word').style.display='inline';">
            <span id="pw-word" class="word" style="display:none;">{{ $siteTitle }}</span>
        @else
            <span class="word">{{ $siteTitle }}</span>
        @endif
    </div>

    <h1>🎡 {{ __('Spin & Win') }}</h1>
    <p class="pw-sub">{{ __('Give the wheel a spin for a chance to win Huduma rewards — points, discounts and more. This is a sample to preview the experience.') }}</p>

    <div class="wheel-stage">
        <div class="pw-pointer"></div>
        <div class="pw-ring">
            <canvas id="wheel" width="460" height="460"></canvas>
        </div>
        <div class="pw-hub" id="hub"><span>{{ __('Spin') }}</span></div>
    </div>

    <div class="pw-actions">
        <div class="pw-spins">{{ __('Spins left') }}: <b id="spinsLeft">3</b></div>
        <button class="pw-btn" id="spinBtn">{{ __('Spin the Wheel') }}</button>
    </div>

    <div class="pw-foot">© {{ date('Y') }} {{ $siteTitle }} · {{ __('Preview only — no rewards are issued on this page.') }}</div>

    <!-- Result modal -->
    <div class="pw-modal" id="resultModal">
        <div class="pw-card">
            <div class="pw-emoji" id="resEmoji">🎉</div>
            <h2 id="resTitle">{{ __('Congratulations!') }}</h2>
            <div class="pw-prize" id="resPrize">50 HP</div>
            <p id="resNote">{{ __('You landed a reward. In the real version this would be added to your account.') }}</p>
            <button class="pw-btn" id="againBtn">{{ __('Spin Again') }}</button>
            <button class="pw-btn ghost" id="closeBtn">{{ __('Close') }}</button>
        </div>
    </div>

    <script>
    (function(){
        "use strict";
        var BRAND = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#ff6b2c';

        // Sample reward segments (weight = relative chance). Edit freely once approved.
        var segments = [
            { label: '50 HP',        short:'50 HP',        emoji:'⭐', color:'#ff8a54', weight:20, win:true },
            { label: '10% OFF',      short:'10% OFF',      emoji:'🏷️', color:'#39d0c9', weight:16, win:true },
            { label: 'Try Again',    short:'Try Again',    emoji:'🔁', color:'#5a6488', weight:22, win:false },
            { label: '100 HP',       short:'100 HP',       emoji:'💎', color:'#a66bff', weight:12, win:true },
            { label: 'Free Delivery',short:'Free\nDelivery',emoji:'🚚', color:'#4caf72', weight:10, win:true },
            { label: '25 HP',        short:'25 HP',        emoji:'✨', color:'#4c8dff', weight:18, win:true },
            { label: '20% OFF',      short:'20% OFF',      emoji:'🔥', color:'#ff5d7a', weight:8,  win:true },
            { label: '500 HP',       short:'500 HP',       emoji:'🏆', color:'#ffcf4d', weight:2,  win:true }
        ];

        var canvas = document.getElementById('wheel');
        var ctx = canvas.getContext('2d');
        var N = segments.length;
        var seg = (2 * Math.PI) / N;
        var R = canvas.width / 2;

        function drawWheel(){
            ctx.clearRect(0,0,canvas.width,canvas.height);
            for (var i = 0; i < N; i++){
                var start = -Math.PI/2 + i * seg;
                var end = start + seg;
                // slice
                ctx.beginPath();
                ctx.moveTo(R, R);
                ctx.arc(R, R, R - 4, start, end);
                ctx.closePath();
                ctx.fillStyle = segments[i].color;
                ctx.fill();
                ctx.strokeStyle = 'rgba(255,255,255,.18)';
                ctx.lineWidth = 2;
                ctx.stroke();
                // label (flipped on the left half so it never reads upside-down)
                var mid = start + seg/2;
                var deg = ((mid * 180 / Math.PI) % 360 + 360) % 360;
                var flip = (deg > 90 && deg < 270);
                ctx.save();
                ctx.translate(R, R);
                ctx.rotate(mid);
                if (flip) { ctx.rotate(Math.PI); }
                ctx.textAlign = flip ? 'left' : 'right';
                ctx.fillStyle = '#10152f';
                ctx.font = '700 19px Poppins, sans-serif';
                var lines = segments[i].short.split('\n');
                var lx = flip ? -(R - 26) : (R - 26);
                var ex = flip ? -(R - 96) : (R - 96);
                for (var l = 0; l < lines.length; l++){
                    ctx.fillText(lines[l], lx, 6 + (l * 20) - ((lines.length-1)*10));
                }
                ctx.font = '22px sans-serif';
                ctx.fillText(segments[i].emoji, ex, 8);
                ctx.restore();
            }
            // inner circle
            ctx.beginPath();
            ctx.arc(R, R, 58, 0, 2*Math.PI);
            ctx.fillStyle = '#10152f';
            ctx.fill();
            ctx.strokeStyle = BRAND;
            ctx.lineWidth = 4;
            ctx.stroke();
        }
        drawWheel();

        function weightedPick(){
            var total = 0, i;
            for (i=0;i<N;i++) total += segments[i].weight;
            var r = Math.random() * total;
            for (i=0;i<N;i++){ r -= segments[i].weight; if (r <= 0) return i; }
            return N-1;
        }

        var spinning = false;
        var currentRotation = 0;
        var spinsLeft = 3;
        var spinBtn = document.getElementById('spinBtn');
        var hub = document.getElementById('hub');
        var spinsLeftEl = document.getElementById('spinsLeft');

        function spin(){
            if (spinning) return;
            if (spinsLeft <= 0){ flashNoSpins(); return; }
            spinning = true;
            spinsLeft--;
            spinsLeftEl.textContent = spinsLeft;
            spinBtn.disabled = true;
            hub.classList.add('disabled');

            var idx = weightedPick();
            // angle to bring segment idx centre under the top pointer
            var segDeg = 360 / N;
            var jitter = (Math.random() - 0.5) * segDeg * 0.6;
            var target = 360 * 6 - (idx + 0.5) * segDeg + jitter;
            // keep accumulating so it always spins forward
            currentRotation += target;
            canvas.style.transform = 'rotate(' + currentRotation + 'deg)';

            setTimeout(function(){
                spinning = false;
                spinBtn.disabled = false;
                hub.classList.remove('disabled');
                showResult(segments[idx]);
            }, 5200);
        }

        function flashNoSpins(){
            spinsLeftEl.parentElement.style.color = '#ff7a7a';
            setTimeout(function(){ spinsLeftEl.parentElement.style.color = ''; }, 900);
        }

        var modal = document.getElementById('resultModal');
        function showResult(s){
            document.getElementById('resEmoji').textContent = s.win ? s.emoji : '😅';
            document.getElementById('resTitle').textContent = s.win ? '{{ __('Congratulations!') }}' : '{{ __('So close!') }}';
            document.getElementById('resPrize').textContent = s.label;
            document.getElementById('resNote').textContent = s.win
                ? '{{ __('You won this reward. In the live version it would be added to your account automatically.') }}'
                : '{{ __('No prize this time — give it another spin!') }}';
            if (s.win) burstConfetti();
            modal.classList.add('show');
        }
        function closeModal(){ modal.classList.remove('show'); }

        function burstConfetti(){
            var colors = ['#ffcf4d','#ff8a54','#39d0c9','#a66bff','#4c8dff','#ff5d7a'];
            for (var i=0;i<90;i++){
                var c = document.createElement('div');
                c.className = 'confetti';
                c.style.left = Math.random()*100 + 'vw';
                c.style.background = colors[i % colors.length];
                c.style.animation = 'fall ' + (2.4 + Math.random()*1.8) + 's linear ' + (Math.random()*0.4) + 's forwards';
                c.style.transform = 'rotate(' + (Math.random()*360) + 'deg)';
                document.body.appendChild(c);
                (function(el){ setTimeout(function(){ el.remove(); }, 4800); })(c);
            }
        }

        spinBtn.addEventListener('click', spin);
        hub.addEventListener('click', spin);
        document.getElementById('closeBtn').addEventListener('click', closeModal);
        document.getElementById('againBtn').addEventListener('click', function(){ closeModal(); setTimeout(spin, 250); });
        modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
    })();
    </script>
</body>
</html>
