<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Mengambil detail satu transaksi (header, barang, dan pengiriman)
 * untuk halaman pembeli, halaman admin, dan chatbot.
 */
class OrderQuery
{
    /**
     * @param  int|null  $userId  isi untuk membatasi ke pemilik transaksi (cegah IDOR); null untuk admin
     */
    public static function find(string $trxId, ?int $userId = null): ?array
    {
        $trx = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
            ->where('transactions.trx_id', $trxId)
            ->when($userId !== null, fn ($q) => $q->where('transactions.user_id', $userId))
            ->first();

        if (!$trx) {
            return null;
        }

        $items = DB::table('orders')
            ->leftJoin('products', 'orders.product_id', '=', 'products.product_id')
            ->where('orders.trx_id', $trxId)
            ->select(
                'orders.*',
                'products.product_title',
                'products.product_image',
                DB::raw('COALESCE(orders.price, products.product_price, 0) as unit_price')
            )
            ->get();

        $shipment = DB::table('order_addresses')->where('trx_id', $trxId)->first();

        return compact('trx', 'items', 'shipment');
    }
}
