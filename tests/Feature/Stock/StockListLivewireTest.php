<?php

declare(strict_types=1);

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

final class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->member()->create();
        Storage::fake('public');
    }

    public function test_renders_stock_list_page_and_empty_state(): void
    {
        $this->actingAs($this->member);

        Livewire::test(StockList::class)
            ->assertStatus(200)
            ->assertSee('Daftar Stok')
            ->assertSee('Belum ada stok barang');
    }

    public function test_renders_stocks_with_cover_and_badges(): void
    {
        $this->actingAs($this->member);

        $stock = Stock::factory()->create([
            'user_id' => $this->member->id,
            'name' => 'Kertas A4 Spesial',
            'sku' => 'SKU-KRT-01',
            'description' => 'Deskripsi markdown penting',
            'quantity' => 20,
            'unit' => 'rim',
            'price' => 50000.00,
            'min_stock' => 5,
            'status' => StockStatus::AVAILABLE,
            'cover_image_path' => 'stocks/covers/sample.jpg',
        ]);

        Livewire::test(StockList::class)
            ->assertStatus(200)
            ->assertSee('Kertas A4 Spesial')
            ->assertSee('SKU-KRT-01')
            ->assertSee('Tersedia')
            ->assertSee('20 rim')
            ->assertSee('50.000');
    }

    public function test_updating_search_and_status_filter(): void
    {
        $this->actingAs($this->member);

        Livewire::test(StockList::class)
            ->set('search', 'Kertas')
            ->assertSet('search', 'Kertas')
            ->set('statusFilter', 'available')
            ->assertSet('statusFilter', 'available');
    }

    public function test_create_stock_via_modal(): void
    {
        $this->actingAs($this->member);

        $file = UploadedFile::fake()->image('stock_cover.jpg');

        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->assertSet('isModalOpen', true)
            ->set('name', 'Barang Baru Livewire')
            ->set('sku', 'SKU-BRG-01')
            ->set('description', 'Catatan spesifikasi')
            ->set('quantity', 15)
            ->set('unit', 'pcs')
            ->set('price', 25000.00)
            ->set('min_stock', 5)
            ->set('coverImage', $file)
            ->call('saveStock')
            ->assertSet('isModalOpen', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $this->member->id,
            'name' => 'Barang Baru Livewire',
            'sku' => 'SKU-BRG-01',
            'quantity' => 15,
            'status' => 'available',
        ]);
    }

    public function test_validation_errors_when_saving_stock(): void
    {
        $this->actingAs($this->member);

        Livewire::test(StockList::class)
            ->set('name', '')
            ->set('quantity', -1)
            ->set('min_stock', -1)
            ->call('saveStock')
            ->assertHasErrors(['name', 'quantity', 'min_stock']);
    }

    public function test_edit_stock_via_modal(): void
    {
        $this->actingAs($this->member);

        $stock = Stock::factory()->create([
            'user_id' => $this->member->id,
            'name' => 'Nama Sebelum Edit',
            'quantity' => 10,
            'unit' => 'pcs',
            'min_stock' => 5,
            'status' => StockStatus::AVAILABLE,
        ]);

        Livewire::test(StockList::class)
            ->call('openEditModal', $stock->id)
            ->assertSet('editingStockId', $stock->id)
            ->assertSet('name', 'Nama Sebelum Edit')
            ->set('name', 'Nama Setelah Edit')
            ->set('quantity', 2)
            ->call('saveStock')
            ->assertSet('isModalOpen', false);

        $this->assertDatabaseHas('stocks', [
            'id' => $stock->id,
            'name' => 'Nama Setelah Edit',
            'quantity' => 2,
            'status' => 'low_stock',
        ]);
    }

    public function test_close_modal_resets_state(): void
    {
        $this->actingAs($this->member);

        Livewire::test(StockList::class)
            ->call('openCreateModal')
            ->set('name', 'Teks Sementara')
            ->call('closeModal')
            ->assertSet('isModalOpen', false)
            ->assertSet('name', '');
    }

    public function test_adjust_quantity(): void
    {
        $this->actingAs($this->member);

        $stock = Stock::factory()->create([
            'user_id' => $this->member->id,
            'quantity' => 5,
            'min_stock' => 5,
            'status' => StockStatus::LOW_STOCK,
        ]);

        // Increase quantity (+2 -> 7, should become available)
        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, 2);

        $this->assertSame(7, $stock->fresh()->quantity);
        $this->assertSame(StockStatus::AVAILABLE, $stock->fresh()->status);

        // Decrease quantity (-7 -> 0, should become out_of_stock)
        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, -7);

        $this->assertSame(0, $stock->fresh()->quantity);
        $this->assertSame(StockStatus::OUT_OF_STOCK, $stock->fresh()->status);
    }

    public function test_adjust_quantity_cannot_go_below_zero(): void
    {
        $this->actingAs($this->member);

        $stock = Stock::factory()->create([
            'user_id' => $this->member->id,
            'quantity' => 2,
            'min_stock' => 5,
            'status' => StockStatus::LOW_STOCK,
        ]);

        Livewire::test(StockList::class)
            ->call('adjustQuantity', $stock->id, -5);

        // Quantity remains unchanged
        $this->assertSame(2, $stock->fresh()->quantity);
    }

    public function test_delete_stock(): void
    {
        $this->actingAs($this->member);

        $stock = Stock::factory()->create(['user_id' => $this->member->id]);

        Livewire::test(StockList::class)
            ->call('deleteStock', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_export_excel(): void
    {
        $this->actingAs($this->member);

        Stock::factory()->create(['user_id' => $this->member->id]);

        $component = Livewire::test(StockList::class)
            ->call('exportExcel');

        $response = $component->instance()->exportExcel(app(StockExcelService::class));
        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_import_excel(): void
    {
        $this->actingAs($this->member);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status'],
            ['Barang Impor Livewire', 'SKU-IMP-01', 12, 'box', 35000, 5, 'available'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_lw_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        $content = file_get_contents($tempPath);
        @unlink($tempPath);

        $uploadedFile = UploadedFile::fake()->createWithContent('stocks.xlsx', $content);

        Livewire::test(StockList::class)
            ->set('excelFile', $uploadedFile)
            ->call('importExcel');

        $this->assertDatabaseHas('stocks', [
            'user_id' => $this->member->id,
            'name' => 'Barang Impor Livewire',
            'sku' => 'SKU-IMP-01',
            'quantity' => 12,
            'unit' => 'box',
        ]);
    }

    public function test_import_excel_validation_and_error_handling(): void
    {
        $this->actingAs($this->member);

        Livewire::test(StockList::class)
            ->set('excelFile', null)
            ->call('importExcel')
            ->assertHasErrors(['excelFile']);

        // Invalid file format throwing exception
        $badFile = UploadedFile::fake()->create('invalid.xlsx', 10);
        Livewire::test(StockList::class)
            ->set('excelFile', $badFile)
            ->call('importExcel');
    }

    public function test_ai_modal_open_close_and_generate(): void
    {
        $this->actingAs($this->member);

        $aiStub = $this->createStub(AiClientInterface::class);
        $aiStub->method('generateStocks')->willReturn([
            new GeneratedStockItem('AI Item 1', 'SKU-AI-01', 'Desc 1', 10, 'pcs', 10000.0, 5),
            new GeneratedStockItem('AI Item 2', 'SKU-AI-02', 'Desc 2', 20, 'box', 20000.0, 5),
        ]);

        $this->app->instance(AiClientInterface::class, $aiStub);

        Livewire::test(StockList::class)
            ->call('openAiModal')
            ->assertSet('isAiModalOpen', true)
            ->set('aiPrompt', 'Peralatan kantor ATK')
            ->call('generateWithAi')
            ->assertCount('aiGeneratedStocks', 2)
            ->call('saveAiStocks')
            ->assertSet('isAiModalOpen', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $this->member->id,
            'name' => 'AI Item 1',
            'sku' => 'SKU-AI-01',
        ]);
        $this->assertDatabaseHas('stocks', [
            'user_id' => $this->member->id,
            'name' => 'AI Item 2',
            'sku' => 'SKU-AI-02',
        ]);
    }

    public function test_ai_generate_validation_and_empty_result(): void
    {
        $this->actingAs($this->member);

        $aiStub = $this->createStub(AiClientInterface::class);
        $aiStub->method('generateStocks')->willReturn([]);
        $this->app->instance(AiClientInterface::class, $aiStub);

        Livewire::test(StockList::class)
            ->set('aiPrompt', '')
            ->call('generateWithAi')
            ->assertHasErrors(['aiPrompt']);

        Livewire::test(StockList::class)
            ->set('aiPrompt', 'Tes')
            ->call('generateWithAi')
            ->assertCount('aiGeneratedStocks', 0);
    }

    public function test_ai_generate_handles_exception(): void
    {
        $this->actingAs($this->member);

        $aiStub = $this->createStub(AiClientInterface::class);
        $aiStub->method('generateStocks')->willThrowException(new \RuntimeException('API timeout'));
        $this->app->instance(AiClientInterface::class, $aiStub);

        Livewire::test(StockList::class)
            ->set('aiPrompt', 'Topik error')
            ->call('generateWithAi')
            ->assertCount('aiGeneratedStocks', 0);
    }
}
