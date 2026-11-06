<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Database\Factories\StockFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_casts_status_to_enum_and_numeric_types(): void
    {
        $stock = Stock::factory()->create([
            'status' => 'available',
            'quantity' => 15,
            'min_stock' => 5,
            'price' => 50000.50,
        ]);

        $this->assertInstanceOf(StockStatus::class, $stock->status);
        $this->assertSame(StockStatus::AVAILABLE, $stock->status);
        $this->assertIsInt($stock->quantity);
        $this->assertSame(15, $stock->quantity);
        $this->assertIsInt($stock->min_stock);
        $this->assertSame(5, $stock->min_stock);
        $this->assertSame('50000.50', (string) $stock->price);
    }

    public function test_stock_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $stock->user);
        $this->assertSame($user->id, $stock->user->id);
    }

    public function test_factory_method_creates_stock_factory(): void
    {
        $factory = Stock::factory();
        $this->assertInstanceOf(StockFactory::class, $factory);

        $available = Stock::factory()->available()->make();
        $this->assertSame(StockStatus::AVAILABLE, $available->status);
        $this->assertSame(25, $available->quantity);

        $low = Stock::factory()->lowStock()->make();
        $this->assertSame(StockStatus::LOW_STOCK, $low->status);
        $this->assertSame(3, $low->quantity);

        $out = Stock::factory()->outOfStock()->make();
        $this->assertSame(StockStatus::OUT_OF_STOCK, $out->status);
        $this->assertSame(0, $out->quantity);
    }
}
