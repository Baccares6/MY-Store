<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom nominal transaksi diperbesar ke BIGINT.
 * INT bertanda maksimal Rp 2.147.483.647, sehingga keranjang bernilai besar
 * (misal 99 laptop + 99 HP) membuat checkout gagal di MySQL mode strict.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('subtotal')->change();
            $table->bigInteger('shipping_cost')->default(0)->change();
            $table->bigInteger('total')->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('subtotal')->change();
            $table->integer('shipping_cost')->default(0)->change();
            $table->integer('total')->change();
        });
    }
};
