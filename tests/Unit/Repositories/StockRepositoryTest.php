<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private StockRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StockRepository;
    }

    public function test_get_paginated_for_user_with_search_and_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Stock::factory()->create([
            'user_id' => $user->id,
            'name' => 'Kertas HVS A4',
            'sku' => 'SKU-HVS-01',
            'description' => 'Kertas printer tebal',
            'status' => StockStatus::AVAILABLE,
        ]);

        Stock::factory()->create([
            'user_id' => $user->id,
            'name' => 'Pulpen Gel Hitam',
            'sku' => 'SKU-PLP-02',
            'description' => 'Tinta tahan air',
            'status' => StockStatus::LOW_STOCK,
        ]);

        Stock::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Kertas HVS Orang Lain',
            'status' => StockStatus::AVAILABLE,
        ]);

        // Default query
        $all = $this->repository->getPaginatedForUser($user->id, null, null, 10);
        $this->assertCount(2, $all->items());

        // Search by name
        $searchRes = $this->repository->getPaginatedForUser($user->id, 'HVS', null, 10);
        $this->assertCount(1, $searchRes->items());
        $this->assertSame('Kertas HVS A4', $searchRes->items()[0]->name);

        // Search by sku
        $searchSku = $this->repository->getPaginatedForUser($user->id, 'PLP-02', null, 10);
        $this->assertCount(1, $searchSku->items());
        $this->assertSame('Pulpen Gel Hitam', $searchSku->items()[0]->name);

        // Search by description
        $searchDesc = $this->repository->getPaginatedForUser($user->id, 'tahan air', null, 10);
        $this->assertCount(1, $searchDesc->items());
        $this->assertSame('Pulpen Gel Hitam', $searchDesc->items()[0]->name);

        // Filter by status
        $statusRes = $this->repository->getPaginatedForUser($user->id, null, 'low_stock', 10);
        $this->assertCount(1, $statusRes->items());
        $this->assertSame(StockStatus::LOW_STOCK, $statusRes->items()[0]->status);
    }

    public function test_get_all_for_user(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(3)->create(['user_id' => $user->id]);

        $stocks = $this->repository->getAllForUser($user->id);
        $this->assertCount(3, $stocks);
    }

    public function test_find_by_id_and_user(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $user->id]);

        $found = $this->repository->findByIdAndUser($stock->id, $user->id);
        $this->assertNotNull($found);
        $this->assertSame($stock->id, $found->id);

        $notFound = $this->repository->findByIdAndUser($stock->id, 9999);
        $this->assertNull($notFound);
    }

    public function test_find_by_id(): void
    {
        $stock = Stock::factory()->create();

        $found = $this->repository->findById($stock->id);
        $this->assertNotNull($found);
        $this->assertSame($stock->id, $found->id);

        $notFound = $this->repository->findById(99999);
        $this->assertNull($notFound);
    }

    public function test_create_stock(): void
    {
        $user = User::factory()->create();

        $stock = $this->repository->create([
            'user_id' => $user->id,
            'name' => 'Barang Baru',
            'sku' => 'SKU-NEW-01',
            'description' => 'Deskripsi barang baru',
            'quantity' => 10,
            'unit' => 'pcs',
            'price' => 15000.00,
            'min_stock' => 5,
            'status' => 'available',
        ]);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'name' => 'Barang Baru',
            'sku' => 'SKU-NEW-01',
        ]);
    }

    public function test_update_stock(): void
    {
        $stock = Stock::factory()->create(['name' => 'Nama Lama']);

        $updated = $this->repository->update($stock, ['name' => 'Nama Baru']);

        $this->assertSame('Nama Baru', $updated->name);
        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'name' => 'Nama Baru',
        ]);
    }

    public function test_delete_stock(): void
    {
        $stock = Stock::factory()->create();

        $deleted = $this->repository->delete($stock);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }
}
