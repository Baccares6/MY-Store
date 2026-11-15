<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Login/Register dengan akun Google & Facebook (Laravel Socialite)
 * - google_id / facebook_id : ID unik akun dari penyedia, untuk mengenali pengguna saat login berikutnya
 * - avatar                  : foto profil dari penyedia (opsional)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id', 100)->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id', 100)->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropUnique(['facebook_id']);
            $table->dropColumn(['google_id', 'facebook_id', 'avatar']);
        });
    }
};
