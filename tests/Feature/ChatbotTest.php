<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('categories')->insert([
            ['cat_id' => 1, 'cat_title' => 'Electronics'],
            ['cat_id' => 2, 'cat_title' => 'Ladies Wears'],
        ]);
        DB::table('brands')->insert([
            ['brand_id' => 1, 'brand_title' => 'HP'],
            ['brand_id' => 2, 'brand_title' => 'Samsung'],
        ]);
        DB::table('products')->insert([
            ['product_id' => 1, 'product_cat' => 1, 'product_brand' => 2, 'product_title' => 'Samsung Galaxy S7', 'product_price' => 4500000, 'product_desc' => 'HP', 'product_image' => 'product07.png', 'product_keywords' => 'samsung mobile electronics'],
            ['product_id' => 2, 'product_cat' => 1, 'product_brand' => 1, 'product_title' => 'HP Pavilion 15 Laptop', 'product_price' => 13500000, 'product_desc' => 'Laptop', 'product_image' => 'laptop.png', 'product_keywords' => 'hp, laptop, notebook'],
            ['product_id' => 3, 'product_cat' => 2, 'product_brand' => 1, 'product_title' => 'Floral Dress', 'product_price' => 320000, 'product_desc' => 'Dress', 'product_image' => 'dress.png', 'product_keywords' => 'dress, baju, wanita'],
        ]);
    }

    private function ask(string $message)
    {
        return $this->postJson('/chatbot/message', ['message' => $message])->assertOk();
    }

    public function test_greeting_and_logging(): void
    {
        $this->ask('halo')->assertJson(['intent' => 'greeting']);
        $this->assertDatabaseHas('chat_logs', ['message' => 'halo', 'intent' => 'greeting', 'user_id' => null]);
    }

    public function test_product_search_returns_matching_products(): void
    {
        $res = $this->ask('cari laptop dong')->assertJson(['intent' => 'product_search']);
        $this->assertSame('HP Pavilion 15 Laptop', $res->json('products.0.title'));
    }

    public function test_cheapest_and_budget_filter(): void
    {
        $this->ask('produk termurah')->assertJsonPath('products.0.title', 'Floral Dress');

        $res = $this->ask('hp di bawah 5 juta')->assertJson(['intent' => 'product_budget']);
        $titles = collect($res->json('products'))->pluck('title');
        $this->assertTrue($titles->contains('Samsung Galaxy S7'));
        $this->assertFalse($titles->contains('HP Pavilion 15 Laptop'));
    }

    public function test_faq_from_database(): void
    {
        $this->ask('cara pembayaran gimana?')
            ->assertJson(['intent' => 'faq'])
            ->assertJsonPath('meta.topic', 'Metode Pembayaran');
    }

    public function test_guest_must_login_to_check_orders(): void
    {
        $this->ask('cek pesanan saya')->assertJson(['intent' => 'login_required']);
    }

    public function test_order_status_is_scoped_to_owner(): void
    {
        $owner = User::create(['name' => 'budi', 'email' => 'budi@example.com', 'password' => bcrypt('secret123')]);
        $other = User::create(['name' => 'ani', 'email' => 'ani@example.com', 'password' => bcrypt('secret123')]);

        DB::table('orders')->insert([
            'user_id' => $owner->id, 'product_id' => 1, 'qty' => 2, 'trx_id' => 'TRX-ABC1234', 'p_status' => 'Completed',
        ]);

        $this->actingAs($owner);
        $this->ask('status TRX-ABC1234')
            ->assertJson(['intent' => 'order_detail'])
            ->assertSee('Rp 9.000.000');

        // Anti-IDOR: pengguna lain tidak boleh melihat transaksi tersebut
        $this->actingAs($other);
        $res = $this->ask('status TRX-ABC1234');
        $this->assertStringContainsString('tidak ditemukan', $res->json('reply'));
    }

    public function test_cart_summary_for_logged_in_user(): void
    {
        $user = User::create(['name' => 'citra', 'email' => 'citra@example.com', 'password' => bcrypt('secret123')]);
        DB::table('cart')->insert(['p_id' => 3, 'ip_add' => '127.0.0.1', 'user_id' => $user->id, 'qty' => 2]);

        $this->actingAs($user);
        $this->ask('isi keranjang saya')
            ->assertJson(['intent' => 'cart'])
            ->assertSee('Rp 640.000');
    }

    public function test_faq_answers_follow_current_store_features(): void
    {
        // Pembayaran online belum ada, bot tidak boleh menjanjikan transfer/QRIS/COD
        $payment = $this->ask('cara pembayaran gimana?')->json('reply');
        $this->assertStringContainsString('belum dibuka', $payment);

        $this->ask('berapa ongkirnya?')->assertJsonPath('meta.topic', 'Pengiriman & Ongkir');
        $this->ask('gimana cara ganti alamat?')->assertJsonPath('meta.topic', 'Alamat Pengiriman');

        $howTo = $this->ask('cara belanja di sini')->json('reply');
        $this->assertStringContainsString('alamat pengiriman', $howTo);
        $this->assertStringContainsString('Pilih kurir', $howTo);
    }

    public function test_shipping_estimate_by_city_province_and_weight(): void
    {
        // Surabaya → Jawa Timur, berat bawaan 1 kg, SiCepat Rp 11.000/kg
        $this->ask('ongkir ke surabaya berapa?')
            ->assertJson(['intent' => 'shipping_estimate'])
            ->assertJsonPath('meta.province', 'Jawa Timur')
            ->assertSee('SiCepat Reguler: Rp 11.000');

        // 2,5 kg dibulatkan 3 kg, Papua Barat Daya SiCepat Rp 51.000/kg
        $this->ask('ongkir ke sorong 2,5 kg')
            ->assertJsonPath('meta.province', 'Papua Barat Daya')
            ->assertSee('Rp 153.000');

        // Nama provinsi terpanjang harus dikenali utuh
        $this->ask('biaya kirim ke papua barat daya')->assertJsonPath('meta.province', 'Papua Barat Daya');
    }

    public function test_order_answers_include_shipping_cost_and_destination(): void
    {
        $owner = User::create(['name' => 'budi', 'email' => 'budi@example.com', 'password' => bcrypt('secret123')]);
        DB::table('orders')->insert([
            'user_id' => $owner->id, 'product_id' => 1, 'qty' => 2, 'trx_id' => 'TRX-ONG1234', 'p_status' => 'Completed',
        ]);
        DB::table('order_addresses')->insert([
            'trx_id' => 'TRX-ONG1234', 'user_id' => $owner->id, 'recipient_name' => 'Budi Santoso', 'phone' => '081234567890',
            'province' => 'Jawa Timur', 'city' => 'Kota Surabaya', 'district' => 'Gubeng', 'postal_code' => '60281',
            'address_detail' => 'Jl. Kertajaya No. 5', 'courier' => 'JNE', 'courier_service' => 'Reguler',
            'shipping_etd' => '2-3 hari', 'total_weight' => 3000, 'shipping_cost' => 36000,
        ]);

        $this->actingAs($owner);

        $this->ask('status TRX-ONG1234')
            ->assertSee('Ongkir JNE Reguler (3 kg) = Rp 36.000')
            ->assertSee('Dikirim ke: Budi Santoso, Gubeng, Kota Surabaya')
            ->assertSee('Total: Rp 9.036.000');

        $history = $this->ask('cek pesanan saya')->json('reply');
        $this->assertStringContainsString('TRX-ONG1234 — 2 item, Rp 9.036.000', $history);
    }

    public function test_cart_summary_mentions_shipping(): void
    {
        $user = User::create(['name' => 'dewi', 'email' => 'dewi@example.com', 'password' => bcrypt('secret123')]);
        DB::table('cart')->insert(['p_id' => 3, 'ip_add' => '127.0.0.1', 'user_id' => $user->id, 'qty' => 2]);
        $this->actingAs($user);

        $this->ask('isi keranjang saya')->assertSee('Ongkir dihitung setelah Anda memilih alamat');

        // Dengan alamat utama di Sleman: 2 x 1 kg = 2 kg, SiCepat Rp 7.000/kg
        $this->createAddressFor($user->id);
        $this->ask('isi keranjang saya')->assertSee('Ongkir ke Kabupaten Sleman mulai Rp 14.000');

        // Tanpa menyebut tujuan, perkiraan ongkir memakai alamat utama & berat keranjang
        $this->ask('ongkirnya berapa ya')
            ->assertJsonPath('meta.province', 'DI Yogyakarta')
            ->assertSee('berat isi keranjang Anda');
    }

    public function test_unknown_message_falls_back_and_validation(): void
    {
        $this->ask('xyzqwe')->assertJson(['intent' => 'fallback']);
        $this->postJson('/chatbot/message', ['message' => ''])->assertStatus(422);
    }

    public function test_widget_is_rendered_and_admin_can_manage_faq(): void
    {
        $this->get('/')->assertOk()->assertSee('mybot-root', false);

        $admin = User::create(['name' => 'admin', 'email' => 'admin@example.com', 'password' => bcrypt('secret123'), 'is_admin' => true]);
        $this->actingAs($admin);

        $this->get('/admin')->assertOk()->assertSee('Chatbot MY-Bot');

        $this->post('/admin/chatbot/faqs', [
            'topic' => 'Promo', 'keywords' => 'Promo, Diskon', 'answer' => 'Diskon 10% setiap Jumat!',
        ])->assertRedirect();

        $this->assertDatabaseHas('chatbot_faqs', ['topic' => 'Promo', 'keywords' => 'promo, diskon']);
        $this->ask('ada diskon?')->assertJsonPath('reply', 'Diskon 10% setiap Jumat!');
    }
}
