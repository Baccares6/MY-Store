<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Halaman Dashboard Admin
     */
    public function dashboard()
    {
        $products = DB::table('products')
            ->leftJoin('categories', 'products.product_cat', '=', 'categories.cat_id')
            ->leftJoin('brands', 'products.product_brand', '=', 'brands.brand_id')
            ->select('products.*', 'categories.cat_title', 'brands.brand_title')
            ->orderBy('products.product_id', 'desc')
            ->get();

        $logs = DB::table('logs')->orderBy('id', 'desc')->get();
        $categories = DB::table('categories')->get();
        $brands = DB::table('brands')->get();

        $total_products = DB::table('products')->count();
        $total_orders = DB::table('orders')->count();
        $total_users = DB::table('users')->count();
        $total_logs = DB::table('logs')->count();

        // Data Chatbot MY-Bot
        $faqs = DB::table('chatbot_faqs')->orderBy('id')->get();
        $chat_logs = DB::table('chat_logs')
            ->leftJoin('users', 'chat_logs.user_id', '=', 'users.id')
            ->select('chat_logs.*', 'users.name as user_name')
            ->orderByDesc('chat_logs.id')
            ->limit(50)
            ->get();
        $total_chats = DB::table('chat_logs')->count();
        $intent_stats = DB::table('chat_logs')
            ->select('intent', DB::raw('COUNT(*) as total'))
            ->groupBy('intent')
            ->orderByDesc('total')
            ->get();

        return view('admin', compact(
            'products',
            'logs',
            'categories',
            'brands',
            'total_products',
            'total_orders',
            'total_users',
            'total_logs',
            'faqs',
            'chat_logs',
            'total_chats',
            'intent_stats'
        ));
    }

    /**
     * Tambah Produk Baru (CRUD Create)
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'product_title'    => 'required|string|max:255',
            'product_cat'      => 'required|integer',
            'product_brand'    => 'required|integer',
            'product_price'    => 'required|numeric|min:0',
            'product_weight'   => 'nullable|integer|min:1|max:200000',
            'product_desc'     => 'nullable|string',
            'product_image'    => 'nullable|string|max:255',
            'product_keywords' => 'nullable|string|max:255',
        ]);

        DB::table('products')->insert([
            'product_title'    => $validated['product_title'],
            'product_cat'      => $validated['product_cat'],
            'product_brand'    => $validated['product_brand'],
            'product_price'    => $validated['product_price'],
            'product_weight'   => $validated['product_weight'] ?? 1000,
            'product_desc'     => $validated['product_desc'] ?? $validated['product_title'],
            'product_image'    => $validated['product_image'] ?? 'product07.png',
            'product_keywords' => $validated['product_keywords'] ?? strtolower($validated['product_title']),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Produk komoditas baru berhasil ditambahkan ke database!');
    }

    /**
     * Perbarui Data Produk (CRUD Update)
     */
    public function updateProduct(Request $request, $id)
    {
        $validated = $request->validate([
            'product_title'    => 'required|string|max:255',
            'product_cat'      => 'required|integer',
            'product_brand'    => 'required|integer',
            'product_price'    => 'required|numeric|min:0',
            'product_weight'   => 'nullable|integer|min:1|max:200000',
            'product_desc'     => 'nullable|string',
            'product_image'    => 'nullable|string|max:255',
            'product_keywords' => 'nullable|string|max:255',
        ]);

        DB::table('products')->where('product_id', $id)->update([
            'product_title'    => $validated['product_title'],
            'product_cat'      => $validated['product_cat'],
            'product_brand'    => $validated['product_brand'],
            'product_price'    => $validated['product_price'],
            'product_weight'   => $validated['product_weight'] ?? 1000,
            'product_desc'     => $validated['product_desc'],
            'product_image'    => $validated['product_image'],
            'product_keywords' => $validated['product_keywords'],
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Data produk #' . $id . ' berhasil diperbarui!');
    }

    /**
     * Hapus Produk (CRUD Delete)
     */
    public function deleteProduct($id)
    {
        // Cek keterkaitan dengan tabel orders (Foreign Key)
        $hasOrders = DB::table('orders')->where('product_id', $id)->exists();
        if ($hasOrders) {
            return redirect()->route('admin.dashboard')->with('error', 'Produk #' . $id . ' tidak dapat dihapus karena sudah memiliki riwayat pesanan (Constraint Foreign Key).');
        }

        // Hapus juga dari cart jika ada
        DB::table('cart')->where('p_id', $id)->delete();

        DB::table('products')->where('product_id', $id)->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Produk #' . $id . ' berhasil dihapus dari katalog!');
    }

    /**
     * Tambah FAQ Chatbot
     */
    public function storeFaq(Request $request)
    {
        $validated = $request->validate([
            'topic'    => 'required|string|max:100',
            'keywords' => 'required|string|max:500',
            'answer'   => 'required|string|max:1000',
        ]);

        // Rapikan kata kunci: huruf kecil, dipisah koma, tanpa duplikat
        $keywords = collect(explode(',', $validated['keywords']))
            ->map(fn ($k) => mb_strtolower(trim($k)))
            ->filter()
            ->unique()
            ->implode(', ');

        DB::table('chatbot_faqs')->insert([
            'topic'      => $validated['topic'],
            'keywords'   => $keywords,
            'answer'     => $validated['answer'],
            'is_active'  => true,
            'created_at' => now(),
        ]);

        return redirect()->to(route('admin.dashboard') . '#chatbot')->with('success', 'FAQ chatbot "' . $validated['topic'] . '" berhasil ditambahkan!');
    }

    /**
     * Aktif / nonaktifkan FAQ Chatbot
     */
    public function toggleFaq($id)
    {
        $faq = DB::table('chatbot_faqs')->where('id', $id)->first();
        if (!$faq) {
            return redirect()->route('admin.dashboard')->with('error', 'FAQ tidak ditemukan.');
        }

        DB::table('chatbot_faqs')->where('id', $id)->update(['is_active' => !$faq->is_active]);

        return redirect()->to(route('admin.dashboard') . '#chatbot')->with('success', 'Status FAQ "' . $faq->topic . '" berhasil diubah.');
    }

    /**
     * Hapus FAQ Chatbot
     */
    public function deleteFaq($id)
    {
        DB::table('chatbot_faqs')->where('id', $id)->delete();

        return redirect()->to(route('admin.dashboard') . '#chatbot')->with('success', 'FAQ chatbot berhasil dihapus.');
    }
}
