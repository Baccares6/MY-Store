<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ShippingCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ShippingCalculator $shipping;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('categories')->insert(['cat_id' => 1, 'cat_title' => 'Electronics']);
        DB::table('brands')->insert(['brand_id' => 1, 'brand_title' => 'HP']);
        DB::table('products')->insert([
            'product_id' => 1,
            'product_cat' => 1,
            'product_brand' => 1,
            'product_title' => 'HP Pavilion 15 Laptop',
            'product_price' => 13500000,
            'product_desc' => 'Laptop',
            'product_image' => 'laptop.png',
            'product_keywords' => 'hp, laptop',
            'product_weight' => 1500,
        ]);

        $this->user = User::create([
            'name' => 'pembeli',
            'email' => 'pembeli@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->shipping = new ShippingCalculator();
    }

    public function test_every_province_belongs_to_a_shipping_zone(): void
    {
        foreach (config('wilayah.provinces') as $province) {
            $this->assertNotNull($this->shipping->zoneFor($province), "Provinsi $province belum masuk zona ongkir.");
        }
    }

    public function test_weight_is_rounded_like_a_courier(): void
    {
        $this->assertSame(1, $this->shipping->chargeableKg(200));
        $this->assertSame(1, $this->shipping->chargeableKg(1300));
        $this->assertSame(2, $this->shipping->chargeableKg(1301));
        $this->assertSame(3, $this->shipping->chargeableKg(3000));
    }

    public function test_options_are_sorted_and_next_day_service_is_limited_to_java(): void
    {
        $toJava = $this->shipping->options('Jawa Timur', 1000);
        $costs = array_column($toJava, 'cost');
        $sorted = $costs;
        sort($sorted);
        $this->assertSame($sorted, $costs);
        $this->assertContains('jne_yes', array_column($toJava, 'code'));

        $toPapua = $this->shipping->options('Papua', 1000);
        $this->assertNotContains('jne_yes', array_column($toPapua, 'code'));
    }

    public function test_checkout_stores_shipping_cost_calculated_on_the_server(): void
    {
        $addressId = $this->createAddressFor($this->user->id, ['province' => 'Jawa Timur', 'city' => 'Kota Surabaya']);

        $this->actingAs($this->user)->post('/cart/add', ['product_id' => 1, 'qty' => 2]);
        $this->post('/checkout', [
            'address_id' => $addressId,
            'shipping_service' => 'jne_reg',
            'shipping_cost' => 0, // percobaan manipulasi ongkir dari browser, harus diabaikan
        ])->assertRedirect(route('user.dashboard'));

        // 2 x 1.500 g = 3 kg, JNE Reguler ke Pulau Jawa Rp 12.000/kg
        $this->assertDatabaseHas('order_addresses', [
            'user_id' => $this->user->id,
            'courier' => 'JNE',
            'courier_service' => 'Reguler',
            'total_weight' => 3000,
            'shipping_cost' => 36000,
        ]);

        // Total belanja di dashboard = harga produk + ongkir
        $this->get('/user-dashboard')
            ->assertSee('JNE Reguler')
            ->assertSee('Rp 27.036.000');
    }

    public function test_checkout_rejects_missing_or_unavailable_courier(): void
    {
        $addressId = $this->createAddressFor($this->user->id, ['province' => 'Papua', 'city' => 'Kota Jayapura']);
        $this->actingAs($this->user)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);

        $this->post('/checkout', ['address_id' => $addressId])->assertSessionHas('error');
        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'jne_yes'])->assertSessionHas('error');
        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'kurir_palsu'])->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart', ['user_id' => $this->user->id]);
    }

    public function test_cart_shows_courier_choices_and_total_with_shipping(): void
    {
        $this->createAddressFor($this->user->id); // DI Yogyakarta, zona dalam provinsi
        $this->actingAs($this->user)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);

        // 1.500 g dibulatkan 2 kg, termurah SiCepat Reguler Rp 7.000/kg = Rp 14.000
        $this->get('/cart')
            ->assertSee('Pilih Pengiriman')
            ->assertSee('SiCepat Reguler')
            ->assertSee('JNE YES (Besok Sampai)')
            ->assertSee('dihitung 2 kg')
            ->assertSee('Rp 14.000')
            ->assertSee('Rp 13.514.000')
            ->assertDontSee('Gratis (Promo)');
    }
}
