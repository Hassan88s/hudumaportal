<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>{{ $title ?? 'HudumaPortal Live' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="stylesheet" href="{{ asset('assets/frontend/css/line-awesome.min.css') }}">

  <script src="{{ asset('assets/frontend/js/AgoraRTC_N-4.24.0.js') }}"></script>
  <script src="{{ asset('assets/frontend/js/agora-rtm-2.2.2.min.js') }}"></script>

  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    :root{--bg:#0f0f0f;--surface:#1a1a1a;--border:#2a2a2a;--accent:#6366f1;--green:#22c55e;--red:#ef4444;--text:#ffffff;--muted:#a1a1aa;--card:#222}
    html,body{height:100%;background:var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,sans-serif;overflow:hidden}

    /* Main Layout */
    .stage{display:flex;height:100vh;width:100%}
    .main-col{flex:1;display:flex;flex-direction:column;min-width:0;padding:12px 16px}
    .chat-panel{width:360px;background:var(--surface);display:flex;flex-direction:column;border-left:1px solid var(--border)}

    /* Top Bar */
    .top-bar{display:flex;justify-content:space-between;align-items:center;padding:8px 0;gap:12px;flex-shrink:0}
    .stream-info{display:flex;align-items:center;gap:12px;min-width:0;flex:1}
    .live-badge{background:var(--green);color:#fff;font-size:11px;font-weight:800;padding:4px 12px;border-radius:4px;text-transform:uppercase;letter-spacing:.5px;animation:pulse 2s infinite;flex-shrink:0}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.7}}
    .live-badge.offline{background:#555;animation:none}
    .stream-title{font-size:17px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .viewer-count{color:var(--muted);font-size:13px;display:flex;align-items:center;gap:4px;flex-shrink:0}
    .top-actions{display:flex;gap:8px;align-items:center;flex-shrink:0}

    /* Buttons */
    .btn-icon{width:40px;height:40px;border-radius:10px;background:var(--card);border:1px solid var(--border);color:var(--text);font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
    .btn-icon:hover{background:#333;border-color:#444}
    .btn-pill{padding:8px 20px;border-radius:10px;border:none;font-weight:700;font-size:13px;cursor:pointer;transition:all .15s}
    .btn-pill.danger{background:var(--red);color:#fff}
    .btn-pill.danger:hover{background:#dc2626}
    .btn-pill.primary{background:var(--accent);color:#fff}
    .btn-pill.primary:hover{background:#4f46e5}
    .btn-pill.ghost{background:transparent;border:1px solid var(--border);color:var(--muted)}

    /* Video Container */
    .video-wrap{position:relative;flex:1;background:#000;border-radius:12px;overflow:hidden;min-height:200px}
    .video-wrap video{width:100%;height:100%;object-fit:cover;display:block}

    /* Start Overlay */
    .start-overlay{position:absolute;inset:0;background:rgba(0,0,0,.85);display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:50;gap:16px}
    .start-overlay .big-icon{font-size:64px;color:var(--accent);opacity:.8}
    .start-overlay .start-text{color:var(--muted);font-size:15px}
    .start-overlay .btn-start{padding:14px 40px;background:var(--accent);border:none;border-radius:12px;color:#fff;font-size:16px;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:8px}
    .start-overlay .btn-start:hover{background:#4f46e5;transform:scale(1.03)}
    .start-overlay .btn-start:disabled{opacity:.5;cursor:not-allowed;transform:none}
    .start-overlay .waiting-msg{color:#fbbf24;font-size:14px;font-weight:600}

    /* Emoji Layer */
    .emoji-layer{position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:10}
    .f-emoji{position:absolute;bottom:80px;font-size:32px;animation:floatUp 2200ms cubic-bezier(.2,.9,.2,1) forwards;text-shadow:0 4px 16px rgba(0,0,0,.5)}
    @keyframes floatUp{0%{transform:translateY(0) scale(1);opacity:1}60%{transform:translateY(-140px) scale(1.15);opacity:.9}100%{transform:translateY(-280px) scale(.85);opacity:0}}

    /* Emoji Bar (floating on video) */
    .emoji-bar{position:absolute;bottom:16px;left:50%;transform:translateX(-50%);display:flex;gap:6px;background:rgba(0,0,0,.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);padding:8px 16px;border-radius:28px;z-index:20}
    .emoji-bar button{background:none;border:none;font-size:24px;cursor:pointer;padding:4px;transition:transform .12s}
    .emoji-bar button:hover{transform:scale(1.35)}

    /* Controls Bar */
    .controls-bar{display:flex;gap:8px;padding:10px 0;justify-content:center;flex-shrink:0}
    .ctrl-btn{width:48px;height:48px;border-radius:50%;background:var(--card);border:1px solid var(--border);color:var(--text);font-size:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;position:relative}
    .ctrl-btn:hover{background:#333;border-color:#444}
    .ctrl-btn.muted-state{background:var(--red);border-color:var(--red);color:#fff}
    .ctrl-btn .tooltip{position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);background:#333;color:#fff;padding:4px 10px;border-radius:6px;font-size:11px;white-space:nowrap;pointer-events:none;opacity:0;transition:opacity .15s}
    .ctrl-btn:hover .tooltip{opacity:1}

    /* Stream Description */
    .stream-desc{padding:6px 0;color:var(--muted);font-size:13px;line-height:1.5;flex-shrink:0;max-height:60px;overflow:auto}

    /* Share Dropdown */
    .share-wrap{position:relative}
    .share-dropdown{position:absolute;top:calc(100% + 8px);right:0;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px;width:380px;z-index:100;box-shadow:0 8px 32px rgba(0,0,0,.5);display:none}
    .share-dropdown.open{display:block}
    .share-dropdown label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;display:block}
    .share-link-row{display:flex;gap:8px}
    .share-link-row input{flex:1;background:#111;border:1px solid var(--border);border-radius:8px;padding:10px 12px;color:var(--accent);font-size:13px;outline:none}
    .share-link-row button{padding:10px 18px;background:var(--accent);border:none;border-radius:8px;color:#fff;font-weight:700;font-size:13px;cursor:pointer;white-space:nowrap}
    .share-link-row button:hover{background:#4f46e5}

    /* Chat Panel */
    .chat-header{padding:16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-shrink:0}
    .chat-header h3{font-size:15px;font-weight:700}
    .chat-badge{background:var(--accent);color:#fff;font-size:11px;font-weight:700;padding:2px 10px;border-radius:12px}

    /* Audience Section */
    .audience-section{border-bottom:1px solid var(--border);flex-shrink:0}
    .audience-toggle{width:100%;padding:10px 16px;background:none;border:none;color:var(--muted);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;text-align:left}
    .audience-toggle:hover{color:var(--text)}
    .audience-list{padding:8px 16px;max-height:140px;overflow:auto;font-size:13px}
    .audience-list p{padding:4px 0;color:var(--muted)}
    .audience-list .host-tag{color:var(--accent);font-weight:700;font-size:11px;margin-left:4px}

    /* Pending Requests */
    .pending-section{border-bottom:1px solid var(--border);flex-shrink:0}
    .pending-header{padding:10px 16px;font-weight:800;font-size:12px;color:#fbbf24;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:6px}
    .pending-list{padding:4px 16px 12px;max-height:160px;overflow:auto}
    .pending-item{display:flex;gap:8px;align-items:center;justify-content:space-between;margin-bottom:8px;padding:8px;background:rgba(255,255,255,.03);border-radius:8px}
    .pending-item .name{font-weight:700;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:120px}
    .pending-item .uid-label{color:var(--muted);font-size:11px}
    .pending-actions{display:flex;gap:6px;flex-shrink:0}
    .pending-actions button{padding:5px 12px;border-radius:6px;border:none;font-size:12px;font-weight:700;cursor:pointer;transition:all .12s}
    .pending-actions .accept-btn{background:var(--green);color:#fff}
    .pending-actions .accept-btn:hover{background:#16a34a}
    .pending-actions .decline-btn{background:var(--red);color:#fff}
    .pending-actions .decline-btn:hover{background:#dc2626}

    /* Chat Messages */
    .chat-messages{flex:1;overflow-y:auto;padding:12px 16px}
    .chat-messages::-webkit-scrollbar{width:4px}
    .chat-messages::-webkit-scrollbar-thumb{background:#333;border-radius:4px}
    .msg{padding:6px 0;font-size:14px;line-height:1.5;word-break:break-word}
    .msg .author{font-weight:700;color:var(--accent);margin-right:6px}
    .msg.me .author{color:var(--green)}
    .msg.system-msg{color:var(--muted);font-size:13px;font-style:italic;padding:4px 0}

    /* Chat Input */
    .chat-input-wrap{padding:12px 16px;border-top:1px solid var(--border);display:flex;gap:8px;flex-shrink:0}
    #chatInput{flex:1;background:var(--card);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-size:14px;outline:none}
    #chatInput:focus{border-color:var(--accent)}
    #chatInput::placeholder{color:#555}
    .send-btn{width:40px;height:40px;background:var(--accent);border:none;border-radius:8px;color:#fff;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;flex-shrink:0}
    .send-btn:hover{background:#4f46e5}

    /* Fullscreen */
    .video-wrap:fullscreen,
    .video-wrap:-webkit-full-screen{width:100vw!important;height:100vh!important;background:#000;border-radius:0}
    .exit-fs-btn{position:absolute;top:16px;right:16px;z-index:9999;background:rgba(239,68,68,.9);border:none;padding:10px 16px;border-radius:8px;color:#fff;font-weight:700;font-size:13px;cursor:pointer;display:none}
    .video-wrap:fullscreen .exit-fs-btn,
    .video-wrap:-webkit-full-screen .exit-fs-btn{display:inline-block!important}
    .mobile-fullscreen{position:fixed!important;top:0;left:0;width:100vw!important;height:100dvh!important;background:#000!important;z-index:999999!important;border-radius:0!important;overflow:hidden}
    @supports not (height:100dvh){.mobile-fullscreen{height:100vh!important}}
    .mobile-fullscreen .exit-fs-btn{display:inline-block!important}

    /* Mobile */
    @media(max-width:768px){
      body{overflow:auto}
      .stage{flex-direction:column;height:auto;min-height:100vh}
      .main-col{padding:8px}
      .chat-panel{width:100%;border-left:none;border-top:1px solid var(--border);max-height:50vh}
      .video-wrap{min-height:240px;border-radius:8px}
      .stream-title{font-size:14px}
      .ctrl-btn{width:40px;height:40px;font-size:16px}
      .emoji-bar button{font-size:20px}
      .share-dropdown{width:calc(100vw - 32px);right:-40px}
      .top-actions .btn-pill{padding:6px 12px;font-size:12px}
    }
  </style>
</head>

<body>
  <script>
    const AUTH_USER = @json(optional(auth()->user())->username ?? null);
    const CURRENT_USER_ID = @json(optional(auth()->user())->id ?? null);
    const PARAMS = {
      channel: @json(request('channel', 'demoChannel')),
      role: (@json(request('role', 'host')) || 'audience').toLowerCase(),
      redirectURL: @json(url('/'))
    };
    const CSRF = @json(csrf_token());
    const STREAM_TITLE = @json($title ?? '');
    const STREAM_DESC = @json($description ?? '');
    const HOST_NAME = @json($hostName ?? '');
  </script>

  <div class="stage" id="stageRoot">
    {{-- LEFT: Main video area --}}
    <div class="main-col">
      {{-- Top Bar --}}
      <div class="top-bar">
        <div class="stream-info">
          <span class="live-badge offline" id="liveBadge">OFFLINE</span>
          <span class="stream-title" id="streamTitleEl"></span>
          <span class="viewer-count">
            <i class="las la-eye"></i> <span id="audienceCount">0</span> {{ __('watching') }}
          </span>
        </div>
        <div class="top-actions">
          <div class="share-wrap" id="shareWrap" style="display:none">
            <button class="btn-icon" id="shareLinkBtn" title="Share"><i class="las la-share-alt"></i></button>
            <div class="share-dropdown" id="shareDropdown">
              <label>{{ __('Share this link with your audience') }}</label>
              <div class="share-link-row">
                <input type="text" id="shareUrlInput" readonly>
                <button id="copyLinkBtn"><i class="las la-copy"></i> {{ __('Copy') }}</button>
              </div>

              {{-- Share to Chat section --}}
              <div id="shareToChatSection" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
                <label>{{ __('Send to a chat contact') }}</label>
                <div style="display:flex;gap:8px">
                  <select id="chatContactSelect" style="flex:1;background:#111;border:1px solid var(--border);border-radius:8px;padding:10px 12px;color:#fff;font-size:13px;outline:none">
                    <option value="">{{ __('Loading contacts...') }}</option>
                  </select>
                  <button id="sendToChatBtn" style="padding:10px 16px;background:var(--green);border:none;border-radius:8px;color:#fff;font-weight:700;font-size:13px;cursor:pointer;white-space:nowrap">
                    <i class="las la-paper-plane"></i> {{ __('Send') }}
                  </button>
                </div>
                <div id="shareToChatStatus" style="margin-top:8px;font-size:12px;min-height:16px"></div>
              </div>
            </div>
          </div>
          <button class="btn-pill danger" id="leaveBtn" style="display:none">
            <i class="las la-power-off"></i> {{ __('End') }}
          </button>
        </div>
      </div>

      {{-- Video --}}
      <div class="video-wrap" id="videoContainer">
        <video id="video-player" autoplay playsinline muted></video>
        <div class="emoji-layer" id="emojiLayer"></div>
        <button class="exit-fs-btn" id="exitFullscreenBtn">{{ __('Exit Fullscreen') }}</button>

        {{-- Start Overlay --}}
        <div class="start-overlay" id="startOverlay">
          <i class="las la-broadcast-tower big-icon"></i>
          <div class="start-text" id="overlayText"></div>
          <button class="btn-start" id="startBtn"></button>
          <div class="waiting-msg" id="waitingMsg" style="display:none"></div>
        </div>

        {{-- Emoji Bar --}}
        <div class="emoji-bar" id="emojiBar" style="display:none">
          <button class="emoji-btn" data-emoji="👍">👍</button>
          <button class="emoji-btn" data-emoji="❤️">❤️</button>
          <button class="emoji-btn" data-emoji="👏">👏</button>
          <button class="emoji-btn" data-emoji="🔥">🔥</button>
          <button class="emoji-btn" data-emoji="😂">😂</button>
        </div>
      </div>

      {{-- Controls --}}
      <div class="controls-bar" id="controlsBar" style="display:none">
        <button class="ctrl-btn" id="muteBtn" title="Mute"><i class="las la-microphone"></i><span class="tooltip">{{ __('Mute') }}</span></button>
        <button class="ctrl-btn" id="cameraBtn" title="Camera"><i class="las la-video"></i><span class="tooltip">{{ __('Camera') }}</span></button>
        <button class="ctrl-btn" id="switchCamBtn" title="Switch Camera"><i class="las la-sync"></i><span class="tooltip">{{ __('Switch') }}</span></button>
        <button class="ctrl-btn" id="shareBtn" title="Share Screen"><i class="las la-desktop"></i><span class="tooltip">{{ __('Screen') }}</span></button>
        <button class="ctrl-btn" id="fullscreenBtn" title="Fullscreen"><i class="las la-expand"></i><span class="tooltip">{{ __('Fullscreen') }}</span></button>
      </div>

      {{-- Description --}}
      <div class="stream-desc" id="streamDescEl"></div>
    </div>

    {{-- RIGHT: Chat Panel --}}
    <div class="chat-panel">
      <div class="chat-header">
        <h3><i class="las la-comments"></i> {{ __('Live Chat') }}</h3>
        <span class="chat-badge" id="chatUserCount">0</span>
      </div>

      {{-- Audience --}}
      <div class="audience-section">
        <button class="audience-toggle" id="audienceToggle">
          <i class="las la-users"></i> {{ __('Viewers') }} <i class="las la-angle-down" id="audienceArrow"></i>
        </button>
        <div class="audience-list" id="audienceBox" style="display:none"><i>{{ __('No audience yet') }}</i></div>
      </div>

      {{-- Pending Requests (host only) --}}
      <div class="pending-section" id="pendingWrap" style="display:none">
        <div class="pending-header"><i class="las la-user-clock"></i> {{ __('Join Requests') }}</div>
        <div class="pending-list" id="pendingBox"></div>
      </div>

      {{-- Messages --}}
      <div class="chat-messages" id="chatBox">
        <div id="msgList"></div>
      </div>

      {{-- Input --}}
      <div class="chat-input-wrap">
        <input id="chatInput" placeholder="{{ __('Say something...') }}" autocomplete="off" />
        <button class="send-btn" id="sendBtn"><i class="las la-paper-plane"></i></button>
      </div>
    </div>
  </div>

<script>
/**
 * =========================
 *  DOM Setup
 * =========================
 */
const streamTitleEl = document.getElementById('streamTitleEl');
const streamDescEl = document.getElementById('streamDescEl');
const liveBadge = document.getElementById('liveBadge');
const startOverlay = document.getElementById('startOverlay');
const overlayText = document.getElementById('overlayText');
const waitingMsg = document.getElementById('waitingMsg');

streamTitleEl.textContent = STREAM_TITLE || (HOST_NAME ? HOST_NAME + "'s Stream" : 'HudumaPortal Live');
streamDescEl.textContent = STREAM_DESC || '';

if (PARAMS.role === 'host') {
  overlayText.textContent = 'Ready to go live? Click below to start streaming.';
  document.getElementById('startBtn').innerHTML = '<i class="las la-broadcast-tower"></i> Start Streaming';
} else {
  overlayText.textContent = 'Click below to join this live stream.';
  document.getElementById('startBtn').innerHTML = '<i class="las la-play"></i> Join Stream';
}

/**
 * =========================
 *  Agora RTC + RTM
 * =========================
 */
const client = AgoraRTC.createClient({ mode: "live", codec: "vp8" });

let localTracks = [];
let rtcUid = null;
let appID = null;

let rtmClient = null;
let rtmName = (AUTH_USER || `user${Math.floor(Math.random()*100000)}`)
  .toString()
  .replace(/\s+/g, "_")
  .replace(/[^a-zA-Z0-9_]/g, "");

const remoteRtcUsers = {};
const rtmMembers = new Map();
const lastSeen = new Map();

const pendingRequests = new Map();
let joinGate = {
  pendingRtcUid: null,
  requested: false,
  approved: (PARAMS.role === 'host')
};
let pendingRtcJoinPayload = null;

// DOM refs
const videoPlayer = document.getElementById('video-player');
const emojiLayer = document.getElementById('emojiLayer');
const emojiBar = document.getElementById('emojiBar');

const audienceBox = document.getElementById('audienceBox');
const audienceCountEl = document.getElementById('audienceCount');
const chatUserCount = document.getElementById('chatUserCount');

const msgList = document.getElementById('msgList');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');

const startBtn = document.getElementById('startBtn');
const leaveBtn = document.getElementById('leaveBtn');

const muteBtn = document.getElementById('muteBtn');
const cameraBtn = document.getElementById('cameraBtn');
const switchCamBtn = document.getElementById('switchCamBtn');
const shareBtn = document.getElementById('shareBtn');
const controlsBar = document.getElementById('controlsBar');

const emojiButtons = document.querySelectorAll('.emoji-btn');

const pendingWrap = document.getElementById('pendingWrap');
const pendingBox = document.getElementById('pendingBox');

const shareWrap = document.getElementById('shareWrap');

/**
 * UI helpers
 */
function appendChatMessage(text, opts={}) {
  const el = document.createElement('div');
  if (opts.system) {
    el.className = 'msg system-msg';
    el.textContent = text;
  } else {
    el.className = 'msg' + (opts.me ? ' me' : '');
    if (opts.sender) {
      const span = document.createElement('span');
      span.className = 'author';
      span.textContent = opts.sender;
      el.appendChild(span);
      el.appendChild(document.createTextNode(text));
    } else {
      el.textContent = text;
    }
  }
  msgList.appendChild(el);
  const chatBox = document.getElementById('chatBox');
  chatBox.scrollTop = chatBox.scrollHeight;
}

function updateAudienceUI() {
  const names = Array.from(rtmMembers.values());
  const total = names.length;

  const listHTML = names.map(n => {
    const isHost = (PARAMS.role === 'host' && n === rtmName);
    return `<p>${n}${isHost ? ' <span class="host-tag">(Host)</span>' : ''}</p>`;
  }).join('');

  let audienceCount = total;
  if (PARAMS.role === 'host' && rtmMembers.has(rtmName)) {
    audienceCount = Math.max(total - 1, 0);
  }

  audienceCountEl.textContent = audienceCount;
  chatUserCount.textContent = total;
  audienceBox.innerHTML = listHTML || '<i>No audience yet</i>';
}

function renderPendingRequests() {
  if (PARAMS.role !== 'host') { pendingWrap.style.display = 'none'; return; }
  if (pendingRequests.size === 0) { pendingWrap.style.display = 'none'; pendingBox.innerHTML = ''; return; }

  pendingWrap.style.display = 'block';
  pendingBox.innerHTML = Array.from(pendingRequests.entries()).map(([uid, name]) => `
    <div class="pending-item">
      <div>
        <div class="name">${name}</div>
        <div class="uid-label">uid: ${uid}</div>
      </div>
      <div class="pending-actions">
        <button class="accept-btn" data-accept="${uid}">Accept</button>
        <button class="decline-btn" data-decline="${uid}">Decline</button>
      </div>
    </div>
  `).join('');

  pendingBox.querySelectorAll('[data-accept]').forEach(btn => {
    btn.onclick = () => approveAudience(btn.getAttribute('data-accept'));
  });
  pendingBox.querySelectorAll('[data-decline]').forEach(btn => {
    btn.onclick = () => declineAudience(btn.getAttribute('data-decline'));
  });
}

function spawnEmoji(emoji) {
  const el = document.createElement('div');
  el.className = 'f-emoji';
  el.textContent = emoji;
  el.style.left = `${6 + Math.random()*72}%`;
  el.style.animationDuration = `${1600 + Math.random()*1200}ms`;
  emojiLayer.appendChild(el);
  setTimeout(() => el.remove(), 2500);
}

/**
 * Audience toggle
 */
document.getElementById('audienceToggle').onclick = () => {
  const box = document.getElementById('audienceBox');
  const arrow = document.getElementById('audienceArrow');
  if (box.style.display === 'none') {
    box.style.display = 'block';
    arrow.className = 'las la-angle-up';
  } else {
    box.style.display = 'none';
    arrow.className = 'las la-angle-down';
  }
};

/**
 * Share link
 */
document.getElementById('shareLinkBtn').onclick = (e) => {
  e.stopPropagation();
  const dd = document.getElementById('shareDropdown');
  dd.classList.toggle('open');
};
document.addEventListener('click', () => {
  document.getElementById('shareDropdown').classList.remove('open');
});
document.getElementById('shareDropdown').onclick = (e) => e.stopPropagation();

document.getElementById('copyLinkBtn').onclick = () => {
  const input = document.getElementById('shareUrlInput');
  navigator.clipboard.writeText(input.value).then(() => {
    const btn = document.getElementById('copyLinkBtn');
    btn.innerHTML = '<i class="las la-check"></i> Copied!';
    setTimeout(() => { btn.innerHTML = '<i class="las la-copy"></i> Copy'; }, 2000);
  });
};

/**
 * Share stream to a chat contact (host only)
 */
let chatContactsLoaded = false;
async function loadChatContacts() {
  if (chatContactsLoaded) return;
  const select = document.getElementById('chatContactSelect');
  try {
    const resp = await fetch('/seller/chat-contacts-list', { headers: { 'Accept': 'application/json' }});
    const data = await resp.json();
    const contacts = data.contacts || [];

    if (contacts.length === 0) {
      select.innerHTML = '<option value="">No chat contacts yet</option>';
    } else {
      select.innerHTML = '<option value="">-- Select a contact --</option>' +
        contacts.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    }
    chatContactsLoaded = true;
  } catch (e) {
    select.innerHTML = '<option value="">Failed to load contacts</option>';
  }
}

// Load contacts when share dropdown opens for host
document.getElementById('shareLinkBtn').addEventListener('click', () => {
  if (PARAMS.role === 'host') loadChatContacts();
});

document.getElementById('sendToChatBtn').onclick = async () => {
  const select = document.getElementById('chatContactSelect');
  const status = document.getElementById('shareToChatStatus');
  const btn = document.getElementById('sendToChatBtn');
  const toUser = select.value;

  if (!toUser) {
    status.style.color = '#fbbf24';
    status.textContent = 'Please select a contact';
    return;
  }

  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="las la-spinner la-spin"></i>';
  status.style.color = '';
  status.textContent = '';

  try {
    const formData = new FormData();
    formData.append('_token', CSRF);
    formData.append('to_user', toUser);

    const resp = await fetch('/seller/share-stream-to-chat', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      body: formData
    });
    const data = await resp.json();

    if (resp.ok && data.state === 1) {
      status.style.color = '#22c55e';
      status.textContent = '✓ Stream link sent to ' + (select.options[select.selectedIndex].text);
      select.value = '';
      setTimeout(() => { status.textContent = ''; }, 3000);
    } else {
      status.style.color = '#ef4444';
      status.textContent = data.error || 'Failed to send';
    }
  } catch (e) {
    status.style.color = '#ef4444';
    status.textContent = 'Network error';
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
};

// Hide "Send to chat" section for audience
if (PARAMS.role !== 'host') {
  const s = document.getElementById('shareToChatSection');
  if (s) s.style.display = 'none';
}

/**
 * RTM init
 */
async function initRtm(appId, token, channelName) {
  const { RTM } = AgoraRTM;
  const cleanUid = (rtmName || `guest_${Date.now()}`).replace(/\W+/g, "_").substring(0, 60);

  rtmClient = new RTM(appId, cleanUid);
  await rtmClient.login({ token, uid: cleanUid });

  await rtmClient.subscribe(channelName, AgoraRTM.constantsType.ChannelTypeEnum.MESSAGE);

  if (PARAMS.role === 'host') {
    rtmMembers.set(rtmName, rtmName);
    lastSeen.set(rtmName, Date.now());
    updateAudienceUI();

    try {
      await rtmClient.publish(channelName, JSON.stringify({ type: "presence", action: "join", name: rtmName }));
    } catch(e){}
  } else {
    updateAudienceUI();
  }

  rtmClient.addEventListener("message", async (event) => {
    const raw =
      typeof event.message === "string"
        ? event.message
        : typeof event.message?.text === "string"
        ? event.message.text
        : "";

    const rawMessage = (raw || "").trim();
    if (!rawMessage) return;

    let parsed = null;
    try { parsed = JSON.parse(rawMessage); } catch (e) {
      appendChatMessage(rawMessage, { sender: event.publisher + ': ' });
      return;
    }

    if (parsed.type === "system" && parsed.action === "end") {
      appendChatMessage("The host has ended the stream.", { system: true });
      setTimeout(() => {
        alert("The live stream has ended. Redirecting...");
        window.location.href = PARAMS.redirectURL;
      }, 800);
      return;
    }

    if (parsed.type === "join_request") {
      if (PARAMS.role === "host" && parsed.uid && parsed.name) {
        pendingRequests.set(parsed.uid, parsed.name);
        renderPendingRequests();
        appendChatMessage(parsed.name + " wants to join", { system: true });
      }
      return;
    }

    if (parsed.type === "join_response") {
      if (PARAMS.role === "audience") {
        if (!joinGate.pendingRtcUid || parsed.uid !== joinGate.pendingRtcUid) return;

        if (parsed.action === "accept") {
          joinGate.approved = true;
          appendChatMessage("Host accepted you. Joining stream...", { system: true });

          try {
            await rtmClient.publish(PARAMS.channel, JSON.stringify({
              type: "presence", action: "join", name: rtmName
            }));
          } catch(e){}

          setInterval(async () => {
            try {
              if (rtmClient) {
                await rtmClient.publish(PARAMS.channel, JSON.stringify({
                  type: "presence", action: "ping", name: rtmName
                }));
              }
            } catch(e){}
          }, 10000);

          if (pendingRtcJoinPayload) {
            await joinRtcSession(
              pendingRtcJoinPayload.appID,
              pendingRtcJoinPayload.channel,
              pendingRtcJoinPayload.rtcToken,
              pendingRtcJoinPayload.uid
            );
          }
        } else {
          alert("Host declined your request.");
          window.location.href = PARAMS.redirectURL;
        }
      }
      return;
    }

    if (parsed.type === "presence") {
      const now = Date.now();
      if (parsed.action === "join" || parsed.action === "ping") {
        rtmMembers.set(parsed.name, parsed.name);
        lastSeen.set(parsed.name, now);
        updateAudienceUI();
      } else if (parsed.action === "leave") {
        rtmMembers.delete(parsed.name);
        lastSeen.delete(parsed.name);
        updateAudienceUI();
      }
      return;
    }

    if (parsed.type === "emoji") {
      spawnEmoji(parsed.emoji);
      appendChatMessage('reacted ' + parsed.emoji, { sender: event.publisher + ' ' });
      return;
    }

    if (parsed.type === "chat") {
      if (event.publisher === rtmName) return;
      appendChatMessage(parsed.text, { sender: event.publisher + ': ' });
      return;
    }
  });

  window.sendChatMessage = async (msg) => {
    if (!msg || !msg.trim()) return;
    await rtmClient.publish(channelName, JSON.stringify({ type:"chat", text: msg }));
  };

  window.sendEmoji = async (emoji) => {
    await rtmClient.publish(channelName, JSON.stringify({ type:"emoji", emoji }));
  };
}

/**
 * Host Accept / Decline
 */
async function approveAudience(uid) {
  try {
    await rtmClient.publish(PARAMS.channel, JSON.stringify({
      type: "join_response", action: "accept", uid, by: rtmName, ts: Date.now()
    }));
    pendingRequests.delete(uid);
    renderPendingRequests();
  } catch (e) { console.error("approveAudience failed:", e); }
}

async function declineAudience(uid) {
  try {
    await rtmClient.publish(PARAMS.channel, JSON.stringify({
      type: "join_response", action: "decline", uid, by: rtmName, ts: Date.now()
    }));
    pendingRequests.delete(uid);
    renderPendingRequests();
  } catch (e) { console.error("declineAudience failed:", e); }
}

/**
 * Stale member cleanup
 */
setInterval(() => {
  const cutoff = Date.now() - 20000;
  for (const [name, ts] of lastSeen.entries()) {
    if (ts < cutoff) {
      rtmMembers.delete(name);
      lastSeen.delete(name);
    }
  }
  updateAudienceUI();
}, 20000);

/**
 * Join RTC
 */
async function joinRtcSession(appID, channel, rtcToken, uid) {
  await client.join(appID, channel, rtcToken, uid);

  // Hide overlay, show controls
  startOverlay.style.display = 'none';
  emojiBar.style.display = 'flex';
  liveBadge.textContent = 'LIVE';
  liveBadge.classList.remove('offline');
  leaveBtn.style.display = 'inline-flex';

  if (PARAMS.role === 'host') {
    localTracks = await AgoraRTC.createMicrophoneAndCameraTracks();
    await localTracks[1].play(videoPlayer);
    await client.publish(localTracks);

    controlsBar.style.display = 'flex';

    // Share link
    const shareUrl = `${window.location.origin}${window.location.pathname}?channel=${encodeURIComponent(PARAMS.channel)}&role=audience`;
    document.getElementById('shareUrlInput').value = shareUrl;
    shareWrap.style.display = 'block';

    appendChatMessage('You are now live!', { system: true });
  } else {
    videoPlayer.muted = false;
  }

  // RTC events
  client.on('user-published', async (user, mediaType) => {
    await client.subscribe(user, mediaType);
    remoteRtcUsers[user.uid] = user;
    if (mediaType === 'video') user.videoTrack.play(videoPlayer);
    if (mediaType === 'audio') user.audioTrack.play();
  });

  client.on('user-unpublished', (user) => { delete remoteRtcUsers[user.uid]; });
  client.on('user-left', (user) => { delete remoteRtcUsers[user.uid]; });

  // Emoji handlers
  emojiButtons.forEach(b => b.onclick = async () => {
    const emoji = b.dataset.emoji || '❤️';
    spawnEmoji(emoji);
    await window.sendEmoji(emoji);
  });

  // Chat handlers
  sendBtn.onclick = async () => {
    const text = (chatInput.value || '').trim();
    if (!text) return;
    appendChatMessage(text, { me: true, sender: rtmName + ': ' });
    chatInput.value = '';
    await window.sendChatMessage(text);
  };
  chatInput.onkeydown = (e) => { if (e.key === 'Enter') sendBtn.click(); };

  // Host controls
  if (PARAMS.role === 'host') {
    // Mute
    muteBtn.onclick = async () => {
      if (!localTracks[0]) return;
      const enabled = localTracks[0].enabled;
      await localTracks[0].setEnabled(!enabled);
      muteBtn.classList.toggle('muted-state', enabled);
      muteBtn.querySelector('i').className = enabled ? 'las la-microphone-slash' : 'las la-microphone';
      muteBtn.querySelector('.tooltip').textContent = enabled ? 'Unmute' : 'Mute';
    };

    // Camera
    cameraBtn.onclick = async () => {
      try {
        if (!localTracks[1]) return;
        const isEnabled = localTracks[1].enabled;
        if (isEnabled) {
          await client.unpublish(localTracks[1]);
          await localTracks[1].setEnabled(false);
          localTracks[1].stop();
          cameraBtn.classList.add('muted-state');
          cameraBtn.querySelector('i').className = 'las la-video-slash';
          cameraBtn.querySelector('.tooltip').textContent = 'Camera On';
        } else {
          const newVideoTrack = await AgoraRTC.createCameraVideoTrack();
          await newVideoTrack.play(videoPlayer);
          await client.publish(newVideoTrack);
          try { localTracks[1].close(); } catch(e){}
          localTracks[1] = newVideoTrack;
          cameraBtn.classList.remove('muted-state');
          cameraBtn.querySelector('i').className = 'las la-video';
          cameraBtn.querySelector('.tooltip').textContent = 'Camera Off';
        }
      } catch (err) {
        console.error('Camera toggle failed:', err);
      }
    };

    // Switch camera
    let usingFront = true;
    switchCamBtn.onclick = async () => {
      try {
        switchCamBtn.disabled = true;
        usingFront = !usingFront;
        const facingMode = usingFront ? "user" : "environment";

        if (localTracks[1]) {
          try { await client.unpublish(localTracks[1]); } catch(e){}
          try { localTracks[1].stop(); localTracks[1].close(); } catch(e){}
          localTracks[1] = null;
        }
        await new Promise(res => setTimeout(res, 600));
        const newTrack = await AgoraRTC.createCameraVideoTrack({ facingMode, optimizationMode: "motion" });
        localTracks[1] = newTrack;
        await client.publish(newTrack);
        await newTrack.play(videoPlayer);
      } catch (err) {
        console.error("Camera switch failed:", err);
      } finally {
        switchCamBtn.disabled = false;
      }
    };

    // Screen share
    let isScreenSharing = false;
    let screenTrack = null;

    shareBtn.onclick = async () => {
      try {
        if (!isScreenSharing) {
          screenTrack = await AgoraRTC.createScreenVideoTrack();
          await client.unpublish(localTracks[1]);
          await client.publish(screenTrack);
          await screenTrack.play(videoPlayer);
          shareBtn.classList.add('muted-state');
          shareBtn.querySelector('.tooltip').textContent = 'Stop Sharing';
          isScreenSharing = true;

          screenTrack.on('track-ended', async () => { await stopScreenShare(); });
        } else {
          await stopScreenShare();
        }
      } catch (e) {
        console.error("Screen share failed:", e);
      }
    };

    async function stopScreenShare() {
      try {
        if (screenTrack) {
          await client.unpublish(screenTrack);
          screenTrack.close();
          screenTrack = null;
        }
        const newCamTrack = await AgoraRTC.createCameraVideoTrack();
        localTracks[1] = newCamTrack;
        await client.publish(newCamTrack);
        await newCamTrack.play(videoPlayer);
        shareBtn.classList.remove('muted-state');
        shareBtn.querySelector('.tooltip').textContent = 'Share Screen';
        isScreenSharing = false;
      } catch (e) { console.error("stopScreenShare error:", e); }
    }
  }
}

/**
 * Start / Join
 */
startBtn.onclick = async () => {
  if (client.connectionState === 'CONNECTED') return;
  if (PARAMS.role === 'audience' && joinGate.requested) return;

  startBtn.disabled = true;
  startBtn.innerHTML = (PARAMS.role === 'audience')
    ? '<i class="las la-spinner la-spin"></i> Requesting...'
    : '<i class="las la-spinner la-spin"></i> Starting...';

  try {
    rtcUid = (Math.floor(Math.random()*1e8)).toString();
    joinGate.pendingRtcUid = rtcUid;

    const tokenResp = await fetch(`/agora/token?channel=${encodeURIComponent(PARAMS.channel)}&uid=${encodeURIComponent(rtcUid)}&role=${encodeURIComponent(PARAMS.role)}`);
    const tokenData = await tokenResp.json();

    appID = tokenData.appID || tokenData.appId;
    const rtcToken = tokenData.token;

    let rtmToken = null;
    try {
      const rtmResp = await fetch(`/agora/rtm-token?uid=${encodeURIComponent(rtmName)}`);
      if (rtmResp.ok) {
        const rtmJson = await rtmResp.json();
        rtmToken = rtmJson.rtmToken || rtmJson.token || null;
      }
    } catch(e){}

    await client.setClientRole(PARAMS.role);
    await initRtm(appID, rtmToken, PARAMS.channel);

    if (PARAMS.role === 'audience') {
      joinGate.requested = true;
      pendingRtcJoinPayload = { appID, channel: PARAMS.channel, rtcToken, uid: rtcUid };

      waitingMsg.textContent = 'Waiting for host approval...';
      waitingMsg.style.display = 'block';
      startBtn.style.display = 'none';

      await rtmClient.publish(PARAMS.channel, JSON.stringify({
        type: "join_request", uid: rtcUid, name: rtmName, ts: Date.now()
      }));
      return;
    }

    await joinRtcSession(appID, PARAMS.channel, rtcToken, rtcUid);

  } catch (err) {
    console.error('Failed to start/join:', err);
    alert('Failed to start/join. See console.');
    startBtn.disabled = false;
    startBtn.innerHTML = PARAMS.role === 'host'
      ? '<i class="las la-broadcast-tower"></i> Start Streaming'
      : '<i class="las la-play"></i> Join Stream';
  }
};

/**
 * Leave
 */
async function leaveCall() {
  try {
    if (rtmClient && PARAMS.role === 'audience' && joinGate.approved) {
      try { await rtmClient.publish(PARAMS.channel, JSON.stringify({ type:"presence", action:"leave", name: rtmName })); } catch(e){}
    }

    if (PARAMS.role === 'host') {
      try { await rtmClient.publish(PARAMS.channel, JSON.stringify({ type:"system", action:"end" })); } catch(e){}
      try {
        await fetch("{{ route('seller.end.stream') }}", {
          method: "POST",
          headers: { "X-CSRF-TOKEN": CSRF, "Content-Type": "application/json" },
          body: JSON.stringify({ channel: PARAMS.channel, uid: rtcUid })
        });
      } catch(e){}
    }

    try { await client.leave(); } catch(e){}
    if (localTracks && localTracks.length) {
      for (const t of localTracks) { try { t.stop(); t.close(); } catch(e){} }
    }
    try { if (rtmClient) await rtmClient.logout(); } catch(e){}

    window.location.href = PARAMS.redirectURL;
  } catch (err) {
    console.error("leaveCall error:", err);
    window.location.href = PARAMS.redirectURL;
  }
}

leaveBtn.onclick = async () => {
  const msg = PARAMS.role === 'host' ? 'End stream for all viewers?' : 'Leave this stream?';
  if (confirm(msg)) await leaveCall();
};

window.addEventListener("beforeunload", () => {
  try {
    if (rtmClient && PARAMS.role === 'audience' && joinGate.approved) {
      rtmClient.publish(PARAMS.channel, JSON.stringify({ type:"presence", action:"leave", name: rtmName }));
    }
  } catch(e){}
});

/**
 * Fullscreen
 */
document.addEventListener("DOMContentLoaded", () => {
  const fullBtn = document.getElementById('fullscreenBtn');
  const exitBtn = document.getElementById('exitFullscreenBtn');
  const videoContainer = document.getElementById('videoContainer');
  const video = document.getElementById('video-player');

  fullBtn.onclick = async () => {
    try {
      if (videoContainer.requestFullscreen) await videoContainer.requestFullscreen();
      else if (videoContainer.webkitRequestFullscreen) await videoContainer.webkitRequestFullscreen();
    } catch (err) { console.error('Fullscreen error:', err); }
  };

  exitBtn.onclick = async () => {
    try {
      if (document.exitFullscreen) await document.exitFullscreen();
      else if (document.webkitExitFullscreen) await document.webkitExitFullscreen();
    } catch (err) { console.error('Exit fullscreen error:', err); }
  };

  document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement) {
      exitBtn.style.display = 'none';
    }
  });

  window.addEventListener('resize', () => {
    if (videoContainer.classList.contains('mobile-fullscreen')) {
      videoContainer.style.height = `${window.innerHeight}px`;
    }
  });
});
</script>
</body>
</html>
