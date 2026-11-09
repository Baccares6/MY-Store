<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur Chatbot MY-Bot (Rule-Based)
 * - chatbot_faqs : basis pengetahuan (knowledge base) yang dapat dikelola Admin
 * - chat_logs    : riwayat percakapan pengguna dengan chatbot (untuk analisis & audit)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chatbot_faqs')) {
            Schema::create('chatbot_faqs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('topic', 100);
                $table->text('keywords');          // dipisah koma, contoh: "bayar, pembayaran, transfer"
                $table->text('answer');
                $table->boolean('is_active')->default(true);
                $table->dateTime('created_at')->useCurrent();
            });

            DB::table('chatbot_faqs')->insert(self::defaultFaqs());
        }

        if (!Schema::hasTable('chat_logs')) {
            Schema::create('chat_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();   // null = pengunjung tamu
                $table->string('session_id', 100);
                $table->text('message');
                $table->text('reply');
                $table->string('intent', 50);
                $table->dateTime('created_at')->useCurrent();
                $table->index('intent');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_logs');
        Schema::dropIfExists('chatbot_faqs');
    }

    public static function defaultFaqs(): array
    {
        return [
            [
                'topic'    => 'Metode Pembayaran',
                'keywords' => 'bayar, pembayaran, metode pembayaran, transfer, cod, e-wallet, ewallet, ovo, dana, gopay, qris',
                'answer'   => "MY-Store menerima pembayaran melalui:\n• Transfer bank (BCA, BNI, BRI, Mandiri)\n• E-wallet (OVO, DANA, GoPay) & QRIS\n• COD (bayar di tempat) untuk area tertentu",
            ],
            [
                'topic'    => 'Pengiriman & Ongkir',
                'keywords' => 'kirim, pengiriman, ongkir, ongkos kirim, ekspedisi, kurir, estimasi, berapa lama sampai, sampai kapan',
                'answer'   => "Pesanan dikirim melalui JNE, J&T, dan SiCepat.\n• Estimasi Pulau Jawa: 2–3 hari kerja\n• Luar Pulau Jawa: 3–7 hari kerja\nOngkos kirim dihitung otomatis saat checkout.",
            ],
            [
                'topic'    => 'Retur & Garansi',
                'keywords' => 'retur, return, refund, pengembalian, tukar barang, garansi, barang rusak, cacat',
                'answer'   => "Barang dapat diretur maksimal 7 hari setelah diterima jika rusak/tidak sesuai. Sertakan video unboxing dan hubungi CS kami. Produk elektronik bergaransi resmi sesuai ketentuan merek.",
            ],
            [
                'topic'    => 'Akun & Login',
                'keywords' => 'daftar, register, buat akun, registrasi, login, masuk akun, lupa password, lupa kata sandi',
                'answer'   => "Untuk berbelanja Anda perlu akun. Klik tombol \"Daftar\" di pojok kanan atas untuk membuat akun, atau \"Masuk\" jika sudah punya. Lupa kata sandi? Hubungi CS kami agar dibantu reset.",
            ],
            [
                'topic'    => 'Kontak Customer Service',
                'keywords' => 'kontak, hubungi, cs, customer service, telepon, whatsapp, wa, email, komplain',
                'answer'   => "Customer Service MY-Store:\n• WhatsApp: 0812-0000-0000\n• Email: cs@mystore.test\nSilakan hubungi kami pada jam operasional.",
            ],
            [
                'topic'    => 'Jam Operasional',
                'keywords' => 'jam operasional, jam buka, jam kerja, buka jam, tutup jam, hari libur, libur',
                'answer'   => "Toko online MY-Store dapat diakses 24 jam. Layanan CS aktif Senin–Sabtu pukul 08.00–17.00 WIB.",
            ],
            [
                'topic'    => 'Cara Belanja',
                'keywords' => 'cara belanja, cara beli, cara pesan, cara order, cara checkout, langkah belanja, gimana belanja',
                'answer'   => "Cara belanja di MY-Store:\n1. Login / daftar akun\n2. Cari produk lalu klik \"Tambah ke Keranjang\"\n3. Buka Keranjang, atur jumlah\n4. Klik Checkout — pesanan langsung tercatat di Dashboard Anda.",
            ],
        ];
    }
};
