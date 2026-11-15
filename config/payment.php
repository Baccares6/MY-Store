<?php

/**
 * Pengaturan pembayaran manual MY-Store.
 * Nomor rekening dapat diubah lewat .env. Selama 'demo' bernilai true, halaman pembayaran
 * menampilkan peringatan agar pengunjung tidak melakukan transfer sungguhan.
 */
return [
    'demo' => env('PAYMENT_DEMO', true),

    // Batas waktu pembayaran setelah checkout (jam)
    'deadline_hours' => 24,

    'account_holder' => env('PAYMENT_ACCOUNT_HOLDER', 'MY-Store'),

    'methods' => [
        'bank_bca'     => ['type' => 'bank', 'label' => 'Transfer Bank BCA',     'bank' => 'BCA',     'account' => env('PAYMENT_BCA_ACCOUNT', '0000000001')],
        'bank_bni'     => ['type' => 'bank', 'label' => 'Transfer Bank BNI',     'bank' => 'BNI',     'account' => env('PAYMENT_BNI_ACCOUNT', '0000000002')],
        'bank_bri'     => ['type' => 'bank', 'label' => 'Transfer Bank BRI',     'bank' => 'BRI',     'account' => env('PAYMENT_BRI_ACCOUNT', '000000000000003')],
        'bank_mandiri' => ['type' => 'bank', 'label' => 'Transfer Bank Mandiri', 'bank' => 'Mandiri', 'account' => env('PAYMENT_MANDIRI_ACCOUNT', '0000000000004')],
        // QRIS statis: pembeli memindai kode QR toko, memasukkan nominal sendiri, lalu mengunggah bukti bayar.
        // Gambar bawaan hanya kode QR contoh (bukan QRIS asli); ganti dengan gambar QRIS dari bank/penyedia
        // pembayaran lewat PAYMENT_QRIS_IMAGE (path relatif terhadap folder public).
        'qris'         => [
            'type'     => 'qris',
            'label'    => 'QRIS',
            'merchant' => env('PAYMENT_QRIS_MERCHANT', 'MY-Store'),
            'image'    => env('PAYMENT_QRIS_IMAGE', 'images/qris-demo.svg'),
        ],
        'cod'          => ['type' => 'cod',  'label' => 'Bayar di Tempat (COD)'],
    ],

    // COD hanya untuk alamat di provinsi berikut (dekat gudang Sleman)
    'cod_provinces' => ['DI Yogyakarta'],

    // Bukti transfer: format gambar, maksimal 2 MB
    'proof_max_kb' => 2048,
];
