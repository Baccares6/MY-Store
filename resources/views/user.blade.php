<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman User - Profil & Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .profile-card { border: none; border-radius: 16px; overflow: hidden; }
        .profile-header { background: linear-gradient(135deg, #870000 0%, #190a05 100%); height: 100px; }
        .profile-avatar { width: 80px; height: 80px; margin-top: -40px; border: 4px solid #fff; border-radius: 50%; background-color: #ffffff; display: flex; align-items: center; justify-content: center; }
        .stat-card { border: none; border-radius: 14px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .table th { font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .prod-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <!-- Navbar User -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background: linear-gradient(135deg, #870000 0%, #190a05 100%);">
        <div class="container">
            <a class="navbar-brand fw-bold d-inline-flex align-items-center" href="{{ route('home') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffc107" viewBox="0 0 16 16" class="me-2">
                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
                </svg>
                <span>MY-<span style="color: #ffc107;">STORE</span> PORTAL</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-house me-1"></i>Beranda Toko</a>
                <a href="{{ route('cart.index') }}" class="btn btn-outline-light btn-sm"><i class="bi bi-cart3 me-1"></i>Keranjang</a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-sm fw-semibold"><i class="bi bi-shield-lock me-1"></i>Admin Panel</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm text-white border-light-subtle"><i class="bi bi-box-arrow-right me-1"></i>Keluar</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <!-- Profil User -->
            <div class="col-lg-4">
                <div class="card profile-card shadow-sm mb-4 bg-white">
                    <div class="profile-header"></div>
                    <div class="card-body text-center pt-0">
                        <div class="profile-avatar mx-auto shadow-sm">
                            <i class="bi bi-person-circle display-5" style="color: #870000;"></i>
                        </div>
                        <h5 class="fw-bold mt-3 mb-0">{{ $user->name ?? 'Pelanggan Toko' }}</h5>
                        <small class="text-muted">{{ $user->email ?? 'pelanggan@ecommerce.com' }}</small>
                        <hr class="my-3">
                        <div class="text-start small">
                            <p class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Status Akun: <b>Aktif Terverifikasi</b></p>
                            <p class="mb-0"><i class="bi bi-calendar-check text-secondary me-2"></i>Bergabung Sejak: {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '2026-09-01' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Statistik Belanja Pengguna -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="card stat-card bg-white p-3 shadow-sm border">
                            <small class="text-muted fw-semibold">Total Pesanan</small>
                            <h4 class="fw-bold mb-0 mt-1" style="color: #870000;">{{ $total_orders ?? $orders->count() }}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card stat-card bg-white p-3 shadow-sm border">
                            <small class="text-muted fw-semibold">Total Unit</small>
                            <h4 class="fw-bold mb-0 mt-1 text-primary">{{ $total_items ?? $orders->sum('qty') }}</h4>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card stat-card bg-white p-3 shadow-sm border">
                            <small class="text-muted fw-semibold">Total Akumulasi Belanja</small>
                            <h4 class="fw-bold mb-0 mt-1 text-success">Rp {{ number_format($total_spent ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>

                <div class="list-group shadow-sm border-0">
                    <a href="#" class="list-group-item list-group-item-action active" style="background-color: #870000; border-color: #870000;"><i class="bi bi-clock-history me-2"></i>Riwayat Pesanan Belanja</a>
                    <a href="{{ route('cart.index') }}" class="list-group-item list-group-item-action"><i class="bi bi-cart me-2"></i>Lihat Keranjang Belanja</a>
                    <a href="{{ route('home') }}" class="list-group-item list-group-item-action"><i class="bi bi-shop me-2"></i>Kembali Belanja di Toko</a>
                </div>
            </div>

            <!-- Konten Utama: Alamat & Riwayat Pesanan User -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4 bg-white" id="alamat">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title fw-bold mb-0 text-dark"><i class="bi bi-geo-alt me-2" style="color: #870000;"></i>Alamat Pengiriman</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger fw-semibold" data-address-mode="create">+ Tambah Alamat</button>
                    </div>
                    <div class="card-body p-0">
                        @forelse ($addresses as $addr)
                            <div class="address-row d-flex flex-column flex-md-row justify-content-between gap-2 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div class="fw-semibold d-flex flex-wrap align-items-center gap-2">
                                        {{ $addr->recipient_name }}
                                        <span class="text-muted fw-normal">{{ $addr->phone }}</span>
                                        @if ($addr->label)<span class="badge bg-light text-secondary border fw-semibold">{{ $addr->label }}</span>@endif
                                        @if ($addr->is_default)<span class="badge fw-semibold" style="background:#fde8e8; color:#870000;">Utama</span>@endif
                                    </div>
                                    <div class="small text-secondary mt-1" style="line-height:1.55;">
                                        {{ $addr->address_detail }}<br>
                                        {{ $addr->district }}, {{ $addr->city }}, {{ $addr->province }} {{ $addr->postal_code }}
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3 small flex-shrink-0">
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" style="color:#870000;"
                                            data-address-mode="edit" data-address='@json($addr)'>Ubah</button>
                                    @unless ($addr->is_default)
                                        <form action="{{ route('addresses.default', $addr->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none text-secondary fw-semibold">Jadikan Utama</button>
                                        </form>
                                    @endunless
                                    <form action="{{ route('addresses.delete', $addr->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus alamat ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none text-secondary fw-semibold">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-4 text-muted small">
                                Belum ada alamat tersimpan. Tambahkan alamat agar bisa dipilih saat checkout.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Modal Tambah / Ubah Alamat -->
                <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <form class="modal-content" id="addressForm" method="POST"
                              action="{{ old('editing_address_id') ? route('addresses.update', old('editing_address_id')) : route('addresses.store') }}">
                            @csrf
                            <input type="hidden" name="editing_address_id" id="af-editing" value="{{ old('editing_address_id') }}">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="addressModalTitle">{{ old('editing_address_id') ? 'Ubah Alamat' : 'Tambah Alamat Baru' }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                @include('partials.address-fields', ['isFirstAddress' => $addresses->isEmpty()])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn text-white fw-semibold" style="background-color:#870000;">Simpan Alamat</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4 bg-white">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2" style="color: #870000;"></i>Riwayat Transaksi Pesanan Anda</h5>
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-arrow-repeat me-1"></i>Tersinkronisasi Real-Time</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Barang yang Dipesan</th>
                                        <th>Harga Unit</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                        <th>No. Transaksi (Trx ID)</th>
                                        <th>Status Faktur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $ord)
                                    <tr>
                                        <td class="fw-bold">#{{ $ord->order_id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ getProductImageUrl($ord->product_image, $ord->product_title) }}" alt="product" class="prod-thumb">
                                                <div>
                                                    <div class="fw-semibold">{{ $ord->product_title ?? 'Produk ID #' . $ord->product_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Rp {{ number_format($ord->product_price ?? 0, 0, ',', '.') }}</td>
                                        <td><span class="badge bg-secondary">{{ $ord->qty }} unit</span></td>
                                        <td class="fw-bold text-danger">Rp {{ number_format(($ord->product_price ?? 0) * $ord->qty, 0, ',', '.') }}</td>
                                        <td>
                                            <code>{{ $ord->trx_id }}</code>
                                            @if ($shipTo = $orderAddresses->get($ord->trx_id))
                                                <div class="small text-muted mt-1" title="{{ $shipTo->address_detail }}, {{ $shipTo->district }}, {{ $shipTo->city }}, {{ $shipTo->province }} {{ $shipTo->postal_code }}">
                                                    Kirim ke {{ $shipTo->recipient_name }}, {{ $shipTo->city }}
                                                </div>
                                                @if ($shipTo->courier)
                                                    <div class="small text-muted">
                                                        {{ $shipTo->courier }} {{ $shipTo->courier_service }} &middot; Ongkir Rp {{ number_format($shipTo->shipping_cost, 0, ',', '.') }}
                                                    </div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $ord->p_status == 'Completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                {{ $ord->p_status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                            Belum ada riwayat pesanan.<br>
                                            <a href="{{ route('home') }}" class="btn btn-sm btn-danger mt-3" style="background-color: #870000;">
                                                <i class="bi bi-bag-plus me-1"></i>Mulai Belanja Sekarang
                                            </a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const modalEl = document.getElementById('addressModal');
            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('addressForm');
            const title = document.getElementById('addressModalTitle');
            const storeUrl = @json(route('addresses.store'));
            const updateUrl = @json(route('addresses.update', '__ID__'));
            const fields = {
                recipient_name: 'af-recipient', phone: 'af-phone', province: 'af-province', city: 'af-city',
                district: 'af-district', postal_code: 'af-postal', address_detail: 'af-detail', label: 'af-label'
            };

            // Hapus sisa pesan kesalahan dari pengisian sebelumnya
            function clearErrors() {
                form.querySelectorAll('.af-error').forEach(el => el.remove());
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            }

            function fill(address) {
                Object.entries(fields).forEach(([key, id]) => {
                    document.getElementById(id).value = address ? (address[key] ?? '') : '';
                });
                const isDefault = document.getElementById('af-default');
                if (!isDefault) return;
                isDefault.checked = !!(address && Number(address.is_default));
                // Alamat utama tidak bisa "dilepas" statusnya, pilih alamat lain sebagai utama
                isDefault.disabled = !!(address && Number(address.is_default));
            }

            document.querySelectorAll('[data-address-mode]').forEach(btn => {
                btn.addEventListener('click', () => {
                    clearErrors();
                    if (btn.dataset.addressMode === 'edit') {
                        const address = JSON.parse(btn.dataset.address);
                        fill(address);
                        form.action = updateUrl.replace('__ID__', address.id);
                        document.getElementById('af-editing').value = address.id;
                        title.textContent = 'Ubah Alamat';
                    } else {
                        fill(null);
                        form.action = storeUrl;
                        document.getElementById('af-editing').value = '';
                        title.textContent = 'Tambah Alamat Baru';
                    }
                    modal.show();
                });
            });

            // Buka kembali form jika ada isian yang salah, supaya pesan kesalahannya terlihat
            @if ($errors->getBag('address')->any())
                modal.show();
            @endif
        })();
    </script>
    {{-- Widget Chatbot MY-Bot --}}
    @include('partials.chatbot')
</body>
</html>