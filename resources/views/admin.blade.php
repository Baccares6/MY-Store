<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Admin - Dashboard Kendali</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar { min-height: 100vh; background-color: #1e293b; }
        .card-stat { border: none; border-radius: 14px; color: #fff; transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-3px); }
        .prod-img-preview { width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
        .table th { font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Menu Navigasi Admin -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar py-3 px-3 shadow">
            <div class="d-flex align-items-center pb-3 mb-3 border-bottom border-secondary">
                <i class="bi bi-shield-shaded text-warning fs-3 me-2"></i>
                <span class="text-white fw-bold fs-5">Admin Panel</span>
            </div>
            <ul class="nav flex-column gap-1 mt-2">
                <li class="nav-item">
                    <a class="nav-link text-white bg-primary rounded px-3 py-2 fw-semibold" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 py-2" href="#tabel-produk">
                        <i class="bi bi-box me-2"></i>Manajemen Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 py-2" href="#tabel-logs">
                        <i class="bi bi-clock-history me-2"></i>Audit Logs Trigger
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 py-2" href="#chatbot">
                        <i class="bi bi-robot me-2"></i>Chatbot MY-Bot
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 py-2" href="{{ route('home') }}">
                        <i class="bi bi-shop me-2"></i>Ke Halaman Toko
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white-50 px-3 py-2" href="{{ route('user.dashboard') }}">
                        <i class="bi bi-person me-2"></i>User Dashboard
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content Dashboard -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1">Pusat Kendali Administrator</h2>
                    <p class="text-muted small mb-0">Kelola inventaris produk komoditas dan pantau audit trail otomasi basis data.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Ke Toko</a>
                    <span class="badge bg-success py-2 px-3 d-flex align-items-center"><i class="bi bi-check-circle-fill me-1"></i>Sistem Berjalan Aktif</span>
                </div>
            </div>

            <!-- Notifikasi Flash Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Ringkasan Statistik -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-stat bg-primary p-3 shadow-sm">
                        <small class="text-white-50 text-uppercase fw-semibold">Total Produk</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $total_products }}</h3>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-stat bg-success p-3 shadow-sm">
                        <small class="text-white-50 text-uppercase fw-semibold">Total Pesanan</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $total_orders }}</h3>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-stat bg-warning p-3 shadow-sm text-dark">
                        <small class="text-dark-50 text-uppercase fw-semibold">Pengguna Terdaftar</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $total_users }}</h3>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card card-stat bg-dark p-3 shadow-sm">
                        <small class="text-white-50 text-uppercase fw-semibold">Audit Logs Trigger</small>
                        <h3 class="fw-bold mb-0 mt-1">{{ $total_logs }}</h3>
                    </div>
                </div>
            </div>

            <!-- Tabel Manajemen CRUD Produk -->
            <div class="card shadow-sm border-0 mb-4 bg-white" id="tabel-produk">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Manajemen Komoditas Produk</h5>
                    <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalAddProduct">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Produk Baru (Create)
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Foto</th>
                                    <th>Nama Komoditas</th>
                                    <th>Kategori</th>
                                    <th>Merek</th>
                                    <th>Harga (IDR)</th>
                                    <th class="text-center">Aksi (CRUD)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $p)
                                <tr>
                                    <td class="fw-bold">#{{ $p->product_id }}</td>
                                    <td>
                                        <img src="{{ getProductImageUrl($p->product_image, $p->product_title) }}" alt="{{ $p->product_title }}" class="prod-img-preview">
                                    </td>
                                    <td class="fw-semibold">{{ $p->product_title }}</td>
                                    <td><span class="badge bg-secondary">{{ $p->cat_title ?? '-' }}</span></td>
                                    <td><span class="badge bg-light text-dark border">{{ $p->brand_title ?? '-' }}</span></td>
                                    <td>
                                        <div class="fw-bold text-success">Rp {{ number_format($p->product_price, 0, ',', '.') }}</div>
                                        <small class="text-muted">{{ number_format(($p->product_weight ?? 1000) / 1000, 1, ',', '.') }} kg</small>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEditProduct{{ $p->product_id }}" title="Edit Produk">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.products.delete', $p->product_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus Produk">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit Produk -->
                                <div class="modal fade" id="modalEditProduct{{ $p->product_id }}" tabindex="-1" aria-labelledby="modalEditLabel{{ $p->product_id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.products.update', $p->product_id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="modalEditLabel{{ $p->product_id }}">Edit Produk #{{ $p->product_id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nama Produk</label>
                                                        <input type="text" name="product_title" class="form-control" value="{{ $p->product_title }}" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Kategori</label>
                                                            <select name="product_cat" class="form-select" required>
                                                                @foreach($categories as $cat)
                                                                    <option value="{{ $cat->cat_id }}" {{ $p->product_cat == $cat->cat_id ? 'selected' : '' }}>{{ $cat->cat_title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Brand / Merek</label>
                                                            <select name="product_brand" class="form-select" required>
                                                                @foreach($brands as $brand)
                                                                    <option value="{{ $brand->brand_id }}" {{ $p->product_brand == $brand->brand_id ? 'selected' : '' }}>{{ $brand->brand_title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Harga (Rp)</label>
                                                            <input type="number" name="product_price" class="form-control" value="{{ $p->product_price }}" min="0" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold">Berat Kirim (gram)</label>
                                                            <input type="number" name="product_weight" class="form-control" value="{{ $p->product_weight ?? 1000 }}" min="1" max="200000" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Deskripsi Produk</label>
                                                        <textarea name="product_desc" class="form-control" rows="3">{{ $p->product_desc }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nama File Gambar / URL</label>
                                                        <input type="text" name="product_image" class="form-control" value="{{ $p->product_image }}">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Kata Kunci (Keywords)</label>
                                                        <input type="text" name="product_keywords" class="form-control" value="{{ $p->product_keywords }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data komoditas produk.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Tambah Produk Baru -->
            <div class="modal fade" id="modalAddProduct" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('admin.products.store') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold" id="modalAddLabel">Tambah Produk Komoditas Baru</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nama Produk</label>
                                    <input type="text" name="product_title" class="form-control" placeholder="Contoh: Asus ROG Strix G16" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Kategori</label>
                                        <select name="product_cat" class="form-select" required>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->cat_id }}">{{ $cat->cat_title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Brand / Merek</label>
                                        <select name="product_brand" class="form-select" required>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->brand_id }}">{{ $brand->brand_title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Harga (Rp)</label>
                                        <input type="number" name="product_price" class="form-control" placeholder="Contoh: 15000000" min="0" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Berat Kirim (gram)</label>
                                        <input type="number" name="product_weight" class="form-control" placeholder="Contoh: 2500" min="1" max="200000" required>
                                    </div>
                                </div>
                                <small class="text-muted d-block mb-3" style="margin-top:-8px;">Berat termasuk kemasan, dipakai untuk menghitung ongkos kirim.</small>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Deskripsi Produk</label>
                                    <textarea name="product_desc" class="form-control" rows="3" placeholder="Deskripsi spesifikasi produk..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nama File Gambar / URL Gambar</label>
                                    <input type="text" name="product_image" class="form-control" placeholder="product07.png atau URL https://...">
                                    <small class="text-muted">Bisa dikosongkan untuk gambar otomatis berdasarkan kategori.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Kata Kunci (Keywords)</label>
                                    <input type="text" name="product_keywords" class="form-control" placeholder="asus, rog, laptop, gaming">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm">Tambahkan Produk</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Log Audit Trail Trigger -->
            <div class="card shadow-sm border-0 bg-white" id="tabel-logs">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-shield-check me-2 text-success"></i>Audit Trail Log Sistem (Otomasi DBMS Trigger)</h5>
                    <span class="badge bg-secondary">{{ count($logs) }} Catatan Log</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Log ID</th>
                                    <th>User ID</th>
                                    <th>Tindakan / Action Log</th>
                                    <th>Stempel Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $l)
                                <tr>
                                    <td class="fw-bold">#{{ $l->id }}</td>
                                    <td><span class="badge bg-dark">User ID: {{ $l->user_id }}</span></td>
                                    <td class="fw-semibold text-primary">{{ $l->action }}</td>
                                    <td class="text-muted small">{{ $l->date }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat log.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= CHATBOT MY-BOT ================= -->
            <div class="card shadow-sm border-0 bg-white mt-4" id="chatbot">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-robot me-2 text-danger"></i>Chatbot MY-Bot — Basis Pengetahuan (FAQ)</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary">{{ count($faqs) }} FAQ</span>
                        <span class="badge bg-dark">{{ $total_chats }} Percakapan</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Form Tambah FAQ -->
                    <form action="{{ route('admin.faqs.store') }}" method="POST" class="row g-2 mb-4 p-3 rounded" style="background:#f8fafc;border:1px dashed #cbd5e1;">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Topik</label>
                            <input type="text" name="topic" class="form-control form-control-sm" placeholder="mis. Promo Bulan Ini" required maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Kata Kunci (pisahkan dengan koma)</label>
                            <input type="text" name="keywords" class="form-control form-control-sm" placeholder="promo, diskon, voucher" required maxlength="500">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">Jawaban Chatbot</label>
                            <div class="input-group input-group-sm">
                                <textarea name="answer" rows="1" class="form-control" placeholder="Jawaban yang dikirim MY-Bot" required maxlength="1000"></textarea>
                                <button type="submit" class="btn btn-danger"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Topik</th>
                                    <th>Kata Kunci</th>
                                    <th>Jawaban</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($faqs as $faq)
                                <tr>
                                    <td class="fw-semibold">{{ $faq->topic }}</td>
                                    <td class="small text-muted" style="max-width:220px;">{{ $faq->keywords }}</td>
                                    <td class="small" style="max-width:320px; white-space:pre-line;">{{ \Illuminate\Support\Str::limit($faq->answer, 140) }}</td>
                                    <td>
                                        @if($faq->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <form action="{{ route('admin.faqs.toggle', $faq->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" title="Aktif/Nonaktifkan"><i class="bi bi-toggle-{{ $faq->is_active ? 'on' : 'off' }}"></i></button>
                                        </form>
                                        <form action="{{ route('admin.faqs.delete', $faq->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus FAQ ini?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada FAQ chatbot.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Statistik Intent -->
                    <h6 class="fw-bold mb-2"><i class="bi bi-bar-chart me-2"></i>Statistik Pertanyaan Pengguna (per Intent)</h6>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @forelse($intent_stats as $st)
                            <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ $st->intent }} <span class="badge bg-danger ms-1">{{ $st->total }}</span></span>
                        @empty
                            <span class="text-muted small">Belum ada percakapan.</span>
                        @endforelse
                    </div>

                    <!-- Riwayat Percakapan -->
                    <h6 class="fw-bold mb-2"><i class="bi bi-chat-dots me-2"></i>50 Percakapan Terakhir</h6>
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-striped align-middle mb-0 small">
                            <thead class="table-light" style="position: sticky; top: 0;">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Pengguna</th>
                                    <th>Pesan</th>
                                    <th>Intent</th>
                                    <th>Balasan Bot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chat_logs as $c)
                                <tr>
                                    <td class="text-muted text-nowrap">{{ $c->created_at }}</td>
                                    <td>{!! $c->user_name ? e($c->user_name) : '<span class="text-muted fst-italic">Tamu</span>' !!}</td>
                                    <td class="fw-semibold">{{ $c->message }}</td>
                                    <td><span class="badge bg-dark">{{ $c->intent }}</span></td>
                                    <td class="text-muted" style="max-width:320px; white-space:pre-line;">{{ \Illuminate\Support\Str::limit($c->reply, 120) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada percakapan chatbot.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>