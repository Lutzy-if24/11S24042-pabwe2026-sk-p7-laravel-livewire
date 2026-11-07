<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]
        );

        $member = User::firstOrCreate(
            ['username' => 'member'],
            [
                'name' => 'Member User',
                'email' => 'member@example.com',
                'password' => Hash::make('member'),
                'role' => 'member',
            ]
        );

        $ifs = User::firstOrCreate(
            ['username' => 'ifs24042'],
            [
                'name' => 'Martulut Simbolon',
                'email' => 'ifs24042@gmail.com',
                'password' => Hash::make('ifs24042'),
                'role' => 'member',
            ]
        );

        // Seed sample stocks for member
        if ($member->stocks()->count() === 0) {
            // ... 7 blok Stock::create() milik $member tetap sama seperti file Anda ...
        }

        // Seed sample stocks for ifs24042
        if ($ifs->stocks()->count() === 0) {
            Stock::create([
                'user_id' => $ifs->id,
                'name' => 'Keyboard',
                'sku' => 'SKU-KBD-0008',
                'description' => 'Keyboard mekanik.',
                'quantity' => 10,
                'unit' => 'pcs',
                'price' => 350000.00,
                'min_stock' => 5,
                'status' => StockStatus::AVAILABLE,
            ]);

            Stock::create([
                'user_id' => $ifs->id,
                'name' => 'Mouse',
                'sku' => 'SKU-MSE-0009',
                'description' => 'Mouse nirkabel.',
                'quantity' => 3,
                'unit' => 'pcs',
                'price' => 150000.00,
                'min_stock' => 5,
                'status' => StockStatus::LOW_STOCK,
            ]);
        }
    }
}