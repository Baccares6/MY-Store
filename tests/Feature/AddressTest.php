<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('categories')->insert(['cat_id' => 1, 'cat_title' => 'Electronics']);
        DB::table('brands')->insert(['brand_id' => 1, 'brand_title' => 'Samsung']);
        DB::table('products')->insert([
            'product_id' => 1,
            'product_cat' => 1,
            'product_brand' => 1,
            'product_title' => 'Samsung Galaxy S6',
            'product_price' => 5000000,
            'product_desc' => 'Flagship smartphone',
            'product_image' => 'product07.png',
            'product_keywords' => 'samsung, mobile',
        ]);

        $this->user = User::create([
            'name' => 'pembeli',
            'email' => 'pembeli@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    private function validInput(array $overrides = []): array
    {
        return $overrides + [
            'recipient_name' => 'Siti Aminah',
            'phone' => '+62 812-3456-7890',
            'province' => 'Jawa Tengah',
            'city' => 'Kota Semarang',
            'district' => 'Tembalang',
            'postal_code' => '50275',
            'address_detail' => 'Jl. Prof. Soedarto No. 13, RT 03/RW 05',
            'label' => 'Kos',
        ];
    }

    public function test_first_address_becomes_default_and_phone_is_normalized(): void
    {
        $this->actingAs($this->user)
            ->post('/addresses', $this->validInput())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'phone' => '081234567890',
            'is_default' => true,
        ]);
    }

    public function test_invalid_address_is_rejected_with_clear_messages(): void
    {
        $this->actingAs($this->user)
            ->post('/addresses', $this->validInput([
                'phone' => '12345',
                'postal_code' => '55a',
                'province' => 'Provinsi Fiktif',
                'address_detail' => 'Jl. A',
            ]))
            ->assertSessionHasErrorsIn('address', ['phone', 'postal_code', 'province', 'address_detail']);

        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_setting_new_default_unsets_previous_default(): void
    {
        $firstId = $this->createAddressFor($this->user->id);

        $this->actingAs($this->user)->post('/addresses', $this->validInput(['is_default' => '1']));

        $this->assertDatabaseHas('addresses', ['id' => $firstId, 'is_default' => false]);
        $this->assertSame(1, DB::table('addresses')->where('user_id', $this->user->id)->where('is_default', true)->count());
    }

    public function test_deleting_default_address_promotes_another_address(): void
    {
        $defaultId = $this->createAddressFor($this->user->id);
        $otherId = $this->createAddressFor($this->user->id, ['is_default' => false, 'label' => 'Kantor']);

        $this->actingAs($this->user)->post("/addresses/{$defaultId}/delete")->assertSessionHas('success');

        $this->assertDatabaseMissing('addresses', ['id' => $defaultId]);
        $this->assertDatabaseHas('addresses', ['id' => $otherId, 'is_default' => true]);
    }

    public function test_user_cannot_modify_or_delete_another_users_address(): void
    {
        $owner = User::create([
            'name' => 'pemilik',
            'email' => 'pemilik@example.com',
            'password' => Hash::make('password123'),
        ]);
        $ownerAddressId = $this->createAddressFor($owner->id);

        $this->actingAs($this->user)
            ->post("/addresses/{$ownerAddressId}/update", $this->validInput(['recipient_name' => 'Penyusup']))
            ->assertSessionHas('error');
        $this->actingAs($this->user)
            ->post("/addresses/{$ownerAddressId}/delete")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('addresses', ['id' => $ownerAddressId, 'recipient_name' => 'Budi Santoso']);
    }

    public function test_checkout_requires_an_address(): void
    {
        $this->actingAs($this->user)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);

        $this->post('/checkout')
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart', ['user_id' => $this->user->id]);
    }

    public function test_checkout_cannot_use_another_users_address(): void
    {
        $owner = User::create([
            'name' => 'pemilik',
            'email' => 'pemilik@example.com',
            'password' => Hash::make('password123'),
        ]);
        $ownerAddressId = $this->createAddressFor($owner->id);

        $this->actingAs($this->user)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);
        $this->post('/checkout', ['address_id' => $ownerAddressId, 'shipping_service' => 'jne_reg'])->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_keeps_address_snapshot_after_address_is_edited(): void
    {
        $addressId = $this->createAddressFor($this->user->id);

        $this->actingAs($this->user)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);
        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'jne_reg'])->assertRedirect(route('user.dashboard'));

        $trxId = DB::table('orders')->where('user_id', $this->user->id)->value('trx_id');

        // Alamat diubah setelah checkout, salinan di pesanan harus tetap seperti semula
        $this->post("/addresses/{$addressId}/update", $this->validInput(['recipient_name' => 'Nama Baru']));

        $this->assertDatabaseHas('order_addresses', [
            'trx_id' => $trxId,
            'recipient_name' => 'Budi Santoso',
            'city' => 'Kabupaten Sleman',
        ]);

        $this->get('/user-dashboard')
            ->assertSee('Kirim ke Budi Santoso, Kabupaten Sleman')
            ->assertSee('Nama Baru');
    }

    public function test_cart_page_shows_address_choice_and_empty_state(): void
    {
        $this->actingAs($this->user)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);

        $this->get('/cart')
            ->assertSee('Anda belum punya alamat pengiriman')
            ->assertSee('Tambahkan alamat pengiriman untuk melanjutkan.');

        $this->createAddressFor($this->user->id);

        $this->get('/cart')
            ->assertSee('Budi Santoso')
            ->assertSee('Kabupaten Sleman')
            ->assertDontSee('Tambahkan alamat pengiriman untuk melanjutkan.');
    }
}
