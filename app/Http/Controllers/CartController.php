<?php

namespace App\Http\Controllers;

use App\Services\ShippingCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartController extends Controller
{
    // Batas jumlah per produk di keranjang (berlaku saat tambah maupun ubah jumlah)
    private const MAX_QTY = 99;

    private function qtyMessages(): array
    {
        return [
            'qty.required'       => 'Jumlah produk wajib diisi.',
            'qty.integer'        => 'Jumlah produk harus berupa angka.',
            'qty.min'            => 'Jumlah produk minimal 1. Gunakan tombol hapus untuk mengeluarkan produk dari keranjang.',
            'qty.max'            => 'Jumlah produk maksimal ' . self::MAX_QTY . ' unit.',
            'product_id.exists'  => 'Produk tidak ditemukan.',
            'cart_id.exists'     => 'Item keranjang tidak ditemukan.',
        ];
    }

    /**
     * Menampilkan isi keranjang belanja
     */
    public function index(Request $request, ShippingCalculator $shipping)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Anda terlebih dahulu untuk melihat keranjang belanja.');
        }

        $userId = Auth::id();

        $cartItems = DB::table('cart')
            ->join('products', 'cart.p_id', '=', 'products.product_id')
            ->leftJoin('categories', 'products.product_cat', '=', 'categories.cat_id')
            ->leftJoin('brands', 'products.product_brand', '=', 'brands.brand_id')
            ->select(
                'cart.id as cart_id',
                'cart.p_id',
                'cart.qty',
                'products.product_title',
                'products.product_price',
                'products.product_image',
                'products.product_weight',
                'categories.cat_title',
                'brands.brand_title'
            )
            ->where('cart.user_id', $userId)
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product_price * $item->qty;
        });

        $totalItems = $cartItems->sum('qty');
        $totalWeight = $this->totalWeight($cartItems);

        // Buku alamat pengguna, alamat utama ditampilkan paling atas
        $addresses = DB::table('addresses')
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        // Alamat terpilih: pilihan terakhir pengguna, alamat yang baru ditambahkan, atau alamat utama
        $selectedAddressId = (int) (old('address_id') ?: session('selected_address_id'));
        if (!$addresses->contains('id', $selectedAddressId)) {
            $selectedAddressId = (int) optional($addresses->first())->id;
        }
        $provinces = config('wilayah.provinces');

        // Pilihan kurir untuk setiap alamat, supaya daftar kurir bisa langsung berganti saat alamat diganti
        $shippingOptions = $addresses->mapWithKeys(fn ($addr) => [
            $addr->id => $shipping->options($addr->province, $totalWeight),
        ]);
        $chargeableKg = $shipping->chargeableKg($totalWeight);
        $selectedShipping = old('shipping_service');

        return view('cart', compact(
            'cartItems', 'subtotal', 'totalItems', 'addresses', 'selectedAddressId', 'provinces',
            'shippingOptions', 'totalWeight', 'chargeableKg', 'selectedShipping'
        ));
    }

    /**
     * Total berat kirim isi keranjang dalam gram
     */
    private function totalWeight($items): int
    {
        return (int) $items->sum(fn ($item) => ($item->product_weight ?? 1000) * $item->qty);
    }

    /**
     * Menambahkan produk ke keranjang belanja (Wajib Login)
     */
    public function add(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan masuk ke akun Anda terlebih dahulu sebelum memasukkan produk ke keranjang belanja.');
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,product_id',
            'qty'        => 'nullable|integer|min:1|max:' . self::MAX_QTY,
        ], $this->qtyMessages());

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $productId = (int) $request->input('product_id');
        $qty = (int) $request->input('qty', 1);
        $userId = Auth::id();
        $ipAdd = $request->ip();

        // Cari item di keranjang milik user yang sedang aktif
        $existing = DB::table('cart')
            ->where('p_id', $productId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->qty >= self::MAX_QTY) {
                return redirect()->back()->with('error', 'Jumlah produk ini di keranjang sudah mencapai batas ' . self::MAX_QTY . ' unit.');
            }

            // Jumlah total per produk dibatasi, sama dengan batas saat mengubah jumlah di keranjang
            $newQty = min($existing->qty + $qty, self::MAX_QTY);
            DB::table('cart')
                ->where('id', $existing->id)
                ->where('user_id', $userId)
                ->update(['qty' => $newQty]);

            if ($newQty < $existing->qty + $qty) {
                return redirect()->back()->with('success', 'Produk ditambahkan. Jumlah maksimal per produk adalah ' . self::MAX_QTY . ' unit, jadi jumlahnya disesuaikan menjadi ' . self::MAX_QTY . '.');
            }
        } else {
            DB::table('cart')->insert([
                'p_id'    => $productId,
                'ip_add'  => $ipAdd,
                'user_id' => $userId,
                'qty'     => $qty,
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang belanja!');
    }

    /**
     * Memperbarui jumlah item di keranjang (Dilindungi dari IDOR & Wajib Login)
     */
    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|integer|exists:cart,id',
            'qty'     => 'required|integer|min:1|max:' . self::MAX_QTY,
        ], $this->qtyMessages());

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        $cartId = (int) $request->input('cart_id');
        $qty = (int) $request->input('qty');
        $userId = Auth::id();

        // Keamanan Data: Pastikan item keranjang benar-benar milik pengguna yang sedang aktif (Cegah IDOR)
        $affected = DB::table('cart')
            ->where('id', $cartId)
            ->where('user_id', $userId)
            ->update(['qty' => $qty]);

        if ($affected === 0) {
            return redirect()->back()->with('error', 'Akses tidak sah: Anda tidak memiliki izin untuk mengubah item keranjang ini.');
        }

        return redirect()->back()->with('success', 'Jumlah produk berhasil diperbarui!');
    }

    /**
     * Menghapus item dari keranjang (Dilindungi dari IDOR & Wajib Login)
     */
    public function remove(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = Auth::id();

        // Keamanan Data: Pastikan item keranjang benar-benar milik pengguna yang sedang aktif (Cegah IDOR)
        $deleted = DB::table('cart')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();

        if ($deleted === 0) {
            return redirect()->back()->with('error', 'Akses tidak sah: Anda tidak memiliki izin untuk menghapus item keranjang ini.');
        }

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Melakukan checkout pesanan dengan Transaksi Basis Data Atomik (ACID Compliant)
     * Memindahkan item cart ke orders dalam DB::transaction(), memicu DBMS Trigger trg_log_order_insert!
     */
    public function checkout(Request $request, ShippingCalculator $shipping)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk menyelesaikan pesanan (Checkout).');
        }

        $userId = Auth::id();

        // Alamat pengiriman wajib dipilih dan harus milik pengguna yang sedang login (Cegah IDOR)
        $address = DB::table('addresses')
            ->where('id', (int) $request->input('address_id'))
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return redirect()->route('cart.index')->with('error', 'Pilih alamat pengiriman terlebih dahulu sebelum checkout.');
        }

        // Eksekusi checkout dalam transaksi database terisolasi untuk menjamin integritas data (ACID)
        return DB::transaction(function () use ($userId, $address, $request, $shipping) {
            $cartItems = DB::table('cart')
                ->join('products', 'cart.p_id', '=', 'products.product_id')
                ->where('cart.user_id', $userId)
                ->select('cart.*', 'products.product_price', 'products.product_weight')
                ->lockForUpdate() // Mencegah race condition (double checkout)
                ->get();

            if ($cartItems->isEmpty()) {
                return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda masih kosong.');
            }

            // Ongkir dihitung ulang di server dari berat & alamat tujuan (Anti-Price Tampering)
            $totalWeight = $this->totalWeight($cartItems);
            $quote = $shipping->quote((string) $request->input('shipping_service'), $address->province, $totalWeight);

            if (!$quote) {
                return redirect()->route('cart.index')
                    ->withInput(['address_id' => $address->id])
                    ->with('error', 'Pilih kurir pengiriman yang tersedia untuk alamat tujuan Anda.');
            }

            $trxId = 'TRX-' . strtoupper(Str::random(7));

            // Insert ke tabel orders satu per satu agar trigger DBMS berjalan untuk setiap pesanan
            foreach ($cartItems as $item) {
                DB::table('orders')->insert([
                    'user_id'    => $userId,
                    'product_id' => $item->p_id,
                    'qty'        => $item->qty,
                    'trx_id'     => $trxId,
                    'p_status'   => 'Completed',
                ]);
            }

            // Simpan salinan alamat pengiriman untuk transaksi ini
            DB::table('order_addresses')->insert([
                'trx_id'         => $trxId,
                'user_id'        => $userId,
                'recipient_name' => $address->recipient_name,
                'phone'          => $address->phone,
                'province'       => $address->province,
                'city'           => $address->city,
                'district'       => $address->district,
                'postal_code'    => $address->postal_code,
                'address_detail' => $address->address_detail,
                'courier'        => $quote['courier'],
                'courier_service' => $quote['service'],
                'shipping_etd'   => $quote['etd'],
                'total_weight'   => $totalWeight,
                'shipping_cost'  => $quote['cost'],
            ]);

            // Kosongkan keranjang belanja pengguna setelah seluruh pesanan berhasil dibuat
            DB::table('cart')->where('user_id', $userId)->delete();

            return redirect()->route('user.dashboard')->with('success', "Checkout berhasil! Transaksi $trxId akan dikirim dengan {$quote['courier']} {$quote['service']} ke alamat {$address->recipient_name}, {$address->city}.");
        });
    }
}
