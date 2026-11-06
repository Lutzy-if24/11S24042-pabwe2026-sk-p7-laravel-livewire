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

        // Seed sample stocks for member
        if ($member->stocks()->count() === 0) {
            Stock::create([
                'user_id' => $member->id,
                'name' => 'Kertas HVS A4 80gr',
                'sku' => 'SKU-HVS-0001',
                'description' => "Kertas HVS ukuran A4 dengan gramatur 80 gsm.\n\n### Spesifikasi\n- Warna: Putih terang\n- Isi: 500 lembar per rim\n- Cocok untuk printer inkjet & laserjet",
                'quantity' => 50,
                'unit' => 'rim',
                'price' => 45000.00,
                'min_stock' => 10,
                'status' => StockStatus::AVAILABLE,
            ]);

            Stock::create([
                'user_id' => $member->id,
                'name' => 'Pulpen Gel Hitam 0.5mm',
                'sku' => 'SKU-PLP-0002',
                'description' => "Pulpen gel tinta hitam cepat kering dengan ujung jarum 0.5mm halus.\n\n- Nyaman digenggam\n- Tidak mudah bocor",
                'quantity' => 120,
                'unit' => 'pcs',
                'price' => 3500.00,
                'min_stock' => 20,
                'status' => StockStatus::AVAILABLE,
            ]);

            Stock::create([
                'user_id' => $member->id,
                'name' => 'Tinta Printer Epson L3110 Black',
                'sku' => 'SKU-TNT-0003',
                'description' => "Tinta refill original seri 003 untuk printer Epson EcoTank.\n\n- Kapasitas: 65ml\n- Kualitas cetak pekat dan tahan lama",
                'quantity' => 15,
                'unit' => 'botol',
                'price' => 85000.00,
                'min_stock' => 5,
                'status' => StockStatus::AVAILABLE,
            ]);

            Stock::create([
                'user_id' => $member->id,
                'name' => 'Map Folder Dokumen F4',
                'sku' => 'SKU-MAP-0004',
                'description' => "Map dokumen bahan plastik tebal ukuran folio/F4 dengan kancing penutup ganda.",
                'quantity' => 4,
                'unit' => 'box',
                'price' => 25000.00,
                'min_stock' => 5,
                'status' => StockStatus::LOW_STOCK,
            ]);

            Stock::create([
                'user_id' => $member->id,
                'name' => 'Stapler Joyko HD-10',
                'sku' => 'SKU-STP-0005',
                'description' => "Stapler ukuran standar HD-10 untuk kebutuhan kantor harian. Menggunakan isi staples no. 10.",
                'quantity' => 2,
                'unit' => 'pcs',
                'price' => 15000.00,
                'min_stock' => 5,
                'status' => StockStatus::LOW_STOCK,
            ]);

            Stock::create([
                'user_id' => $member->id,
                'name' => 'Buku Catatan Spiral A5',
                'sku' => 'SKU-BKN-0006',
                'description' => "Notebook spiral hardcover A5 bergaris 100 lembar.",
                'quantity' => 0,
                'unit' => 'pcs',
                'price' => 12000.00,
                'min_stock' => 10,
                'status' => StockStatus::OUT_OF_STOCK,
            ]);

            Stock::create([
                'user_id' => $member->id,
                'name' => 'Flashdisk SanDisk 32GB',
                'sku' => 'SKU-USB-0007',
                'description' => "Flash drive USB 3.0 kecepatan transfer hingga 130MB/s.",
                'quantity' => 0,
                'unit' => 'unit',
                'price' => 65000.00,
                'min_stock' => 5,
                'status' => StockStatus::OUT_OF_STOCK,
            ]);
        }
    }
}
