<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Features\Stock\Enums\StockStatus;
use PHPUnit\Framework\TestCase;

final class StockStatusTest extends TestCase
{
    public function test_enum_has_expected_values(): void
    {
        $this->assertSame('available', StockStatus::AVAILABLE->value);
        $this->assertSame('low_stock', StockStatus::LOW_STOCK->value);
        $this->assertSame('out_of_stock', StockStatus::OUT_OF_STOCK->value);
    }

    public function test_label_returns_correct_indonesian_text(): void
    {
        $this->assertSame('Tersedia', StockStatus::AVAILABLE->label());
        $this->assertSame('Stok Menipis', StockStatus::LOW_STOCK->label());
        $this->assertSame('Habis', StockStatus::OUT_OF_STOCK->label());
    }

    public function test_badge_class_returns_correct_color_classes(): void
    {
        $this->assertStringContainsString('bg-green-100', StockStatus::AVAILABLE->badgeClass());
        $this->assertStringContainsString('bg-yellow-100', StockStatus::LOW_STOCK->badgeClass());
        $this->assertStringContainsString('bg-red-100', StockStatus::OUT_OF_STOCK->badgeClass());
    }

    public function test_from_quantity_boundary_synchronization(): void
    {
        // Boundary: 0 and negative -> OUT_OF_STOCK
        $this->assertSame(StockStatus::OUT_OF_STOCK, StockStatus::fromQuantity(0, 5));
        $this->assertSame(StockStatus::OUT_OF_STOCK, StockStatus::fromQuantity(-1, 5));

        // Boundary: quantity <= min_stock -> LOW_STOCK
        $this->assertSame(StockStatus::LOW_STOCK, StockStatus::fromQuantity(1, 5));
        $this->assertSame(StockStatus::LOW_STOCK, StockStatus::fromQuantity(5, 5)); // == min_stock

        // Boundary: quantity > min_stock -> AVAILABLE
        $this->assertSame(StockStatus::AVAILABLE, StockStatus::fromQuantity(6, 5)); // > min_stock
        $this->assertSame(StockStatus::AVAILABLE, StockStatus::fromQuantity(100, 5));
    }
}
