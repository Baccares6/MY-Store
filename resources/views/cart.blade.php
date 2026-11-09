<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Online Shop</title>
    <!-- Google Fonts & Font Awesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #870000;
            --primary-dark: #600000;
            --accent: #190A05;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: linear-gradient(135deg, #870000 0%, #190A05 100%);
            color: white;
            padding: 16px 48px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .brand-logo {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #ffffff;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-nav {
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-block;
        }

        .btn-nav-outline {
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
            background: transparent;
        }

        .btn-nav-outline:hover {
            background: rgba(255,255,255,0.15);
        }

        .container {
            max-width: 1140px;
            margin: 40px auto;
            padding: 0 24px;
            flex: 1;
            width: 100%;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background-color: #ecfdf5;
            border-left: 4px solid var(--success);
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
            color: #065f46;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-error {
            background-color: #fef2f2;
            border-left: 4px solid var(--danger);
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
            color: #991b1b;
            font-size: 14px;
            font-weight: 600;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 28px;
            align-items: start;
        }

        @media (max-width: 860px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            header {
                padding: 16px 20px;
            }
        }

        .cart-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .cart-table th {
            background: #f1f5f9;
            padding: 14px 18px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .cart-table td {
            padding: 18px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .cart-item-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .cart-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            background: #f1f5f9;
            border: 1px solid var(--border);
        }

        .cart-item-title {
            font-weight: 700;
            font-size: 15px;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .cart-item-meta {
            font-size: 12px;
            color: var(--text-muted);
        }

        .qty-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-input {
            width: 60px;
            padding: 6px 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
        }

        .btn-update-qty {
            background: #e2e8f0;
            color: var(--text-dark);
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-update-qty:hover {
            background: #cbd5e1;
        }

        .item-price {
            font-weight: 700;
            color: var(--text-dark);
        }

        .item-subtotal {
            font-weight: 800;
            color: var(--primary);
            font-size: 15px;
        }

        .btn-remove {
            background: transparent;
            color: #ef4444;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-remove:hover {
            background: #fee2e2;
        }

        .summary-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .summary-title {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .summary-row.total-row {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            border-top: 2px dashed var(--border);
            padding-top: 16px;
            margin-top: 16px;
        }

        .summary-row.total-row {
            align-items: center;
            gap: 12px;
        }

        .summary-row.total-row > span:first-child {
            font-size: 15px;
        }

        .total-price {
            color: var(--primary);
            white-space: nowrap;
        }

        .btn-checkout {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #870000 0%, #a81c1c 100%);
            color: white;
            text-align: center;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(135, 0, 0, 0.25);
        }

        .btn-checkout:hover {
            background: linear-gradient(135deg, #700000 0%, #8f1717 100%);
            transform: translateY(-2px);
        }

        .btn-continue-shopping {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
        }

        .btn-continue-shopping:hover {
            color: var(--primary);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 24px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
        }

        .empty-cart i {
            font-size: 64px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .empty-cart h3 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .empty-cart p {
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .btn-shop-now {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        /* Alamat Pengiriman */
        .cart-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        .address-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .address-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .address-card-title {
            font-size: 16px;
            font-weight: 800;
        }

        .btn-text {
            background: none;
            border: none;
            color: var(--primary);
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            padding: 4px 0;
        }

        .btn-text:hover {
            text-decoration: underline;
        }

        .address-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .address-option {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            cursor: pointer;
            transition: border-color 0.15s, background-color 0.15s;
        }

        .address-option:hover {
            border-color: #cbd5e1;
        }

        .address-option:has(input:checked) {
            border-color: var(--primary);
            background: #fff7f7;
        }

        .address-option input {
            margin-top: 3px;
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            flex-shrink: 0;
        }

        .address-name {
            font-size: 14px;
            font-weight: 700;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }

        .address-phone {
            font-weight: 500;
            color: var(--text-muted);
        }

        .address-text {
            font-size: 13px;
            color: #475569;
            line-height: 1.55;
            margin-top: 4px;
        }

        .tag {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #475569;
        }

        .tag-primary {
            background: #fde8e8;
            color: var(--primary);
        }

        .address-empty {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .btn-outline {
            display: inline-block;
            margin-top: 12px;
            padding: 9px 18px;
            background: #fff;
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 8px;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-outline:hover {
            background: #fff7f7;
        }

        .address-manage {
            display: inline-block;
            margin-top: 12px;
            font-size: 12px;
            color: var(--text-muted);
            text-decoration: none;
        }

        .address-manage:hover {
            color: var(--primary);
        }

        /* Pilihan Kurir */
        .shipping-weight {
            font-size: 12px;
            color: var(--text-muted);
        }

        .shipping-info {
            flex: 1;
        }

        .shipping-name {
            font-size: 14px;
            font-weight: 700;
        }

        .shipping-info .address-text {
            margin-top: 2px;
        }

        .shipping-cost {
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .checkout-hint {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            margin-top: 10px;
        }

        .btn-checkout:disabled {
            background: #cbd5e1;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }

        /* Dialog Tambah Alamat */
        .address-dialog {
            width: min(640px, calc(100vw - 32px));
            max-height: calc(100vh - 48px);
            margin: auto;
            border: none;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
        }

        .address-dialog::backdrop {
            background: rgba(15, 23, 42, 0.45);
        }

        .dialog-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
        }

        .dialog-title {
            font-size: 17px;
            font-weight: 800;
        }

        .dialog-close {
            background: none;
            border: none;
            font-size: 22px;
            line-height: 1;
            color: var(--text-muted);
            cursor: pointer;
        }

        .dialog-body {
            padding: 20px 22px;
        }

        .dialog-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 22px;
            border-top: 1px solid var(--border);
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }

        .btn-plain {
            padding: 10px 18px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
        }

        .btn-primary {
            padding: 10px 20px;
            background: var(--primary);
            border: none;
            border-radius: 8px;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 24px 0;
            font-size: 13px;
            text-align: center;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <header>
        <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; text-decoration: none; color: #ffffff; font-weight: 800; font-size: 22px; letter-spacing: 0.5px;">
            <!-- Ikon Tas Belanja -->
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#ffc107" viewBox="0 0 16 16" style="margin-right: 8px;">
                <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z"/>
            </svg>
            <span>MY-<span style="color: #ffc107;">STORE</span></span>
        </a>
        <div class="nav-links">
            <a href="{{ route('home') }}" class="btn-nav btn-nav-outline"><i class="fa fa-arrow-left me-1"></i> Lanjut Belanja</a>
            @auth
                <a href="{{ route('user.dashboard') }}" class="btn-nav btn-nav-outline"><i class="fa fa-user me-1"></i> Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-nav btn-nav-outline">Log In</a>
            @endauth
        </div>
    </header>

    <div class="container">
        <h1 class="page-title"><i class="fa fa-shopping-cart text-danger"></i> Keranjang Belanja Anda</h1>

        @if (session('success'))
            <div class="alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-error">
                ⚠ {{ session('error') }}
            </div>
        @endif

        @if($cartItems->isEmpty())
            <div class="empty-cart">
                <i class="fa fa-shopping-bag"></i>
                <h3>Keranjang Belanja Anda Kosong</h3>
                <p>Belum ada produk yang ditambahkan ke keranjang belanja Anda.</p>
                <a href="{{ route('home') }}" class="btn-shop-now"><i class="fa fa-store me-1"></i> Jelajahi Produk Sekarang</a>
            </div>
        @else
            <div class="cart-layout">
                <div class="cart-main">
                <div class="address-card">
                    <div class="address-card-head">
                        <h2 class="address-card-title">Alamat Pengiriman</h2>
                        @if ($addresses->isNotEmpty())
                            <button type="button" class="btn-text" data-open-address>+ Tambah alamat</button>
                        @endif
                    </div>

                    @if ($addresses->isEmpty())
                        <p class="address-empty">Anda belum punya alamat pengiriman. Tambahkan alamat terlebih dahulu agar pesanan bisa dikirim.</p>
                        <button type="button" class="btn-outline" data-open-address>Tambah Alamat</button>
                    @else
                        <div class="address-list">
                            @foreach ($addresses as $addr)
                                <label class="address-option">
                                    <input type="radio" name="address_id" value="{{ $addr->id }}" form="checkout-form"
                                           {{ (int) $addr->id === $selectedAddressId ? 'checked' : '' }}>
                                    <div>
                                        <div class="address-name">
                                            {{ $addr->recipient_name }}
                                            <span class="address-phone">{{ $addr->phone }}</span>
                                            @if ($addr->label)<span class="tag">{{ $addr->label }}</span>@endif
                                            @if ($addr->is_default)<span class="tag tag-primary">Utama</span>@endif
                                        </div>
                                        <div class="address-text">
                                            {{ $addr->address_detail }}<br>
                                            {{ $addr->district }}, {{ $addr->city }}, {{ $addr->province }} {{ $addr->postal_code }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <a href="{{ route('user.dashboard') }}#alamat" class="address-manage">Ubah atau hapus alamat di Dashboard</a>
                    @endif
                </div>

                @if ($addresses->isNotEmpty())
                    @php
                        $currentOptions = $shippingOptions->get($selectedAddressId, []);
                        $currentCode = collect($currentOptions)->contains('code', $selectedShipping)
                            ? $selectedShipping
                            : ($currentOptions[0]['code'] ?? null);
                        $currentQuote = collect($currentOptions)->firstWhere('code', $currentCode);
                    @endphp
                    <div class="address-card">
                        <div class="address-card-head">
                            <h2 class="address-card-title">Pilih Pengiriman</h2>
                            <span class="shipping-weight">
                                Berat {{ number_format($totalWeight / 1000, 1, ',', '.') }} kg
                                @if ($chargeableKg * 1000 != $totalWeight) &middot; dihitung {{ $chargeableKg }} kg @endif
                            </span>
                        </div>
                        <div class="address-list" id="shipping-list">
                            @forelse ($currentOptions as $opt)
                                <label class="address-option">
                                    <input type="radio" name="shipping_service" value="{{ $opt['code'] }}" form="checkout-form"
                                           {{ $opt['code'] === $currentCode ? 'checked' : '' }}>
                                    <div class="shipping-info">
                                        <div class="shipping-name">{{ $opt['courier'] }} {{ $opt['service'] }}</div>
                                        <div class="address-text">Estimasi tiba {{ $opt['etd'] }}</div>
                                    </div>
                                    <div class="shipping-cost">Rp {{ number_format($opt['cost'], 0, ',', '.') }}</div>
                                </label>
                            @empty
                                <p class="address-empty">Belum ada kurir yang melayani alamat ini.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                <div class="cart-card">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartItems as $item)
                            <tr>
                                <td>
                                    <div class="cart-item-info">
                                        <img src="{{ getProductImageUrl($item->product_image, $item->product_title) }}" alt="{{ $item->product_title }}" class="cart-img">
                                        <div>
                                            <div class="cart-item-title">{{ $item->product_title }}</div>
                                            <div class="cart-item-meta">
                                                <span>{{ $item->cat_title ?? 'Katalog' }}</span> &bull; 
                                                <span>{{ $item->brand_title ?? 'Original' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-price">Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
                                </td>
                                <td>
                                    <form action="{{ route('cart.update') }}" method="POST" class="qty-form">
                                        @csrf
                                        <input type="hidden" name="cart_id" value="{{ $item->cart_id }}">
                                        <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="99" class="qty-input">
                                        <button type="submit" class="btn-update-qty" title="Simpan Perubahan"><i class="fa fa-check"></i></button>
                                    </form>
                                </td>
                                <td>
                                    <div class="item-subtotal">Rp {{ number_format($item->product_price * $item->qty, 0, ',', '.') }}</div>
                                </td>
                                <td>
                                    <form action="{{ route('cart.remove', $item->cart_id) }}" method="POST" onsubmit="return confirm('Hapus produk ini dari keranjang?');">
                                        @csrf
                                        <button type="submit" class="btn-remove" title="Hapus"><i class="fa fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>

                <div class="summary-card">
                    <h3 class="summary-title">Ringkasan Belanja</h3>
                    <div class="summary-row">
                        <span>Total Kuantitas:</span>
                        <span><strong>{{ $totalItems }} unit</strong></span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal Produk:</span>
                        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim<span id="sum-courier">{{ !empty($currentQuote) ? ' (' . $currentQuote['courier'] . ')' : '' }}</span>:</span>
                        <span id="sum-shipping">
                            @if (!empty($currentQuote))
                                Rp {{ number_format($currentQuote['cost'], 0, ',', '.') }}
                            @else
                                &ndash;
                            @endif
                        </span>
                    </div>
                    <div class="summary-row total-row">
                        <span>Total Pembayaran:</span>
                        <span class="total-price" id="sum-total">Rp {{ number_format($subtotal + ($currentQuote['cost'] ?? 0), 0, ',', '.') }}</span>
                    </div>

                    <form action="{{ route('checkout') }}" method="POST" id="checkout-form">
                        @csrf
                        <button type="submit" class="btn-checkout" {{ $addresses->isEmpty() ? 'disabled' : '' }}>
                            <i class="fa fa-credit-card me-2"></i> Proses Checkout
                        </button>
                    </form>
                    @if ($addresses->isEmpty())
                        <p class="checkout-hint">Tambahkan alamat pengiriman untuk melanjutkan.</p>
                    @endif

                    <a href="{{ route('home') }}" class="btn-continue-shopping">
                        <i class="fa fa-arrow-left me-1"></i> Tambah Barang Lain
                    </a>
                </div>
            </div>

            <dialog class="address-dialog" id="address-dialog" aria-labelledby="address-dialog-title">
                <form action="{{ route('addresses.store') }}" method="POST">
                    @csrf
                    <div class="dialog-head">
                        <h2 class="dialog-title" id="address-dialog-title">Tambah Alamat Baru</h2>
                        <button type="button" class="dialog-close" data-close-address aria-label="Tutup">&times;</button>
                    </div>
                    <div class="dialog-body">
                        @include('partials.address-fields', ['isFirstAddress' => $addresses->isEmpty()])
                    </div>
                    <div class="dialog-foot">
                        <button type="button" class="btn-plain" data-close-address>Batal</button>
                        <button type="submit" class="btn-primary">Simpan Alamat</button>
                    </div>
                </form>
            </dialog>

            <script>
                (function () {
                    const dialog = document.getElementById('address-dialog');
                    document.querySelectorAll('[data-open-address]').forEach(function (btn) {
                        btn.addEventListener('click', function () { dialog.showModal(); });
                    });
                    document.querySelectorAll('[data-close-address]').forEach(function (btn) {
                        btn.addEventListener('click', function () { dialog.close(); });
                    });
                    // Buka kembali form jika ada isian yang salah, supaya pesan kesalahannya terlihat
                    @if ($errors->getBag('address')->any())
                        dialog.showModal();
                    @endif

                    // Pilihan kurir & total ikut berubah saat alamat atau kurir diganti.
                    // Angka di sini hanya tampilan; ongkir final tetap dihitung ulang di server saat checkout.
                    const shippingOptions = @json($shippingOptions);
                    const subtotal = {{ (int) $subtotal }};
                    const list = document.getElementById('shipping-list');
                    if (!list) return;

                    const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');
                    const escapeHtml = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

                    function updateSummary() {
                        const addressId = document.querySelector('input[name="address_id"]:checked')?.value;
                        const code = list.querySelector('input[name="shipping_service"]:checked')?.value;
                        const opt = (shippingOptions[addressId] || []).find(o => o.code === code);
                        document.getElementById('sum-courier').textContent = opt ? ' (' + opt.courier + ')' : '';
                        document.getElementById('sum-shipping').textContent = opt ? rupiah(opt.cost) : '–';
                        document.getElementById('sum-total').textContent = rupiah(subtotal + (opt ? opt.cost : 0));
                    }

                    function renderOptions(addressId) {
                        const options = shippingOptions[addressId] || [];
                        const previous = list.querySelector('input[name="shipping_service"]:checked')?.value;
                        const keep = options.some(o => o.code === previous) ? previous : options[0]?.code;

                        list.innerHTML = options.length ? options.map(o => `
                            <label class="address-option">
                                <input type="radio" name="shipping_service" value="${escapeHtml(o.code)}" form="checkout-form" ${o.code === keep ? 'checked' : ''}>
                                <div class="shipping-info">
                                    <div class="shipping-name">${escapeHtml(o.courier)} ${escapeHtml(o.service)}</div>
                                    <div class="address-text">Estimasi tiba ${escapeHtml(o.etd)}</div>
                                </div>
                                <div class="shipping-cost">${rupiah(o.cost)}</div>
                            </label>`).join('')
                            : '<p class="address-empty">Belum ada kurir yang melayani alamat ini.</p>';
                        updateSummary();
                    }

                    document.querySelectorAll('input[name="address_id"]').forEach(r => r.addEventListener('change', () => renderOptions(r.value)));
                    list.addEventListener('change', updateSummary);
                })();
            </script>
        @endif
    </div>

    <footer>
        <p>&copy; 2026 Online Shop System. E-Commerce Platform dengan DBMS Triggers & Transactions.</p>
    </footer>

    {{-- Widget Chatbot MY-Bot --}}
    @include('partials.chatbot')
</body>
</html>
