<?php

namespace App\Http\Controllers;

use App\Services\OrderQuery;
use App\Services\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kelola Pesanan (Admin): periksa bukti pembayaran, konfirmasi/tolak pembayaran,
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

        // Pembeli diberi waktu baru untuk mengunggah ulang bukti yang benar.
        // File bukti yang ditolak tetap disimpan sebagai arsip (dihapus otomatis saat pembeli mengunggah bukti baru).
        return $this->transition($trx, [OrderStatus::WAITING], OrderStatus::PENDING, [
            'admin_note'       => $data['reason'],
            'payment_deadline' => now()->addHours(config('payment.deadline_hours')),
        ], 'Bukti pembayaran ditolak. Pembeli diminta mengunggah ulang.');
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

        $order = OrderQuery::find($trx);
        abort_unless($order, 404);

        // Pesanan yang sudah dibayar (bukti diunggah/dikonfirmasi) perlu pengembalian dana
        $paid = $order['trx']->paid_at || $order['trx']->status === OrderStatus::WAITING;
        $note = 'Dibatalkan oleh admin: ' . $data['reason'] . ($paid ? ' Dana akan dikembalikan ke pembeli.' : '');

        return $this->transition($trx, [OrderStatus::PENDING, OrderStatus::WAITING, OrderStatus::PROCESSING], OrderStatus::CANCELLED,
            ['cancelled_at' => now(), 'admin_note' => mb_substr($note, 0, 255)],
            $paid ? 'Pesanan dibatalkan. Jangan lupa kembalikan dana ke pembeli.' : 'Pesanan dibatalkan.');
    }

    /**
     * Pindahkan status hanya jika status saat ini termasuk yang diizinkan
     */
    private function transition(string $trx, array $from, string $to, array $extra, string $message)
    {
        $order = OrderQuery::find($trx);
        abort_unless($order, 404);

        // Status dicek ulang di dalam kunci baris, agar aksi yang bersamaan tidak saling menimpa
        $changed = OrderStatus::change($trx, $to, $extra, $from);

        if (!$changed) {
            $current = OrderStatus::label(OrderQuery::find($trx)['trx']->status);
            return redirect()->route('admin.orders.show', $trx)
                ->with('error', "Aksi ini tidak bisa dilakukan untuk pesanan berstatus {$current}. Status mungkin baru saja berubah.");
        }

        return redirect()->route('admin.orders.show', $trx)->with('success', $message);
    }
}
