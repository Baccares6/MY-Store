<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Membuat alamat pengiriman untuk pengguna dan mengembalikan id-nya.
     * Checkout mewajibkan alamat, sehingga test checkout memakai helper ini.
     */
    protected function createAddressFor(int $userId, array $overrides = []): int
    {
        return DB::table('addresses')->insertGetId($overrides + [
            'user_id'        => $userId,
            'label'          => 'Rumah',
            'recipient_name' => 'Budi Santoso',
            'phone'          => '081234567890',
            'province'       => 'DI Yogyakarta',
            'city'           => 'Kabupaten Sleman',
            'district'       => 'Depok',
            'postal_code'    => '55281',
            'address_detail' => 'Jl. Colombo No. 1, RT 01/RW 02',
            'is_default'     => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}
