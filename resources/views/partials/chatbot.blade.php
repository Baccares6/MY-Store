{{-- =====================================================================
     MY-Bot — Widget Chatbot Asisten Belanja MY-Store
     Cara pakai: @include('partials.chatbot') tepat sebelum </body>
     Widget ini mandiri (CSS + JS sendiri), tidak bergantung pada Bootstrap.
===================================================================== --}}
<style>
    #mybot-root { --mb-primary: #870000; --mb-primary-dark: #600000; --mb-accent: #190A05; --mb-bg: #f8fafc; --mb-border: #e2e8f0; --mb-text: #0f172a; --mb-muted: #64748b;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; position: fixed; right: 24px; bottom: 24px; z-index: 9999; }
    #mybot-root * { box-sizing: border-box; }
    #mybot-toggle { width: 60px; height: 60px; border-radius: 50%; border: none; cursor: pointer; color: #fff;
        background: linear-gradient(135deg, var(--mb-primary) 0%, var(--mb-accent) 100%);
        box-shadow: 0 8px 24px rgba(135, 0, 0, .35); display: flex; align-items: center; justify-content: center; transition: transform .2s; position: relative; }
    #mybot-toggle:hover { transform: scale(1.06); }
    #mybot-toggle svg { width: 28px; height: 28px; }
    #mybot-toggle .mb-dot { position: absolute; top: 4px; right: 4px; width: 14px; height: 14px; background: #22c55e; border: 2px solid #fff; border-radius: 50%; }
    #mybot-panel { position: absolute; right: 0; bottom: 76px; width: 370px; height: 540px; max-height: calc(100vh - 120px); background: #fff;
        border-radius: 18px; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); display: none; flex-direction: column; overflow: hidden; border: 1px solid var(--mb-border); }
    #mybot-root.mb-open #mybot-panel { display: flex; animation: mbUp .22s ease-out; }
    @keyframes mbUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
    .mb-header { background: linear-gradient(135deg, var(--mb-primary) 0%, var(--mb-accent) 100%); color: #fff; padding: 14px 16px; display: flex; align-items: center; gap: 12px; }
    .mb-avatar { width: 40px; height: 40px; border-radius: 50%; background: #fff; color: var(--mb-primary); font-weight: 800; font-size: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .mb-title { font-weight: 800; font-size: 15px; line-height: 1.2; }
    .mb-sub { font-size: 12px; opacity: .85; display: flex; align-items: center; gap: 5px; }
    .mb-sub::before { content: ''; width: 7px; height: 7px; background: #4ade80; border-radius: 50%; }
    .mb-head-btn { margin-left: auto; background: rgba(255,255,255,.15); border: none; color: #fff; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .mb-head-btn + .mb-head-btn { margin-left: 6px; }
    .mb-head-btn:hover { background: rgba(255,255,255,.28); }
    .mb-head-btn svg { width: 16px; height: 16px; }
    #mybot-messages { flex: 1; overflow-y: auto; padding: 16px; background: var(--mb-bg); display: flex; flex-direction: column; gap: 10px; }
    .mb-msg { max-width: 85%; padding: 10px 13px; border-radius: 14px; font-size: 13.5px; line-height: 1.5; white-space: pre-line; word-wrap: break-word; color: var(--mb-text); }
    .mb-bot { align-self: flex-start; background: #fff; border: 1px solid var(--mb-border); border-bottom-left-radius: 4px; }
    .mb-user { align-self: flex-end; background: var(--mb-primary); color: #fff; border-bottom-right-radius: 4px; }
    .mb-time { display: block; font-size: 10.5px; margin-top: 4px; opacity: .6; }
    .mb-products { display: flex; flex-direction: column; gap: 8px; margin-top: 8px; white-space: normal; }
    .mb-product { display: flex; gap: 10px; align-items: center; padding: 8px; border: 1px solid var(--mb-border); border-radius: 10px; text-decoration: none; color: inherit; background: #fff; transition: border-color .15s; }
    .mb-product:hover { border-color: var(--mb-primary); }
    .mb-product img { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; background: var(--mb-bg); flex-shrink: 0; }
    .mb-p-title { font-weight: 700; font-size: 13px; line-height: 1.3; }
    .mb-p-meta { font-size: 11px; color: var(--mb-muted); }
    .mb-p-price { font-weight: 800; font-size: 13px; color: var(--mb-primary); }
    .mb-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; white-space: normal; }
    .mb-action { background: var(--mb-primary); color: #fff !important; text-decoration: none; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 8px; }
    .mb-action:hover { background: var(--mb-primary-dark); }
    #mybot-suggestions { display: flex; gap: 6px; padding: 8px 12px; overflow-x: auto; background: #fff; border-top: 1px solid var(--mb-border); scrollbar-width: none; }
    #mybot-suggestions::-webkit-scrollbar { display: none; }
    .mb-chip { flex-shrink: 0; border: 1px solid var(--mb-primary); color: var(--mb-primary); background: #fff; border-radius: 20px; padding: 5px 12px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; }
    .mb-chip:hover { background: var(--mb-primary); color: #fff; }
    #mybot-form { display: flex; gap: 8px; padding: 12px; background: #fff; border-top: 1px solid var(--mb-border); }
    #mybot-input { flex: 1; border: 1px solid var(--mb-border); border-radius: 24px; padding: 10px 16px; font-size: 13.5px; outline: none; font-family: inherit; color: var(--mb-text); }
    #mybot-input:focus { border-color: var(--mb-primary); box-shadow: 0 0 0 3px rgba(135,0,0,.1); }
    #mybot-send { width: 42px; height: 42px; border-radius: 50%; border: none; background: var(--mb-primary); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    #mybot-send:disabled { opacity: .5; cursor: not-allowed; }
    #mybot-send svg { width: 18px; height: 18px; }
    .mb-typing { display: inline-flex; gap: 4px; padding: 4px 2px; }
    .mb-typing span { width: 7px; height: 7px; background: var(--mb-muted); border-radius: 50%; animation: mbBlink 1.2s infinite; }
    .mb-typing span:nth-child(2) { animation-delay: .2s; } .mb-typing span:nth-child(3) { animation-delay: .4s; }
    @keyframes mbBlink { 0%, 80%, 100% { opacity: .25; } 40% { opacity: 1; } }
    @media (max-width: 480px) {
        #mybot-root { right: 16px; bottom: 16px; }
        #mybot-panel { position: fixed; right: 8px; left: 8px; bottom: 88px; width: auto; height: calc(100vh - 110px); }
    }
</style>

<div id="mybot-root" aria-live="polite">
    <div id="mybot-panel" role="dialog" aria-label="Chatbot MY-Bot">
        <div class="mb-header">
            <div class="mb-avatar">MY</div>
            <div>
                <div class="mb-title">MY-Bot</div>
                <div class="mb-sub">Asisten Belanja MY-Store</div>
            </div>
            <button type="button" class="mb-head-btn" id="mybot-reset" title="Mulai ulang percakapan" aria-label="Mulai ulang percakapan">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            </button>
            <button type="button" class="mb-head-btn" id="mybot-close" title="Tutup" aria-label="Tutup chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="mybot-messages"></div>
        <div id="mybot-suggestions"></div>
        <form id="mybot-form" autocomplete="off">
            <input id="mybot-input" type="text" maxlength="300" placeholder="Tulis pesan… (contoh: cari laptop)" aria-label="Pesan untuk MY-Bot">
            <button id="mybot-send" type="submit" aria-label="Kirim">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
            </button>
        </form>
    </div>
    <button type="button" id="mybot-toggle" aria-label="Buka chat MY-Bot">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h5"/></svg>
        <span class="mb-dot"></span>
    </button>
</div>

<script>
(function () {
    const ENDPOINT = @json(route('chatbot.message'));
    const CSRF = @json(csrf_token());
    const USER_NAME = @json(auth()->check() ? auth()->user()->name : null);
    const STORE_KEY = 'mybot_history_' + @json(auth()->id() ?? 'guest');
    const DEFAULT_CHIPS = ['Produk termurah', 'Lihat kategori', 'Cek pesanan saya', 'Cara pembayaran'];

    const root = document.getElementById('mybot-root');
    const box = document.getElementById('mybot-messages');
    const chips = document.getElementById('mybot-suggestions');
    const form = document.getElementById('mybot-form');
    const input = document.getElementById('mybot-input');
    const sendBtn = document.getElementById('mybot-send');

    let history = [];
    let busy = false;

    // ---------- Penyimpanan riwayat (per tab browser) ----------
    function load() { try { return JSON.parse(sessionStorage.getItem(STORE_KEY)) || []; } catch (e) { return []; } }
    function save() { try { sessionStorage.setItem(STORE_KEY, JSON.stringify(history.slice(-40))); } catch (e) {} }

    function timeNow() { return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }

    // ---------- Render (pakai textContent agar aman dari XSS) ----------
    function el(tag, cls, text) { const n = document.createElement(tag); if (cls) n.className = cls; if (text != null) n.textContent = text; return n; }

    function renderMessage(m) {
        const bubble = el('div', 'mb-msg ' + (m.from === 'user' ? 'mb-user' : 'mb-bot'));
        bubble.appendChild(document.createTextNode(m.text));

        if (m.products && m.products.length) {
            const wrap = el('div', 'mb-products');
            m.products.forEach(p => {
                const a = el('a', 'mb-product'); a.href = p.url;
                const img = document.createElement('img'); img.src = p.image; img.alt = p.title; img.loading = 'lazy';
                const info = el('div');
                info.appendChild(el('div', 'mb-p-title', p.title));
                info.appendChild(el('div', 'mb-p-meta', [p.brand, p.category].filter(Boolean).join(' • ')));
                info.appendChild(el('div', 'mb-p-price', p.price));
                a.appendChild(img); a.appendChild(info); wrap.appendChild(a);
            });
            bubble.appendChild(wrap);
        }
        if (m.actions && m.actions.length) {
            const wrap = el('div', 'mb-actions');
            m.actions.forEach(act => { const a = el('a', 'mb-action', act.label); a.href = act.url; wrap.appendChild(a); });
            bubble.appendChild(wrap);
        }
        bubble.appendChild(el('span', 'mb-time', m.time));
        box.appendChild(bubble);
        box.scrollTop = box.scrollHeight;
    }

    function renderChips(list) {
        chips.innerHTML = '';
        (list && list.length ? list : DEFAULT_CHIPS).forEach(label => {
            const b = el('button', 'mb-chip', label); b.type = 'button';
            b.addEventListener('click', () => send(label));
            chips.appendChild(b);
        });
    }

    function push(m) { history.push(m); save(); renderMessage(m); }

    function welcome() {
        push({ from: 'bot', time: timeNow(), text: 'Halo' + (USER_NAME ? ' ' + USER_NAME : '') + '! 👋 Saya MY-Bot, asisten belanja MY-Store.\nTanyakan produk, harga, isi keranjang, atau status pesanan Anda.' });
        renderChips(DEFAULT_CHIPS);
    }

    function showTyping() {
        const t = el('div', 'mb-msg mb-bot'); t.id = 'mybot-typing';
        t.innerHTML = '<span class="mb-typing"><span></span><span></span><span></span></span>';
        box.appendChild(t); box.scrollTop = box.scrollHeight;
    }
    function hideTyping() { const t = document.getElementById('mybot-typing'); if (t) t.remove(); }

    // ---------- Kirim pesan ke server (AJAX / fetch) ----------
    async function send(text) {
        text = (text || '').trim();
        if (!text || busy) return;
        busy = true; sendBtn.disabled = true; input.value = '';
        push({ from: 'user', time: timeNow(), text });
        showTyping();

        try {
            const res = await fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text })
            });

            let data;
            if (res.status === 429) data = { reply: 'Terlalu banyak pesan dalam waktu singkat. Tunggu sebentar lalu coba lagi ya 🙏' };
            else if (res.status === 419) data = { reply: 'Sesi halaman sudah kedaluwarsa. Silakan muat ulang (refresh) halaman.' };
            else if (!res.ok) data = { reply: 'Maaf, terjadi gangguan pada server. Silakan coba lagi.' };
            else data = await res.json();

            await new Promise(r => setTimeout(r, 350)); // jeda kecil agar terasa natural
            hideTyping();
            push({ from: 'bot', time: timeNow(), text: data.reply, products: data.products || [], actions: data.actions || [] });
            renderChips(data.suggestions);
        } catch (e) {
            hideTyping();
            push({ from: 'bot', time: timeNow(), text: 'Koneksi terputus. Periksa jaringan Anda lalu coba lagi.' });
        } finally {
            busy = false; sendBtn.disabled = false; input.focus();
        }
    }

    // ---------- Event ----------
    document.getElementById('mybot-toggle').addEventListener('click', () => {
        root.classList.toggle('mb-open');
        if (root.classList.contains('mb-open')) input.focus();
    });
    document.getElementById('mybot-close').addEventListener('click', () => root.classList.remove('mb-open'));
    document.getElementById('mybot-reset').addEventListener('click', () => {
        history = []; save(); box.innerHTML = ''; welcome();
    });
    form.addEventListener('submit', e => { e.preventDefault(); send(input.value); });

    // ---------- Inisialisasi ----------
    history = load();
    if (history.length) { history.forEach(renderMessage); renderChips(DEFAULT_CHIPS); }
    else welcome();
})();
</script>
