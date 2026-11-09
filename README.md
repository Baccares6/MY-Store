# MY-Store

Toko online sederhana berbasis Laravel 12 yang dibuat untuk Praktikum Manajemen Sistem Informasi, Universitas Negeri Yogyakarta.

> Proyek ini adalah website demo. Belum ada transaksi atau pembayaran sungguhan.

## Fitur

- **Katalog produk**: pencarian dan filter kategori.
- **Akun pengguna**: daftar, masuk, dan dashboard riwayat pesanan.
- **Keranjang belanja**: ubah jumlah dan hapus barang. Data keranjang dilindungi dari akses akun lain (IDOR).
- **Alamat pengiriman**: buku alamat (maksimal 10), alamat utama, dan pilihan alamat saat checkout. Alamat disalin ke pesanan, sehingga riwayat tidak berubah walaupun alamatnya diedit.
- **Ongkos kirim**: JNE, J&T, dan SiCepat. Ongkir dihitung dari zona provinsi tujuan dan berat barang, dengan gudang asal di Kabupaten Sleman. Tarifnya simulasi dan bisa diubah di `config/shipping.php`.
- **Checkout atomik**: memakai `DB::transaction` dan `lockForUpdate`. Harga dan ongkir selalu dihitung ulang di server.
- **Panel admin**: CRUD produk (termasuk berat kirim), log aktivitas, dan pengelolaan FAQ chatbot.
- **MY-Bot**: chatbot rule-based untuk mencari produk, filter harga, cek keranjang, status pesanan, perkiraan ongkir ("ongkir ke surabaya 2 kg"), dan FAQ dari database.

Pembayaran online (payment gateway) belum tersedia.

## Teknologi

PHP 8.2, Laravel 12, MySQL/MariaDB (XAMPP), Blade, Bootstrap 5, JavaScript tanpa framework.

## Instalasi (XAMPP)

1. Clone ke folder `htdocs`, lalu install dependensi:
   ```bash
   git clone <url-repo> ecommerce-laravel
   cd ecommerce-laravel
   composer install
   ```
2. Salin konfigurasi dan buat app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Buat database kosong bernama `ecommerce` di phpMyAdmin, lalu sesuaikan `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecommerce
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Buat tabel dan isi data katalog contoh:
   ```bash
   php artisan migrate --seed
   ```
5. Jalankan server:
   ```bash
   php artisan serve
   ```
   Buka `http://127.0.0.1:8000`.

### Menjadikan akun sebagai admin

Daftar akun lewat halaman Register, lalu jalankan di phpMyAdmin:

```sql
UPDATE users SET is_admin = 1 WHERE email = 'email-anda@contoh.com';
```

### Trigger audit log (opsional)

Panel admin menampilkan log pesanan dari tabel `logs`. Log ini diisi oleh trigger MySQL berikut:

```sql
DELIMITER $$
CREATE TRIGGER trg_log_order_insert AFTER INSERT ON orders FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, date)
    VALUES (NEW.user_id, CONCAT('Pemesanan Produk ID: ', NEW.product_id, ' Status: ', NEW.p_status), NOW());
END$$
DELIMITER ;
```

## Menjalankan test

Test memakai SQLite in-memory, sehingga tidak menyentuh database MySQL.

```bash
php artisan test
```

## Struktur penting

| Lokasi | Isi |
|---|---|
| `app/Http/Controllers/CartController.php` | Keranjang dan checkout |
| `app/Http/Controllers/AddressController.php` | Buku alamat pengiriman |
| `app/Services/ShippingCalculator.php` | Perhitungan ongkir |
| `app/Http/Controllers/ChatbotController.php` | Mesin aturan MY-Bot |
| `config/shipping.php` | Tabel tarif dan zona ongkir |
| `config/wilayah.php` | Daftar 38 provinsi |
| `database/seeders/CatalogSeeder.php` | Data katalog contoh |
| `tests/Feature/` | Test fitur, keamanan, alamat, ongkir, dan chatbot |
