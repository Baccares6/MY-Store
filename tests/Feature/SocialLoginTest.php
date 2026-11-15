<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'google-test-id',
            'services.google.client_secret' => 'google-test-secret',
            'services.facebook.client_id' => 'facebook-test-id',
            'services.facebook.client_secret' => 'facebook-test-secret',
        ]);
    }

    /** Simulasikan data akun yang dikembalikan Google/Facebook setelah pengguna menyetujui login */
    private function fakeSocialUser(string $provider, array $data): void
    {
        $user = (new SocialiteUser())->map($data + ['nickname' => null, 'avatar' => null]);

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('redirectUrl')->andReturnSelf();
        $driver->shouldReceive('scopes')->andReturnSelf();
        $driver->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
    }

    public function test_redirect_sends_user_to_google_login_page(): void
    {
        $response = $this->get('/auth/google/redirect');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://accounts.google.com/', $response->headers->get('Location'));
        $this->assertStringContainsString('client_id=google-test-id', $response->headers->get('Location'));
    }

    public function test_callback_url_follows_the_address_being_used_including_port(): void
    {
        // Contoh: website dibuka di localhost:8000 sementara APP_URL hanya "http://localhost"
        $response = $this->get('http://localhost:8000/auth/google/redirect');

        $this->assertStringContainsString(
            'redirect_uri=' . urlencode('http://localhost:8000/auth/google/callback'),
            $response->headers->get('Location')
        );
    }

    public function test_new_google_user_gets_an_account_and_is_logged_in(): void
    {
        $this->fakeSocialUser('google', ['id' => 'g-123', 'name' => 'Budi Santoso', 'email' => 'budi@gmail.com', 'avatar' => 'https://example.com/a.png']);

        $this->get('/auth/google/callback?code=abc&state=xyz')->assertRedirect(route('home'));

        $user = User::where('email', 'budi@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('g-123', $user->google_id);
        $this->assertSame('Budi.Santoso', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->isAdmin());
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_account_with_same_email_is_linked_not_duplicated(): void
    {
        $existing = User::create(['name' => 'citra', 'email' => 'citra@example.com', 'password' => Hash::make('secret123')]);
        $this->fakeSocialUser('facebook', ['id' => 'fb-777', 'name' => 'Citra Lestari', 'email' => 'citra@example.com']);

        $this->get('/auth/facebook/callback?code=abc')->assertRedirect(route('home'));

        $this->assertSame(1, User::count());
        $this->assertSame('fb-777', $existing->fresh()->facebook_id);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_returning_user_is_found_by_provider_id(): void
    {
        $user = User::create(['name' => 'dewi', 'email' => 'dewi@example.com', 'password' => Hash::make('secret123')]);
        $user->forceFill(['google_id' => 'g-555'])->save();

        // Email di akun Google sudah berganti, tetap dikenali dari ID Google
        $this->fakeSocialUser('google', ['id' => 'g-555', 'name' => 'Dewi', 'email' => 'dewi.baru@gmail.com']);
        $this->get('/auth/google/callback?code=abc')->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_username_is_made_unique(): void
    {
        User::create(['name' => 'Budi.Santoso', 'email' => 'lain@example.com', 'password' => Hash::make('secret123')]);
        $this->fakeSocialUser('google', ['id' => 'g-1', 'name' => 'Budi Santoso', 'email' => 'budi2@gmail.com']);

        $this->get('/auth/google/callback?code=abc');

        $name = User::where('email', 'budi2@gmail.com')->value('name');
        $this->assertMatchesRegularExpression('/^Budi\.Santoso\d{3,4}$/', $name);
    }

    public function test_account_without_email_is_rejected(): void
    {
        $this->fakeSocialUser('facebook', ['id' => 'fb-1', 'name' => 'Tanpa Email', 'email' => null]);

        $this->get('/auth/facebook/callback?code=abc')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'tidak membagikan alamat email'));

        $this->assertSame(0, User::count());
        $this->assertGuest();
    }

    public function test_cancelled_login_shows_message(): void
    {
        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Login dengan Google dibatalkan.');
    }

    public function test_unconfigured_provider_is_hidden_and_blocked(): void
    {
        config(['services.facebook.client_id' => null]);

        $this->get('/login')->assertSee('Continue with Google')->assertDontSee('Continue with Facebook');
        $this->get('/register')->assertSee('Continue with Google');

        $this->get('/auth/facebook/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Login dengan Facebook belum tersedia saat ini.');
    }

    public function test_unknown_provider_returns_404(): void
    {
        $this->get('/auth/twitter/redirect')->assertNotFound();
    }
}
