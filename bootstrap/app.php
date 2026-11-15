<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            // Pesan disesuaikan dengan halaman/aksi yang dicoba dibuka oleh tamu
            $message = match (true) {
                $request->is('cart/add')                  => 'Silakan masuk ke akun Anda terlebih dahulu sebelum memasukkan produk ke keranjang belanja.',
                $request->is('cart', 'cart/*', 'checkout') => 'Silakan masuk ke akun Anda terlebih dahulu untuk melihat keranjang belanja.',
                $request->is('user-dashboard')            => 'Silakan masuk ke akun Anda terlebih dahulu untuk membuka Dashboard.',
                $request->is('admin', 'admin/*')          => 'Silakan masuk dengan akun admin terlebih dahulu.',
                default                                   => 'Silakan masuk ke akun Anda terlebih dahulu.',
            };
            session()->flash('error', $message);

            return route('login');
        });

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
