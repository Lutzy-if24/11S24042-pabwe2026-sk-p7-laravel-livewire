<?php

declare(strict_types=1);

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

final class StockService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository
    ) {}

    /**
     * Get paginated stocks for the specified user.
     */
    public function getStocksForUser(
        int $userId,
        ?string $search = null,
        ?string $status = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        return $this->stockRepository->getPaginatedForUser($userId, $search, $status, $perPage);
    }

    /**
     * Get all stocks for a specific user.
     *
     * @return Collection<int, Stock>
     */
    public function getAllStocksForUser(int $userId): Collection
    {
        return $this->stockRepository->getAllForUser($userId);
    }

    /**
     * Get a single stock by ID for a user.
     */
    public function getStock(int $id, int $userId): Stock
    {
        $stock = $this->stockRepository->findByIdAndUser($id, $userId);

        if (! $stock) {
            throw (new ModelNotFoundException)->setModel(Stock::class, [$id]);
        }

        return $stock;
    }

    /**
     * Create a new stock item.
     */
    public function createStock(
        int $userId,
        string $name,
        ?string $sku = null,
        ?string $description = null,
        int $quantity = 0,
        string $unit = 'pcs',
        ?float $price = null,
        int $minStock = 5,
        ?UploadedFile $coverImage = null,
        ?StockStatus $status = null
    ): Stock {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Jumlah stok tidak boleh kurang dari 0.');
        }

        if ($minStock < 0) {
            throw new InvalidArgumentException('Batas minimum stok tidak boleh kurang dari 0.');
        }

        $statusEnum = $status ?? StockStatus::fromQuantity($quantity, $minStock);

        $coverPath = null;
        if ($coverImage !== null) {
            $coverPath = $coverImage->store('stocks/covers', 'public');
        }

        return $this->stockRepository->create([
            'user_id' => $userId,
            'name' => $name,
            'sku' => $sku,
            'description' => $description,
            'quantity' => $quantity,
            'unit' => $unit,
            'price' => $price,
            'min_stock' => $minStock,
            'status' => $statusEnum->value,
            'cover_image_path' => $coverPath,
        ]);
    }

    /**
     * Update an existing stock item.
     */
    public function updateStock(
        Stock $stock,
        string $name,
        ?string $sku = null,
        ?string $description = null,
        ?int $quantity = null,
        ?string $unit = null,
        ?float $price = null,
        ?int $minStock = null,
        ?UploadedFile $coverImage = null,
        bool $removeCover = false,
        ?StockStatus $status = null
    ): Stock {
        $qty = $quantity ?? $stock->quantity;
        if ($qty < 0) {
            throw new InvalidArgumentException('Jumlah stok tidak boleh kurang dari 0.');
        }

        $min = $minStock ?? $stock->min_stock;
        if ($min < 0) {
            throw new InvalidArgumentException('Batas minimum stok tidak boleh kurang dari 0.');
        }

        $statusEnum = $status ?? StockStatus::fromQuantity($qty, $min);

        $data = [
            'name' => $name,
            'sku' => $sku,
            'description' => $description,
            'quantity' => $qty,
            'unit' => $unit ?? $stock->unit,
            'price' => $price,
            'min_stock' => $min,
            'status' => $statusEnum->value,
        ];

        if ($removeCover && $stock->cover_image_path !== null) {
            Storage::disk('public')->delete($stock->cover_image_path);
            $data['cover_image_path'] = null;
        }

        if ($coverImage !== null) {
            if ($stock->cover_image_path !== null) {
                Storage::disk('public')->delete($stock->cover_image_path);
            }
            $data['cover_image_path'] = $coverImage->store('stocks/covers', 'public');
        }

        return $this->stockRepository->update($stock, $data);
    }

    /**
     * Adjust the quantity of a stock item with automatic status synchronization.
     */
    public function adjustQuantity(Stock $stock, int $delta): Stock
    {
        $newQuantity = $stock->quantity + $delta;
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('Jumlah stok tidak boleh kurang dari 0.');
        }

        $newStatus = StockStatus::fromQuantity($newQuantity, $stock->min_stock);

        return $this->stockRepository->update($stock, [
            'quantity' => $newQuantity,
            'status' => $newStatus->value,
        ]);
    }

    /**
     * Delete a stock item and its associated cover image.
     */
    public function deleteStock(Stock $stock): bool
    {
        if ($stock->cover_image_path !== null) {
            Storage::disk('public')->delete($stock->cover_image_path);
        }

        return $this->stockRepository->delete($stock);
    }
}
