<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

final class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StockService::class);
        Storage::fake('public');
    }

    public function test_get_stocks_for_user(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(3)->create(['user_id' => $user->id]);

        $paginator = $this->service->getStocksForUser($user->id, null, null, 10);
        $this->assertCount(3, $paginator->items());
    }

    public function test_get_all_stocks_for_user(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(2)->create(['user_id' => $user->id]);

        $stocks = $this->service->getAllStocksForUser($user->id);
        $this->assertCount(2, $stocks);
    }

    public function test_get_stock_success_and_not_found(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $user->id]);

        $found = $this->service->getStock($stock->id, $user->id);
        $this->assertSame($stock->id, $found->id);

        $this->expectException(ModelNotFoundException::class);
        $this->service->getStock(9999, $user->id);
    }

    public function test_create_stock_with_cover_and_status_synchronization(): void
    {
        $user = User::factory()->create();

        // Available (qty > min_stock)
        $stockAvailable = $this->service->createStock(
            userId: $user->id,
            name: 'Stok Banyak',
            sku: 'SKU-01',
            description: 'Deskripsi',
            quantity: 20,
            unit: 'pcs',
            price: 15000.0,
            minStock: 5
        );
        $this->assertSame(StockStatus::AVAILABLE, $stockAvailable->status);
        $this->assertNull($stockAvailable->cover_image_path);

        // Low stock (qty <= min_stock)
        $stockLow = $this->service->createStock(
            userId: $user->id,
            name: 'Stok Menipis',
            sku: 'SKU-02',
            quantity: 4,
            unit: 'box',
            minStock: 5
        );
        $this->assertSame(StockStatus::LOW_STOCK, $stockLow->status);

        // Out of stock (qty == 0) with cover
        $file = UploadedFile::fake()->image('cover.jpg');
        $stockOut = $this->service->createStock(
            userId: $user->id,
            name: 'Stok Habis',
            sku: 'SKU-03',
            quantity: 0,
            unit: 'pcs',
            minStock: 5,
            coverImage: $file
        );
        $this->assertSame(StockStatus::OUT_OF_STOCK, $stockOut->status);
        $this->assertNotNull($stockOut->cover_image_path);
        Storage::disk('public')->assertExists($stockOut->cover_image_path);
    }

    public function test_create_stock_validation_negative_values(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->service->createStock(
            userId: $user->id,
            name: 'Invalid Qty',
            quantity: -5
        );
    }

    public function test_create_stock_validation_negative_min_stock(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->service->createStock(
            userId: $user->id,
            name: 'Invalid Min',
            quantity: 10,
            minStock: -1
        );
    }

    public function test_update_stock_with_cover_replacement_and_removal(): void
    {
        $user = User::factory()->create();
        $initialFile = UploadedFile::fake()->image('initial.jpg');

        $stock = $this->service->createStock(
            userId: $user->id,
            name: 'Initial Name',
            quantity: 20,
            minStock: 5,
            coverImage: $initialFile
        );

        $oldCoverPath = $stock->cover_image_path;
        Storage::disk('public')->assertExists($oldCoverPath);

        // Replace cover and change quantity to low_stock
        $newFile = UploadedFile::fake()->image('new.jpg');
        $updated = $this->service->updateStock(
            stock: $stock,
            name: 'Updated Name',
            sku: 'SKU-UPD',
            description: 'Updated Description',
            quantity: 3,
            unit: 'rim',
            price: 50000.0,
            minStock: 5,
            coverImage: $newFile
        );

        Storage::disk('public')->assertMissing($oldCoverPath);
        Storage::disk('public')->assertExists($updated->cover_image_path);
        $this->assertSame('Updated Name', $updated->name);
        $this->assertSame(StockStatus::LOW_STOCK, $updated->status);
        $this->assertSame(3, $updated->quantity);

        // Remove cover
        $removed = $this->service->updateStock(
            stock: $updated,
            name: 'Updated Name',
            coverImage: null,
            removeCover: true
        );

        $this->assertNull($removed->cover_image_path);
    }

    public function test_update_stock_validation_negative_quantity(): void
    {
        $stock = Stock::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->service->updateStock(
            stock: $stock,
            name: 'Test',
            quantity: -10
        );
    }

    public function test_update_stock_validation_negative_min_stock(): void
    {
        $stock = Stock::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->service->updateStock(
            stock: $stock,
            name: 'Test',
            minStock: -2
        );
    }

    public function test_adjust_quantity_boundaries(): void
    {
        $stock = Stock::factory()->create([
            'quantity' => 10,
            'min_stock' => 5,
            'status' => StockStatus::AVAILABLE,
        ]);

        // Adjust down to boundary (== min_stock, 10 - 5 = 5) -> LOW_STOCK
        $low = $this->service->adjustQuantity($stock, -5);
        $this->assertSame(5, $low->quantity);
        $this->assertSame(StockStatus::LOW_STOCK, $low->status);

        // Adjust down to boundary (0) -> OUT_OF_STOCK
        $out = $this->service->adjustQuantity($low, -5);
        $this->assertSame(0, $out->quantity);
        $this->assertSame(StockStatus::OUT_OF_STOCK, $out->status);

        // Adjust up (> min_stock, 0 + 6 = 6) -> AVAILABLE
        $avail = $this->service->adjustQuantity($out, 6);
        $this->assertSame(6, $avail->quantity);
        $this->assertSame(StockStatus::AVAILABLE, $avail->status);

        // Adjust below zero throws InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->service->adjustQuantity($avail, -10);
    }

    public function test_delete_stock_removes_cover(): void
    {
        $file = UploadedFile::fake()->image('cover_to_delete.jpg');
        $stock = $this->service->createStock(
            userId: User::factory()->create()->id,
            name: 'Hapus Saya',
            coverImage: $file
        );

        $path = $stock->cover_image_path;
        Storage::disk('public')->assertExists($path);

        $result = $this->service->deleteStock($stock);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }
}
