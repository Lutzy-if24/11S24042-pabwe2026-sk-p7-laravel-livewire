<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Features\Auth\Livewire\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Baru');
    }

    public function test_authenticated_admin_is_redirected_from_register_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(Register::class)
            ->assertRedirect(route('admin.users'));
    }

    public function test_authenticated_member_is_redirected_from_register_page(): void
    {
        $member = User::factory()->member()->create();

        $this->actingAs($member);

        Livewire::test(Register::class)
            ->assertRedirect(route('stocks.index'));
    }

    public function test_register_validation_errors(): void
    {
        Livewire::test(Register::class)
            ->set('name', '')
            ->set('username', 'ab')
            ->set('email', 'invalid-email')
            ->set('password', '123')
            ->set('password_confirmation', '456')
            ->call('register')
            ->assertHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_register_creates_member_and_redirects(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Budi Santoso')
            ->set('username', 'budi_santoso')
            ->set('email', 'budi@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('stocks.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'budi_santoso',
            'email' => 'budi@example.com',
            'role' => 'member',
        ]);

        $this->assertAuthenticated();
        /** @var User $user */
        $user = auth()->user();
        $this->assertSame('member', $user->role);
    }
}
