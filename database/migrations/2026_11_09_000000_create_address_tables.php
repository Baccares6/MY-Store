<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur Alamat Pengiriman
 * - addresses       : buku alamat milik pengguna (bisa lebih dari satu, salah satunya alamat utama)
 * - order_addresses : salinan alamat saat checkout, agar riwayat pesanan tidak berubah
 *                     walaupun pengguna mengubah/menghapus alamatnya di kemudian hari
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('label', 30)->nullable();      // contoh: Rumah, Kantor, Kos
                $table->string('recipient_name', 100);
                $table->string('phone', 20);
                $table->string('province', 60);
                $table->string('city', 100);
                $table->string('district', 100);
                $table->string('postal_code', 5);
                $table->string('address_detail', 255);        // jalan, nomor rumah, RT/RW, patokan
                $table->boolean('is_default')->default(false);
                $table->timestamps();
                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('order_addresses')) {
            Schema::create('order_addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->string('trx_id', 255)->unique();
                $table->integer('user_id');
                $table->string('recipient_name', 100);
                $table->string('phone', 20);
                $table->string('province', 60);
                $table->string('city', 100);
                $table->string('district', 100);
                $table->string('postal_code', 5);
                $table->string('address_detail', 255);
                $table->dateTime('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('addresses');
    }
};
