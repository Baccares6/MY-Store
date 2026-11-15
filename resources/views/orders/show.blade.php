@php
    use App\Services\OrderStatus;

    $status = $trx->status;
    $isBank = ($method['type'] ?? null) === 'bank';
    $isQris = ($method['type'] ?? null) === 'qris';
    // Transfer bank & QRIS sama-sama dibayar manual lalu dikonfirmasi admin
    $isManual = $isBank || $isQris;
    $steps = $isManual
        ? [OrderStatus::PENDING, OrderStatus::WAITING, OrderStatus::PROCESSING, OrderStatus::SHIPPED, OrderStatus::COMPLETED]
        : [OrderStatus::PROCESSING, OrderStatus::SHIPPED, OrderStatus::COMPLETED];
    $currentStep = array_search($status, $steps, true);
    $isClosed = in_array($status, [OrderStatus::CANCELLED, OrderStatus::EXPIRED], true);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan {{ $trx->trx_id }} - MY-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .card { border: none; border-radius: 14px; }
        .prod-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
        .steps { display: flex; gap: 6px; }
        .step { flex: 1; text-align: center; font-size: 12px; color: #94a3b8; font-weight: 600; }
        .step .bar { height: 6px; border-radius: 3px; background: #e2e8f0; margin-bottom: 8px; }
        .step.done { color: #870000; }
        .step.done .bar { background: #870000; }
        .step.current { color: #0f172a; }
        .pay-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; }
        .pay-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px dashed #fde68a; }
        .pay-row:last-child { border-bottom: none; }
        .pay-value { font-weight: 700; font-size: 15px; }
        .btn-copy { font-size: 12px; padding: 2px 10px; }
        .qris-card { max-width: 300px; margin: 0 auto; text-align: center; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px 18px; }
        .qris-head { font-weight: 800; font-size: 20px; letter-spacing: .08em; }
        .qris-merchant { font-weight: 600; color: #334155; margin-bottom: 10px; }
        .qris-code { width: 220px; height: 220px; image-rendering: pixelated; display: block; margin: 0 auto 10px; }
        .qris-amount { font-size: 15px; }
        .btn-brand { background-color: #870000; border-color: #870000; color: #fff; }
        .btn-brand:hover { background-color: #600000; border-color: #600000; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark shadow-sm mb-4" style="background: linear-gradient(135deg, #870000 0%, #190a05 100%);">
        <div class="container">
            <a class="navbar-brand fw-bold d-inline-flex align-items-center" href="{{ route('home') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffc107" viewBox="0 0 16 16" class="me-2">
                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                </svg>
                <span>MY-<span style="color: #ffc107;">STORE</span></span>
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-receipt me-1"></i>Pesanan Saya</a>
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-shop me-1"></i>Toko</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5" style="max-width: 1040px;">
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <h1 class="h4 fw-bold mb-1">Pesanan <code class="fs-5">{{ $trx->trx_id }}</code></h1>
                <div class="text-muted small">Dibuat {{ formatWib($trx->created_at) }}</div>
            </div>
            <span class="badge {{ OrderStatus::badge($status) }} fs-6 px-3 py-2">{{ OrderStatus::label($status) }}</span>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Tahapan pesanan --}}
                @if (!$isClosed && $trx->payment_method !== 'legacy')
                    <div class="card shadow-sm p-4 mb-4">
                        <div class="steps">
                            @foreach ($steps as $i => $step)
                                <div class="step {{ $currentStep !== false && $i < $currentStep ? 'done' : '' }} {{ $i === $currentStep ? 'done current' : '' }}">
                                    <div class="bar"></div>{{ OrderStatus::label($step) }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($isClosed)
                    <div class="alert alert-secondary border-0 shadow-sm">
                        <strong>Pesanan {{ strtolower(OrderStatus::label($status)) }}.</strong>
                        {{ $trx->admin_note }}
                    </div>
                @endif

                {{-- Instruksi pembayaran manual: transfer bank atau QRIS --}}
                @if ($isManual && in_array($status, [OrderStatus::PENDING, OrderStatus::WAITING], true))
                    <div class="card shadow-sm p-4 mb-4">
                        <h2 class="h6 fw-bold mb-3">Pembayaran</h2>

                        @if ($status === OrderStatus::PENDING && $trx->admin_note)
                            <div class="alert alert-danger py-2 small"><strong>Bukti pembayaran ditolak:</strong> {{ $trx->admin_note }} Silakan unggah ulang bukti yang benar.</div>
                        @endif

                        @if ($status === OrderStatus::WAITING)
                            <div class="alert alert-info py-2 small mb-3">
                                Bukti pembayaran sudah kami terima {{ formatWib($trx->proof_uploaded_at) }} dan sedang diperiksa admin,
                                biasanya kurang dari 1x24 jam. Anda tidak perlu membayar lagi.
                                <a href="{{ route('orders.proof', $trx->trx_id) }}" target="_blank" rel="noopener">Lihat bukti</a>
                            </div>
                            <div class="small text-muted mb-3">Total dibayar: <strong class="text-dark">{{ rupiah($trx->total) }}</strong> lewat {{ OrderStatus::methodLabel($trx->payment_method) }}</div>
                        @endif

                        {{-- Instruksi bayar hanya saat belum membayar; setelah bukti diunggah cukup tampilkan status --}}
                        @if ($status === OrderStatus::PENDING)
                        @if ($isDemo)
                            <div class="alert alert-warning py-2 small">
                                <i class="bi bi-info-circle me-1"></i><strong>Website demo.</strong>
                                {{ $isQris ? 'Kode QRIS di bawah ini hanya contoh dan tidak bisa dipakai membayar.' : 'Nomor rekening di bawah ini hanya contoh.' }}
                                Jangan melakukan pembayaran sungguhan.
                            </div>
                        @endif

                        @if ($isQris)
                            <div class="qris-card mb-3">
                                <div class="qris-head">QRIS</div>
                                <div class="qris-merchant">{{ $method['merchant'] }}</div>
                                <img src="{{ asset($method['image']) }}" alt="Kode QRIS {{ $method['merchant'] }}" class="qris-code">
                                <div class="qris-amount">Bayar <strong>{{ rupiah($trx->total) }}</strong></div>
                                @if ($status === OrderStatus::PENDING)
                                    <div class="small text-muted">sebelum {{ formatWib($trx->payment_deadline) }}</div>
                                @endif
                            </div>
                            <ol class="small text-muted ps-3 mb-3">
                                <li>Buka GoPay, OVO, DANA, ShopeePay, atau aplikasi m-banking, lalu pilih menu <strong>Scan QR / Bayar</strong>.</li>
                                <li>Pindai kode QRIS di atas, pastikan nama penerima <strong>{{ $method['merchant'] }}</strong>.</li>
                                <li>Masukkan nominal <strong>{{ rupiah($trx->total) }}</strong>
                                    <button type="button" class="btn btn-outline-secondary btn-copy ms-1" data-copy="{{ $trx->total }}">Salin</button>, lalu selesaikan pembayaran.</li>
                                <li>Simpan screenshot bukti pembayaran, lalu unggah di bawah ini.</li>
                            </ol>
                        @else
                        <div class="pay-box px-3 py-1 mb-3">
                            <div class="pay-row">
                                <span class="text-muted small">Bank</span>
                                <span class="pay-value">{{ $method['bank'] }}</span>
                            </div>
                            <div class="pay-row">
                                <span class="text-muted small">Nomor rekening</span>
                                <span class="d-flex align-items-center gap-2">
                                    <span class="pay-value" id="acc-number">{{ $method['account'] }}</span>
                                    <button type="button" class="btn btn-outline-secondary btn-copy" data-copy="{{ $method['account'] }}">Salin</button>
                                </span>
                            </div>
                            <div class="pay-row">
                                <span class="text-muted small">Atas nama</span>
                                <span class="pay-value">{{ $holder }}</span>
                            </div>
                            <div class="pay-row">
                                <span class="text-muted small">Jumlah transfer</span>
                                <span class="d-flex align-items-center gap-2">
                                    <span class="pay-value text-danger">{{ rupiah($trx->total) }}</span>
                                    <button type="button" class="btn btn-outline-secondary btn-copy" data-copy="{{ $trx->total }}">Salin</button>
                                </span>
                            </div>
                            @if ($status === OrderStatus::PENDING)
                                <div class="pay-row">
                                    <span class="text-muted small">Bayar sebelum</span>
                                    <span class="pay-value">{{ formatWib($trx->payment_deadline) }}</span>
                                </div>
                            @endif
                        </div>
                        <p class="small text-muted mb-3">Transfer sesuai jumlah di atas agar pembayaran mudah dicocokkan. Setelah transfer, unggah foto atau screenshot bukti transfernya.</p>
                        @endif
                        @endif

                        @if ($status === OrderStatus::WAITING)
                            {{-- Ganti bukti disembunyikan agar tidak terkesan harus membayar lagi --}}
                            <details class="small">
                                <summary class="text-muted" style="cursor:pointer;">Salah mengunggah bukti? Ganti bukti pembayaran</summary>
                                <div class="mt-2">
                        @endif
                        <form action="{{ route('orders.proof.upload', $trx->trx_id) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-sm-row gap-2">
                            @csrf
                            <input type="file" name="proof" accept="image/jpeg,image/png,image/webp" class="form-control" required>
                            <button type="submit" class="btn btn-brand fw-semibold text-nowrap">
                                {{ $status === OrderStatus::WAITING ? 'Ganti Bukti' : 'Unggah Bukti' }}
                            </button>
                        </form>
                        <div class="form-text">JPG, PNG, atau WEBP, maksimal {{ $maxKb / 1024 }} MB.</div>
                        @if ($status === OrderStatus::WAITING)
                                </div>
                            </details>
                        @endif
                    </div>
                @endif

                @if ($status === OrderStatus::PROCESSING)
                    <div class="alert alert-primary border-0 shadow-sm">
                        @if ($isManual)
                            Pembayaran sudah dikonfirmasi {{ formatWib($trx->paid_at) }}. Pesanan sedang dikemas.
                        @else
                            Pesanan sedang dikemas. Siapkan uang tunai {{ rupiah($trx->total) }} saat kurir tiba.
                        @endif
                    </div>
                @endif

                @if ($status === OrderStatus::SHIPPED)
                    <div class="card shadow-sm p-4 mb-4">
                        <h2 class="h6 fw-bold mb-2">Pesanan dalam pengiriman</h2>
                        <p class="mb-1 small text-muted">Dikirim {{ formatWib($trx->shipped_at) }} dengan {{ $shipment->courier ?? '' }} {{ $shipment->courier_service ?? '' }}</p>
                        <p class="mb-3">Nomor resi: <strong>{{ $trx->tracking_number }}</strong></p>
                        <form action="{{ route('orders.received', $trx->trx_id) }}" method="POST"
                              data-confirm-title="Pesanan sudah diterima?" data-confirm-ok="Ya, Sudah Diterima" data-confirm-variant="warning" data-confirm-icon="package"
                              data-confirm="Pastikan semua barang sudah sampai dalam kondisi baik. Setelah dikonfirmasi, pesanan dinyatakan selesai.">
                            @csrf
                            <button type="submit" class="btn btn-brand fw-semibold">Pesanan Sudah Diterima</button>
                        </form>
                    </div>
                @endif

                @if ($status === OrderStatus::COMPLETED && $trx->payment_method !== 'legacy')
                    <div class="alert alert-success border-0 shadow-sm">Pesanan selesai {{ formatWib($trx->completed_at) }}. Terima kasih telah berbelanja di MY-Store!</div>
                @endif

                {{-- Daftar barang --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-0">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-4">Barang</th><th>Harga</th><th>Jumlah</th><th class="text-end pe-4">Subtotal</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $it)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ getProductImageUrl($it->product_image, $it->product_title) }}" alt="" class="prod-thumb">
                                                <span class="fw-semibold">{{ $it->product_title ?? 'Produk #' . $it->product_id }}</span>
                                            </div>
                                        </td>
                                        <td>{{ rupiah($it->unit_price) }}</td>
                                        <td>{{ $it->qty }}</td>
                                        <td class="text-end pe-4 fw-semibold">{{ rupiah($it->unit_price * $it->qty) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pembeli hanya bisa membatalkan sebelum membayar; setelah bukti diunggah, pembatalan lewat CS --}}
                @if ($status === OrderStatus::PENDING)
                    <form action="{{ route('orders.cancel', $trx->trx_id) }}" method="POST"
                          data-confirm-title="Batalkan pesanan?" data-confirm-ok="Ya, Batalkan" data-confirm-cancel="Tidak" data-confirm-variant="danger"
                          data-confirm="Pesanan {{ $trx->trx_id }} akan dibatalkan dan tidak bisa diaktifkan kembali.">
                        @csrf
                        <button type="submit" class="btn btn-link text-secondary p-0 small">Batalkan pesanan</button>
                    </form>
                @elseif (in_array($status, [OrderStatus::WAITING, OrderStatus::PROCESSING], true))
                    <p class="small text-muted mb-0">Ingin membatalkan pesanan yang sudah dibayar? Hubungi CS kami (tanya lewat MY-Bot: "kontak CS") agar pengembalian dana bisa diurus.</p>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm p-4 mb-4">
                    <h2 class="h6 fw-bold mb-3">Ringkasan</h2>
                    <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Subtotal produk</span><span>{{ rupiah($trx->subtotal) }}</span></div>
                    <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Ongkos kirim</span><span>{{ rupiah($trx->shipping_cost) }}</span></div>
                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2"><span>Total</span><span style="color:#870000;">{{ rupiah($trx->total) }}</span></div>
                    <div class="small text-muted mt-3">Metode bayar: <strong class="text-dark">{{ OrderStatus::methodLabel($trx->payment_method) }}</strong></div>
                </div>

                @if ($shipment)
                    <div class="card shadow-sm p-4">
                        <h2 class="h6 fw-bold mb-3">Pengiriman</h2>
                        <div class="fw-semibold">{{ $shipment->recipient_name }} <span class="text-muted fw-normal">{{ $shipment->phone }}</span></div>
                        <div class="small text-secondary mt-1" style="line-height:1.55;">
                            {{ $shipment->address_detail }}<br>
                            {{ $shipment->district }}, {{ $shipment->city }}, {{ $shipment->province }} {{ $shipment->postal_code }}
                        </div>
                        @if ($shipment->courier)
                            <div class="small mt-3">{{ $shipment->courier }} {{ $shipment->courier_service }} · estimasi {{ $shipment->shipping_etd }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-copy]').forEach(btn => btn.addEventListener('click', () => {
            navigator.clipboard?.writeText(btn.dataset.copy).then(() => {
                const old = btn.textContent;
                btn.textContent = 'Tersalin';
                setTimeout(() => btn.textContent = old, 1500);
            });
        }));
    </script>
    @include('partials.chatbot')
    {{-- Jendela konfirmasi MY-Store (pengganti confirm() bawaan browser) --}}
    @include('partials.confirm-dialog')
</body>
</html>
