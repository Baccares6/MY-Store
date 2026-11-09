<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur Ongkos Kirim
 * - products.product_weight : berat kirim produk dalam gram (termasuk kemasan)
 * - order_addresses         : kurir, layanan, estimasi, berat, dan ongkir yang dipilih saat checkout
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'product_weight')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('product_weight')->default(1000);
            });
        }

        if (!Schema::hasColumn('order_addresses', 'shipping_cost')) {
            Schema::table('order_addresses', function (Blueprint $table) {
                $table->string('courier', 30)->nullable();
                $table->string('courier_service', 50)->nullable();
                $table->string('shipping_etd', 30)->nullable();
                $table->integer('total_weight')->default(0);   // gram
                $table->integer('shipping_cost')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->dropColumn(['courier', 'courier_service', 'shipping_etd', 'total_weight', 'shipping_cost']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_weight');
        });
    }
};
