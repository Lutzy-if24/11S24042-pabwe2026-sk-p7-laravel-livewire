<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Features\Admin\Livewire\AdminUserStockList;
use App\Features\Admin\Livewire\UserList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

final class AdminLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_user_list_page(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create(['name' => 'Member Test']);
        Stock::factory()->count(2)->create(['user_id' => $member->id]);

        $this->actingAs($admin);

        Livewire::test(UserList::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Member Test')
            ->assertSee('2 item');
    }

    public function test_search_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->member()->create(['name' => 'Charlie Chaplin']);

        $this->actingAs($admin);

        Livewire::test(UserList::class)
            ->set('search', 'Charlie')
            ->assertSet('search', 'Charlie')
            ->assertSee('Charlie Chaplin');
    }

    public function test_renders_user_stock_list_read_only(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create([
            'name' => 'Target User',
            'profile_photo_path' => 'profile-photos/sample.jpg',
        ]);

        $stock = Stock::factory()->create([
            'user_id' => $member->id,
            'name' => 'Barang Khusus Target',
            'sku' => 'SKU-TGT-001',
            'description' => 'Deskripsi Khusus Target',
            'cover_image_path' => 'stocks/covers/sample.jpg',
            'quantity' => 10,
            'unit' => 'box',
            'price' => 150000.00,
        ]);

        $this->actingAs($admin);

        Livewire::test(AdminUserStockList::class, ['user' => $member])
            ->assertStatus(200)
            ->assertSee('Mode Inspeksi (Hanya Baca / Read-Only)')
            ->assertSee('Target User')
            ->assertSee('Barang Khusus Target')
            ->assertSee('SKU-TGT-001')
            ->assertSee('10 box')
            ->assertSee('150.000')
            ->assertDontSee('wire:click="deleteStock"', false)
            ->assertDontSee('wire:click="openEditModal"', false)
            ->assertDontSee('wire:click="adjustQuantity"', false);
    }

    public function test_renders_empty_state_when_user_has_no_stocks(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create(['name' => 'User Kosong']);

        $this->actingAs($admin);

        Livewire::test(AdminUserStockList::class, ['user' => $member])
            ->assertStatus(200)
            ->assertSee('Pengguna ini belum memiliki stok barang yang dibuat.');
    }

    public function test_admin_cannot_update_or_delete_user_stock_via_policy(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create();
        $stock = Stock::factory()->create(['user_id' => $member->id]);

        $this->assertFalse(Gate::forUser($admin)->allows('update', $stock));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $stock));
    }
}
