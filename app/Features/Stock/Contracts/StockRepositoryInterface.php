<?php

declare(strict_types=1);

namespace App\Features\Stock\Contracts;

use App\Features\Stock\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockRepositoryInterface
{
    /**
     * Get paginated stocks for a specific user with optional search and status filters.
     */
    public function getPaginatedForUser(int $userId, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all stocks for a specific user.
     *
     * @return Collection<int, Stock>
     */
    public function getAllForUser(int $userId): Collection;

    /**
     * Find a stock by ID belonging to a specific user.
     */
    public function findByIdAndUser(int $id, int $userId): ?Stock;

    /**
     * Find a stock by ID.
     */
    public function findById(int $id): ?Stock;

    /**
     * Create a new stock item.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Stock;

    /**
     * Update an existing stock item.
     *
     * @param array<string, mixed> $data
     */
    public function update(Stock $stock, array $data): Stock;

    /**
     * Delete a stock item.
     */
    public function delete(Stock $stock): bool;
}
