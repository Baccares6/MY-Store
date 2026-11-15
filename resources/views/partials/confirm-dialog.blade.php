{{--
    Jendela konfirmasi MY-Store, pengganti confirm() bawaan browser.

    Pemakaian pada form:
        <form ... data-confirm="Pesan penjelasan"
                  data-confirm-title="Judul singkat?"
                  data-confirm-ok="Teks tombol setuju"
                  data-confirm-cancel="Teks tombol batal"     (opsional, bawaan: Batal)
                  data-confirm-variant="danger|success|primary"> (opsional, bawaan: primary)
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
    /* Atribut hidden tidak berlaku pada SVG, jadi ikon dipilih lewat CSS sesuai variant */
    .ms-confirm[data-variant="primary"] [data-icon="primary"],
    .ms-confirm[data-variant="danger"]  [data-icon="danger"],
    .ms-confirm[data-variant="success"] [data-icon="success"] { display: block; }
    .ms-confirm[data-variant="primary"] .ms-confirm-icon { background: #fde8e8; color: #870000; }
    .ms-confirm[data-variant="danger"]  .ms-confirm-icon { background: #fee2e2; color: #dc2626; }
    .ms-confirm[data-variant="success"] .ms-confirm-icon { background: #dcfce7; color: #16a34a; }
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
    .ms-confirm-ok:disabled { opacity: .7; cursor: wait; }
</style>

<dialog class="ms-confirm" id="ms-confirm" data-variant="primary" aria-labelledby="ms-confirm-title" aria-describedby="ms-confirm-message">
    <div class="ms-confirm-body">
        <div class="ms-confirm-icon" aria-hidden="true">
            {{-- Hanya ikon yang sesuai variant yang tampil (diatur lewat CSS) --}}
            <svg data-icon="primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
            <svg data-icon="danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            <svg data-icon="success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
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

            const variant = ['danger', 'success', 'primary'].includes(form.dataset.confirmVariant) ? form.dataset.confirmVariant : 'primary';
            dialog.dataset.variant = variant;

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
            pendingForm.submit();
        });

        cancelBtn.addEventListener('click', () => dialog.close());

        // Klik area gelap di luar kotak = batal
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) dialog.close();
        });

        dialog.addEventListener('close', () => { pendingForm = null; });
    })();
</script>
