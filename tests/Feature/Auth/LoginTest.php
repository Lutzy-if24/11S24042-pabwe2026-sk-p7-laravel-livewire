<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_required_ids_and_text(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('id="login-username"', false);
        $response->assertSee('id="login-password"', false);
        $response->assertSee('id="login-submit"', false);
        $response->assertSee('Masuk');
    }

    public function test_authenticated_admin_is_redirected_from_login_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(Login::class)
            ->assertRedirect(route('admin.users'));
    }

    public function test_authenticated_member_is_redirected_from_login_page(): void
    {
        $member = User::factory()->member()->create();

        $this->actingAs($member);

        Livewire::test(Login::class)
            ->assertRedirect(route('stocks.index'));
    }

    public function test_login_validation_errors(): void
    {
        Livewire::test(Login::class)
            ->set('login', '')
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['login', 'password']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'username' => 'testuser',
            'password' => Hash::make('password123'),
        ]);

        Livewire::test(Login::class)
            ->set('login', 'testuser')
            ->set('password', 'wrongpassword')
            ->call('authenticate')
            ->assertHasErrors(['login']);

        $this->assertGuest();
    }

    public function test_login_succeeds_with_username_for_member(): void
    {
        $user = User::factory()->member()->create([
            'username' => 'memberuser',
            'password' => Hash::make('secret123'),
        ]);

        Livewire::test(Login::class)
            ->set('login', 'memberuser')
            ->set('password', 'secret123')
            ->call('authenticate')
            ->assertRedirect(route('stocks.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_succeeds_with_email_for_admin(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
        ]);

        Livewire::test(Login::class)
            ->set('login', 'admin@example.com')
            ->set('password', 'adminpass')
            ->call('authenticate')
            ->assertRedirect(route('admin.users'));

        $this->assertAuthenticatedAs($admin);
    }
}
