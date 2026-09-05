{{-- resources/views/partials/ai-chat-widget.blade.php --}}

<style>
  /* Wrapper */
  #aiChatWidget{
    position:fixed;
    left:20px;
    bottom:20px;
    width:340px;
    max-width:92vw;
    z-index:9999;
    font-family: Arial, sans-serif;
  }

  /* Toggle button */
  #aiToggleBtn{
    width:100%;
    padding:12px 14px;
    border:0;
    border-radius:12px;
    background:#111;
    color:#fff;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    font-size:14px;
  }

  /* Panel */
  #aiPanel{
    display:none;
    margin-top:10px;
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:12px;
    /*overflow:hidden;*/
    box-shadow:0 10px 30px rgba(0,0,0,.12);
  }

  /* Header layout fixed (no overlap) */
  .aiHeader{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:10px 12px;
    border-bottom:1px solid #eee;
  }
  .aiTitle{
    font-weight:700;
    font-size:14px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width:55%;
  }
  .aiHeaderRight{
    display:flex;
    align-items:center;
    gap:8px;
    flex-shrink:0;
  }
  #aiLang{
    border:1px solid #ddd;
    border-radius:8px;
    padding:5px 8px;
    font-size:12px;
    background:#fff;
    height:30px;
  }
  #aiCloseBtn{
    border:0;
    background:transparent;
    font-size:18px;
    cursor:pointer;
    line-height:1;
    padding:0 4px;
  }

  /* Messages */
 #aiMessages{
  height:320px;
  overflow:auto;  /* keep this */
  padding:12px;
  background:#fafafa;
  border-bottom-left-radius:12px;
  border-bottom-right-radius:12px;
}

  /* Footer input row fixed (no overlap) */
  .aiFooter{
    display:flex;
    gap:8px;
    padding:10px;
    border-top:1px solid #eee;
    background:#fff;
  }
  #aiInput{
    flex:1;
    min-width:0;
    padding:10px;
    border:1px solid #ddd;
    border-radius:10px;
    outline:none;
    font-size:14px;
  }
  #aiSendBtn{
    padding:10px 14px;
    border:0;
    border-radius:10px;
    background:#111;
    color:#fff;
    cursor:pointer;
    font-size:14px;
    white-space:nowrap;
  }

  /* Bubbles */
  .aiRow{
    margin-bottom:10px;
    display:flex;
  }
  .aiRow.user{ justify-content:flex-end; }
  .aiRow.bot{ justify-content:flex-start; }

  .aiBubble{
    max-width:80%;
    padding:10px 12px;
    border-radius:12px;
    white-space:pre-wrap;
    border:1px solid #e5e7eb;
    background:#fff;
    color:#111;
    font-size:14px;
  }
  .aiRow.user .aiBubble{
    background:#111;
    color:#fff;
    border-color:#111;
  }
#aiLang{
  position:relative;
  z-index:100000;
}
.aiLangBtn{
  border:1px solid #ddd;
  background:#fff;
  border-radius:8px;
  padding:5px 8px;
  font-size:12px;
  height:30px;
  cursor:pointer;
}
.aiLangBtn.active{
  background:#111;
  color:#fff;
  border-color:#111;
}
#aiChatWidget{ z-index:100000; }
  /* Mobile */
  @media (max-width: 576px){
    #aiChatWidget{ width:260px; left:12px; bottom:12px; }
    #aiToggleBtn{ padding:8px 10px; font-size:13px; border-radius:10px; }
    #aiMessages{ height:240px; }
    #aiSendBtn{ padding:8px 10px; font-size:13px; }
    #aiInput{ padding:8px 10px; font-size:13px; }
    .aiTitle{ max-width:50%; }
  }
</style>

<div id="aiChatWidget">
  <button id="aiToggleBtn" type="button">
   <span id="aiToggleText">Chat with us</span>
  </button>

  <div id="aiPanel">
    <div class="aiHeader">
      <div class="aiTitle">HudumaPortal Bot</div>

      <div class="aiHeaderRight">
       <div id="aiLangBtns" style="display:flex;gap:6px;">
  <button type="button" class="aiLangBtn" data-lang="en">EN</button>
  <button type="button" class="aiLangBtn" data-lang="sw">SW</button>
</div>
        <button id="aiCloseBtn" type="button">X</button>
      </div>
    </div>

    <div id="aiMessages"></div>

    <div class="aiFooter">
      <input id="aiInput" type="text" placeholder="Type your message...">
      <button id="aiSendBtn" type="button"><span id="aiSendText">Send</span></button>
    </div>
  </div>
</div>

<script>
(function () {
  const toggleBtn  = document.getElementById('aiToggleBtn');
  const panel      = document.getElementById('aiPanel');
  const closeBtn   = document.getElementById('aiCloseBtn');
  const messagesEl = document.getElementById('aiMessages');
  const inputEl    = document.getElementById('aiInput');
  const sendBtn    = document.getElementById('aiSendBtn');

  const toggleText = document.getElementById('aiToggleText');
  const sendText   = document.getElementById('aiSendText');

  const langBtns = document.querySelectorAll('.aiLangBtn');

  // Storage keys
  const STORE_KEY = 'huduma_bot_chat_v1';
  const TTL_MS = 24 * 60 * 60 * 1000;

  // Chat state
  let lang = localStorage.getItem('ai_lang') || 'en';
  let history = [];
  let greeted = false;

  const i18n = {
    en: {
      toggle: 'Chat with us',
      send: 'Send',
      placeholder: 'Type your message...',
      greeting: 'Hi! I am HudumaPortal Bot. How can I help you with booking a service today?',
      typing: 'Typing...',
      neterr: 'Network error. Please try again.',
      switched: '✅ Language switched to English.'
    },
    sw: {
      toggle: 'Ongea nasi',
      send: 'Tuma',
      placeholder: 'Andika ujumbe wako...',
      greeting: 'Habari! Mimi ni HudumaPortal Bot. Naweza kukusaidiaje kuweka nafasi (booking) ya huduma leo?',
      typing: 'Naandika...',
      neterr: 'Hitilafu ya mtandao. Tafadhali jaribu tena.',
      switched: '✅ Lugha imebadilishwa kuwa Kiswahili.'
    }
  };

  function currentLang() {
    return (lang === 'sw') ? 'sw' : 'en';
  }

  function esc(str) {
    return String(str).replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function addMsg(role, text) {
    const row = document.createElement('div');
    row.className = 'aiRow ' + (role === 'user' ? 'user' : 'bot');

    const bubble = document.createElement('div');
    bubble.className = 'aiBubble';
    bubble.innerHTML = esc(text);

    row.appendChild(bubble);
    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function redrawFromHistory() {
    messagesEl.innerHTML = '';
    for (const m of history) {
      if (!m || !m.role || !m.content) continue;
      addMsg(m.role === 'assistant' ? 'assistant' : 'user', m.content);
    }
  }

  function saveChat() {
    try {
      const payload = {
        ts: Date.now(),
        lang: currentLang(),
        greeted: greeted,
        history: history.slice(-50) // keep last 50 messages
      };
      localStorage.setItem(STORE_KEY, JSON.stringify(payload));
      localStorage.setItem('ai_lang', currentLang());
    } catch (e) {}
  }

  function loadChat() {
    try {
      const raw = localStorage.getItem(STORE_KEY);
      if (!raw) return;

      const payload = JSON.parse(raw);
      if (!payload || !payload.ts) return;

      // Expire after 24 hours
      if (Date.now() - payload.ts > TTL_MS) {
        localStorage.removeItem(STORE_KEY);
        return;
      }

      if (payload.lang === 'sw' || payload.lang === 'en') lang = payload.lang;
      greeted = !!payload.greeted;
      history = Array.isArray(payload.history) ? payload.history : [];

      redrawFromHistory();
    } catch (e) {}
  }

  function applyLangUI() {
    const L = currentLang();
    toggleText.textContent = i18n[L].toggle;
    sendText.textContent   = i18n[L].send;
    inputEl.placeholder    = i18n[L].placeholder;
    langBtns.forEach(b => b.classList.toggle('active', b.dataset.lang === L));
  }

  function greetOnceIfNeeded() {
    if (greeted) return;
    const L = currentLang();
    addMsg('assistant', i18n[L].greeting);
    history.push({ role: 'assistant', content: i18n[L].greeting });
    greeted = true;
    saveChat();
  }

  function setLang(newLang, silent = false) {
    lang = (newLang === 'sw') ? 'sw' : 'en';
    applyLangUI();

    // Reset context to avoid mixing languages
    history = [];
    greeted = false;

    if (!silent) addMsg('assistant', i18n[lang].switched);
    greetOnceIfNeeded();
    saveChat();
  }

  // Bind language buttons
  langBtns.forEach(btn => btn.addEventListener('click', () => setLang(btn.dataset.lang)));

  // Restore chat (24h)
  loadChat();
  applyLangUI();

  // Open/close panel
  toggleBtn.addEventListener('click', () => {
    const isOpen = panel.style.display === 'block';
    panel.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) {
      // If empty, greet; otherwise keep existing chat
      if (history.length === 0) greetOnceIfNeeded();
      setTimeout(() => inputEl.focus(), 50);
    }
  });

  closeBtn.addEventListener('click', () => panel.style.display = 'none');

  async function send() {
    const msg = inputEl.value.trim();
    if (!msg) return;

    const L = currentLang();

    inputEl.value = '';
    addMsg('user', msg);
    history.push({ role: 'user', content: msg });
    saveChat();

    addMsg('assistant', i18n[L].typing);
    const typingRow = messagesEl.lastChild;
    const typingBubble = typingRow.querySelector('.aiBubble');

    try {
      const res = await fetch('/ai/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ message: msg, history: history.slice(-16), language: L })
      });

      const txt = await res.text();
      let data;
      try { data = JSON.parse(txt); } catch(e) { data = { reply: txt }; }

      if (!res.ok) {
        typingBubble.innerHTML = esc('Error: ' + (data.message || data.reply || txt));
        return;
      }

      const reply = data.reply || '';
      typingBubble.innerHTML = esc(reply);

      // Replace the "Typing..." message in history with actual reply:
      // Remove the last assistant typing from DOM history handling is simpler:
      // We'll just push assistant reply and keep UI as-is.
      history.push({ role: 'assistant', content: reply });

      // Trim stored history
      if (history.length > 50) history = history.slice(-50);
      saveChat();

    } catch (e) {
      typingBubble.innerHTML = esc(i18n[L].neterr);
    }
  }

  sendBtn.addEventListener('click', send);
  inputEl.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') send();
  });
})();
</script>