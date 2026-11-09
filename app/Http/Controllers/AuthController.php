<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:users,name',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'      => 'Username is required',
            'name.unique'        => 'Username already exists',
            'email.required'     => 'Email is required',
            'email.unique'       => 'Email already exists',
            'password.required'  => 'Password is required',
            'password.confirmed' => 'The two passwords do not match',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Sinkronisasi data ke tabel user_info jika tabel tersedia di skema basis data
        if (Schema::hasTable('user_info')) {
            try {
                DB::table('user_info')->insert([
                    'user_id'    => $user->id,
                    'first_name' => $user->name,
                    'last_name'  => '',
                    'email'      => $user->email,
                    'password'   => Hash::make($validated['password']),
                    'mobile'     => '',
                    'address1'   => '',
                    'address2'   => '',
                ]);
            } catch (\Throwable $e) {
                // Abaikan jika tabel atau primary key unik sudah ada
            }
        }

        Auth::login($user);

        // Pindahkan keranjang belanja dari guest IP ke user yang baru mendaftar
        $ipAdd = $request->ip();
        DB::table('cart')
            ->where('ip_add', $ipAdd)
            ->where(function($q) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', 0)
                  ->orWhere('user_id', -1);
            })
            ->update(['user_id' => $user->id]);

        return redirect()->route('home')->with('success', 'Registrasi berhasil! Anda sekarang telah masuk.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $validated['email'];
        // Tentukan apakah pengguna memasukkan email atau username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $validated['password']])) {
            $request->session()->regenerate();

            // Pindahkan keranjang belanja jika ada ke akun pengguna
            $ipAdd = $request->ip();
            DB::table('cart')
                ->where('ip_add', $ipAdd)
                ->where(function($q) {
                    $q->whereNull('user_id')
                      ->orWhere('user_id', 0)
                      ->orWhere('user_id', -1);
                })
                ->update(['user_id' => Auth::id()]);

            return redirect()->route('home')->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email/Password salah',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah berhasil keluar (Logout).');
    }
}