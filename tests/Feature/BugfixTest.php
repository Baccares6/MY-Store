<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Test regresi untuk bug yang ditemukan saat pengecekan menyeluruh MY-Store,
 * agar bug yang sama tidak muncul kembali.
 */
class BugfixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('categories')->insert([
            ['cat_id' => 1, 'cat_title' => 'Electronics'],
            ['cat_id' => 2, 'cat_title' => 'Ladies Wears'],
        ]);
        DB::table('brands')->insert(['brand_id' => 1, 'brand_title' => 'Apple']);
        DB::table('products')->insert([
            ['product_id' => 1, 'product_cat' => 1, 'product_brand' => 1, 'product_title' => 'iPhone 5s', 'product_price' => 1850000,
             'product_desc' => 'iphone', 'product_image' => 'iphone.png', 'product_keywords' => 'iphone', 'product_weight' => 300],
            ['product_id' => 2, 'product_cat' => 2, 'product_brand' => 1, 'product_title' => 'Floral Dress', 'product_price' => 320000,
             'product_desc' => 'dress', 'product_image' => 'dress.png', 'product_keywords' => 'dress', 'product_weight' => 300],
        ]);
    }

    private function user(string $name = 'pembeli', string $email = 'pembeli@example.com', bool $admin = false): User
    {
        return User::create(['name' => $name, 'email' => $email, 'password' => Hash::make('password123'), 'is_admin' => $admin]);
    }

    /* ---------- Admin ---------- */

    public function test_admin_can_clear_optional_product_fields_when_editing(): void
    {
        $this->actingAs($this->user('admin', 'admin@example.com', true));

        $this->post('/admin/products/1/update', [
            'product_title' => 'iPhone 5s Baru', 'product_cat' => 1, 'product_brand' => 1, 'product_price' => 1900000,
            'product_weight' => 300, 'product_desc' => '', 'product_image' => '', 'product_keywords' => '',
        ])->assertRedirect(route('admin.dashboard'))->assertSessionHas('success');

        // Kolom kosong diisi nilai bawaan, bukan NULL (kolom di MySQL tidak boleh NULL)
        $this->assertDatabaseHas('products', [
            'product_id' => 1,
            'product_desc' => 'iPhone 5s Baru',
            'product_image' => '',
            'product_keywords' => 'iphone 5s baru',
        ]);
    }

    public function test_admin_actions_on_missing_items_report_not_found(): void
    {
        $this->actingAs($this->user('admin', 'admin@example.com', true));
        $product = [
            'product_title' => 'X', 'product_cat' => 1, 'product_brand' => 1, 'product_price' => 1000, 'product_weight' => 100,
        ];

        $this->post('/admin/products/999/update', $product)->assertSessionHas('error');
        $this->post('/admin/products/999/delete')->assertSessionHas('error');
        $this->post('/admin/chatbot/faqs/999/delete')->assertSessionHas('error');
    }

    /* ---------- Keranjang ---------- */

    public function test_cart_add_is_limited_to_99_per_product(): void
    {
        $user = $this->user();
        $this->actingAs($user);

        // Jumlah di luar batas ditolak dengan pesan yang jelas
        $this->post('/cart/add', ['product_id' => 1, 'qty' => 1000])->assertSessionHas('error', 'Jumlah produk maksimal 99 unit.');
        $this->assertDatabaseCount('cart', 0);

        // Penambahan berulang dibatasi menjadi 99
        $this->post('/cart/add', ['product_id' => 1, 'qty' => 60]);
        $this->post('/cart/add', ['product_id' => 1, 'qty' => 60])->assertSessionHas('success');
        $this->assertDatabaseHas('cart', ['user_id' => $user->id, 'p_id' => 1, 'qty' => 99]);

        // Sudah 99, tambah lagi ditolak
        $this->post('/cart/add', ['product_id' => 1, 'qty' => 1])->assertSessionHas('error');
        $this->assertDatabaseHas('cart', ['user_id' => $user->id, 'p_id' => 1, 'qty' => 99]);
    }

    public function test_invalid_cart_quantity_update_shows_a_message(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $this->post('/cart/add', ['product_id' => 1, 'qty' => 2]);
        $cartId = DB::table('cart')->where('user_id', $user->id)->value('id');

        $this->post('/cart/update', ['cart_id' => $cartId, 'qty' => 0])
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, 'minimal 1'));
        $this->post('/cart/update', ['cart_id' => $cartId, 'qty' => 150])
            ->assertSessionHas('error', 'Jumlah produk maksimal 99 unit.');

        $this->assertDatabaseHas('cart', ['id' => $cartId, 'qty' => 2]);
    }

    /* ---------- Login & Registrasi ---------- */

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $this->user();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'pembeli', 'password' => 'salah']);
        }

        // Percobaan ke-6 ditolak walaupun password benar
        $this->post('/login', ['email' => 'pembeli', 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->assertStringStartsWith('Terlalu banyak percobaan login', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_username_cannot_contain_at_sign_but_old_ones_can_still_log_in(): void
    {
        $this->post('/register', [
            'name' => 'budi@toko.id', 'email' => 'budi@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertSessionHasErrors('name');
        $this->assertGuest();

        // Akun lama yang username-nya terlanjur berbentuk email tetap bisa login dengan username tersebut
        $this->user('lama@toko.id', 'lain@example.com');
        $this->post('/login', ['email' => 'lama@toko.id', 'password' => 'password123'])->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_guest_gets_matching_message_and_returns_to_intended_page_after_login(): void
    {
        $this->user();

        $this->get('/user-dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Silakan masuk ke akun Anda terlebih dahulu untuk membuka Dashboard.');

        $this->post('/login', ['email' => 'pembeli', 'password' => 'password123'])
            ->assertRedirect(route('user.dashboard'));
    }

    public function test_login_does_not_take_over_leftover_guest_cart_rows(): void
    {
        DB::table('cart')->insert(['p_id' => 2, 'ip_add' => '127.0.0.1', 'user_id' => null, 'qty' => 3]);
        $user = $this->user();

        $this->post('/login', ['email' => 'pembeli', 'password' => 'password123']);

        $this->assertDatabaseMissing('cart', ['user_id' => $user->id]);
    }

    /* ---------- Beranda & Dashboard ---------- */

    public function test_home_ignores_malformed_filters_instead_of_crashing(): void
    {
        $this->get('/?q[]=a')->assertOk()->assertSee('iPhone 5s');
        $this->get('/?category=abc')->assertOk()->assertSee('iPhone 5s')->assertSee('Floral Dress');
        $this->get('/?category=2')->assertOk()->assertSee('Floral Dress')->assertDontSee('iPhone 5s');
    }

    public function test_dashboard_counts_transactions_not_item_rows(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $addressId = $this->createAddressFor($user->id);

        $this->post('/cart/add', ['product_id' => 1, 'qty' => 1]);
        $this->post('/cart/add', ['product_id' => 2, 'qty' => 2]);
        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'jne_reg', 'payment_method' => 'bank_bca']);

        $this->get('/user-dashboard')
            ->assertViewHas('total_orders', 1)
            ->assertViewHas('total_items', 3);
    }
}
