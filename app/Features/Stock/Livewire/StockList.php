<?php

declare(strict_types=1);

namespace App\Features\Stock\Livewire;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Services\StockExcelService;
use App\Features\Stock\Services\StockService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class StockList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    // Modal state for create / edit
    public bool $isModalOpen = false;
    public ?int $editingStockId = null;
    public string $name = '';
    public string $sku = '';
    public string $description = '';
    public int $quantity = 0;
    public string $unit = 'pcs';
    public ?float $price = null;
    public int $min_stock = 5;
    public $coverImage = null;

    // Excel import state
    public $excelFile = null;

    // AI generator state
    public bool $isAiModalOpen = false;
    public string $aiPrompt = '';
    /** @var list<array{name: string, sku: ?string, description: string, quantity: int, unit: string, price: ?float, min_stock: int, selected: bool}> */
    public array $aiGeneratedStocks = [];
    public bool $isGeneratingAi = false;

    /**
     * Reset pagination when search or status filter changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetModal();
        $this->isModalOpen = true;
    }

    public function openEditModal(int $id, StockService $stockService): void
    {
        $this->resetModal();
        $stock = $stockService->getStock($id, (int) auth()->id());

        $this->editingStockId = $stock->id;
        $this->name = $stock->name;
        $this->sku = $stock->sku ?? '';
        $this->description = $stock->description ?? '';
        $this->quantity = $stock->quantity;
        $this->unit = $stock->unit;
        $this->price = $stock->price !== null ? (float) $stock->price : null;
        $this->min_stock = $stock->min_stock;
        $this->isModalOpen = true;
    }

    public function closeModal(): void
    {
        $this->resetModal();
        $this->isModalOpen = false;
    }

    public function resetModal(): void
    {
        $this->resetValidation();
        $this->editingStockId = null;
        $this->name = '';
        $this->sku = '';
        $this->description = '';
        $this->quantity = 0;
        $this->unit = 'pcs';
        $this->price = null;
        $this->min_stock = 5;
        $this->coverImage = null;
    }

    public function saveStock(StockService $stockService): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'coverImage' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'quantity.required' => 'Jumlah stok wajib diisi.',
            'quantity.min' => 'Jumlah stok minimal 0.',
            'min_stock.min' => 'Batas minimum stok minimal 0.',
            'coverImage.image' => 'File cover harus berupa gambar.',
            'coverImage.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $file = $this->coverImage instanceof UploadedFile ? $this->coverImage : null;

        if ($this->editingStockId) {
            $stock = $stockService->getStock($this->editingStockId, (int) auth()->id());
            $stockService->updateStock(
                stock: $stock,
                name: $this->name,
                sku: $this->sku ?: null,
                description: $this->description ?: null,
                quantity: $this->quantity,
                unit: $this->unit,
                price: $this->price,
                minStock: $this->min_stock,
                coverImage: $file
            );
            session()->flash('success', 'Stok barang berhasil diperbarui.');
        } else {
            $stockService->createStock(
                userId: (int) auth()->id(),
                name: $this->name,
                sku: $this->sku ?: null,
                description: $this->description ?: null,
                quantity: $this->quantity,
                unit: $this->unit,
                price: $this->price,
                minStock: $this->min_stock,
                coverImage: $file
            );
            session()->flash('success', 'Stok barang berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function adjustQuantity(int $id, int $delta, StockService $stockService): void
    {
        try {
            $stock = $stockService->getStock($id, (int) auth()->id());
            $stockService->adjustQuantity($stock, $delta);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteStock(int $id, StockService $stockService): void
    {
        $stock = $stockService->getStock($id, (int) auth()->id());
        $stockService->deleteStock($stock);
        session()->flash('success', 'Stok barang berhasil dihapus.');
    }

    // ================= EXCEL ACTIONS =================
    public function exportExcel(StockExcelService $excelService): mixed
    {
        return $excelService->export((int) auth()->id());
    }

    public function importExcel(StockExcelService $excelService): void
    {
        $this->validate([
            'excelFile' => ['required', 'file', 'mimes:xlsx,xls'],
        ], [
            'excelFile.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'excelFile.mimes' => 'Format file harus berupa Excel (.xlsx).',
        ]);

        try {
            $count = $excelService->import((int) auth()->id(), $this->excelFile->getRealPath());
            session()->flash('success', "Berhasil mengimpor {$count} item stok dari file Excel.");
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }

        $this->excelFile = null;
    }

    // ================= AI GENERATOR ACTIONS =================
    public function openAiModal(): void
    {
        $this->aiPrompt = '';
        $this->aiGeneratedStocks = [];
        $this->isAiModalOpen = true;
    }

    public function closeAiModal(): void
    {
        $this->isAiModalOpen = false;
        $this->aiPrompt = '';
        $this->aiGeneratedStocks = [];
    }

    public function generateWithAi(AiClientInterface $aiClient): void
    {
        $this->validate([
            'aiPrompt' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'aiPrompt.required' => 'Topik atau kategori inventaris wajib diisi.',
            'aiPrompt.min' => 'Topik minimal 3 karakter.',
        ]);

        $this->isGeneratingAi = true;

        try {
            $items = $aiClient->generateStocks($this->aiPrompt);
            $this->aiGeneratedStocks = array_map(fn (GeneratedStockItem $item) => [
                'name' => $item->name,
                'sku' => $item->sku,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'price' => $item->price,
                'min_stock' => $item->min_stock,
                'selected' => true,
            ], $items);

            if (empty($this->aiGeneratedStocks)) {
                session()->flash('error', 'Tidak ada rekomendasi stok yang berhasil di-generate.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menghubungkan ke AI: ' . $e->getMessage());
        } finally {
            $this->isGeneratingAi = false;
        }
    }

    public function saveAiStocks(StockService $stockService): void
    {
        $savedCount = 0;
        foreach ($this->aiGeneratedStocks as $item) {
            if (! empty($item['selected'])) {
                $stockService->createStock(
                    userId: (int) auth()->id(),
                    name: $item['name'],
                    sku: $item['sku'] ?: null,
                    description: $item['description'] ?: null,
                    quantity: (int) ($item['quantity'] ?? 0),
                    unit: $item['unit'] ?: 'pcs',
                    price: isset($item['price']) && is_numeric($item['price']) ? (float) $item['price'] : null,
                    minStock: (int) ($item['min_stock'] ?? 5)
                );
                $savedCount++;
            }
        }

        if ($savedCount > 0) {
            session()->flash('success', "Berhasil menambahkan {$savedCount} item stok dari AI.");
        }

        $this->closeAiModal();
    }

    public function render(StockService $stockService): View
    {
        $stocks = $stockService->getStocksForUser(
            userId: (int) auth()->id(),
            search: $this->search ?: null,
            status: $this->statusFilter ?: null,
            perPage: 6
        );

        return view('livewire.stock.stock-list', [
            'stocks' => $stocks,
        ])->layout('components.layouts.app', ['title' => 'Daftar Stok - Stock']);
    }
}
