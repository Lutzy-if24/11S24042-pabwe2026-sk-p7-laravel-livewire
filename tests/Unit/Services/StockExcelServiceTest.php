<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

final class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockExcelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StockExcelService::class);
    }

    public function test_export_stocks(): void
    {
        $user = User::factory()->create();
        Stock::factory()->create([
            'user_id' => $user->id,
            'name' => 'Barang Export',
            'sku' => 'SKU-EXP-01',
            'quantity' => 15,
            'unit' => 'box',
            'price' => 50000.00,
            'min_stock' => 5,
            'status' => StockStatus::AVAILABLE,
        ]);

        $response = $this->service->export($user->id);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));

        // Capture output stream
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertNotEmpty($content);
    }

    public function test_import_stocks_successfully(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status'],
            ['Kertas HVS', 'SKU-HVS-01', 25, 'rim', 45000, 10, 'available'],
            ['', '', '', '', '', '', ''], // empty row in middle to verify line 111 skip
            ['Pulpen Gel', 'SKU-PLP-02', 3, 'pcs', 3500, 5, 'low_stock'],
            ['Buku Tulis', 'SKU-BKU-03', 0, 'pcs', 5000, 5, ''], // status derived
            ['Spidol Habis', 'SKU-SPD-04', 0, 'pcs', 8000, 5, 'out_of_stock'], // explicit out_of_stock
        ], null, 'A1', true);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $importedCount = $this->service->import($user->id, $tempPath);

        @unlink($tempPath);

        $this->assertSame(4, $importedCount);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Kertas HVS',
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Pulpen Gel',
            'status' => 'low_stock',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Buku Tulis',
            'status' => 'out_of_stock',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $user->id,
            'name' => 'Spidol Habis',
            'status' => 'out_of_stock',
        ]);
    }

    public function test_import_throws_exception_when_file_is_empty(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $tempPath = tempnam(sys_get_temp_dir(), 'test_empty_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header Excel tidak ditemukan.');

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_import_throws_exception_when_header_is_missing_required_columns(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Deskripsi', 'Kategori'],
            ['Nilai 1', 'Nilai 2'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_no_header_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header Excel tidak valid: kolom wajib "name" dan "qty" tidak ditemukan.');

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_import_throws_exception_when_name_is_empty(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'qty'],
            ['', 10],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_empty_name_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama barang pada baris 2 tidak boleh kosong.');

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_import_throws_exception_when_quantity_is_invalid(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'qty'],
            ['Barang X', -5],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_invalid_qty_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Jumlah (qty) pada baris 2 harus berupa angka >= 0.');

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_import_throws_exception_when_min_stock_is_invalid(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'qty', 'min_stock'],
            ['Barang X', 10, -1],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_invalid_min_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Batas minimum stok pada baris 2 harus berupa angka >= 0.');

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_import_throws_exception_when_price_is_invalid(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'qty', 'price'],
            ['Barang X', 10, -1000],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_invalid_price_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Harga pada baris 2 harus berupa angka >= 0.');

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_import_throws_exception_when_status_is_unknown(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'qty', 'min_stock', 'status'],
            ['Barang X', 10, 5, 'status_aneh'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_unknown_status_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Status 'status_aneh' pada baris 2 tidak valid.");

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }

    public function test_import_throws_exception_when_status_contradicts_quantity_and_min_stock(): void
    {
        $user = User::factory()->create();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'qty', 'min_stock', 'status'],
            ['Barang X', 0, 5, 'available'], // 0 qty cannot be available
        ], null, 'A1', true);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_contradict_status_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Status 'available' tidak sesuai dengan quantity (0) dan min_stock (5) pada baris 2.");

        try {
            $this->service->import($user->id, $tempPath);
        } finally {
            @unlink($tempPath);
        }
    }
}
