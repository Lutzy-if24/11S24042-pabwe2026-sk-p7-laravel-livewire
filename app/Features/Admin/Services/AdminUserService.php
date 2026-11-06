<?php

declare(strict_types=1);

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AdminUserService
{
    /**
     * Get paginated users with their stocks count for admin overview.
     */
    public function getPaginatedUsers(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = User::query()->withCount('stocks');

        if ($search !== null && trim($search) !== '') {
            $searchTerm = trim($search);
            $query->where(function ($q) use ($searchTerm): void {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('username', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Get all users with their stocks count.
     *
     * @return Collection<int, User>
     */
    public function getAllUsers(): Collection
    {
        return User::query()->withCount('stocks')->latest('id')->get();
    }

    /**
     * Find a user with their stocks for read-only inspection.
     *
     * @throws ModelNotFoundException
     */
    public function getUserWithStocks(int $userId): User
    {
        /** @var User|null $user */
        $user = User::query()->with(['stocks' => function ($q): void {
            $q->latest('id');
        }])->withCount('stocks')->find($userId);

        if (! $user) {
            throw (new ModelNotFoundException)->setModel(User::class, [$userId]);
        }

        return $user;
    }
}
