<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->increments('cat_id');
                $table->string('cat_title', 255);
            });
        }

        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->increments('brand_id');
                $table->string('brand_title', 255);
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('product_id');
                $table->integer('product_cat');
                $table->integer('product_brand');
                $table->string('product_title', 255);
                $table->integer('product_price');
                $table->text('product_desc')->nullable();
                $table->text('product_image')->nullable();
                $table->text('product_keywords')->nullable();
            });
        }

        if (!Schema::hasTable('cart')) {
            Schema::create('cart', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('p_id');
                $table->string('ip_add', 250);
                $table->integer('user_id')->nullable();
                $table->integer('qty')->default(1);
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->increments('order_id');
                $table->integer('user_id');
                $table->integer('product_id');
                $table->integer('qty');
                $table->string('trx_id', 255);
                $table->string('p_status', 20);
            });
        }

        if (!Schema::hasTable('logs')) {
            Schema::create('logs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_id', 100);
                $table->string('action', 255);
                $table->dateTime('date')->useCurrent();
            });
        }

        if (!Schema::hasTable('user_info')) {
            Schema::create('user_info', function (Blueprint $table) {
                $table->integer('user_id')->primary();
                $table->string('first_name', 100)->nullable();
                $table->string('last_name', 100)->nullable();
                $table->string('email', 300)->nullable();
                $table->string('password', 300)->nullable();
                $table->string('mobile', 100)->nullable();
                $table->string('address1', 300)->nullable();
                $table->string('address2', 300)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversal for migrations
    }
};
