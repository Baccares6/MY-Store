<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin MY-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar { min-height: 100vh; background-color: #1e293b; }
        .card { border: none; border-radius: 14px; }
        .table th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .prod-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar py-3 px-3 shadow">
            <div class="d-flex align-items-center pb-3 mb-3 border-bottom border-secondary">
                <i class="bi bi-shield-shaded text-warning fs-3 me-2"></i>
                <span class="text-white fw-bold fs-5">Admin Panel</span>
            </div>
            <ul class="nav flex-column gap-1 mt-2">
                <li class="nav-item"><a class="nav-link text-white-50 px-3 py-2" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white bg-primary rounded px-3 py-2 fw-semibold" href="{{ route('admin.orders') }}"><i class="bi bi-receipt me-2"></i>Kelola Pesanan</a></li>
                <li class="nav-item"><a class="nav-link text-white-50 px-3 py-2" href="{{ route('admin.dashboard') }}#tabel-produk"><i class="bi bi-box me-2"></i>Manajemen Produk</a></li>
                <li class="nav-item"><a class="nav-link text-white-50 px-3 py-2" href="{{ route('home') }}"><i class="bi bi-shop me-2"></i>Ke Halaman Toko</a></li>
            </ul>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Jendela konfirmasi MY-Store (pengganti confirm() bawaan browser) --}}
    @include('partials.confirm-dialog')
</body>
</html>
