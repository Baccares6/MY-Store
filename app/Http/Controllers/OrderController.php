<?php

namespace App\Http\Controllers;

use App\Services\OrderQuery;
use App\Services\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Halaman pesanan untuk pembeli: detail & instruksi bayar, unggah bukti transfer,
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
            return back()->with('error', 'Bukti transfer hanya bisa diunggah saat pesanan menunggu pembayaran.');
        }

        $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:' . config('payment.proof_max_kb'),
        ], [
            'proof.required' => 'Pilih foto bukti transfer terlebih dahulu.',
            'proof.image'    => 'Bukti transfer harus berupa gambar.',
            'proof.mimes'    => 'Format bukti transfer harus JPG, PNG, atau WEBP.',
            'proof.max'      => 'Ukuran bukti transfer maksimal ' . (config('payment.proof_max_kb') / 1024) . ' MB.',
        ]);

        // Disimpan di penyimpanan privat (bukan folder public), hanya bisa dibuka pemilik & admin
        $path = $request->file('proof')->storeAs(
            'payment-proofs',
            $trx . '-' . Str::random(8) . '.' . $request->file('proof')->extension(),
            'local'
        );

        try {
            OrderStatus::change($trx, OrderStatus::WAITING, [
                'payment_proof'     => $path,
                'proof_uploaded_at' => now(),
                'admin_note'        => null,
            ]);
        } catch (\Throwable $e) {
            // Jangan tinggalkan file yatim jika penyimpanan status gagal
            Storage::disk('local')->delete($path);
            throw $e;
        }

        // Bukti lama dihapus setelah bukti baru berhasil tersimpan
        if ($order['trx']->payment_proof) {
            Storage::disk('local')->delete($order['trx']->payment_proof);
        }

        return redirect()->route('orders.show', $trx)->with('success', 'Bukti transfer berhasil diunggah. Admin akan memeriksanya, biasanya kurang dari 1x24 jam.');
    }

    public function cancel(string $trx)
    {
        $order = OrderQuery::find($trx, Auth::id());
        abort_unless($order, 404);

        if (!in_array($order['trx']->status, [OrderStatus::PENDING, OrderStatus::WAITING], true)) {
            return back()->with('error', 'Pesanan yang sudah diproses tidak bisa dibatalkan. Hubungi CS kami jika ada kendala.');
        }

        OrderStatus::change($trx, OrderStatus::CANCELLED, [
            'cancelled_at' => now(),
            'admin_note'   => 'Dibatalkan oleh pembeli.',
        ]);

        return redirect()->route('orders.show', $trx)->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function received(string $trx)
    {
        $order = OrderQuery::find($trx, Auth::id());
        abort_unless($order, 404);

        if ($order['trx']->status !== OrderStatus::SHIPPED) {
            return back()->with('error', 'Pesanan hanya bisa dikonfirmasi diterima setelah dikirim.');
        }

        OrderStatus::change($trx, OrderStatus::COMPLETED, ['completed_at' => now()]);

        return redirect()->route('orders.show', $trx)->with('success', 'Terima kasih! Pesanan telah selesai.');
    }

    /**
     * Menampilkan gambar bukti transfer (hanya untuk pemilik pesanan dan admin)
     */
    public function proof(string $trx)
    {
        $user = Auth::user();
        $order = OrderQuery::find($trx, $user->isAdmin() ? null : $user->id);
        abort_unless($order && $order['trx']->payment_proof && Storage::disk('local')->exists($order['trx']->payment_proof), 404);

        return Storage::disk('local')->response($order['trx']->payment_proof);
    }
}
