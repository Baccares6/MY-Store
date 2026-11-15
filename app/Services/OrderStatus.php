<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Status transaksi & aturan perpindahannya.
 *
 *   Transfer bank : Menunggu Pembayaran → (unggah bukti) Menunggu Konfirmasi → (admin) Diproses
 *                   → (admin isi resi) Dikirim → (pembeli/admin) Selesai
 *   COD           : langsung Diproses → Dikirim → Selesai
 *   Lainnya       : Dibatalkan (pembeli/admin), Kedaluwarsa (lewat batas waktu bayar)
 *
 * Setiap perubahan juga memperbarui orders.p_status (dipakai trigger & riwayat lama)
 * dan dicatat ke tabel logs sebagai jejak audit.
 */
class OrderStatus
{
    public const PENDING    = 'pending_payment';
    public const WAITING    = 'waiting_confirmation';
    public const PROCESSING = 'processing';
    public const SHIPPED    = 'shipped';
    public const COMPLETED  = 'completed';
    public const CANCELLED  = 'cancelled';
    public const EXPIRED    = 'expired';

    public const LABELS = [
        self::PENDING    => 'Menunggu Pembayaran',
        self::WAITING    => 'Menunggu Konfirmasi',
        self::PROCESSING => 'Diproses',
        self::SHIPPED    => 'Dikirim',
        self::COMPLETED  => 'Selesai',
        self::CANCELLED  => 'Dibatalkan',
        self::EXPIRED    => 'Kedaluwarsa',
    ];

    // Warna badge Bootstrap untuk tiap status
    public const BADGES = [
        self::PENDING    => 'bg-warning text-dark',
        self::WAITING    => 'bg-info text-dark',
        self::PROCESSING => 'bg-primary',
        self::SHIPPED    => 'bg-primary',
        self::COMPLETED  => 'bg-success',
        self::CANCELLED  => 'bg-secondary',
        self::EXPIRED    => 'bg-secondary',
    ];

    // Nilai orders.p_status (VARCHAR 20) untuk kompatibilitas data & trigger lama
    private const LEGACY = [
        self::PENDING    => 'Pending',
        self::WAITING    => 'Verifying',
        self::PROCESSING => 'Processing',
        self::SHIPPED    => 'Shipped',
        self::COMPLETED  => 'Completed',
        self::CANCELLED  => 'Cancelled',
        self::EXPIRED    => 'Expired',
    ];

    public static function label(?string $status): string
    {
        return self::LABELS[$status] ?? ($status ?: '-');
    }

    public static function badge(?string $status): string
    {
        return self::BADGES[$status] ?? 'bg-secondary';
    }

    public static function legacy(string $status): string
    {
        return self::LEGACY[$status];
    }

    public static function methodLabel(?string $method): string
    {
        if ($method === 'legacy') {
            return 'Pesanan lama (sebelum fitur pembayaran)';
        }

        return config("payment.methods.{$method}.label", $method ?? '-');
    }

    /**
     * Ubah status transaksi beserta kolom pendukungnya, sinkronkan orders.p_status, dan catat ke log.
     */
    public static function change(string $trxId, string $status, array $extra = []): void
    {
        DB::transaction(function () use ($trxId, $status, $extra) {
            $trx = DB::table('transactions')->where('trx_id', $trxId)->lockForUpdate()->first();
            if (!$trx) {
                return;
            }

            DB::table('transactions')->where('trx_id', $trxId)->update($extra + [
                'status'     => $status,
                'updated_at' => now(),
            ]);

            DB::table('orders')->where('trx_id', $trxId)->update(['p_status' => self::legacy($status)]);

            // Tabel logs di database lama: charset latin1 & kolom action VARCHAR(50),
            // jadi pesan memakai kode status pendek ASCII, contoh "Status TRX-AB12CD3: Pending -> Verifying"
            DB::table('logs')->insert([
                'user_id' => (string) (Auth::id() ?? 'system'),
                'action'  => mb_substr("Status {$trxId}: " . (self::LEGACY[$trx->status] ?? $trx->status) . ' -> ' . self::legacy($status), 0, 50),
                // Waktu dari database (sama seperti trigger trg_log_order_insert) agar isi tabel logs konsisten
                'date'    => DB::raw('CURRENT_TIMESTAMP'),
            ]);
        });
    }

    /**
     * Tandai pesanan transfer yang melewati batas waktu bayar sebagai Kedaluwarsa.
     * Dipanggil saat halaman pesanan/admin dibuka dan oleh perintah terjadwal `orders:expire`.
     */
    public static function expireOverdue(?int $userId = null): int
    {
        $query = DB::table('transactions')
            ->where('status', self::PENDING)
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now());

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $expired = 0;
        foreach ($query->pluck('trx_id') as $trxId) {
            self::change($trxId, self::EXPIRED, ['cancelled_at' => now(), 'admin_note' => 'Batas waktu pembayaran terlewati.']);
            $expired++;
        }

        return $expired;
    }
}
