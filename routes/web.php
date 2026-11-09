<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AddressController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Utama Toko Online (Katalog Produk & Pencarian)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Autentikasi Pengguna (Registrasi, Login & Logout)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.perform');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Keranjang Belanja & Alur Transaksi Checkout (Wajib Login)
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');

    // Buku Alamat Pengiriman (Dilindungi dari IDOR)
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::post('/addresses/{id}/update', [AddressController::class, 'update'])->name('addresses.update');
    Route::post('/addresses/{id}/delete', [AddressController::class, 'destroy'])->name('addresses.delete');
    Route::post('/addresses/{id}/default', [AddressController::class, 'makeDefault'])->name('addresses.default');
});

// Halaman Dashboard Pengguna (Riwayat Transaksi Pesanan)
Route::get('/user-dashboard', [UserController::class, 'dashboard'])
    ->middleware('auth')
    ->name('user.dashboard');

// Halaman Admin Panel & Manajemen Komoditas Produk (CRUD) - Terproteksi Role Admin
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/products', [AdminController::class, 'storeProduct'])->name('admin.products.store');
    Route::post('/products/{id}/update', [AdminController::class, 'updateProduct'])->name('admin.products.update');
    Route::post('/products/{id}/delete', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');

    // Manajemen Basis Pengetahuan (FAQ) Chatbot
    Route::post('/chatbot/faqs', [AdminController::class, 'storeFaq'])->name('admin.faqs.store');
    Route::post('/chatbot/faqs/{id}/toggle', [AdminController::class, 'toggleFaq'])->name('admin.faqs.toggle');
    Route::post('/chatbot/faqs/{id}/delete', [AdminController::class, 'deleteFaq'])->name('admin.faqs.delete');
});

// Chatbot MY-Bot (Rule-Based) - dapat diakses tamu & pengguna login, dibatasi 30 pesan/menit
Route::post('/chatbot/message', [ChatbotController::class, 'message'])
    ->middleware('throttle:30,1')
    ->name('chatbot.message');