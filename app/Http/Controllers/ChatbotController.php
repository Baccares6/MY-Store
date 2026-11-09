<?php

namespace App\Http\Controllers;

use App\Services\ShippingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * MY-Bot — Chatbot Asisten Belanja MY-Store (Rule-Based + Database)
 *
 * Alur kerja:
 *   1. Pesan pengguna dinormalisasi (huruf kecil, spasi dirapikan).
 *   2. Pesan dicocokkan ke "intent" secara berurutan (pattern matching / kata kunci).
 *   3. Untuk intent yang membutuhkan data, chatbot melakukan query ke MySQL
 *      (products, categories, brands, cart, orders, chatbot_faqs).
 *   4. Setiap percakapan dicatat ke tabel chat_logs.
 *   5. Respons dikirim dalam format JSON ke widget chat (AJAX / fetch).
 */
class ChatbotController extends Controller
{
    /** Saran cepat (quick reply) bawaan */
    private const DEFAULT_SUGGESTIONS = ['Produk termurah', 'Lihat kategori', 'Cek pesanan saya', 'Cara pembayaran'];

    /** Kata yang diabaikan saat mencari produk */
    private const STOPWORDS = [
        'cari', 'carikan', 'mencari', 'ada', 'adakah', 'apakah', 'jual', 'menjual', 'harga', 'berapa', 'produk',
        'barang', 'saya', 'aku', 'mau', 'ingin', 'pengen', 'beli', 'membeli', 'yang', 'dong', 'kak', 'min', 'gan',
        'tolong', 'gak', 'nggak', 'ga', 'tidak', 'ini', 'itu', 'punya', 'tersedia', 'stok', 'info', 'tentang',
        'lihat', 'tampilkan', 'kasih', 'rekomendasi', 'rekomendasiin', 'dan', 'atau', 'untuk', 'buat', 'di', 'ke',
        'the', 'a', 'an', 'is', 'please', 'show', 'me', 'find', 'search', 'do', 'you', 'have', 'sih', 'nya',
        'bawah', 'dibawah', 'kurang', 'dari', 'maksimal', 'maks', 'max', 'budget', 'bujet', 'under', 'rp',
        'juta', 'jt', 'ribu', 'rb', 'termurah', 'termahal', 'paling', 'murah', 'mahal', 'halo', 'hai', 'hi',
    ];

    /** Sinonim sederhana: istilah sehari-hari → kata kunci produk di database */
    private const SYNONYMS = [
        'hp'        => ['mobile', 'smartphone'],
        'handphone' => ['mobile', 'smartphone'],
        'ponsel'    => ['mobile', 'smartphone'],
        'smartphone'=> ['mobile'],
        'baju'      => ['shirt', 'dress', 'wear'],
        'kemeja'    => ['shirt'],
        'gaun'      => ['dress'],
        'pria'      => ['mens'],
        'wanita'    => ['ladies'],
        'kulkas'    => ['refrigerator'],
        'tablet'    => ['ipad'],
        'komputer'  => ['laptop', 'computer'],
    ];

    /** Nama kota/singkatan populer → provinsi, untuk perkiraan ongkir lewat chat */
    private const CITY_PROVINCES = [
        'jakarta' => 'DKI Jakarta',
        'jogja' => 'DI Yogyakarta', 'yogya' => 'DI Yogyakarta', 'yogyakarta' => 'DI Yogyakarta', 'diy' => 'DI Yogyakarta',
        'sleman' => 'DI Yogyakarta', 'bantul' => 'DI Yogyakarta', 'kulon progo' => 'DI Yogyakarta', 'gunungkidul' => 'DI Yogyakarta', 'gunung kidul' => 'DI Yogyakarta',
        'bandung' => 'Jawa Barat', 'bogor' => 'Jawa Barat', 'bekasi' => 'Jawa Barat', 'cirebon' => 'Jawa Barat', 'tasikmalaya' => 'Jawa Barat',
        'tangerang' => 'Banten', 'serang' => 'Banten',
        'semarang' => 'Jawa Tengah', 'solo' => 'Jawa Tengah', 'surakarta' => 'Jawa Tengah', 'magelang' => 'Jawa Tengah', 'klaten' => 'Jawa Tengah', 'purwokerto' => 'Jawa Tengah',
        'surabaya' => 'Jawa Timur', 'malang' => 'Jawa Timur', 'sidoarjo' => 'Jawa Timur', 'kediri' => 'Jawa Timur', 'jember' => 'Jawa Timur',
        'denpasar' => 'Bali',
        'banda aceh' => 'Aceh', 'medan' => 'Sumatera Utara', 'padang' => 'Sumatera Barat', 'pekanbaru' => 'Riau', 'batam' => 'Kepulauan Riau',
        'palembang' => 'Sumatera Selatan', 'pangkal pinang' => 'Kepulauan Bangka Belitung', 'bandar lampung' => 'Lampung',
        'pontianak' => 'Kalimantan Barat', 'palangka raya' => 'Kalimantan Tengah', 'banjarmasin' => 'Kalimantan Selatan',
        'balikpapan' => 'Kalimantan Timur', 'samarinda' => 'Kalimantan Timur', 'tarakan' => 'Kalimantan Utara',
        'makassar' => 'Sulawesi Selatan', 'manado' => 'Sulawesi Utara', 'palu' => 'Sulawesi Tengah', 'kendari' => 'Sulawesi Tenggara', 'mamuju' => 'Sulawesi Barat',
        'mataram' => 'Nusa Tenggara Barat', 'lombok' => 'Nusa Tenggara Barat', 'ntb' => 'Nusa Tenggara Barat',
        'kupang' => 'Nusa Tenggara Timur', 'ntt' => 'Nusa Tenggara Timur',
        'ambon' => 'Maluku', 'ternate' => 'Maluku Utara', 'jayapura' => 'Papua', 'manokwari' => 'Papua Barat',
        'sorong' => 'Papua Barat Daya', 'nabire' => 'Papua Tengah', 'wamena' => 'Papua Pegunungan', 'merauke' => 'Papua Selatan',
    ];

    /**
     * Endpoint utama: POST /chatbot/message
     */
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:300',
        ]);

        $original = trim(preg_replace('/\s+/u', ' ', $validated['message']));
        $text = mb_strtolower($original);

        $response = $this->resolve($text);

        $this->logConversation($request, $original, $response['reply'], $response['intent']);

        return response()->json($response);
    }

    /**
     * Mesin aturan (rule engine): menentukan intent dan menyusun jawaban.
     */
    private function resolve(string $text): array
    {
        // 1) Kode transaksi spesifik, contoh: "status TRX-AB12CD3"
        if (preg_match('/\btrx-[a-z0-9]{4,}\b/i', $text, $m)) {
            return $this->orderDetail(strtoupper($m[0]));
        }

        // 2) Sapaan (hanya jika pesan singkat, mis. "halo" / "selamat pagi kak")
        if (preg_match('/^(halo+|hal+o|hai+|hi+|hello|hey|hei|pagi|siang|sore|malam|selamat (pagi|siang|sore|malam)|assalamu.?alaikum|permisi|p)\b/u', $text)
            && str_word_count(preg_replace('/[^a-z ]/', '', $text)) <= 3) {
            return $this->greeting();
        }

        // 3) Ucapan terima kasih / penutup
        if (preg_match('/\b(terima ?kasih|makasih|makasi|thanks|thank you|thx|tq|oke makasih)\b/u', $text)) {
            return $this->reply('thanks', 'Sama-sama! 😊 Senang bisa membantu. Ada lagi yang bisa MY-Bot bantu?');
        }

        // 4) Bantuan / menu
        if (preg_match('/\b(bantuan|help|menu|bisa apa|fitur|tolong bantu)\b/u', $text)) {
            return $this->help();
        }

        // 5) Perkiraan ongkir ke kota/provinsi tertentu, contoh: "ongkir ke surabaya", "ongkir ke papua 3 kg".
        //    Jika tujuan tidak disebut (dan belum punya alamat), pertanyaan diteruskan ke FAQ pengiriman.
        if (preg_match('/\b(ongkir\w*|ongkos kirim|biaya (kirim|pengiriman))\b/u', $text)
            && ($estimate = $this->shippingEstimate($text))) {
            return $estimate;
        }

        // 6) FAQ dari database (knowledge base yang dikelola admin)
        if ($faq = $this->matchFaq($text)) {
            return $this->reply('faq', $faq->answer, ['meta' => ['topic' => $faq->topic]]);
        }

        // 7) Status / riwayat pesanan
        if (preg_match('/\b(pesanan|order|status|transaksi|riwayat|histori|pembelian saya)\b/u', $text)) {
            return $this->orderHistory();
        }

        // 8) Isi keranjang
        if (preg_match('/\b(keranjang|cart|troli)\b/u', $text)) {
            return $this->cartSummary();
        }

        // 9) Produk dengan batas anggaran: "hp di bawah 5 juta"
        if (preg_match('/\b(di ?bawah|kurang dari|maksimal|maks|max|budget|bujet|under)\b/u', $text)
            && ($budget = $this->parseRupiah($text)) !== null) {
            return $this->productsByBudget($text, $budget);
        }

        // 10) Termurah / termahal
        if (preg_match('/\b(termurah|paling murah|murah)\b/u', $text)) {
            return $this->productsSorted('asc', $text);
        }
        if (preg_match('/\b(termahal|paling mahal|mahal)\b/u', $text)) {
            return $this->productsSorted('desc', $text);
        }

        // 11) Daftar kategori & merek
        if (preg_match('/\b(kategori|category|jenis produk)\b/u', $text)) {
            return $this->listCategories();
        }
        if (preg_match('/\b(merek|merk|brand)\b/u', $text)) {
            return $this->listBrands();
        }

        // 12) Pencarian produk (default untuk kalimat lain)
        $terms = $this->extractTerms($text);
        if (!empty($terms)) {
            $products = $this->searchProducts($terms);
            if ($products->isNotEmpty()) {
                return $this->reply(
                    'product_search',
                    'Saya menemukan ' . $products->count() . ' produk yang cocok dengan "' . implode(' ', $terms) . '":',
                    ['products' => $this->formatProducts($products)]
                );
            }
        } elseif (preg_match('/\b(cari|produk|barang)\b/u', $text)) {
            return $this->reply('product_search', 'Mau cari produk apa? Ketik nama produk, merek, atau kategori. Contoh: "cari laptop" atau "samsung".', [
                'suggestions' => ['Cari laptop', 'Cari iphone', 'Lihat kategori'],
            ]);
        }

        // 13) Fallback — tidak dikenali
        return $this->reply(
            'fallback',
            "Maaf, MY-Bot belum memahami pertanyaan tersebut 🙏\nCoba tanyakan nama produk (misal \"laptop\"), \"produk termurah\", \"cek pesanan saya\", atau ketik \"menu\" untuk melihat semua yang bisa saya bantu."
        );
    }

    /* ======================================================================
     *  HANDLER INTENT
     * ==================================================================== */

    private function greeting(): array
    {
        $name = Auth::check() ? ' ' . Auth::user()->name : '';
        return $this->reply('greeting', "Halo{$name}! 👋 Saya MY-Bot, asisten belanja MY-Store.\nSaya bisa bantu cari produk, cek harga, lihat isi keranjang, dan cek status pesanan. Mau mulai dari mana?");
    }

    private function help(): array
    {
        return $this->reply('help', "Berikut yang bisa MY-Bot bantu:\n• Cari produk — ketik nama/merek, contoh: \"laptop\", \"samsung\"\n• Filter harga — \"hp di bawah 5 juta\"\n• Produk termurah / termahal\n• Daftar kategori & merek\n• Isi keranjang belanja Anda\n• Status pesanan — \"cek pesanan\" atau kode \"TRX-XXXXXXX\"\n• Perkiraan ongkir — \"ongkir ke surabaya\"\n• Info pembayaran, pengiriman, alamat, retur, dan kontak CS");
    }

    /**
     * Perkiraan ongkir dari gudang Sleman ke kota/provinsi yang disebut pengguna.
     * Berat: angka yang disebut di pesan ("2 kg"), atau isi keranjang, atau 1 kg.
     * Mengembalikan null jika tujuan tidak dikenali, agar pertanyaan dijawab FAQ pengiriman.
     */
    private function shippingEstimate(string $text): ?array
    {
        $destination = $this->detectDestination($text);

        // Tanpa tujuan di pesan, gunakan alamat utama pengguna (jika ada)
        if (!$destination && Auth::check()) {
            $address = DB::table('addresses')->where('user_id', Auth::id())->where('is_default', true)->first();
            if ($address) {
                $destination = [$address->province, "{$address->city} (alamat utama Anda)"];
            }
        }

        if (!$destination) {
            return null;
        }

        [$province, $place] = $destination;

        if (preg_match('/(\d+(?:[.,]\d+)?)\s*(kg|kilo|kilogram|gram|gr)\b/u', $text, $w)) {
            $value = (float) str_replace(',', '.', $w[1]);
            $grams = (int) round(in_array($w[2], ['kg', 'kilo', 'kilogram'], true) ? $value * 1000 : $value);
            $weightNote = 'berat yang Anda sebutkan';
        } elseif (Auth::check() && ($cartGrams = $this->cartWeight()) > 0) {
            $grams = $cartGrams;
            $weightNote = 'berat isi keranjang Anda';
        } else {
            $grams = 1000;
            $weightNote = 'contoh berat 1 kg';
        }

        $calculator = app(ShippingCalculator::class);
        $options = $calculator->options($province, max($grams, 1));
        $kg = $calculator->chargeableKg(max($grams, 1));

        $lines = ["Perkiraan ongkir dari Sleman ke {$place}, {$kg} kg ({$weightNote}):"];
        foreach ($options as $opt) {
            $lines[] = "• {$opt['courier']} {$opt['service']}: " . $this->rupiah($opt['cost']) . " (estimasi {$opt['etd']})";
        }
        $lines[] = 'Ongkir pasti dihitung di halaman Keranjang sesuai alamat dan berat barang.';

        return $this->reply('shipping_estimate', implode("\n", $lines), [
            'meta'        => ['province' => $province],
            'suggestions' => ['Ongkir ke Jakarta', 'Ongkir ke Papua', 'Cek pesanan saya', 'Isi keranjang'],
        ]);
    }

    /**
     * Mencari nama provinsi atau kota populer di dalam pesan.
     * Nama terpanjang dicek lebih dulu agar "papua barat daya" tidak terbaca sebagai "papua".
     */
    private function detectDestination(string $text): ?array
    {
        $candidates = [];
        foreach (config('wilayah.provinces') as $province) {
            $candidates[mb_strtolower($province)] = [$province, $province];
        }
        foreach (self::CITY_PROVINCES as $city => $province) {
            $candidates[$city] = [$province, ucwords($city) . " ({$province})"];
        }
        uksort($candidates, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($candidates as $name => $result) {
            if (preg_match('/(?<![\p{L}])' . preg_quote($name, '/') . '(?![\p{L}])/u', $text)) {
                return $result;
            }
        }

        return null;
    }

    private function cartWeight(): int
    {
        return (int) DB::table('cart')
            ->join('products', 'cart.p_id', '=', 'products.product_id')
            ->where('cart.user_id', Auth::id())
            ->sum(DB::raw('products.product_weight * cart.qty'));
    }

    private function matchFaq(string $text): ?object
    {
        $faqs = DB::table('chatbot_faqs')->where('is_active', true)->get();

        $best = null;
        $bestScore = 0;
        foreach ($faqs as $faq) {
            $score = 0;
            foreach (array_filter(array_map('trim', explode(',', mb_strtolower($faq->keywords)))) as $kw) {
                // Pencocokan per kata utuh agar "wa" tidak cocok dengan "wanita"
                if (preg_match('/(?<![\p{L}\p{N}])' . preg_quote($kw, '/') . '(?![\p{L}\p{N}])/u', $text)) {
                    // Frasa yang lebih panjang lebih spesifik → bobot lebih besar
                    $score += mb_strlen($kw);
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        return $best;
    }

    private function orderHistory(): array
    {
        if (!Auth::check()) {
            return $this->loginRequired('melihat status pesanan');
        }

        $transactions = DB::table('orders')
            ->join('products', 'orders.product_id', '=', 'products.product_id')
            ->where('orders.user_id', Auth::id())
            ->groupBy('orders.trx_id', 'orders.p_status')
            ->select(
                'orders.trx_id',
                'orders.p_status',
                DB::raw('SUM(orders.qty) as total_qty'),
                DB::raw('SUM(orders.qty * products.product_price) as total_price'),
                DB::raw('MAX(orders.order_id) as last_id')
            )
            ->orderByDesc('last_id')
            ->limit(5)
            ->get();

        if ($transactions->isEmpty()) {
            return $this->reply('order_status', 'Anda belum memiliki pesanan. Yuk mulai belanja! 🛍️', [
                'suggestions' => ['Produk termurah', 'Lihat kategori'],
            ]);
        }

        // Ongkir tiap transaksi diambil dari data pengiriman saat checkout
        $shipments = DB::table('order_addresses')
            ->where('user_id', Auth::id())
            ->whereIn('trx_id', $transactions->pluck('trx_id'))
            ->get()
            ->keyBy('trx_id');

        $lines = ['Berikut ' . $transactions->count() . ' transaksi terakhir Anda:'];
        foreach ($transactions as $t) {
            $total = $t->total_price + ($shipments->get($t->trx_id)->shipping_cost ?? 0);
            $lines[] = "• {$t->trx_id} — {$t->total_qty} item, " . $this->rupiah($total) . " ({$t->p_status})";
        }
        $lines[] = 'Ketik kode transaksi (misal ' . $transactions->first()->trx_id . ') untuk melihat detailnya.';

        return $this->reply('order_status', implode("\n", $lines), [
            'actions' => [['label' => 'Buka Dashboard Pesanan', 'url' => route('user.dashboard')]],
        ]);
    }

    private function orderDetail(string $trxId): array
    {
        if (!Auth::check()) {
            return $this->loginRequired('mengecek transaksi');
        }

        // Anti-IDOR: transaksi hanya bisa dilihat oleh pemiliknya
        $items = DB::table('orders')
            ->join('products', 'orders.product_id', '=', 'products.product_id')
            ->where('orders.trx_id', $trxId)
            ->where('orders.user_id', Auth::id())
            ->select('products.product_title', 'products.product_price', 'orders.qty', 'orders.p_status')
            ->get();

        if ($items->isEmpty()) {
            return $this->reply('order_detail', "Transaksi {$trxId} tidak ditemukan pada akun Anda. Pastikan kode transaksi sudah benar.", [
                'suggestions' => ['Cek pesanan saya'],
            ]);
        }

        $total = 0;
        $lines = ["Detail transaksi {$trxId} — Status: {$items->first()->p_status}"];
        foreach ($items as $it) {
            $subtotal = $it->product_price * $it->qty;
            $total += $subtotal;
            $lines[] = "• {$it->product_title} × {$it->qty} = " . $this->rupiah($subtotal);
        }

        $shipment = DB::table('order_addresses')
            ->where('trx_id', $trxId)
            ->where('user_id', Auth::id())
            ->first();

        if ($shipment) {
            if ($shipment->courier) {
                $kg = app(ShippingCalculator::class)->chargeableKg((int) $shipment->total_weight);
                $lines[] = "• Ongkir {$shipment->courier} {$shipment->courier_service} ({$kg} kg) = " . $this->rupiah($shipment->shipping_cost);
                $total += $shipment->shipping_cost;
            }
            $lines[] = "Dikirim ke: {$shipment->recipient_name}, {$shipment->district}, {$shipment->city}";
        }
        $lines[] = 'Total: ' . $this->rupiah($total);

        return $this->reply('order_detail', implode("\n", $lines), [
            'actions' => [['label' => 'Buka Dashboard Pesanan', 'url' => route('user.dashboard')]],
        ]);
    }

    private function cartSummary(): array
    {
        if (!Auth::check()) {
            return $this->loginRequired('melihat keranjang belanja');
        }

        $items = DB::table('cart')
            ->join('products', 'cart.p_id', '=', 'products.product_id')
            ->where('cart.user_id', Auth::id())
            ->select('products.product_title', 'products.product_price', 'products.product_weight', 'cart.qty')
            ->get();

        if ($items->isEmpty()) {
            return $this->reply('cart', 'Keranjang belanja Anda masih kosong.', [
                'suggestions' => ['Produk termurah', 'Lihat kategori'],
            ]);
        }

        $total = $items->sum(fn ($i) => $i->product_price * $i->qty);
        $lines = ['Isi keranjang Anda (' . $items->sum('qty') . ' item):'];
        foreach ($items as $it) {
            $lines[] = "• {$it->product_title} × {$it->qty}";
        }
        $lines[] = 'Subtotal: ' . $this->rupiah($total) . ' (belum termasuk ongkir)';

        // Perkiraan ongkir termurah ke alamat utama, jika pengguna sudah punya alamat
        $address = DB::table('addresses')->where('user_id', Auth::id())->where('is_default', true)->first();
        $grams = (int) $items->sum(fn ($i) => ($i->product_weight ?? 1000) * $i->qty);
        $cheapest = $address ? (app(ShippingCalculator::class)->options($address->province, $grams)[0] ?? null) : null;

        $lines[] = $cheapest
            ? "Ongkir ke {$address->city} mulai " . $this->rupiah($cheapest['cost']) . " ({$cheapest['courier']} {$cheapest['service']}). Kurir bisa dipilih di halaman Keranjang."
            : 'Ongkir dihitung setelah Anda memilih alamat dan kurir di halaman Keranjang.';

        return $this->reply('cart', implode("\n", $lines), [
            'actions' => [['label' => 'Buka Keranjang & Checkout', 'url' => route('cart.index')]],
        ]);
    }

    private function productsByBudget(string $text, int $budget): array
    {
        $terms = $this->extractTerms(preg_replace('/\d[\d.,]*\s*(juta|jt|ribu|rb|k)?/u', ' ', $text));

        $query = $this->productQuery()->where('products.product_price', '<=', $budget);
        if (!empty($terms)) {
            $this->applyTermFilter($query, $terms);
        }
        $products = $query->orderByDesc('products.product_price')->limit(5)->get();

        if ($products->isEmpty()) {
            return $this->reply('product_budget', 'Belum ada produk' . (empty($terms) ? '' : ' "' . implode(' ', $terms) . '"') . ' dengan harga di bawah ' . $this->rupiah($budget) . '.', [
                'suggestions' => ['Produk termurah', 'Lihat kategori'],
            ]);
        }

        return $this->reply('product_budget', 'Produk' . (empty($terms) ? '' : ' "' . implode(' ', $terms) . '"') . ' dengan harga maksimal ' . $this->rupiah($budget) . ':', [
            'products' => $this->formatProducts($products),
        ]);
    }

    private function productsSorted(string $direction, string $text): array
    {
        $terms = $this->extractTerms($text);

        $query = $this->productQuery();
        if (!empty($terms)) {
            $this->applyTermFilter($query, $terms);
        }
        $products = $query->orderBy('products.product_price', $direction)->limit(3)->get();

        if ($products->isEmpty()) {
            return $this->reply('product_sorted', 'Produk yang Anda cari belum tersedia.');
        }

        $label = $direction === 'asc' ? 'termurah' : 'termahal';
        return $this->reply('product_sorted', 'Berikut produk ' . $label . (empty($terms) ? '' : ' untuk "' . implode(' ', $terms) . '"') . ' di MY-Store:', [
            'products' => $this->formatProducts($products),
        ]);
    }

    private function listCategories(): array
    {
        $cats = DB::table('categories')
            ->leftJoin('products', 'categories.cat_id', '=', 'products.product_cat')
            ->groupBy('categories.cat_id', 'categories.cat_title')
            ->select('categories.cat_id', 'categories.cat_title', DB::raw('COUNT(products.product_id) as total'))
            ->orderBy('categories.cat_title')
            ->get();

        $lines = ['Kategori produk di MY-Store:'];
        foreach ($cats as $c) {
            $lines[] = "• {$c->cat_title} ({$c->total} produk)";
        }

        return $this->reply('categories', implode("\n", $lines), [
            'suggestions' => $cats->where('total', '>', 0)->take(4)->map(fn ($c) => 'Cari ' . $c->cat_title)->values()->all(),
        ]);
    }

    private function listBrands(): array
    {
        $brands = DB::table('brands')->orderBy('brand_title')->pluck('brand_title');

        return $this->reply('brands', "Merek yang tersedia di MY-Store:\n• " . $brands->implode("\n• "), [
            'suggestions' => $brands->take(4)->map(fn ($b) => 'Cari ' . $b)->values()->all(),
        ]);
    }

    /* ======================================================================
     *  UTILITAS
     * ==================================================================== */

    private function productQuery()
    {
        return DB::table('products')
            ->leftJoin('categories', 'products.product_cat', '=', 'categories.cat_id')
            ->leftJoin('brands', 'products.product_brand', '=', 'brands.brand_id')
            ->select('products.*', 'categories.cat_title', 'brands.brand_title');
    }

    private function expandTerms(array $terms): array
    {
        $expanded = $terms;
        foreach ($terms as $t) {
            $expanded = array_merge($expanded, self::SYNONYMS[$t] ?? []);
        }
        return array_values(array_unique($expanded));
    }

    private function applyTermFilter($query, array $terms): void
    {
        $terms = $this->expandTerms($terms);
        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $like = '%' . $term . '%';
                $q->orWhere('products.product_title', 'LIKE', $like)
                  ->orWhere('products.product_keywords', 'LIKE', $like)
                  ->orWhere('categories.cat_title', 'LIKE', $like)
                  ->orWhere('brands.brand_title', 'LIKE', $like);
            }
        });
    }

    /**
     * Cari produk berdasarkan beberapa kata kunci, lalu urutkan berdasarkan
     * jumlah kata yang cocok (relevansi sederhana).
     */
    private function searchProducts(array $terms)
    {
        $query = $this->productQuery();
        $this->applyTermFilter($query, $terms);

        $terms = $this->expandTerms($terms);

        return $query->limit(30)->get()
            ->map(function ($p) use ($terms) {
                $haystack = mb_strtolower("{$p->product_title} {$p->product_keywords} {$p->cat_title} {$p->brand_title}");
                $p->score = collect($terms)->filter(fn ($t) => str_contains($haystack, $t))->count()
                    + collect($terms)->filter(fn ($t) => str_contains(mb_strtolower($p->product_title), $t))->count();
                return $p;
            })
            ->sortByDesc('score')
            ->take(5)
            ->values();
    }

    private function extractTerms(string $text): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_filter($words, function ($w) {
            return mb_strlen($w) >= 2 && !in_array($w, self::STOPWORDS, true);
        })));
    }

    /**
     * Mengubah teks nominal menjadi angka rupiah.
     * Contoh: "5 juta" → 5000000, "500rb" → 500000, "1.5jt" → 1500000, "750000" → 750000
     */
    private function parseRupiah(string $text): ?int
    {
        if (!preg_match_all('/(\d+(?:[.,]\d+)*)\s*(juta|jt|ribu|rb|k)?\b/u', $text, $all, PREG_SET_ORDER)) {
            return null;
        }

        // Utamakan angka yang memiliki satuan (juta/ribu/k); jika tidak ada, ambil angka terakhir
        $withUnit = array_values(array_filter($all, fn ($x) => !empty($x[2])));
        $m = $withUnit[0] ?? end($all);

        $unit = $m[2] ?? '';
        $number = $m[1];

        if ($unit === '') {
            // "5.000.000" atau "5000000" → buang pemisah ribuan
            $value = (float) str_replace(['.', ','], '', $number);
        } else {
            // "1,5 juta" / "1.5 jt" → desimal
            $value = (float) str_replace(',', '.', $number);
            $value *= in_array($unit, ['juta', 'jt'], true) ? 1_000_000 : 1_000;
        }

        return $value > 0 ? (int) round($value) : null;
    }

    private function formatProducts($products): array
    {
        return collect($products)->map(function ($p) {
            $image = function_exists('getProductImageUrl')
                ? getProductImageUrl($p->product_image, $p->product_title, $p->product_cat)
                : asset('product_images/' . $p->product_image);

            return [
                'id'       => $p->product_id,
                'title'    => $p->product_title,
                'price'    => $this->rupiah($p->product_price),
                'category' => $p->cat_title,
                'brand'    => $p->brand_title,
                'image'    => $image,
                'url'      => route('home', ['q' => $p->product_title]),
            ];
        })->values()->all();
    }

    private function loginRequired(string $purpose): array
    {
        return $this->reply('login_required', "Silakan login terlebih dahulu untuk {$purpose}. 🔒", [
            'actions' => [
                ['label' => 'Masuk', 'url' => route('login')],
                ['label' => 'Daftar Akun', 'url' => route('register')],
            ],
        ]);
    }

    private function rupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    private function reply(string $intent, string $reply, array $extra = []): array
    {
        return array_merge([
            'intent'      => $intent,
            'reply'       => $reply,
            'products'    => [],
            'actions'     => [],
            'suggestions' => self::DEFAULT_SUGGESTIONS,
        ], $extra);
    }

    private function logConversation(Request $request, string $message, string $reply, string $intent): void
    {
        try {
            DB::table('chat_logs')->insert([
                'user_id'    => Auth::id(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : 'no-session',
                'message'    => $message,
                'reply'      => $reply,
                'intent'     => $intent,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Chatbot tetap berjalan walau pencatatan log gagal (misal tabel belum dibuat)
            report($e);
        }
    }
}
