<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Stock>
     */
    protected $model = Stock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(10, 100);
        $minStock = 5;

        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'sku' => 'SKU-' . strtoupper(fake()->bothify('??-####')),
            'description' => fake()->paragraph(),
            'quantity' => $quantity,
            'unit' => fake()->randomElement(['pcs', 'box', 'unit', 'kg', 'rim']),
            'price' => fake()->randomFloat(2, 5000, 500000),
            'min_stock' => $minStock,
            'status' => StockStatus::fromQuantity($quantity, $minStock),
            'cover_image_path' => null,
        ];
    }

    /**
     * Indicate that the stock is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 25,
            'min_stock' => 5,
            'status' => StockStatus::AVAILABLE,
        ]);
    }

    /**
     * Indicate that the stock is low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 3,
            'min_stock' => 5,
            'status' => StockStatus::LOW_STOCK,
        ]);
    }

    /**
     * Indicate that the stock is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
            'min_stock' => 5,
            'status' => StockStatus::OUT_OF_STOCK,
        ]);
    }
}
