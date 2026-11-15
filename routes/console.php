<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tandai pesanan transfer yang melewati batas waktu bayar sebagai Kedaluwarsa.
// Halaman pesanan juga melakukan pengecekan ini saat dibuka, jadi scheduler bersifat tambahan.
Artisan::command('orders:expire', function () {
    $count = \App\Services\OrderStatus::expireOverdue();
    $this->info("{$count} pesanan ditandai kedaluwarsa.");
})->purpose('Kedaluwarsakan pesanan yang melewati batas waktu pembayaran');

\Illuminate\Support\Facades\Schedule::command('orders:expire')->hourly();
