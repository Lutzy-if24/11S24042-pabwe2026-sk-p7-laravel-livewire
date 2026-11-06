<?php

declare(strict_types=1);

namespace App\Features\Stock\Repositories;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Models\Stock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class StockRepository implements StockRepositoryInterface
{
    public function getPaginatedForUser(int $userId, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Stock::query()->where('user_id', $userId);

        if ($search !== null && trim($search) !== '') {
            $searchTerm = trim($search);
            $query->where(function ($q) use ($searchTerm): void {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($status !== null && trim($status) !== '') {
            $query->where('status', trim($status));
        }

        return $query->latest('id')->paginate($perPage);
    }

    public function getAllForUser(int $userId): Collection
    {
        return Stock::query()->where('user_id', $userId)->latest('id')->get();
    }

    public function findByIdAndUser(int $id, int $userId): ?Stock
    {
        return Stock::query()->where('id', $id)->where('user_id', $userId)->first();
    }

    public function findById(int $id): ?Stock
    {
        return Stock::query()->find($id);
    }

    public function create(array $data): Stock
    {
        return Stock::create($data);
    }

    public function update(Stock $stock, array $data): Stock
    {
        $stock->update($data);

        return $stock->fresh() ?? $stock;
    }

    public function delete(Stock $stock): bool
    {
        return (bool) $stock->delete();
    }
}
