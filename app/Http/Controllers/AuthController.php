<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            // Username tanpa spasi dan tanpa "@", agar tidak tertukar dengan email saat login
            'name'     => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_.\-]+$/', 'unique:users,name'],
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'      => 'Username is required',
            'name.unique'        => 'Username already exists',
            'name.regex'         => 'Username may only contain letters, numbers, dots, underscores and dashes (no spaces or @)',
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

        // Batasi percobaan login: 5 kali gagal per menit untuk kombinasi akun + IP (cegah tebak password)
        $throttleKey = Str::lower($loginInput) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        // Coba sebagai email lebih dulu jika formatnya email, lalu sebagai username
        $fields = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? ['email', 'name'] : ['name'];
        foreach ($fields as $field) {
            if (Auth::attempt([$field => $loginInput, 'password' => $validated['password']])) {
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();

                // Kembali ke halaman yang tadi ingin dibuka sebelum diminta login
                return redirect()->intended(route('home'))->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
            }
        }

        RateLimiter::hit($throttleKey, 60);

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