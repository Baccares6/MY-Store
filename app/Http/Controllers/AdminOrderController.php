<?php

namespace App\Http\Controllers;

use App\Services\OrderQuery;
use App\Services\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Kelola Pesanan (Admin): periksa bukti transfer, konfirmasi/tolak pembayaran,
 * kirim pesanan dengan nomor resi, selesaikan, atau batalkan pesanan.
 */
class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        OrderStatus::expireOverdue();

        $status = $request->query('status');
        if (!is_string($status) || !array_key_exists($status, OrderStatus::LABELS)) {
            $status = null;
        }

        $transactions = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('order_addresses', 'transactions.trx_id', '=', 'order_addresses.trx_id')
            ->select('transactions.*', 'users.name as user_name', 'order_addresses.recipient_name', 'order_addresses.city')
            ->when($status, fn ($q) => $q->where('transactions.status', $status))
            ->orderByDesc('transactions.id')
            ->get();

        $counts = DB::table('transactions')->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status');

        return view('admin.orders.index', compact('transactions', 'counts', 'status'));
    }

    public function show(string $trx)
    {
        $order = OrderQuery::find($trx);
        abort_unless($order, 404);

        return view('admin.orders.show', $order);
    }

    public function confirm(string $trx)
    {
        return $this->transition($trx, [OrderStatus::WAITING], OrderStatus::PROCESSING, ['paid_at' => now(), 'admin_note' => null],
            'Pembayaran dikonfirmasi. Pesanan masuk tahap Diproses.');
    }

    public function reject(Request $request, string $trx)
    {
        $data = $request->validate(['reason' => 'required|string|max:200'], ['reason.required' => 'Tuliskan alasan penolakan agar pembeli tahu apa yang harus diperbaiki.']);

        $order = OrderQuery::find($trx);
        abort_unless($order, 404);

        if ($order['trx']->status !== OrderStatus::WAITING) {
            return back()->with('error', 'Bukti transfer hanya bisa ditolak saat pesanan menunggu konfirmasi.');
        }

        if ($order['trx']->payment_proof) {
            Storage::disk('local')->delete($order['trx']->payment_proof);
        }

        // Pembeli diberi waktu baru untuk mengunggah ulang bukti yang benar
        OrderStatus::change($trx, OrderStatus::PENDING, [
            'payment_proof'     => null,
            'proof_uploaded_at' => null,
            'admin_note'        => $data['reason'],
            'payment_deadline'  => now()->addHours(config('payment.deadline_hours')),
        ]);

        return redirect()->route('admin.orders.show', $trx)->with('success', 'Bukti transfer ditolak. Pembeli diminta mengunggah ulang.');
    }

    public function ship(Request $request, string $trx)
    {
        $data = $request->validate(
            ['tracking_number' => ['required', 'string', 'min:5', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/']],
            [
                'tracking_number.required' => 'Nomor resi wajib diisi.',
                'tracking_number.min'      => 'Nomor resi minimal 5 karakter.',
                'tracking_number.regex'    => 'Nomor resi hanya boleh berisi huruf, angka, dan tanda hubung.',
            ]
        );

        return $this->transition($trx, [OrderStatus::PROCESSING], OrderStatus::SHIPPED,
            ['tracking_number' => strtoupper($data['tracking_number']), 'shipped_at' => now()],
            'Pesanan ditandai Dikirim dengan nomor resi ' . strtoupper($data['tracking_number']) . '.');
    }

    public function complete(string $trx)
    {
        return $this->transition($trx, [OrderStatus::SHIPPED], OrderStatus::COMPLETED, ['completed_at' => now()],
            'Pesanan ditandai Selesai.');
    }

    public function cancel(Request $request, string $trx)
    {
        $data = $request->validate(['reason' => 'required|string|max:200'], ['reason.required' => 'Tuliskan alasan pembatalan.']);

        return $this->transition($trx, [OrderStatus::PENDING, OrderStatus::WAITING, OrderStatus::PROCESSING], OrderStatus::CANCELLED,
            ['cancelled_at' => now(), 'admin_note' => 'Dibatalkan oleh admin: ' . $data['reason']],
            'Pesanan dibatalkan.');
    }

    /**
     * Pindahkan status hanya jika status saat ini termasuk yang diizinkan
     */
    private function transition(string $trx, array $from, string $to, array $extra, string $message)
    {
        $order = OrderQuery::find($trx);
        abort_unless($order, 404);

        if (!in_array($order['trx']->status, $from, true)) {
            return back()->with('error', 'Aksi ini tidak bisa dilakukan untuk pesanan berstatus ' . OrderStatus::label($order['trx']->status) . '.');
        }

        OrderStatus::change($trx, $to, $extra);

        return redirect()->route('admin.orders.show', $trx)->with('success', $message);
    }
}
