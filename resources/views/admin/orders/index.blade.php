@extends('admin.orders.layout')
@php($S = \App\Services\OrderStatus::class)

@section('title', 'Kelola Pesanan')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
        <div>
            <h1 class="h4 fw-bold mb-1">Kelola Pesanan</h1>
            <p class="text-muted small mb-0">Periksa bukti transfer, kirim pesanan, dan pantau status pembayaran.</p>
        </div>
    </div>

    @if (($counts[$S::WAITING] ?? 0) > 0)
        <div class="alert alert-info border-0 shadow-sm d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bell-fill me-2"></i><strong>{{ $counts[$S::WAITING] }} pesanan</strong> menunggu konfirmasi pembayaran.</span>
            <a href="{{ route('admin.orders', ['status' => $S::WAITING]) }}" class="btn btn-sm btn-dark">Periksa sekarang</a>
        </div>
    @endif

    {{-- Filter status --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.orders') }}" class="btn btn-sm {{ $status ? 'btn-outline-secondary' : 'btn-dark' }}">
            Semua <span class="badge bg-light text-dark ms-1">{{ $counts->sum() }}</span>
        </a>
        @foreach ($S::LABELS as $code => $label)
            <a href="{{ route('admin.orders', ['status' => $code]) }}" class="btn btn-sm {{ $status === $code ? 'btn-dark' : 'btn-outline-secondary' }}">
                {{ $label }} <span class="badge bg-light text-dark ms-1">{{ $counts[$code] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Transaksi</th>
                        <th>Pembeli</th>
                        <th>Metode</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th class="pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $t)
                        <tr>
                            <td class="ps-4">
                                <code>{{ $t->trx_id }}</code>
                                <div class="small text-muted">{{ formatWib($t->created_at) }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $t->user_name ?? 'Pengguna #' . $t->user_id }}</div>
                                @if ($t->recipient_name)
                                    <div class="small text-muted">Kirim ke {{ $t->recipient_name }}, {{ $t->city }}</div>
                                @endif
                            </td>
                            <td class="small">{{ $S::methodLabel($t->payment_method) }}</td>
                            <td class="text-end fw-semibold">{{ rupiah($t->total) }}</td>
                            <td><span class="badge {{ $S::badge($t->status) }}">{{ $S::label($t->status) }}</span></td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.orders.show', $t->trx_id) }}" class="btn btn-sm {{ $t->status === $S::WAITING ? 'btn-dark' : 'btn-outline-secondary' }}">
                                    {{ $t->status === $S::WAITING ? 'Periksa' : 'Detail' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada pesanan{{ $status ? ' dengan status ' . $S::label($status) : '' }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
