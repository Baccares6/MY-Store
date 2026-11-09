<?php

namespace App\Http\Controllers;

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

        $orders = DB::table('orders')
            ->where('orders.user_id', $user->id)
            ->leftJoin('products', 'orders.product_id', '=', 'products.product_id')
            ->select('orders.*', 'products.product_title', 'products.product_price', 'products.product_image')
            ->orderBy('orders.order_id', 'desc')
            ->get();

        $total_orders = $orders->count();
        $total_items = $orders->sum('qty');
        $total_spent = $orders->sum(function ($order) {
            return ($order->product_price ?? 0) * $order->qty;
        });

        // Buku alamat pengguna, alamat utama ditampilkan paling atas
        $addresses = DB::table('addresses')
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
        $provinces = config('wilayah.provinces');

        // Alamat tujuan tiap transaksi (salinan saat checkout), dikelompokkan per trx_id
        $orderAddresses = DB::table('order_addresses')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('trx_id');

        // Total belanja ikut menghitung ongkos kirim setiap transaksi
        $total_spent += $orderAddresses->sum('shipping_cost');

        return view('user', compact('user', 'orders', 'total_orders', 'total_items', 'total_spent', 'addresses', 'provinces', 'orderAddresses'));
    }
}
