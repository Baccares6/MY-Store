<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyesuaikan jawaban FAQ MY-Bot dengan fitur alamat & ongkos kirim.
 * Jawaban hanya diganti jika isinya masih sama dengan teks bawaan,
 * sehingga FAQ yang sudah diubah Admin lewat panel tidak ikut tertimpa.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chatbot_faqs')) {
            return;
        }

        foreach (self::changes() as $topic => $change) {
            $row = DB::table('chatbot_faqs')->where('topic', $topic)->first();

            if (!$row) {
                DB::table('chatbot_faqs')->insert([
                    'topic'    => $topic,
                    'keywords' => $change['keywords'],
                    'answer'   => $change['answer'],
                ]);
                continue;
            }

            if ($this->sameText($row->answer, $change['old_answer'])) {
                DB::table('chatbot_faqs')->where('id', $row->id)->update([
                    'keywords' => $change['keywords'],
                    'answer'   => $change['answer'],
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::changes() as $topic => $change) {
            if ($change['old_answer'] === null) {
                DB::table('chatbot_faqs')->where('topic', $topic)->where('answer', $change['answer'])->delete();
                continue;
            }

            DB::table('chatbot_faqs')
                ->where('topic', $topic)
                ->where('answer', $change['answer'])
                ->update(['keywords' => $change['old_keywords'], 'answer' => $change['old_answer']]);
        }
    }

    // Abaikan perbedaan baris baru (\n vs \r\n) saat membandingkan teks
    private function sameText(?string $a, ?string $b): bool
    {
        return $a !== null && $b !== null && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b);
    }

    private static function changes(): array
    {
        return [
            'Metode Pembayaran' => [
                'old_keywords' => 'bayar, pembayaran, metode pembayaran, transfer, cod, e-wallet, ewallet, ovo, dana, gopay, qris',
                'old_answer'   => "MY-Store menerima pembayaran melalui:\n• Transfer bank (BCA, BNI, BRI, Mandiri)\n• E-wallet (OVO, DANA, GoPay) & QRIS\n• COD (bayar di tempat) untuk area tertentu",
                'keywords'     => 'bayar, pembayaran, metode pembayaran, transfer, cod, e-wallet, ewallet, ovo, dana, gopay, qris',
                'answer'       => "Saat ini MY-Store masih dalam tahap uji coba, jadi pembayaran online (transfer bank, e-wallet, QRIS, maupun COD) belum dibuka.\nPesanan yang Anda checkout tetap tercatat di Dashboard. Metode pembayaran akan kami umumkan setelah tersedia.",
            ],
            'Pengiriman & Ongkir' => [
                'old_keywords' => 'kirim, pengiriman, ongkir, ongkos kirim, ekspedisi, kurir, estimasi, berapa lama sampai, sampai kapan',
                'old_answer'   => "Pesanan dikirim melalui JNE, J&T, dan SiCepat.\n• Estimasi Pulau Jawa: 2–3 hari kerja\n• Luar Pulau Jawa: 3–7 hari kerja\nOngkos kirim dihitung otomatis saat checkout.",
                'keywords'     => 'kirim, pengiriman, ongkir, ongkirnya, ongkos kirim, biaya kirim, ekspedisi, kurir, estimasi, berapa lama sampai, sampai kapan, jne, j&t, sicepat',
                'answer'       => "Pesanan dikirim dari gudang kami di Kabupaten Sleman, DIY melalui JNE, J&T, dan SiCepat.\nEstimasi tiba:\n• DI Yogyakarta: 1–2 hari\n• Pulau Jawa: 2–4 hari (JNE YES besok sampai untuk DIY & Jawa)\n• Sumatera & Bali: 3–5 hari\n• Kalimantan, Sulawesi & Nusa Tenggara: 4–7 hari\n• Maluku & Papua: 5–9 hari\nOngkir dihitung dari berat barang dan provinsi tujuan, dan terlihat di halaman Keranjang setelah memilih alamat. Ketik \"ongkir ke [kota/provinsi]\" untuk perkiraan.",
            ],
            'Cara Belanja' => [
                'old_keywords' => 'cara belanja, cara beli, cara pesan, cara order, cara checkout, langkah belanja, gimana belanja',
                'old_answer'   => "Cara belanja di MY-Store:\n1. Login / daftar akun\n2. Cari produk lalu klik \"Tambah ke Keranjang\"\n3. Buka Keranjang, atur jumlah\n4. Klik Checkout — pesanan langsung tercatat di Dashboard Anda.",
                'keywords'     => 'cara belanja, cara beli, cara pesan, cara order, cara checkout, langkah belanja, gimana belanja',
                'answer'       => "Cara belanja di MY-Store:\n1. Login / daftar akun\n2. Cari produk lalu klik \"Add to Cart\"\n3. Buka Keranjang, atur jumlah barang\n4. Pilih atau tambahkan alamat pengiriman\n5. Pilih kurir, ongkir dan total pembayaran langsung terlihat\n6. Klik \"Proses Checkout\", pesanan tercatat di Dashboard Anda.",
            ],
            'Alamat Pengiriman' => [
                'old_keywords' => null,
                'old_answer'   => null,
                'keywords'     => 'alamat, alamat pengiriman, tambah alamat, ubah alamat, ganti alamat, hapus alamat, alamat utama, edit alamat',
                'answer'       => "Alamat pengiriman bisa ditambahkan langsung di halaman Keranjang lewat tombol \"+ Tambah alamat\".\nUntuk mengubah, menghapus, atau mengganti alamat utama, buka Dashboard lalu bagian \"Alamat Pengiriman\". Anda bisa menyimpan hingga 10 alamat.",
            ],
        ];
    }
};
