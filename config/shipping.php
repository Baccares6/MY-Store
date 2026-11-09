<?php

/**
 * Tabel tarif ongkos kirim MY-Store (simulasi, bukan tarif resmi kurir).
 *
 * Ongkir = tarif per kg sesuai zona tujuan x berat yang ditagihkan.
 * Zona ditentukan dari provinsi tujuan, relatif terhadap gudang asal di Kabupaten Sleman, DIY.
 * Layanan yang tidak punya tarif untuk suatu zona berarti tidak tersedia ke zona tersebut.
 */
return [
    'origin' => [
        'city'     => 'Kabupaten Sleman',
        'province' => 'DI Yogyakarta',
    ],

    // Toleransi pembulatan berat seperti kurir: 1,3 kg dihitung 1 kg, 1,31 kg dihitung 2 kg
    'weight_tolerance' => 300,

    'zones' => [
        'local' => [
            'name'      => 'Dalam provinsi',
            'provinces' => ['DI Yogyakarta'],
        ],
        'jawa' => [
            'name'      => 'Pulau Jawa',
            'provinces' => ['DKI Jakarta', 'Jawa Barat', 'Banten', 'Jawa Tengah', 'Jawa Timur'],
        ],
        'sumatera_bali' => [
            'name'      => 'Sumatera & Bali',
            'provinces' => [
                'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi',
                'Sumatera Selatan', 'Kepulauan Bangka Belitung', 'Bengkulu', 'Lampung', 'Bali',
            ],
        ],
        'tengah' => [
            'name'      => 'Kalimantan, Sulawesi & Nusa Tenggara',
            'provinces' => [
                'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
                'Sulawesi Utara', 'Gorontalo', 'Sulawesi Tengah', 'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tenggara',
                'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
            ],
        ],
        'timur' => [
            'name'      => 'Maluku & Papua',
            'provinces' => [
                'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat', 'Papua Barat Daya',
                'Papua Tengah', 'Papua Pegunungan', 'Papua Selatan',
            ],
        ],
    ],

    'services' => [
        'jne_reg' => [
            'courier' => 'JNE',
            'service' => 'Reguler',
            'rates'   => ['local' => 8000, 'jawa' => 12000, 'sumatera_bali' => 21000, 'tengah' => 33000, 'timur' => 56000],
            'etd'     => ['local' => '1-2 hari', 'jawa' => '2-3 hari', 'sumatera_bali' => '3-5 hari', 'tengah' => '4-6 hari', 'timur' => '5-8 hari'],
        ],
        'jne_yes' => [
            'courier' => 'JNE',
            'service' => 'YES (Besok Sampai)',
            'rates'   => ['local' => 16000, 'jawa' => 26000],
            'etd'     => ['local' => '1 hari', 'jawa' => '1 hari'],
        ],
        'jnt_ez' => [
            'courier' => 'J&T',
            'service' => 'EZ',
            'rates'   => ['local' => 8000, 'jawa' => 11000, 'sumatera_bali' => 20000, 'tengah' => 31000, 'timur' => 53000],
            'etd'     => ['local' => '1-2 hari', 'jawa' => '2-4 hari', 'sumatera_bali' => '3-5 hari', 'tengah' => '4-7 hari', 'timur' => '5-9 hari'],
        ],
        'sicepat_reg' => [
            'courier' => 'SiCepat',
            'service' => 'Reguler',
            'rates'   => ['local' => 7000, 'jawa' => 11000, 'sumatera_bali' => 19000, 'tengah' => 30000, 'timur' => 51000],
            'etd'     => ['local' => '1-2 hari', 'jawa' => '2-3 hari', 'sumatera_bali' => '3-5 hari', 'tengah' => '4-6 hari', 'timur' => '6-9 hari'],
        ],
    ],
];
