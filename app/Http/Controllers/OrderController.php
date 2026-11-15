<?php

namespace App\Http\Controllers;

use App\Services\OrderQuery;
use App\Services\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Halaman pesanan untuk pembeli: detail & instruksi bayar, unggah bukti pembayaran,
 * batalkan pesanan, dan konfirmasi pesanan diterima.
 * Semua aksi dibatasi ke pemilik transaksi (Cegah IDOR).
 */
class OrderController extends Controller
{
    public function show(string $trx)
    {
        OrderStatus::expireOverdue(Auth::id());

        $order = OrderQuery::find($trx, Auth::id());
        abort_unless($order, 404);

        return view('orders.show', $order + [
            'method'   => config("payment.methods.{$order['trx']->payment_method}"),
            'isDemo'   => config('payment.demo'),
            'holder'   => config('payment.account_holder'),
            'maxKb'    => config('payment.proof_max_kb'),
        ]);
    }

    public function uploadProof(Request $request, string $trx)
    {
        $order = OrderQuery::find($trx, Auth::id());
        abort_unless($order, 404);

        if (!in_array($order['trx']->status, [OrderStatus::PENDING, OrderStatus::WAITING], true)) {
            return back()->with('error', 'Bukti pembayaran hanya bisa diunggah saat pesanan menunggu pembayaran.');
        }

        $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:' . config('payment.proof_max_kb'),
        ], [
            'proof.required' => 'Pilih foto bukti pembayaran terlebih dahulu.',
            'proof.image'    => 'Bukti pembayaran harus berupa gambar.',
            'proof.mimes'    => 'Format bukti pembayaran harus JPG, PNG, atau WEBP.',
            'proof.max'      => 'Ukuran bukti pembayaran maksimal ' . (config('payment.proof_max_kb') / 1024) . ' MB.',
        ]);

        // Disimpan di penyimpanan privat (bukan folder public), hanya bisa dibuka pemilik & admin
        $path = $request->file('proof')->storeAs(
            'payment-proofs',
            $trx . '-' . Str::random(8) . '.' . $request->file('proof')->extension(),
            'local'
        );

        try {
            $before = OrderStatus::change($trx, OrderStatus::WAITING, [
                'payment_proof'     => $path,
                'proof_uploaded_at' => now(),
                'admin_note'        => null,
            ], [OrderStatus::PENDING, OrderStatus::WAITING]);
        } catch (\Throwable $e) {
            // Jangan tinggalkan file yatim jika penyimpanan status gagal
            Storage::disk('local')->delete($path);
            throw $e;
        }

        if (!$before) {
            Storage::disk('local')->delete($path);
            return redirect()->route('orders.show', $trx)->with('error', 'Status pesanan sudah berubah, bukti tidak jadi diunggah. Silakan cek status terbaru di bawah.');
        }

        // Bukti lama (termasuk yang pernah ditolak) dihapus setelah bukti baru berhasil tersimpan.
        // Diambil dari baris yang dikunci saat perubahan, agar unggahan beruntun tidak meninggalkan file.
        if ($before->payment_proof && $before->payment_proof !== $path) {
            Storage::disk('local')->delete($before->payment_proof);
        }

        return redirect()->route('orders.show', $trx)->with('success', 'Bukti pembayaran berhasil diunggah. Admin akan memeriksanya, biasanya kurang dari 1x24 jam.');
    }

    public function cancel(string $trx)
    {
        $order = OrderQuery::find($trx, Auth::id());
        abort_unless($order, 404);

        // Setelah bukti pembayaran diunggah, pembeli dianggap sudah membayar: pembatalan lewat CS/admin
        // agar pengembalian dana bisa diurus, bukan dibatalkan sepihak dari halaman pesanan
        $changed = OrderStatus::change($trx, OrderStatus::CANCELLED, [
            'cancelled_at' => now(),
            'admin_note'   => 'Dibatalkan oleh pembeli.',
        ], [OrderStatus::PENDING]);

        if (!$changed) {
            return redirect()->route('orders.show', $trx)->with('error', 'Pesanan ini tidak bisa dibatalkan karena pembayaran sudah dikirim atau pesanan sudah diproses. Hubungi CS kami jika ingin membatalkan.');
        }

        return redirect()->route('orders.show', $trx)->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function received(string $trx)
    {
        $order = OrderQuery::find($trx, Auth::id());
        abort_unless($order, 404);

        $changed = OrderStatus::change($trx, OrderStatus::COMPLETED, ['completed_at' => now()], [OrderStatus::SHIPPED]);

        if (!$changed) {
            return redirect()->route('orders.show', $trx)->with('error', 'Pesanan hanya bisa dikonfirmasi diterima setelah dikirim.');
        }

        return redirect()->route('orders.show', $trx)->with('success', 'Terima kasih! Pesanan telah selesai.');
    }

    /**
     * Menampilkan gambar bukti pembayaran (hanya untuk pemilik pesanan dan admin)
     */
    public function proof(string $trx)
    {
        $user = Auth::user();
        $order = OrderQuery::find($trx, $user->isAdmin() ? null : $user->id);
        abort_unless($order && $order['trx']->payment_proof && Storage::disk('local')->exists($order['trx']->payment_proof), 404);

        return Storage::disk('local')->response($order['trx']->payment_proof);
    }
}
