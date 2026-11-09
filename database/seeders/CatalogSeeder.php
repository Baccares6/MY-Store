<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data katalog contoh MY-Store (kategori, merek, produk) untuk instalasi baru.
 * Tidak berisi akun pengguna maupun data pribadi.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insertOrIgnore([
            ['cat_id' => 1, 'cat_title' => 'Electronics'],
            ['cat_id' => 2, 'cat_title' => 'Ladies Wears'],
            ['cat_id' => 3, 'cat_title' => 'Mens Wear'],
            ['cat_id' => 4, 'cat_title' => 'Kids Wear'],
            ['cat_id' => 5, 'cat_title' => 'Furnitures'],
            ['cat_id' => 6, 'cat_title' => 'Home Appliances'],
            ['cat_id' => 7, 'cat_title' => 'Electronics Gadgets'],
        ]);

        DB::table('brands')->insertOrIgnore([
            ['brand_id' => 1, 'brand_title' => 'HP'],
            ['brand_id' => 2, 'brand_title' => 'Samsung'],
            ['brand_id' => 3, 'brand_title' => 'Apple'],
            ['brand_id' => 4, 'brand_title' => 'motorolla'],
            ['brand_id' => 5, 'brand_title' => 'LG'],
            ['brand_id' => 6, 'brand_title' => 'Cloth Brand'],
        ]);

        // product_weight dalam gram (termasuk kemasan), dipakai untuk menghitung ongkir
        DB::table('products')->insertOrIgnore([
            [
                'product_id' => 1, 'product_cat' => 1, 'product_brand' => 2,
                'product_title' => 'Samsung galaxy s7 edge', 'product_price' => 4500000, 'product_weight' => 400,
                'product_desc' => 'Samsung galaxy s7 edge', 'product_image' => 'product07.png',
                'product_keywords' => 'samsung mobile electronics',
            ],
            [
                'product_id' => 2, 'product_cat' => 1, 'product_brand' => 3,
                'product_title' => 'iPhone 5s', 'product_price' => 1850000, 'product_weight' => 300,
                'product_desc' => 'iphone 5s', 'product_image' => 'iphone.png',
                'product_keywords' => 'mobile iphone apple',
            ],
            [
                'product_id' => 3, 'product_cat' => 1, 'product_brand' => 3,
                'product_title' => 'iPad air 2', 'product_price' => 3850000, 'product_weight' => 800,
                'product_desc' => 'ipad apple brand', 'product_image' => 'ipad.png',
                'product_keywords' => 'apple ipad tablet',
            ],
            [
                'product_id' => 4, 'product_cat' => 1, 'product_brand' => 1,
                'product_title' => 'HP Pavilion 15 Laptop', 'product_price' => 13500000, 'product_weight' => 2500,
                'product_desc' => 'Laptop performa tinggi dengan prosesor Intel Core i7 dan RAM 16GB untuk komputasi cepat.',
                'product_image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=500&q=80',
                'product_keywords' => 'hp, laptop, notebook, computer, electronics',
            ],
            [
                'product_id' => 5, 'product_cat' => 2, 'product_brand' => 6,
                'product_title' => 'Casual Summer Floral Dress', 'product_price' => 320000, 'product_weight' => 300,
                'product_desc' => 'Gaun kasual wanita bermotif bunga elegan dan nyaman dipakai sehari-hari.',
                'product_image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=500&q=80',
                'product_keywords' => 'dress, ladies, fashion, baju, wanita',
            ],
            [
                'product_id' => 6, 'product_cat' => 3, 'product_brand' => 6,
                'product_title' => 'Premium Slim-Fit Oxford Shirt', 'product_price' => 290000, 'product_weight' => 300,
                'product_desc' => 'Kemeja pria lengan panjang bahan katun oxford premium untuk acara kasual maupun formal.',
                'product_image' => 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=500&q=80',
                'product_keywords' => 'shirt, kemeja, pria, mens, cloth',
            ],
            [
                'product_id' => 7, 'product_cat' => 6, 'product_brand' => 5,
                'product_title' => 'LG Smart Inverter Refrigerator', 'product_price' => 6800000, 'product_weight' => 65000,
                'product_desc' => 'Kulkas dua pintu hemat energi dengan teknologi pendinginan multi-air flow mutakhir.',
                'product_image' => 'lg_refrigerator.png',
                'product_keywords' => 'lg, refrigerator, kulkas, home, appliances',
            ],
            [
                'product_id' => 8, 'product_cat' => 7, 'product_brand' => 4,
                'product_title' => 'Motorola Edge 50 Ultra', 'product_price' => 9200000, 'product_weight' => 400,
                'product_desc' => 'Smartphone flagship dengan kamera 50MP dan layar lengkung OLED super jernih.',
                'product_image' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=500&q=80',
                'product_keywords' => 'motorolla, edge, smartphone, gadget',
            ],
        ]);
    }
}
