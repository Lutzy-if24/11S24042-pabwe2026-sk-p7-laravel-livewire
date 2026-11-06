<?php

declare(strict_types=1);

namespace App\Features\Stock\Livewire;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Livewire\Component;
use Livewire\WithFileUploads;

class StockDetail extends Component
{
    use WithFileUploads;

    public int $stockId;
    public string $name = '';
    public string $sku = '';
    public string $description = '';
    public int $quantity = 0;
    public string $unit = 'pcs';
    public ?float $price = null;
    public int $min_stock = 5;
    public string $status = 'available';
    public ?string $currentCoverPath = null;
    public $newCoverImage = null;
    public bool $isEditing = false;

    public function mount(Stock $stock): void
    {
        if ($stock->user_id !== (int) auth()->id()) {
            abort(403, 'Akses ditolak: Anda bukan pemilik stok ini.');
        }

        $this->stockId = $stock->id;
        $this->name = $stock->name;
        $this->sku = $stock->sku ?? '';
        $this->description = $stock->description ?? '';
        $this->quantity = $stock->quantity;
        $this->unit = $stock->unit;
        $this->price = $stock->price !== null ? (float) $stock->price : null;
        $this->min_stock = $stock->min_stock;
        $this->status = $stock->status->value;
        $this->currentCoverPath = $stock->cover_image_path;
    }

    public function toggleEdit(): void
    {
        $this->isEditing = ! $this->isEditing;
    }

    public function removeCover(StockService $stockService): void
    {
        $stock = $stockService->getStock($this->stockId, (int) auth()->id());
        $stockService->updateStock(
            stock: $stock,
            name: $this->name,
            sku: $this->sku ?: null,
            description: $this->description ?: null,
            quantity: $this->quantity,
            unit: $this->unit,
            price: $this->price,
            minStock: $this->min_stock,
            coverImage: null,
            removeCover: true
        );

        $this->currentCoverPath = null;
        session()->flash('success', 'Gambar sampul berhasil dihapus.');
    }

    public function save(StockService $stockService): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'newCoverImage' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Nama barang wajib diisi.',
            'quantity.required' => 'Jumlah stok wajib diisi.',
            'quantity.min' => 'Jumlah stok minimal 0.',
            'min_stock.min' => 'Batas minimum stok minimal 0.',
            'newCoverImage.image' => 'File sampul harus berupa gambar.',
            'newCoverImage.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        $stock = $stockService->getStock($this->stockId, (int) auth()->id());
        $file = $this->newCoverImage instanceof UploadedFile ? $this->newCoverImage : null;

        $updated = $stockService->updateStock(
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

        $this->currentCoverPath = $updated->cover_image_path;
        $this->newCoverImage = null;
        $this->status = $updated->status->value;
        $this->isEditing = false;

        session()->flash('success', 'Stok barang berhasil diperbarui.');
    }

    public function adjustQuantity(int $delta, StockService $stockService): void
    {
        try {
            $stock = $stockService->getStock($this->stockId, (int) auth()->id());
            $updated = $stockService->adjustQuantity($stock, $delta);
            $this->quantity = $updated->quantity;
            $this->status = $updated->status->value;
            session()->flash('success', 'Jumlah stok berhasil disesuaikan.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(StockService $stockService): View
    {
        $stock = $stockService->getStock($this->stockId, (int) auth()->id());

        return view('livewire.stock.stock-detail', [
            'stock' => $stock,
        ])->layout('components.layouts.app', ['title' => "Detail Stok - {$stock->name}"]);
    }
}
