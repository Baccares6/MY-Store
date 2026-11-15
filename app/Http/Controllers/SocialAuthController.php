<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * Login / Register dengan akun Google & Facebook (Laravel Socialite, OAuth 2.0)
 *
 * Alur:
 *   1. Pengguna menekan tombol "Masuk dengan Google/Facebook" → diarahkan ke halaman login penyedia.
 *   2. Setelah setuju, penyedia mengembalikan pengguna ke /auth/{provider}/callback.
 *   3. Akun dicari berdasarkan ID penyedia; jika belum ada, digabungkan dengan akun ber-email sama;
 *      jika tetap belum ada, akun baru dibuat dengan username otomatis.
 */
class SocialAuthController extends Controller
{
    public const PROVIDERS = ['google' => 'Google', 'facebook' => 'Facebook'];

    /**
     * Penyedia hanya ditampilkan/dipakai jika Client ID & Secret sudah diisi di .env
     */
    public static function isConfigured(string $provider): bool
    {
        return filled(config("services.{$provider}.client_id")) && filled(config("services.{$provider}.client_secret"));
    }

    public function redirect(string $provider)
    {
        if (!self::isConfigured($provider)) {
            return $this->failed('Login dengan ' . self::PROVIDERS[$provider] . ' belum tersedia saat ini.');
        }

        return $this->driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        $label = self::PROVIDERS[$provider];

        if (!self::isConfigured($provider)) {
            return $this->failed("Login dengan {$label} belum tersedia saat ini.");
        }

        // Pengguna menekan "Batal" di halaman penyedia
        if ($request->has('error') || $request->has('error_code')) {
            return $this->failed("Login dengan {$label} dibatalkan.");
        }

        try {
            $social = $this->driver($provider)->user();
        } catch (\Throwable $e) {
            report($e);
            return $this->failed("Login dengan {$label} gagal. Silakan coba lagi.");
        }

        $column = "{$provider}_id";
        $user = User::where($column, $social->getId())->first();

        if (!$user) {
            $email = $social->getEmail();
            if (!$email) {
                return $this->failed("Akun {$label} Anda tidak membagikan alamat email. Izinkan akses email, atau daftar dengan email biasa.");
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                // Email sudah terdaftar: hubungkan akun penyedia ke akun yang sudah ada
                $user->forceFill([
                    $column  => $social->getId(),
                    'avatar' => $user->avatar ?: $social->getAvatar(),
                ])->save();
            } else {
                // Akun baru: password acak karena pengguna masuk lewat penyedia, bukan password
                $user = new User();
                $user->forceFill([
                    'name'              => $this->uniqueUsername($social->getNickname() ?: $social->getName() ?: Str::before($email, '@')),
                    'email'             => $email,
                    'password'          => Hash::make(Str::random(40)),
                    'email_verified_at' => now(),
                    $column             => $social->getId(),
                    'avatar'            => $social->getAvatar(),
                ])->save();
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Selamat datang, ' . $user->name . '!');
    }

    /**
     * Driver Socialite dengan alamat callback yang mengikuti alamat yang sedang dibuka
     * (localhost:8000, 127.0.0.1:8000, atau domain hosting), kecuali diisi URL lengkap di .env.
     * Bawaan Socialite memakai APP_URL, yang di lokal tidak memuat port sehingga ditolak Google.
     */
    private function driver(string $provider)
    {
        $driver = Socialite::driver($provider);

        if (!str_starts_with((string) config("services.{$provider}.redirect"), 'http')) {
            $driver->redirectUrl(route('social.callback', $provider));
        }

        if ($provider === 'facebook') {
            $driver->scopes(['email']);
        }

        return $driver;
    }

    private function failed(string $message)
    {
        return redirect()->route('login')->with('error', $message);
    }

    /**
     * Username dari nama akun penyedia, disesuaikan dengan aturan username MY-Store
     * (huruf, angka, titik, garis bawah, tanda hubung) dan dibuat unik.
     */
    private function uniqueUsername(string $base): string
    {
        $base = Str::limit(preg_replace('/[^A-Za-z0-9_.\-]/', '', Str::ascii(str_replace(' ', '.', trim($base)))), 30, '');
        $base = trim($base, '.-_') ?: 'pengguna';

        $username = $base;
        while (User::where('name', $username)->exists()) {
            $username = $base . random_int(100, 9999);
        }

        return $username;
    }
}
