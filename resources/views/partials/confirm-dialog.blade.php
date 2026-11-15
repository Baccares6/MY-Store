{{--
    Jendela konfirmasi MY-Store, pengganti confirm() bawaan browser.

    Pemakaian pada form:
        <form ... data-confirm="Pesan penjelasan"
                  data-confirm-title="Judul singkat?"
                  data-confirm-ok="Teks tombol setuju"
                  data-confirm-cancel="Teks tombol batal"     (opsional, bawaan: Batal)
                  data-confirm-variant="danger|success|warning|primary"  (opsional, bawaan: primary)
                  data-confirm-icon="question|trash|check|hourglass|package"> (opsional, bawaan sesuai variant)
    Warna: danger = hapus/batalkan, warning = periksa dulu sebelum menyetujui,
           success = aksi yang sudah pasti benar, primary = umum.
    Ikon bawaan: primary = question, danger = trash, success = check, warning = hourglass.
--}}
<style>
    .ms-confirm {
        width: min(420px, calc(100vw - 32px));
        margin: auto;
        padding: 0;
        border: none;
        border-radius: 16px;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.25);
        font-family: inherit;
        color: #0f172a;
    }
    .ms-confirm::backdrop { background: rgba(15, 23, 42, 0.5); }
    .ms-confirm[open] { animation: ms-confirm-in .16s ease-out; }
    @keyframes ms-confirm-in { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: none; } }
    .ms-confirm-body { padding: 24px 24px 8px; text-align: center; }
    .ms-confirm-icon {
        width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .ms-confirm-icon svg { width: 26px; height: 26px; display: none; }
    /* Atribut hidden tidak berlaku pada SVG, jadi ikon dipilih lewat CSS sesuai data-icon pada dialog */
    .ms-confirm[data-icon="question"]  [data-glyph="question"],
    .ms-confirm[data-icon="trash"]     [data-glyph="trash"],
    .ms-confirm[data-icon="check"]     [data-glyph="check"],
    .ms-confirm[data-icon="hourglass"] [data-glyph="hourglass"],
    .ms-confirm[data-icon="package"]   [data-glyph="package"] { display: block; }
    .ms-confirm[data-variant="primary"] .ms-confirm-icon { background: #fde8e8; color: #870000; }
    .ms-confirm[data-variant="danger"]  .ms-confirm-icon { background: #fee2e2; color: #dc2626; }
    .ms-confirm[data-variant="success"] .ms-confirm-icon { background: #dcfce7; color: #16a34a; }
    .ms-confirm[data-variant="warning"] .ms-confirm-icon { background: #fef3c7; color: #d97706; }
    .ms-confirm-title { font-size: 18px; font-weight: 800; margin: 0 0 6px; }
    .ms-confirm-message { font-size: 14px; line-height: 1.6; color: #475569; margin: 0; }
    .ms-confirm-actions { display: flex; gap: 10px; padding: 20px 24px 24px; }
    .ms-confirm-actions button {
        flex: 1; padding: 11px 14px; border-radius: 10px; font: inherit; font-size: 14px; font-weight: 700;
        cursor: pointer; transition: background-color .15s, border-color .15s;
    }
    .ms-confirm-cancel { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
    .ms-confirm-cancel:hover { background: #f1f5f9; }
    .ms-confirm-ok { color: #fff; border: 1px solid transparent; }
    .ms-confirm[data-variant="primary"] .ms-confirm-ok { background: #870000; }
    .ms-confirm[data-variant="primary"] .ms-confirm-ok:hover { background: #600000; }
    .ms-confirm[data-variant="danger"]  .ms-confirm-ok { background: #dc2626; }
    .ms-confirm[data-variant="danger"]  .ms-confirm-ok:hover { background: #b91c1c; }
    .ms-confirm[data-variant="success"] .ms-confirm-ok { background: #16a34a; }
    .ms-confirm[data-variant="success"] .ms-confirm-ok:hover { background: #15803d; }
    /* Kuning tua agar teks putih tetap mudah dibaca */
    .ms-confirm[data-variant="warning"] .ms-confirm-ok { background: #b45309; }
    .ms-confirm[data-variant="warning"] .ms-confirm-ok:hover { background: #92400e; }
    .ms-confirm-ok:disabled { opacity: .7; cursor: wait; }
</style>

<dialog class="ms-confirm" id="ms-confirm" data-variant="primary" data-icon="question" aria-labelledby="ms-confirm-title" aria-describedby="ms-confirm-message">
    <div class="ms-confirm-body">
        <div class="ms-confirm-icon" aria-hidden="true">
            {{-- Hanya ikon yang dipilih yang tampil (diatur lewat CSS) --}}
            <svg data-glyph="question" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
            <svg data-glyph="trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            <svg data-glyph="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            {{-- Jam pasir: tahap yang masih menunggu keputusan/konfirmasi --}}
            <svg data-glyph="hourglass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.17a2 2 0 0 0-.59-1.42L12 12l-4.41 4.41A2 2 0 0 0 7 17.83V22"/><path d="M7 2v4.17a2 2 0 0 0 .59 1.42L12 12l4.41-4.41A2 2 0 0 0 17 6.17V2"/></svg>
            {{-- Kotak paket: konfirmasi penerimaan barang --}}
            <svg data-glyph="package" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/><path d="m7.5 4.27 9 5.15"/></svg>
        </div>
        <h2 class="ms-confirm-title" id="ms-confirm-title"></h2>
        <p class="ms-confirm-message" id="ms-confirm-message"></p>
    </div>
    <div class="ms-confirm-actions">
        <button type="button" class="ms-confirm-cancel" data-action="cancel">Batal</button>
        <button type="button" class="ms-confirm-ok" data-action="ok">Ya</button>
    </div>
</dialog>

<script>
    (function () {
        const dialog = document.getElementById('ms-confirm');
        if (!dialog || dialog.dataset.ready) return;
        dialog.dataset.ready = '1';

        const title = dialog.querySelector('.ms-confirm-title');
        const message = dialog.querySelector('.ms-confirm-message');
        const okBtn = dialog.querySelector('[data-action="ok"]');
        const cancelBtn = dialog.querySelector('[data-action="cancel"]');
        let pendingForm = null;

        // Cegat form yang memiliki data-confirm, tampilkan jendela konfirmasi terlebih dahulu
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form.dataset || !form.dataset.confirm || form.dataset.confirmed === '1') return;
            e.preventDefault();

            const variant = ['danger', 'success', 'warning', 'primary'].includes(form.dataset.confirmVariant) ? form.dataset.confirmVariant : 'primary';
            dialog.dataset.variant = variant;

            const defaultIcons = { primary: 'question', danger: 'trash', success: 'check', warning: 'hourglass' };
            const icons = ['question', 'trash', 'check', 'hourglass', 'package'];
            dialog.dataset.icon = icons.includes(form.dataset.confirmIcon) ? form.dataset.confirmIcon : defaultIcons[variant];

            title.textContent = form.dataset.confirmTitle || 'Lanjutkan?';
            message.textContent = form.dataset.confirm;
            okBtn.textContent = form.dataset.confirmOk || 'Ya, lanjutkan';
            cancelBtn.textContent = form.dataset.confirmCancel || 'Batal';
            okBtn.disabled = false;

            pendingForm = form;
            dialog.showModal();
            // Untuk aksi berisiko, fokus awal di tombol batal agar tidak sengaja tertekan Enter
            (variant === 'danger' ? cancelBtn : okBtn).focus();
        });

        okBtn.addEventListener('click', function () {
            if (!pendingForm) return;
            okBtn.disabled = true;           // cegah klik ganda
            pendingForm.dataset.confirmed = '1';
            pendingForm.querySelectorAll('button[type="submit"], button:not([type])').forEach(btn => {
                if (!btn.disabled) { btn.disabled = true; btn.dataset.autoDisabled = '1'; }
            });
            pendingForm.submit();
        });

        cancelBtn.addEventListener('click', () => dialog.close());

        // Klik area gelap di luar kotak = batal
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });

        dialog.addEventListener('close', () => { pendingForm = null; });

        // Cegah klik ganda: setelah form benar-benar dikirim, tombol submit-nya dinonaktifkan.
        // (Didaftarkan setelah pencegat konfirmasi di atas, jadi form yang masih menunggu konfirmasi tidak ikut.)
        document.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            const form = e.target;
            setTimeout(() => {
                form.querySelectorAll('button[type="submit"], button:not([type])').forEach(btn => {
                    if (btn.disabled) return;
                    btn.disabled = true;
                    btn.dataset.autoDisabled = '1';   // penanda: dinonaktifkan oleh skrip ini, bukan dari server
                });
            }, 0);
        });

        // Halaman yang dipulihkan lewat tombol Back (bfcache) dikembalikan ke keadaan awal
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            if (dialog.open) dialog.close();
            okBtn.disabled = false;
            document.querySelectorAll('form[data-confirmed]').forEach(f => delete f.dataset.confirmed);
            document.querySelectorAll('button[data-auto-disabled]').forEach(btn => {
                btn.disabled = false;
                delete btn.dataset.autoDisabled;
            });
        });
    })();
</script>
