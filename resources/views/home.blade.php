<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY-STORE - E-Commerce Platform</title>
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

        .user-greeting {
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
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

        .btn-login {
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
            background: transparent;
        }

        .btn-register {
            background: #ffffff;
            color: var(--primary);
        }

        .btn-logout {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.4);
            color: white;
            cursor: pointer;
        }

        .hero {
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1200&q=80') center/cover;
            color: white;
            padding: 50px 24px;
            text-align: center;
        }

        .hero h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .hero p {
            color: #94a3b8;
            font-size: 15px;
        }

        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .alert-success {
            background-color: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 14px 18px;
            border-radius: 8px;
            margin-top: 24px;
            color: #065f46;
            font-size: 14px;
            font-weight: 600;
        }

        .shop-collections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin: 40px auto;
        }

        .shop-card {
            position: relative;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            height: 200px;
            display: flex;
            align-items: center;
            padding: 24px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .shop-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .shop-content {
            z-index: 2;
            width: 55%;
        }

        .shop-content h3 {
            font-size: 20px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 12px;
            color: var(--text-dark);
        }

        .shop-content .cta-btn {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .shop-img-bg {
            position: absolute;
            right: -10px;
            bottom: -10px;
            width: 160px;
            height: 160px;
            object-fit: cover;
            opacity: 0.9;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-dark);
            text-transform: uppercase;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 60px;
        }

        .product-card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            position: relative;
            transition: all 0.2s;
        }

        .product-card:hover {
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }

        .product-badges {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 2;
        }

        .badge-sale {
            background: var(--primary);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .badge-new {
            background: #0f172a;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #f1f5f9;
        }

        .product-info {
            padding: 16px;
            text-align: center;
        }

        .product-category {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .product-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .product-price {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .product-old-price {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: line-through;
            margin-left: 6px;
        }

        .product-rating {
            color: #f59e0b;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .product-action-btns {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .product-action-btns button {
            background: transparent;
            border: 1px solid var(--border);
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-dark);
            transition: all 0.2s;
        }

        .product-action-btns button:hover {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .btn-cart {
            width: 100%;
            padding: 10px;
            background: var(--text-dark);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-cart:hover {
            background: var(--primary);
        }

        .hot-deal-section {
            background: linear-gradient(rgba(135, 0, 0, 0.9), rgba(25, 10, 5, 0.95)), url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80') center/cover;
            color: white;
            padding: 60px 24px;
            border-radius: 16px;
            text-align: center;
            margin: 40px auto 60px auto;
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .countdown-item {
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            width: 70px;
            height: 70px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .countdown-item h3 {
            font-size: 22px;
            font-weight: 800;
        }

        .countdown-item span {
            font-size: 11px;
            text-transform: uppercase;
        }

        .hot-deal-section h2 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .btn-hot-deal {
            background: #ffffff;
            color: var(--primary);
            padding: 12px 28px;
            font-weight: 700;
            font-size: 14px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 16px;
        }

        .newsletter-section {
            border-top: 1px solid var(--border);
            padding: 40px 24px;
            text-align: center;
            background: #ffffff;
        }

        .newsletter-section h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .newsletter-form {
            display: flex;
            justify-content: center;
            max-width: 440px;
            margin: 0 auto;
        }

        .newsletter-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px 0 0 8px;
            outline: none;
        }

        .newsletter-btn {
            padding: 12px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            font-weight: 700;
            cursor: pointer;
        }

        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 50px 0 24px 0;
            font-size: 13px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 32px;
            margin-bottom: 40px;
        }

        .footer-col h4 {
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .footer-bottom {
            border-top: 1px solid #1e293b;
            padding-top: 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .payment-icons {
            display: flex;
            gap: 14px;
            font-size: 20px;
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
            <!-- Tombol Keranjang Belanja (Tampil untuk semua pengunjung) -->
            <a href="{{ route('cart.index') }}" style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 50%; color: #ffffff; text-decoration: none; margin-right: 6px; vertical-align: middle;" title="Keranjang Belanja">
                <i class="fa fa-shopping-cart" style="font-size: 16px;"></i>
                <span style="position: absolute; top: -3px; right: -3px; background: #ffffff; color: var(--primary); font-size: 11px; font-weight: 800; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                    {{ $cart_count ?? 0 }}
                </span>
            </a>

            @auth
                <div class="user-greeting">👋 Hi, {{ Auth::user()->name }}</div>
                <a href="{{ route('user.dashboard') }}" class="btn-nav btn-login" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);"><i class="fa fa-user me-1"></i> Dashboard</a>
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-nav btn-login" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);"><i class="fa fa-shield-alt me-1"></i> Admin</a>
                @endif
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-nav btn-logout">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-nav btn-login">Log In</a>
                <a href="{{ route('register') }}" class="btn-nav btn-register">Register User</a>
            @endauth
        </div>
    </header>

    <div class="hero">
        <h1>Welcome to MY-Store</h1>
    </div>

    <!-- ================= MULAI SEARCH BAR ================= -->
    <div style="max-width: 1200px; margin: 30px auto 16px auto; padding: 0 15px;">
        <form action="{{ route('home') }}" method="GET" style="display: flex; align-items: center; width: 100%; background: #ffffff; border: 2px solid #e1e1e1; border-radius: 40px; padding: 6px 8px 6px 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); box-sizing: border-box;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#888" viewBox="0 0 16 16" style="margin-right: 12px; flex-shrink: 0;">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input 
                type="text" 
                name="q" 
                placeholder="Cari produk elektronik, aksesoris, laptop, tablet..." 
                value="{{ request('q') }}"
                style="flex: 1; border: none; outline: none; font-size: 16px; color: #333; background: transparent; padding: 10px 0;"
            >
            @if(request('q') || request('category') || request('brand'))
                <a href="{{ route('home') }}" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: #f0f0f0; color: #666; text-decoration: none; margin-right: 10px; font-size: 16px;" title="Reset Filter">✕</a>
            @endif
            <button type="submit" style="background-color: var(--primary); color: #ffffff; border: none; outline: none; padding: 12px 32px; font-size: 15px; font-weight: 600; border-radius: 30px; cursor: pointer; transition: 0.2s;">
                Cari
            </button>
        </form>
    </div>
    <!-- ================= SELESAI SEARCH BAR ================= -->

    <!-- Quick Category Filter Bar -->
    @if(isset($categories) && $categories->isNotEmpty())
    <div style="max-width: 1200px; margin: 0 auto 30px auto; padding: 0 15px; display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;">
        <a href="{{ route('home') }}" style="padding: 6px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 700; background: {{ (!request('category') && !request('q')) ? 'var(--primary)' : '#ffffff' }}; color: {{ (!request('category') && !request('q')) ? '#ffffff' : 'var(--text-dark)' }}; border: 1px solid var(--border); box-shadow: 0 2px 4px rgba(0,0,0,0.03); transition: all 0.2s;">
            Semua Komoditas
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->cat_id]) }}" style="padding: 6px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 700; background: {{ request('category') == $cat->cat_id ? 'var(--primary)' : '#ffffff' }}; color: {{ request('category') == $cat->cat_id ? '#ffffff' : 'var(--text-dark)' }}; border: 1px solid var(--border); box-shadow: 0 2px 4px rgba(0,0,0,0.03); transition: all 0.2s;">
                {{ $cat->cat_title }}
            </a>
        @endforeach
    </div>
    @endif

    <div class="container">
        @if (session('success'))
            <div class="alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 14px 18px; border-radius: 8px; margin-top: 24px; color: #991b1b; font-size: 14px; font-weight: 600;">
                ⚠ {{ session('error') }}
            </div>
        @endif

        <div class="shop-collections">
            <div class="shop-card">
                <div class="shop-content">
                    <h3>Electronics<br>Gadgets</h3>
                    <a href="{{ route('home', ['category' => 1]) }}" class="cta-btn">Belanja Sekarang <i class="fa fa-arrow-circle-right"></i></a>
                </div>
                <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=300&q=80" alt="Electronics" class="shop-img-bg">
            </div>

            <div class="shop-card">
                <div class="shop-content">
                    <h3>Fashion &<br>Wears</h3>
                    <a href="{{ route('home', ['category' => 2]) }}" class="cta-btn">Belanja Sekarang <i class="fa fa-arrow-circle-right"></i></a>
                </div>
                <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=300&q=80" alt="Fashion" class="shop-img-bg">
            </div>

            <div class="shop-card">
                <div class="shop-content">
                    <h3>Apple &<br>Smartphones</h3>
                    <a href="{{ route('home', ['q' => 'Apple']) }}" class="cta-btn">Belanja Sekarang <i class="fa fa-arrow-circle-right"></i></a>
                </div>
                <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=300&q=80" alt="Smartphones" class="shop-img-bg">
            </div>
        </div>

        <div class="section-header">
            <h2 class="section-title">
                @if(request('category'))
                    Kategori: {{ $categories->firstWhere('cat_id', request('category'))->cat_title ?? 'Pilihan' }}
                @elseif(request('q'))
                    Hasil Pencarian
                @else
                    Katalog Komoditas Produk
                @endif
            </h2>
            <div style="font-size: 14px; color: var(--text-muted); font-weight: 600;">
                Menampilkan {{ $products->count() }} produk
            </div>
        </div>

        @if(request('q') || request('category') || request('brand'))
            <div style="background: #f1f5f9; border-radius: 8px; padding: 12px 18px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 14px; color: var(--text-dark);">
                    Filter aktif: 
                    @if(request('q'))
                        Kata kunci <strong>"{{ request('q') }}"</strong>
                    @endif
                    @if(request('category'))
                        Kategori <strong>"{{ $categories->firstWhere('cat_id', request('category'))->cat_title ?? request('category') }}"</strong>
                    @endif
                    @if(request('brand'))
                        Brand <strong>"{{ $brands->firstWhere('brand_id', request('brand'))->brand_title ?? request('brand') }}"</strong>
                    @endif
                </span>
                <a href="{{ route('home') }}" style="color: var(--primary); font-size: 13px; font-weight: 700; text-decoration: none;">Reset Filter ✕</a>
            </div>
        @endif

        <div class="product-grid">
            @forelse($products as $product)
            <div class="product-card">
                <div class="product-badges">
                    <span class="badge-new">ID #{{ $product->product_id }}</span>
                    @if(!empty($product->cat_title))
                        <span class="badge-sale">{{ $product->cat_title }}</span>
                    @endif
                </div>
                <img src="{{ getProductImageUrl($product->product_image, $product->product_title, $product->product_cat) }}" alt="{{ $product->product_title }}" class="product-img">
                <div class="product-info">
                    <div class="product-category">
                        {{ $product->brand_title ?? 'Brand Asli' }} &bull; {{ $product->cat_title ?? 'Katalog' }}
                    </div>
                    <div class="product-title">{{ $product->product_title }}</div>
                    <div class="product-price">
                        Rp {{ number_format($product->product_price, 0, ',', '.') }}
                    </div>
                    <div class="product-rating">
                        <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-alt"></i>
                    </div>
                    <div class="product-action-btns">
                        @if($product->product_cat)
                            <a href="{{ route('home', ['category' => $product->product_cat]) }}" title="Filter Kategori" style="text-decoration:none;"><button type="button"><i class="fa fa-tag"></i></button></a>
                        @endif
                        @if($product->product_brand)
                            <a href="{{ route('home', ['brand' => $product->product_brand]) }}" title="Filter Brand" style="text-decoration:none;"><button type="button"><i class="fa fa-copyright"></i></button></a>
                        @endif
                        <button type="button" title="Detail"><i class="fa fa-eye"></i></button>
                    </div>
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn-cart"><i class="fa fa-shopping-cart"></i> Add to Cart</button>
                    </form>
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 24px; background: #ffffff; border-radius: 12px; border: 1px solid var(--border);">
                <i class="fa fa-box-open" style="font-size: 52px; color: #cbd5e1; margin-bottom: 16px;"></i>
                <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 8px;">Tidak Ada Produk Ditemukan</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
                    @if(request('q'))
                        Tidak ada komoditas dengan kata kunci "<strong>{{ request('q') }}</strong>".
                    @elseif(request('category'))
                        Belum ada komoditas pada kategori ini.
                    @else
                        Katalog produk masih kosong.
                    @endif
                </p>
                <a href="{{ route('home') }}" class="btn-nav btn-register" style="background: var(--primary); color: white; display: inline-block;">
                    <i class="fa fa-rotate-left me-1"></i> Reset Filter & Lihat Semua Produk
                </a>
            </div>
            @endforelse
        </div>

        <div class="hot-deal-section">
            <div class="countdown">
                <div class="countdown-item">
                    <h3>02</h3>
                    <span>Days</span>
                </div>
                <div class="countdown-item">
                    <h3>10</h3>
                    <span>Hours</span>
                </div>
                <div class="countdown-item">
                    <h3>34</h3>
                    <span>Mins</span>
                </div>
                <div class="countdown-item">
                    <h3>60</h3>
                    <span>Secs</span>
                </div>
            </div>
            <h2>Hot Deal This Week</h2>
            <p>New Collection Up to 50% OFF</p>
            <a href="{{ route('home') }}" class="btn-hot-deal">Shop Now</a>
        </div>
    </div>

    <div class="newsletter-section">
        <h3>Sign Up for the <strong>OFFER UPDATES</strong></h3>
        <div class="newsletter-form">
            <input type="email" class="newsletter-input" placeholder="Enter Your Email">
            <button class="newsletter-btn"><i class="fa fa-envelope"></i> Subscribe</button>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>About Us</h4>
                    <p style="margin-bottom: 12px; line-height: 1.6;">Modern Laravel E-Commerce Management System implemented with MVC architecture and MySQL DBMS triggers.</p>
                    <ul class="footer-links">
                        <li><i class="fa fa-map-marker-alt" style="margin-right: 8px;"></i> Yogyakarta, Indonesia</li>
                        <li><i class="fa fa-phone" style="margin-right: 8px;"></i> +62 812-3456-7890</li>
                        <li><i class="fa fa-envelope" style="margin-right: 8px;"></i> support@onlineshop.com</li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul class="footer-links">
                        @if(isset($categories))
                            @foreach($categories->take(5) as $cat)
                                <li><a href="{{ route('home', ['category' => $cat->cat_id]) }}">{{ $cat->cat_title }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Information</h4>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}">Beranda Toko</a></li>
                        <li><a href="{{ route('cart.index') }}">Keranjang Belanja</a></li>
                        <li><a href="{{ route('user.dashboard') }}">Dashboard Pengguna</a></li>
                        @auth
                            @if(Auth::user()->isAdmin())
                                <li><a href="{{ route('admin.dashboard') }}">Admin Panel Kendali</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Account & Service</h4>
                    <ul class="footer-links">
                        @auth
                            <li><a href="{{ route('user.dashboard') }}">Profil Saya ({{ Auth::user()->name }})</a></li>
                            <li><a href="{{ route('cart.index') }}">Lihat Keranjang ({{ $cart_count ?? 0 }})</a></li>
                            @if(Auth::user()->isAdmin())
                                <li><a href="{{ route('admin.dashboard') }}">Pusat Administrator</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}">Masuk Akun (Log In)</a></li>
                            <li><a href="{{ route('register') }}">Daftar Pengguna Baru</a></li>
                            <li><a href="{{ route('cart.index') }}">Keranjang Belanja</a></li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="payment-icons">
                    <i class="fa-brands fa-cc-visa"></i>
                    <i class="fa-brands fa-cc-mastercard"></i>
                    <i class="fa-brands fa-cc-paypal"></i>
                    <i class="fa-brands fa-cc-discover"></i>
                </div>
                <p>&copy; 2026 Online Shop System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    {{-- Widget Chatbot MY-Bot --}}
    @include('partials.chatbot')
</body>
</html>