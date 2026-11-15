<?php

namespace App\Http\Controllers;

use App\Services\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Halaman Dashboard Akun Pengguna
     */
    public function dashboard()
    {
        $user = Auth::user();

        OrderStatus::expireOverdue($user->id);

        // Riwayat pesanan per transaksi, lengkap dengan alamat & kurir tujuan
        $transactions = DB::table('transactions')
            ->leftJoin('order_addresses', 'transactions.trx_id', '=', 'order_addresses.trx_id')
            ->where('transactions.user_id', $user->id)
            ->select(
                'transactions.*',
                'order_addresses.recipient_name',
                'order_addresses.city',
                'order_addresses.courier',
                'order_addresses.courier_service'
            )
            ->orderByDesc('transactions.id')
            ->get();

        // Barang di tiap transaksi (harga saat beli), dikelompokkan per trx_id
        $itemsByTrx = DB::table('orders')
            ->leftJoin('products', 'orders.product_id', '=', 'products.product_id')
            ->where('orders.user_id', $user->id)
            ->select('orders.trx_id', 'orders.qty', 'products.product_title', 'products.product_image')
            ->orderBy('orders.order_id')
            ->get()
            ->groupBy('trx_id');

        // Pesanan yang dibatalkan/kedaluwarsa tidak dihitung sebagai belanja
        $active = $transactions->reject(fn ($t) => in_array($t->status, [OrderStatus::CANCELLED, OrderStatus::EXPIRED], true));

        $total_orders = $transactions->count();
        $total_items = $active->sum(fn ($t) => $itemsByTrx->get($t->trx_id, collect())->sum('qty'));
        $total_spent = $active->sum('total');

        // Buku alamat pengguna, alamat utama ditampilkan paling atas
        $addresses = DB::table('addresses')
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
        $provinces = config('wilayah.provinces');

        return view('user', compact('user', 'transactions', 'itemsByTrx', 'total_orders', 'total_items', 'total_spent', 'addresses', 'provinces'));
    }
}
