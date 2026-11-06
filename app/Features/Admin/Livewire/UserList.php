<?php

declare(strict_types=1);

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\AdminUserService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(AdminUserService $adminUserService): View
    {
        $users = $adminUserService->getPaginatedUsers(
            perPage: 10,
            search: $this->search ?: null
        );

        return view('livewire.admin.user-list', [
            'users' => $users,
        ])->layout('components.layouts.app', ['title' => 'Manajemen Pengguna - Admin Stock']);
    }
}
