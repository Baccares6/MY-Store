<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur Pembayaran Manual (Transfer Bank / COD)
 * - transactions  : satu baris per transaksi (trx_id) berisi total, metode bayar, status,
 *                   batas waktu bayar, bukti transfer, nomor resi, dan catatan admin
 * - orders.price  : harga produk saat dibeli, agar riwayat tidak berubah ketika harga produk diubah
 *
 * Pesanan lama (sebelum fitur ini) dibuatkan baris transaksi berstatus "completed".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'price')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('price')->nullable();
            });
        }

        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('trx_id', 255)->unique();
                $table->integer('user_id')->index();
                $table->integer('subtotal');
                $table->integer('shipping_cost')->default(0);
                $table->integer('total');
                $table->string('payment_method', 20);
                $table->string('status', 30)->index();
                $table->dateTime('payment_deadline')->nullable();
                $table->string('payment_proof', 255)->nullable();
                $table->dateTime('proof_uploaded_at')->nullable();
                $table->dateTime('paid_at')->nullable();
                $table->string('tracking_number', 50)->nullable();
                $table->dateTime('shipped_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->dateTime('cancelled_at')->nullable();
                $table->string('admin_note', 255)->nullable();   // alasan tolak bukti / pembatalan
                $table->timestamps();
            });
        }

        // Pesanan lama: simpan harga saat ini sebagai harga beli, lalu buat baris transaksinya
        DB::table('orders')
            ->whereNull('price')
            ->update(['price' => DB::raw('(SELECT product_price FROM products WHERE products.product_id = orders.product_id)')]);

        // order_addresses.created_at diisi MySQL (CURRENT_TIMESTAMP) dalam zona waktu server,
        // sedangkan Laravel menyimpan waktu dalam UTC: hitung selisihnya untuk dikonversi ke UTC
        $offsetSeconds = DB::getDriverName() === 'mysql'
            ? (int) DB::selectOne('SELECT TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), NOW()) AS s')->s
            : 0;

        $legacy = DB::table('orders')
            ->select('trx_id', 'user_id', DB::raw('SUM(qty * COALESCE(price, 0)) as subtotal'))
            ->groupBy('trx_id', 'user_id')
            ->get();

        foreach ($legacy as $trx) {
            if (DB::table('transactions')->where('trx_id', $trx->trx_id)->exists()) {
                continue;
            }

            // Pesanan lama tanpa data pengiriman tidak punya catatan waktu: biarkan kosong (ditampilkan "-")
            $shipment = DB::table('order_addresses')->where('trx_id', $trx->trx_id)->first();
            $shipping = (int) ($shipment->shipping_cost ?? 0);
            $createdAt = $shipment && $shipment->created_at
                ? \Illuminate\Support\Carbon::parse($shipment->created_at)->subSeconds($offsetSeconds)
                : null;

            DB::table('transactions')->insert([
                'trx_id'         => $trx->trx_id,
                'user_id'        => $trx->user_id,
                'subtotal'       => (int) $trx->subtotal,
                'shipping_cost'  => $shipping,
                'total'          => (int) $trx->subtotal + $shipping,
                'payment_method' => 'legacy',
                'status'         => 'completed',
                'completed_at'   => $createdAt,
                'created_at'     => $createdAt,
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');

        if (Schema::hasColumn('orders', 'price')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
