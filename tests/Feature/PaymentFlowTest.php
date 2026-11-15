<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        DB::table('categories')->insert(['cat_id' => 1, 'cat_title' => 'Electronics']);
        DB::table('brands')->insert(['brand_id' => 1, 'brand_title' => 'HP']);
        DB::table('products')->insert([
            'product_id' => 1, 'product_cat' => 1, 'product_brand' => 1, 'product_title' => 'HP Pavilion 15 Laptop',
            'product_price' => 13500000, 'product_desc' => 'Laptop', 'product_image' => 'laptop.png',
            'product_keywords' => 'laptop', 'product_weight' => 2500,
        ]);

        $this->buyer = User::create(['name' => 'pembeli', 'email' => 'pembeli@example.com', 'password' => Hash::make('password123')]);
        $this->admin = User::create(['name' => 'admin', 'email' => 'admin@example.com', 'password' => Hash::make('password123'), 'is_admin' => true]);
    }

    /** Checkout 1 laptop (2,5 kg → 3 kg) ke alamat pembeli dan kembalikan trx_id-nya */
    private function checkout(string $method = 'bank_bca', array $address = []): string
    {
        $addressId = $this->createAddressFor($this->buyer->id, $address);
        $this->actingAs($this->buyer)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);
        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'sicepat_reg', 'payment_method' => $method]);

        return DB::table('transactions')->where('user_id', $this->buyer->id)->latest('id')->value('trx_id');
    }

    private function trxStatus(string $trx): string
    {
        return DB::table('transactions')->where('trx_id', $trx)->value('status');
    }

    /** Gambar PNG 1x1 sungguhan (tidak butuh ekstensi GD seperti UploadedFile::fake()->image()) */
    private function fakeProof(string $name = 'bukti.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    public function test_bank_transfer_checkout_waits_for_payment_with_deadline_and_price_snapshot(): void
    {
        $trx = $this->checkout();

        $t = DB::table('transactions')->where('trx_id', $trx)->first();
        $this->assertSame(OrderStatus::PENDING, $t->status);
        $this->assertSame(13500000, (int) $t->subtotal);
        $this->assertSame(21000, (int) $t->shipping_cost);   // SiCepat DIY Rp 7.000 x 3 kg
        $this->assertSame(13521000, (int) $t->total);
        $this->assertNotNull($t->payment_deadline);
        $this->assertDatabaseHas('orders', ['trx_id' => $trx, 'price' => 13500000, 'p_status' => 'Pending']);

        // Halaman pesanan menampilkan instruksi transfer & peringatan demo
        $this->get("/orders/{$trx}")
            ->assertOk()
            ->assertSee('Menunggu Pembayaran')
            ->assertSee('Transfer Bank BCA')
            ->assertSee('Rp 13.521.000')
            ->assertSee('Jangan melakukan pembayaran sungguhan');

        // Harga produk naik setelah checkout: riwayat tetap memakai harga saat beli
        DB::table('products')->where('product_id', 1)->update(['product_price' => 20000000]);
        $this->get("/orders/{$trx}")->assertSee('Rp 13.500.000')->assertDontSee('Rp 20.000.000');
    }

    public function test_payment_method_is_required_and_cod_only_for_yogyakarta(): void
    {
        $addressId = $this->createAddressFor($this->buyer->id, ['province' => 'Jawa Timur', 'city' => 'Kota Surabaya']);
        $this->actingAs($this->buyer)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);

        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'sicepat_reg'])->assertSessionHas('error');
        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'sicepat_reg', 'payment_method' => 'cod'])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'COD'));
        $this->post('/checkout', ['address_id' => $addressId, 'shipping_service' => 'sicepat_reg', 'payment_method' => 'paypal'])->assertSessionHas('error');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_qris_order_shows_qr_code_and_accepts_payment_proof(): void
    {
        $this->actingAs($this->buyer)->post('/cart/add', ['product_id' => 1, 'qty' => 1]);
        $this->createAddressFor($this->buyer->id);
        $this->get('/cart')->assertSee('QRIS')->assertSee('Scan dengan GoPay, OVO, DANA, ShopeePay');

        $trx = $this->checkout('qris');
        $this->assertSame(OrderStatus::PENDING, $this->trxStatus($trx));
        $this->assertNotNull(DB::table('transactions')->where('trx_id', $trx)->value('payment_deadline'));

        // Halaman pesanan menampilkan kode QR, nama toko, nominal, dan peringatan bahwa QR hanya contoh
        $total = DB::table('transactions')->where('trx_id', $trx)->value('total');
        $this->get("/orders/{$trx}")
            ->assertOk()
            ->assertSee('images/qris-demo.svg')
            ->assertSee('Pindai kode QRIS')
            ->assertSee('Bayar <strong>' . rupiah($total) . '</strong>', false)
            ->assertSee('Kode QRIS di bawah ini hanya contoh')
            ->assertDontSee('Nomor rekening');

        $this->post("/orders/{$trx}/proof", ['proof' => $this->fakeProof()])->assertSessionHas('success');
        $this->assertSame(OrderStatus::WAITING, $this->trxStatus($trx));

        $this->actingAs($this->admin)->get("/admin/orders/{$trx}")->assertSee('Periksa Bukti Pembayaran')->assertSee('lewat QRIS');
        $this->post("/admin/orders/{$trx}/confirm")->assertSessionHas('success');
        $this->assertSame(OrderStatus::PROCESSING, $this->trxStatus($trx));
    }

    public function test_cod_order_goes_straight_to_processing(): void
    {
        $trx = $this->checkout('cod');   // alamat bawaan helper: DI Yogyakarta

        $this->assertSame(OrderStatus::PROCESSING, $this->trxStatus($trx));
        $this->assertNull(DB::table('transactions')->where('trx_id', $trx)->value('payment_deadline'));
    }

    public function test_full_bank_transfer_flow_from_upload_to_completed(): void
    {
        $trx = $this->checkout();

        // Pembeli mengunggah bukti transfer
        $this->post("/orders/{$trx}/proof", ['proof' => $this->fakeProof()])->assertSessionHas('success');
        $this->assertSame(OrderStatus::WAITING, $this->trxStatus($trx));
        $proof = DB::table('transactions')->where('trx_id', $trx)->value('payment_proof');
        Storage::disk('local')->assertExists($proof);

        // Admin mengonfirmasi, lalu mengirim dengan nomor resi
        $this->actingAs($this->admin);
        $this->get('/admin/orders')->assertOk()->assertSee($trx)->assertSee('menunggu konfirmasi pembayaran');
        $this->get("/orders/{$trx}/proof")->assertOk();
        $this->post("/admin/orders/{$trx}/confirm")->assertSessionHas('success');
        $this->assertSame(OrderStatus::PROCESSING, $this->trxStatus($trx));

        $this->post("/admin/orders/{$trx}/ship", ['tracking_number' => ''])->assertSessionHasErrors('tracking_number');
        $this->post("/admin/orders/{$trx}/ship", ['tracking_number' => 'sicepat123456'])->assertSessionHas('success');
        $this->assertSame(OrderStatus::SHIPPED, $this->trxStatus($trx));
        $this->assertDatabaseHas('transactions', ['trx_id' => $trx, 'tracking_number' => 'SICEPAT123456']);

        // Pembeli mengonfirmasi pesanan diterima
        $this->actingAs($this->buyer);
        $this->get("/orders/{$trx}")->assertSee('SICEPAT123456');
        $this->post("/orders/{$trx}/received")->assertSessionHas('success');
        $this->assertSame(OrderStatus::COMPLETED, $this->trxStatus($trx));
        $this->assertDatabaseHas('orders', ['trx_id' => $trx, 'p_status' => 'Completed']);

        // Setiap perubahan status tercatat di log audit. Tabel logs di MySQL lama memakai
        // charset latin1 dan kolom action VARCHAR(50), jadi pesan harus ASCII & maksimal 50 karakter
        $logs = DB::table('logs')->where('action', 'like', "Status {$trx}:%")->pluck('action');
        $this->assertCount(4, $logs);
        $this->assertContains("Status {$trx}: Processing -> Shipped", $logs);
        foreach ($logs as $action) {
            $this->assertMatchesRegularExpression('/^[\x20-\x7E]{1,50}$/', $action);
        }
    }

    public function test_admin_can_reject_proof_and_buyer_gets_new_deadline(): void
    {
        $trx = $this->checkout();
        $this->post("/orders/{$trx}/proof", ['proof' => $this->fakeProof()]);

        $this->actingAs($this->admin)->post("/admin/orders/{$trx}/reject", ['reason' => ''])->assertSessionHasErrors('reason');
        $this->post("/admin/orders/{$trx}/reject", ['reason' => 'Nominal transfer tidak sesuai'])->assertSessionHas('success');

        $t = DB::table('transactions')->where('trx_id', $trx)->first();
        $this->assertSame(OrderStatus::PENDING, $t->status);
        $this->assertNull($t->payment_proof);
        $this->assertSame('Nominal transfer tidak sesuai', $t->admin_note);

        $this->actingAs($this->buyer)->get("/orders/{$trx}")->assertSee('Bukti transfer ditolak')->assertSee('Nominal transfer tidak sesuai');
    }

    public function test_invalid_transitions_and_uploads_are_rejected(): void
    {
        $trx = $this->checkout();

        // Admin tidak bisa mengirim pesanan yang belum dibayar
        $this->actingAs($this->admin)->post("/admin/orders/{$trx}/ship", ['tracking_number' => 'JNE123456'])->assertSessionHas('error');
        $this->post("/admin/orders/{$trx}/confirm")->assertSessionHas('error');
        $this->assertSame(OrderStatus::PENDING, $this->trxStatus($trx));

        // Bukti harus berupa gambar
        $this->actingAs($this->buyer)
            ->post("/orders/{$trx}/proof", ['proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('proof');

        // Pembeli tidak bisa menandai diterima sebelum dikirim
        $this->post("/orders/{$trx}/received")->assertSessionHas('error');
    }

    public function test_buyer_can_cancel_unpaid_order_but_not_processed_one(): void
    {
        $trx = $this->checkout();
        $this->post("/orders/{$trx}/cancel")->assertSessionHas('success');
        $this->assertSame(OrderStatus::CANCELLED, $this->trxStatus($trx));

        $cod = $this->checkout('cod');
        $this->post("/orders/{$cod}/cancel")->assertSessionHas('error');
        $this->assertSame(OrderStatus::PROCESSING, $this->trxStatus($cod));

        // Pesanan dibatalkan tidak dihitung sebagai belanja
        $this->get('/user-dashboard')->assertViewHas('total_spent', 13521000);
    }

    public function test_unpaid_orders_expire_after_deadline(): void
    {
        $trx = $this->checkout();
        DB::table('transactions')->where('trx_id', $trx)->update(['payment_deadline' => now()->subMinute()]);

        $this->get('/user-dashboard')->assertOk();

        $this->assertSame(OrderStatus::EXPIRED, $this->trxStatus($trx));
        $this->post("/orders/{$trx}/proof", ['proof' => $this->fakeProof()])->assertSessionHas('error');
    }

    public function test_orders_are_private_to_their_owner_and_admin(): void
    {
        $trx = $this->checkout();
        $this->post("/orders/{$trx}/proof", ['proof' => $this->fakeProof()]);

        $other = User::create(['name' => 'lain', 'email' => 'lain@example.com', 'password' => Hash::make('password123')]);
        $this->actingAs($other);

        $this->get("/orders/{$trx}")->assertNotFound();
        $this->get("/orders/{$trx}/proof")->assertNotFound();
        $this->post("/orders/{$trx}/cancel")->assertNotFound();
        $this->get('/admin/orders')->assertRedirect(route('home'));
        $this->post("/admin/orders/{$trx}/confirm")->assertRedirect(route('home'));

        $this->assertSame(OrderStatus::WAITING, $this->trxStatus($trx));
    }

    public function test_chatbot_reports_payment_status_and_instructions(): void
    {
        $trx = $this->checkout();

        $reply = $this->postJson('/chatbot/message', ['message' => "status {$trx}"])->json('reply');
        $this->assertStringContainsString('Status: Menunggu Pembayaran', $reply);
        $this->assertStringContainsString('Segera bayar sebelum', $reply);
        $this->assertStringContainsString('Total: Rp 13.521.000', $reply);

        $faq = $this->postJson('/chatbot/message', ['message' => 'cara pembayaran gimana?'])->json('reply');
        $this->assertStringContainsString('Transfer bank BCA', $faq);
    }
}
