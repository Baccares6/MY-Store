<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Halaman Utama Toko Online
     */
    public function index(Request $request)
    {
        $keyword = $request->input('q');
        $selectedCat = $request->input('category');
        $selectedBrand = $request->input('brand');

        // Query dasar produk berelasi dengan kategori & brand
        $query = DB::table('products')
            ->leftJoin('categories', 'products.product_cat', '=', 'categories.cat_id')
            ->leftJoin('brands', 'products.product_brand', '=', 'brands.brand_id')
            ->select('products.*', 'categories.cat_title', 'brands.brand_title');

        // Filter pencarian berdasarkan nama, kategori, brand, atau kata kunci
        if (!empty($keyword)) {
            $query->where(function($q) use ($keyword) {
                $q->where('products.product_title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('products.product_keywords', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('categories.cat_title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('brands.brand_title', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Filter kategori jika dipilih
        if (!empty($selectedCat)) {
            $query->where('products.product_cat', $selectedCat);
        }

        // Filter brand jika dipilih
        if (!empty($selectedBrand)) {
            $query->where('products.product_brand', $selectedBrand);
        }

        $products = $query->orderBy('products.product_id', 'desc')->get();

        // Ambil daftar kategori dan merek untuk navigasi filter dinamis
        $categories = DB::table('categories')->get();
        $brands = DB::table('brands')->get();

        // Hitung total item belanja di keranjang belanja pengguna yang sedang aktif (Hanya untuk pengguna login)
        $cart_count = 0;
        if (Auth::check()) {
            $cart_count = (int) DB::table('cart')->where('user_id', Auth::id())->sum('qty');
        }

        return view('home', compact(
            'products',
            'categories',
            'brands',
            'keyword',
            'selectedCat',
            'selectedBrand',
            'cart_count'
        ));
    }
}