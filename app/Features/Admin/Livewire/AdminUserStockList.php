<?php

declare(strict_types=1);

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\AdminUserService;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class AdminUserStockList extends Component
{
    public int $userId;

    public function mount(User $user): void
    {
        $this->userId = $user->id;
    }

    public function render(AdminUserService $adminUserService): View
    {
        $targetUser = $adminUserService->getUserWithStocks($this->userId);

        return view('livewire.admin.admin-user-stock-list', [
            'targetUser' => $targetUser,
        ])->layout('components.layouts.app', ['title' => "Daftar Stok: {$targetUser->name} - Admin"]);
    }
}
