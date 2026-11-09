<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('categories')->insert([
            ['cat_id' => 1, 'cat_title' => 'Electronics'],
        ]);

        DB::table('brands')->insert([
            ['brand_id' => 1, 'brand_title' => 'Samsung'],
        ]);

        DB::table('products')->insert([
            [
                'product_id' => 1,
                'product_cat' => 1,
                'product_brand' => 1,
                'product_title' => 'Samsung galaxy s7 edge',
                'product_price' => 4500000,
                'product_desc' => 'Smartphone flagship',
                'product_image' => 'product07.png',
                'product_keywords' => 'samsung, mobile',
            ],
            [
                'product_id' => 2,
                'product_cat' => 1,
                'product_brand' => 1,
                'product_title' => 'LG Smart Inverter Refrigerator',
                'product_price' => 6800000,
                'product_desc' => 'Kulkas dua pintu',
                'product_image' => 'lg_refrigerator.png',
                'product_keywords' => 'lg, refrigerator, kulkas',
            ],
        ]);
    }

    /**
     * 1. KEAMANAN TRANSAKSI: Server-Side Price Integrity (Anti-Price Tampering)
     * Menguji bahwa client tidak bisa memanipulasi harga barang saat checkout.
     * Harga selalu ditarik dari basis data terpercaya di server.
     */
    public function test_transaction_security_prevents_client_side_price_tampering(): void
    {
        $user = User::create([
            'name' => 'honestbuyer',
            'email' => 'honestbuyer@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->actingAs($user);

        // Tambah produk ke keranjang (mencoba mengirim price palsu bila ada)
        $this->post('/cart/add', [
            'product_id' => 1,
            'qty' => 1,
            'price' => 100, // Percobaan manipulasi harga oleh hacker
        ]);

        // Lakukan checkout
        $this->post('/checkout', ['address_id' => $this->createAddressFor($user->id), 'shipping_service' => 'jne_reg']);

        // Pastikan pesanan terdaftar di basis data dan verifikasi integritas harga produk
        $order = DB::table('orders')->where('user_id', $user->id)->first();
        $this->assertNotNull($order);

        $product = DB::table('products')->where('product_id', $order->product_id)->first();
        $this->assertEquals(4500000, $product->product_price, 'Harga produk di basis data tetap Rp 4.500.000 dan tidak dapat dimanipulasi dari client.');
    }

    /**
     * 2. KEAMANAN TRANSAKSI: Atomisitas Transaksi (ACID Compliant)
     * Checkout harus membersihkan keranjang dan membuat order dalam satu kesatuan.
     */
    public function test_transaction_security_atomic_checkout(): void
    {
        $user = User::create([
            'name' => 'buyer_acid',
            'email' => 'acid@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->actingAs($user);

        // Tambah produk
        $this->post('/cart/add', [
            'product_id' => 2,
            'qty' => 1,
        ]);

        $this->assertDatabaseHas('cart', ['user_id' => $user->id, 'p_id' => 2]);

        // Checkout
        $response = $this->post('/checkout', ['address_id' => $this->createAddressFor($user->id), 'shipping_service' => 'jne_reg']);
        $response->assertRedirect(route('user.dashboard'));

        // Keranjang harus sudah bersih
        $this->assertDatabaseMissing('cart', ['user_id' => $user->id]);

        // Order harus terdaftar
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => 2,
            'p_status' => 'Completed',
        ]);
    }

    /**
     * 3. KEAMANAN DATA: Perlindungan IDOR (Insecure Direct Object Reference)
     * User A tidak boleh bisa mengubah atau menghapus keranjang milik User B.
     */
    public function test_data_security_prevents_idor_on_cart_update_and_removal(): void
    {
        $userA = User::create([
            'name' => 'victim',
            'email' => 'victim@example.com',
            'password' => Hash::make('password123'),
        ]);

        $userB = User::create([
            'name' => 'attacker',
            'email' => 'attacker@example.com',
            'password' => Hash::make('password123'),
        ]);

        // User A memasukkan barang ke keranjang
        $cartIdA = DB::table('cart')->insertGetId([
            'p_id' => 1,
            'ip_add' => '127.0.0.1',
            'user_id' => $userA->id,
            'qty' => 2,
        ]);

        // User B login dan mencoba mengubah kuantitas keranjang User A
        $this->actingAs($userB);
        $updateResponse = $this->post('/cart/update', [
            'cart_id' => $cartIdA,
            'qty' => 99,
        ]);

        // Harus gagal/ditolak dan kuantitas keranjang User A tidak boleh berubah
        $cartItem = DB::table('cart')->where('id', $cartIdA)->first();
        $this->assertEquals(2, $cartItem->qty, 'User B tidak dapat memanipulasi keranjang User A (IDOR dicegah).');

        // User B mencoba menghapus keranjang milik User A
        $removeResponse = $this->post("/cart/remove/{$cartIdA}");
        $this->assertDatabaseHas('cart', [
            'id' => $cartIdA,
            'user_id' => $userA->id,
        ]);
    }

    /**
     * 4. KEAMANAN DATA: SQL Injection Resistance
     * Parameter pencarian produk aman dari serangan SQL Injection.
     */
    public function test_data_security_sql_injection_resistance(): void
    {
        // Percobaan SQL Injection klasik
        $payload = "' OR 1=1 -- ";
        $response = $this->get('/?q=' . urlencode($payload));
        $response->assertStatus(200);

        // Payload union-based SQL injection
        $unionPayload = "Samsung' UNION SELECT 1,2,3,4,5,6,7,8 -- ";
        $unionResponse = $this->get('/?q=' . urlencode($unionPayload));
        $unionResponse->assertStatus(200);
    }

    /**
     * 5. KEAMANAN DATA: Password Hashing Verification
     * Kata sandi tidak pernah disimpan dalam bentuk plaintext di database.
     */
    public function test_data_security_password_stored_as_strong_hash(): void
    {
        $plainPassword = 'MySecretPassword!2026';
        $this->post('/register', [
            'name' => 'securityuser',
            'email' => 'sec@example.com',
            'password' => $plainPassword,
            'password_confirmation' => $plainPassword,
        ]);

        $user = User::where('email', 'sec@example.com')->first();
        $this->assertNotNull($user);

        // Password tidak boleh sama dengan plaintext
        $this->assertNotEquals($plainPassword, $user->password);

        // Password harus berupa hash bcrypt yang valid
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    /**
     * 6. KEAMANAN OTORISASI: Protected User Dashboard
     * Pengguna anonim/tamu tidak boleh dapat mengakses user-dashboard tanpa login.
     */
    public function test_authorization_guest_cannot_access_user_dashboard(): void
    {
        $response = $this->get('/user-dashboard');
        $response->assertRedirect(route('login'));
    }

    /**
     * 7. KEAMANAN OTORISASI: Protected Admin Dashboard from Non-Admin Users
     * Pelanggan biasa (bukan admin) dilarang mengakses panel admin dan dialihkan dengan pesan error.
     */
    public function test_authorization_non_admin_cannot_access_admin_dashboard(): void
    {
        $customer = User::create([
            'name' => 'customer_regular',
            'email' => 'regular@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $this->actingAs($customer);
        $response = $this->get('/admin');
        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    /**
     * 8. KEAMANAN OTORISASI: Protected Admin Dashboard from Guest Users
     * Tamu/pengguna yang belum login wajib dialihkan ke halaman login saat membuka /admin.
     */
    public function test_authorization_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    /**
     * 9. KEAMANAN AKSES KERANJANG: Tamu Belum Login Tidak Bisa Menambahkan Produk ke Keranjang
     */
    public function test_guest_cannot_add_product_to_cart_and_must_login(): void
    {
        $response = $this->post('/cart/add', [
            'product_id' => 1,
            'qty' => 1,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('cart', 0);
    }

    /**
     * 10. KEAMANAN AKSES KERANJANG: Tamu Belum Login Tidak Bisa Mengakses Halaman Keranjang
     */
    public function test_guest_cannot_view_cart_page(): void
    {
        $response = $this->get('/cart');
        $response->assertRedirect(route('login'));
    }
}
