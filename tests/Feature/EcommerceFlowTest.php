<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed initial categories, brands, and products
        DB::table('categories')->insert([
            ['cat_id' => 1, 'cat_title' => 'Electronics'],
            ['cat_id' => 2, 'cat_title' => 'Ladies Wears'],
        ]);

        DB::table('brands')->insert([
            ['brand_id' => 1, 'brand_title' => 'Apple'],
            ['brand_id' => 2, 'brand_title' => 'Samsung'],
        ]);

        DB::table('products')->insert([
            [
                'product_id' => 1,
                'product_cat' => 1,
                'product_brand' => 2,
                'product_title' => 'Samsung Galaxy S6',
                'product_price' => 5000000,
                'product_desc' => 'Flagship smartphone',
                'product_image' => 'product07.png',
                'product_keywords' => 'samsung, mobile',
            ],
            [
                'product_id' => 2,
                'product_cat' => 1,
                'product_brand' => 1,
                'product_title' => 'iPhone 13',
                'product_price' => 12000000,
                'product_desc' => 'Apple smartphone',
                'product_image' => 'iphone 5s.png',
                'product_keywords' => 'apple, iphone',
            ],
        ]);
    }

    public function test_home_page_displays_products_and_search(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S6');
        $response->assertSee('iPhone 13');

        // Test search filter
        $searchResponse = $this->get('/?q=Samsung');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Samsung Galaxy S6');
    }

    public function test_user_registration_and_authentication(): void
    {
        $response = $this->post('/register', [
            'name' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    public function test_cart_add_view_and_checkout_flow(): void
    {
        // 1. Create and authenticate user
        $user = User::create([
            'name' => 'buyer',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($user);

        // 2. Add product to cart
        $addResponse = $this->post('/cart/add', [
            'product_id' => 1,
            'qty' => 2,
        ]);
        $addResponse->assertSessionHas('success');
        $this->assertDatabaseHas('cart', [
            'user_id' => $user->id,
            'p_id' => 1,
            'qty' => 2,
        ]);

        // 3. View cart page
        $cartPage = $this->get('/cart');
        $cartPage->assertStatus(200);
        $cartPage->assertSee('Samsung Galaxy S6');

        // 4. Perform checkout
        $checkoutResponse = $this->post('/checkout', ['address_id' => $this->createAddressFor($user->id), 'shipping_service' => 'jne_reg']);
        $checkoutResponse->assertRedirect(route('user.dashboard'));

        // 5. Verify order is in database and cart is cleared
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => 1,
            'qty' => 2,
            'p_status' => 'Completed',
        ]);
        $this->assertDatabaseMissing('cart', [
            'user_id' => $user->id,
        ]);

        // 6. View User Dashboard
        $dashboardPage = $this->get('/user-dashboard');
        $dashboardPage->assertStatus(200);
        $dashboardPage->assertSee('Samsung Galaxy S6');
    }

    public function test_admin_dashboard_and_product_crud(): void
    {
        // 1. Authenticate as Admin
        $admin = User::create([
            'name' => 'adminuser',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);
        $this->actingAs($admin);

        // 2. View admin dashboard
        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Samsung Galaxy S6');

        // 2. Add new product via admin
        $storeResponse = $this->post('/admin/products', [
            'product_title' => 'MacBook Pro M3',
            'product_cat' => 1,
            'product_brand' => 1,
            'product_price' => 25000000,
            'product_desc' => 'High performance laptop',
            'product_image' => 'laptop.png',
            'product_keywords' => 'macbook, apple, laptop',
        ]);
        $storeResponse->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('products', [
            'product_title' => 'MacBook Pro M3',
        ]);
    }
}
