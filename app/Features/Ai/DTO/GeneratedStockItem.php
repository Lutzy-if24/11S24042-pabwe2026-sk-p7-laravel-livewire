<?php

declare(strict_types=1);

namespace App\Features\Ai\DTO;

final readonly class GeneratedStockItem
{
    public function __construct(
        public string $name,
        public ?string $sku = null,
        public string $description = '',
        public int $quantity = 0,
        public string $unit = 'pcs',
        public ?float $price = null,
        public int $min_stock = 5,
    ) {}
}
