<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan QRIS ke jawaban FAQ MY-Bot.
 * Jawaban hanya diganti jika isinya masih sama dengan teks bawaan sebelumnya,
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
            if ($row && $this->sameText($row->answer, $change['old_answer'])) {
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
            DB::table('chatbot_faqs')
                ->where('topic', $topic)
                ->where('answer', $change['answer'])
                ->update(['keywords' => $change['old_keywords'], 'answer' => $change['old_answer']]);
        }
    }

    private function sameText(?string $a, ?string $b): bool
    {
        return $a !== null && $b !== null && str_replace("\r\n", "\n", $a) === str_replace("\r\n", "\n", $b);
    }

    private static function changes(): array
    {
        return [
            'Metode Pembayaran' => [
                'old_keywords' => 'bayar, pembayaran, metode pembayaran, cara bayar, transfer, transfer bank, bca, bni, bri, mandiri, cod, bayar di tempat, bukti transfer, upload bukti, konfirmasi pembayaran, e-wallet, ewallet, ovo, dana, gopay, qris',
                'old_answer'   => "MY-Store menerima pembayaran:\n• Transfer bank BCA, BNI, BRI, dan Mandiri. Setelah checkout, transfer sesuai total lalu unggah bukti transfer di halaman pesanan. Batas waktu bayar 24 jam.\n• Bayar di Tempat (COD), khusus alamat di DI Yogyakarta.\nBukti transfer dicek admin (biasanya kurang dari 1x24 jam), lalu pesanan diproses dan dikirim. E-wallet dan QRIS belum tersedia.\nCatatan: website ini masih tahap uji coba, jangan melakukan transfer uang sungguhan.",
                'keywords'     => 'bayar, pembayaran, metode pembayaran, cara bayar, transfer, transfer bank, bca, bni, bri, mandiri, cod, bayar di tempat, bukti transfer, bukti bayar, upload bukti, konfirmasi pembayaran, e-wallet, ewallet, ovo, dana, gopay, shopeepay, qris, scan qr',
                'answer'       => "MY-Store menerima pembayaran:\n• Transfer bank BCA, BNI, BRI, dan Mandiri.\n• QRIS: scan kode QR toko dengan GoPay, OVO, DANA, ShopeePay, atau m-banking, lalu masukkan nominal sesuai total.\n• Bayar di Tempat (COD), khusus alamat di DI Yogyakarta.\nUntuk transfer bank dan QRIS, unggah bukti pembayaran di halaman pesanan paling lambat 24 jam setelah checkout. Bukti dicek admin (biasanya kurang dari 1x24 jam), lalu pesanan diproses dan dikirim.\nCatatan: website ini masih tahap uji coba, jangan melakukan pembayaran sungguhan.",
            ],
            'Cara Belanja' => [
                'old_keywords' => 'cara belanja, cara beli, cara pesan, cara order, cara checkout, langkah belanja, gimana belanja',
                'old_answer'   => "Cara belanja di MY-Store:\n1. Login / daftar akun\n2. Cari produk lalu klik \"Add to Cart\"\n3. Buka Keranjang, atur jumlah barang\n4. Pilih alamat, kurir, dan metode pembayaran\n5. Klik \"Proses Checkout\"\n6. Untuk transfer bank: bayar sesuai total, lalu unggah bukti transfer di halaman pesanan\n7. Setelah dikonfirmasi admin, pesanan dikirim dan nomor resi muncul di halaman pesanan\n8. Tekan \"Pesanan Sudah Diterima\" setelah barang sampai.",
                'keywords'     => 'cara belanja, cara beli, cara pesan, cara order, cara checkout, langkah belanja, gimana belanja',
                'answer'       => "Cara belanja di MY-Store:\n1. Login / daftar akun\n2. Cari produk lalu klik \"Add to Cart\"\n3. Buka Keranjang, atur jumlah barang\n4. Pilih alamat, kurir, dan metode pembayaran\n5. Klik \"Proses Checkout\"\n6. Untuk transfer bank atau QRIS: bayar sesuai total, lalu unggah bukti pembayaran di halaman pesanan\n7. Setelah dikonfirmasi admin, pesanan dikirim dan nomor resi muncul di halaman pesanan\n8. Tekan \"Pesanan Sudah Diterima\" setelah barang sampai.",
            ],
        ];
    }
};
