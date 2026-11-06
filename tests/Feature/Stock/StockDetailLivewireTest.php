<?php

declare(strict_types=1);

namespace Tests\Feature\Stock;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class StockDetailLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->member()->create();
        Storage::fake('public');
    }

    public function test_access_aborts_when_not_owner(): void
    {
        $otherUser = User::factory()->member()->create();
        $stock = Stock::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->member);

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->assertStatus(403);
    }

    public function test_renders_stock_detail_page(): void
    {
        $this->actingAs($this->member);

        $stock = Stock::factory()->create([
            'user_id' => $this->member->id,
            'name' => 'Barang Detail Spesial',
            'sku' => 'SKU-DTL-01',
            'description' => '**Markdown bold text**',
            'cover_image_path' => 'stocks/covers/sample.jpg',
            'quantity' => 15,
            'unit' => 'box',
            'price' => 75000.00,
            'min_stock' => 5,
            'status' => StockStatus::AVAILABLE,
        ]);

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->assertStatus(200)
            ->assertSee('Barang Detail Spesial')
            ->assertSee('SKU-DTL-01')
            ->assertSee('15 box')
            ->assertSee('75.000')
            ->assertSee('Markdown bold text');
    }

    public function test_toggle_edit_mode(): void
    {
        $this->actingAs($this->member);
        $stock = Stock::factory()->create(['user_id' => $this->member->id]);

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->assertSet('isEditing', false)
            ->call('toggleEdit')
            ->assertSet('isEditing', true);
    }

    public function test_save_validation_errors(): void
    {
        $this->actingAs($this->member);
        $stock = Stock::factory()->create(['user_id' => $this->member->id]);

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->set('name', '')
            ->set('quantity', -1)
            ->set('min_stock', -1)
            ->call('save')
            ->assertHasErrors(['name', 'quantity', 'min_stock']);
    }

    public function test_save_updates_with_cover_and_status(): void
    {
        $this->actingAs($this->member);
        $stock = Stock::factory()->create(['user_id' => $this->member->id, 'quantity' => 10, 'min_stock' => 5]);
        $newCover = UploadedFile::fake()->image('updated_cover.png');

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->set('name', 'Barang Terupdate')
            ->set('sku', 'SKU-UPD-01')
            ->set('description', 'Deskripsi terupdate')
            ->set('quantity', 2)
            ->set('unit', 'unit')
            ->set('price', 99000.00)
            ->set('min_stock', 5)
            ->set('newCoverImage', $newCover)
            ->call('save')
            ->assertSet('isEditing', false)
            ->assertSet('status', 'low_stock');

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'name' => 'Barang Terupdate',
            'sku' => 'SKU-UPD-01',
            'quantity' => 2,
            'status' => 'low_stock',
        ]);

        $this->assertNotNull($stock->fresh()->cover_image_path);
        Storage::disk('public')->assertExists($stock->fresh()->cover_image_path);
    }

    public function test_remove_cover_image(): void
    {
        $this->actingAs($this->member);
        $file = UploadedFile::fake()->image('cover_to_remove.png');
        $path = $file->store('stocks/covers', 'public');

        $stock = Stock::factory()->create([
            'user_id' => $this->member->id,
            'cover_image_path' => $path,
        ]);

        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->call('removeCover')
            ->assertSet('currentCoverPath', null);

        $this->assertNull($stock->fresh()->cover_image_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_adjust_quantity(): void
    {
        $this->actingAs($this->member);
        $stock = Stock::factory()->create([
            'user_id' => $this->member->id,
            'quantity' => 4,
            'min_stock' => 5,
            'status' => StockStatus::LOW_STOCK,
        ]);

        // Increase by 2 (quantity 6, min_stock 5 -> available)
        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->call('adjustQuantity', 2)
            ->assertSet('quantity', 6)
            ->assertSet('status', 'available');

        $this->assertSame(6, $stock->fresh()->quantity);
        $this->assertSame(StockStatus::AVAILABLE, $stock->fresh()->status);

        // Decrease by 6 (quantity 0 -> out_of_stock)
        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->call('adjustQuantity', -6)
            ->assertSet('quantity', 0)
            ->assertSet('status', 'out_of_stock');

        $this->assertSame(0, $stock->fresh()->quantity);
        $this->assertSame(StockStatus::OUT_OF_STOCK, $stock->fresh()->status);

        // Attempting to decrease below 0 should fail cleanly without reducing below 0
        Livewire::test(StockDetail::class, ['stock' => $stock])
            ->call('adjustQuantity', -1);

        $this->assertSame(0, $stock->fresh()->quantity);
    }
}
