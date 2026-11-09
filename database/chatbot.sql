-- =====================================================================
-- MY-Store : Fitur Chatbot MY-Bot (Rule-Based + Database)
-- Import file ini lewat phpMyAdmin (pilih database ecommerce -> tab Import)
-- ATAU jalankan: php artisan migrate
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel basis pengetahuan (FAQ) chatbot — dikelola dari Halaman Admin
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chatbot_faqs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic` varchar(100) NOT NULL,
  `keywords` text NOT NULL,
  `answer` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chatbot_faqs` (`topic`, `keywords`, `answer`, `is_active`) VALUES
('Metode Pembayaran', 'bayar, pembayaran, metode pembayaran, transfer, cod, e-wallet, ewallet, ovo, dana, gopay, qris', 'MY-Store menerima pembayaran melalui:\n• Transfer bank (BCA, BNI, BRI, Mandiri)\n• E-wallet (OVO, DANA, GoPay) & QRIS\n• COD (bayar di tempat) untuk area tertentu', 1),
('Pengiriman & Ongkir', 'kirim, pengiriman, ongkir, ongkos kirim, ekspedisi, kurir, estimasi, berapa lama sampai, sampai kapan', 'Pesanan dikirim melalui JNE, J&T, dan SiCepat.\n• Estimasi Pulau Jawa: 2–3 hari kerja\n• Luar Pulau Jawa: 3–7 hari kerja\nOngkos kirim dihitung otomatis saat checkout.', 1),
('Retur & Garansi', 'retur, return, refund, pengembalian, tukar barang, garansi, barang rusak, cacat', 'Barang dapat diretur maksimal 7 hari setelah diterima jika rusak/tidak sesuai. Sertakan video unboxing dan hubungi CS kami. Produk elektronik bergaransi resmi sesuai ketentuan merek.', 1),
('Akun & Login', 'daftar, register, buat akun, registrasi, login, masuk akun, lupa password, lupa kata sandi', 'Untuk berbelanja Anda perlu akun. Klik tombol "Daftar" di pojok kanan atas untuk membuat akun, atau "Masuk" jika sudah punya. Lupa kata sandi? Hubungi CS kami agar dibantu reset.', 1),
('Kontak Customer Service', 'kontak, hubungi, cs, customer service, telepon, whatsapp, wa, email, komplain', 'Customer Service MY-Store:\n• WhatsApp: 0812-0000-0000\n• Email: cs@mystore.test\nSilakan hubungi kami pada jam operasional.', 1),
('Jam Operasional', 'jam operasional, jam buka, jam kerja, buka jam, tutup jam, hari libur, libur', 'Toko online MY-Store dapat diakses 24 jam. Layanan CS aktif Senin–Sabtu pukul 08.00–17.00 WIB.', 1),
('Cara Belanja', 'cara belanja, cara beli, cara pesan, cara order, cara checkout, langkah belanja, gimana belanja', 'Cara belanja di MY-Store:\n1. Login / daftar akun\n2. Cari produk lalu klik "Tambah ke Keranjang"\n3. Buka Keranjang, atur jumlah\n4. Klik Checkout — pesanan langsung tercatat di Dashboard Anda.', 1);

-- ---------------------------------------------------------------------
-- Tabel riwayat percakapan chatbot
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `chat_logs` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `reply` text NOT NULL,
  `intent` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chat_logs_intent_index` (`intent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catat migration agar "php artisan migrate" tidak membuat ulang tabel
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_01_000000_create_chatbot_tables', IFNULL(MAX(`batch`), 0) + 1 FROM `migrations`
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_01_000000_create_chatbot_tables');
