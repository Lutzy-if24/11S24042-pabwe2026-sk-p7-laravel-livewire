<?php

declare(strict_types=1);

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

final class GeneratedStockItemTest extends TestCase
{
    public function test_can_instantiate_dto_with_defaults(): void
    {
        $item = new GeneratedStockItem('Barang Test');

        $this->assertSame('Barang Test', $item->name);
        $this->assertNull($item->sku);
        $this->assertSame('', $item->description);
        $this->assertSame(0, $item->quantity);
        $this->assertSame('pcs', $item->unit);
        $this->assertNull($item->price);
        $this->assertSame(5, $item->min_stock);
    }

    public function test_can_instantiate_dto_with_custom_values(): void
    {
        $item = new GeneratedStockItem(
            name: 'Kertas HVS',
            sku: 'SKU-HVS-01',
            description: 'Ukuran A4 80gr',
            quantity: 25,
            unit: 'rim',
            price: 45000.00,
            min_stock: 10
        );

        $this->assertSame('Kertas HVS', $item->name);
        $this->assertSame('SKU-HVS-01', $item->sku);
        $this->assertSame('Ukuran A4 80gr', $item->description);
        $this->assertSame(25, $item->quantity);
        $this->assertSame('rim', $item->unit);
        $this->assertSame(45000.00, $item->price);
        $this->assertSame(10, $item->min_stock);
    }
}
