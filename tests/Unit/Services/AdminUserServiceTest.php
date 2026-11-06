<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdminUserService;
    }

    public function test_get_paginated_users_with_and_without_search(): void
    {
        $user1 = User::factory()->create(['name' => 'Alice Wonderful', 'username' => 'alice']);
        $user2 = User::factory()->create(['name' => 'Bob Builder', 'username' => 'bob']);

        Stock::factory()->count(2)->create(['user_id' => $user1->id]);

        $paginated = $this->service->getPaginatedUsers(10);
        $this->assertCount(2, $paginated->items());

        $searched = $this->service->getPaginatedUsers(10, 'Alice');
        $this->assertCount(1, $searched->items());
        $this->assertSame('Alice Wonderful', $searched->items()[0]->name);
        $this->assertSame(2, $searched->items()[0]->stocks_count);
    }

    public function test_get_all_users(): void
    {
        User::factory()->count(3)->create();

        $all = $this->service->getAllUsers();
        $this->assertCount(3, $all);
    }

    public function test_get_user_with_stocks_success_and_failure(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(3)->create(['user_id' => $user->id]);

        $result = $this->service->getUserWithStocks($user->id);
        $this->assertSame($user->id, $result->id);
        $this->assertCount(3, $result->stocks);

        $this->expectException(ModelNotFoundException::class);
        $this->service->getUserWithStocks(9999);
    }
}
