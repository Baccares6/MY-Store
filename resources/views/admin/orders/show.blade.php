@extends('admin.orders.layout')
@php($S = \App\Services\OrderStatus::class)

@section('title', 'Pesanan ' . $trx->trx_id)

@section('content')
    <a href="{{ route('admin.orders') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke daftar pesanan</a>

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mt-2 pb-3 mb-4 border-bottom">
        <div>
            <h1 class="h4 fw-bold mb-1">Pesanan <code>{{ $trx->trx_id }}</code></h1>
            <div class="text-muted small">Dibuat {{ formatWib($trx->created_at) }} oleh {{ $trx->user_name ?? 'Pengguna #' . $trx->user_id }} ({{ $trx->user_email }})</div>
        </div>
        <span class="badge {{ $S::badge($trx->status) }} fs-6 px-3 py-2">{{ $S::label($trx->status) }}</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            {{-- Aksi sesuai status --}}
            @if ($trx->status === $S::WAITING)
                <div class="card shadow-sm p-4 mb-4">
                    <h2 class="h6 fw-bold mb-3">Periksa Bukti Pembayaran</h2>
                    <p class="small text-muted mb-2">Cocokkan nominal dan tujuan pembayaran di bukti dengan data pesanan: <strong class="text-dark">{{ rupiah($trx->total) }}</strong> lewat {{ $S::methodLabel($trx->payment_method) }}.</p>
                    <a href="{{ route('orders.proof', $trx->trx_id) }}" target="_blank" rel="noopener">
                        <img src="{{ route('orders.proof', $trx->trx_id) }}" alt="Bukti pembayaran" class="img-fluid rounded border mb-3" style="max-height: 420px;">
                    </a>
                    <div class="small text-muted mb-3">Diunggah {{ formatWib($trx->proof_uploaded_at) }}</div>

                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('admin.orders.confirm', $trx->trx_id) }}" method="POST"
                              data-confirm-title="Konfirmasi pembayaran?" data-confirm-ok="Ya, Konfirmasi" data-confirm-variant="warning"
                              data-confirm="Pastikan dana {{ rupiah($trx->total) }} sudah benar-benar masuk sesuai bukti. Setelah dikonfirmasi, pesanan {{ $trx->trx_id }} masuk tahap Diproses.">
                            @csrf
                            <button class="btn btn-success fw-semibold"><i class="bi bi-check2-circle me-1"></i>Konfirmasi Pembayaran</button>
                        </form>
                        <button class="btn btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#rejectForm">Tolak Bukti</button>
                    </div>
                    <form action="{{ route('admin.orders.reject', $trx->trx_id) }}" method="POST" class="collapse mt-3" id="rejectForm">
                        @csrf
                        <label class="form-label small fw-semibold">Alasan penolakan (ditampilkan ke pembeli)</label>
                        <input type="text" name="reason" class="form-control mb-2" maxlength="200" placeholder="Contoh: Nominal transfer tidak sesuai" required>
                        <button class="btn btn-danger btn-sm">Tolak dan minta unggah ulang</button>
                    </form>
                </div>
            @elseif ($trx->status === $S::PENDING)
                <div class="alert alert-warning border-0 shadow-sm">
                    Menunggu pembeli membayar dan mengunggah bukti pembayaran. Batas waktu: <strong>{{ formatWib($trx->payment_deadline) }}</strong>.
                    @if ($trx->admin_note)<div class="small mt-1">Catatan terakhir: {{ $trx->admin_note }}</div>@endif
                    @if ($trx->payment_proof)
                        <div class="small mt-1"><a href="{{ route('orders.proof', $trx->trx_id) }}" target="_blank" rel="noopener">Lihat bukti yang ditolak</a></div>
                    @endif
                </div>
            @elseif ($trx->status === $S::PROCESSING)
                <div class="card shadow-sm p-4 mb-4">
                    <h2 class="h6 fw-bold mb-1">Kirim Pesanan</h2>
                    <p class="small text-muted mb-3">
                        @if ($trx->paid_at) Pembayaran dikonfirmasi {{ formatWib($trx->paid_at) }}. @else Pesanan COD, pembeli membayar saat barang tiba. @endif
                        Kemas pesanan, kirim lewat {{ $shipment->courier ?? 'kurir' }} {{ $shipment->courier_service ?? '' }}, lalu isi nomor resinya.
                    </p>
                    <form action="{{ route('admin.orders.ship', $trx->trx_id) }}" method="POST" class="d-flex flex-column flex-sm-row gap-2">
                        @csrf
                        <input type="text" name="tracking_number" class="form-control" maxlength="30" placeholder="Nomor resi, contoh: JNE1234567890" required>
                        <button class="btn btn-primary fw-semibold text-nowrap"><i class="bi bi-truck me-1"></i>Tandai Dikirim</button>
                    </form>
                </div>
            @elseif ($trx->status === $S::SHIPPED)
                <div class="card shadow-sm p-4 mb-4">
                    <h2 class="h6 fw-bold mb-1">Dalam Pengiriman</h2>
                    <p class="small mb-3">Resi <strong>{{ $trx->tracking_number }}</strong>, dikirim {{ formatWib($trx->shipped_at) }}. Pesanan otomatis selesai saat pembeli menekan "Pesanan Sudah Diterima".</p>
                    <form action="{{ route('admin.orders.complete', $trx->trx_id) }}" method="POST"
                          data-confirm-title="Tandai pesanan selesai?" data-confirm-ok="Tandai Selesai" data-confirm-variant="warning" data-confirm-icon="package"
                          data-confirm="Gunakan ini jika barang sudah diterima pembeli tetapi pembeli belum menekan &quot;Pesanan Sudah Diterima&quot;.">
                        @csrf
                        <button class="btn btn-outline-success btn-sm">Tandai Selesai</button>
                    </form>
                </div>
            @else
                <div class="alert alert-secondary border-0 shadow-sm">
                    Pesanan {{ strtolower($S::label($trx->status)) }}.
                    @if ($trx->admin_note) {{ $trx->admin_note }} @endif
                    @if ($trx->tracking_number) Resi: <strong>{{ $trx->tracking_number }}</strong>. @endif
                </div>
            @endif

            {{-- Barang --}}
            <div class="card shadow-sm mb-4">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-4">Barang</th><th>Harga</th><th>Jumlah</th><th class="text-end pe-4">Subtotal</th></tr></thead>
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

            {{-- Bukti pembayaran tetap bisa dilihat setelah dikonfirmasi, sebagai arsip --}}
            @if ($trx->payment_proof && in_array($trx->status, [$S::PROCESSING, $S::SHIPPED, $S::COMPLETED, $S::CANCELLED], true))
                <p class="small mb-3"><a href="{{ route('orders.proof', $trx->trx_id) }}" target="_blank" rel="noopener"><i class="bi bi-image me-1"></i>Lihat bukti pembayaran</a></p>
            @endif

            @if (in_array($trx->status, [$S::PENDING, $S::WAITING, $S::PROCESSING], true))
                <button class="btn btn-link text-danger p-0 small" type="button" data-bs-toggle="collapse" data-bs-target="#cancelForm">Batalkan pesanan</button>
                <form action="{{ route('admin.orders.cancel', $trx->trx_id) }}" method="POST" class="collapse mt-2" id="cancelForm">
                    @csrf
                    @if ($trx->paid_at || $trx->status === $S::WAITING)
                        <div class="alert alert-warning py-2 small mb-2">Pembeli sudah membayar. Setelah dibatalkan, kembalikan dana ke pembeli.</div>
                    @endif
                    <input type="text" name="reason" class="form-control mb-2" maxlength="200" placeholder="Alasan pembatalan (ditampilkan ke pembeli)" required>
                    <button class="btn btn-danger btn-sm">Batalkan pesanan</button>
                </form>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm p-4 mb-4">
                <h2 class="h6 fw-bold mb-3">Ringkasan Pembayaran</h2>
                <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Subtotal produk</span><span>{{ rupiah($trx->subtotal) }}</span></div>
                <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Ongkos kirim</span><span>{{ rupiah($trx->shipping_cost) }}</span></div>
                <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2"><span>Total</span><span>{{ rupiah($trx->total) }}</span></div>
                <div class="small text-muted mt-3">Metode: <strong class="text-dark">{{ $S::methodLabel($trx->payment_method) }}</strong></div>
            </div>

            @if ($shipment)
                <div class="card shadow-sm p-4">
                    <h2 class="h6 fw-bold mb-3">Alamat Pengiriman</h2>
                    <div class="fw-semibold">{{ $shipment->recipient_name }} <span class="text-muted fw-normal">{{ $shipment->phone }}</span></div>
                    <div class="small text-secondary mt-1" style="line-height:1.55;">
                        {{ $shipment->address_detail }}<br>
                        {{ $shipment->district }}, {{ $shipment->city }}, {{ $shipment->province }} {{ $shipment->postal_code }}
                    </div>
                    @if ($shipment->courier)
                        <div class="small mt-3">{{ $shipment->courier }} {{ $shipment->courier_service }} · {{ number_format($shipment->total_weight / 1000, 1, ',', '.') }} kg · {{ rupiah($shipment->shipping_cost) }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
