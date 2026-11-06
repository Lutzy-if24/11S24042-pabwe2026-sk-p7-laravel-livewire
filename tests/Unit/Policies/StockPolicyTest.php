<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StockPolicyTest extends TestCase
{
    use RefreshDatabase;

    private StockPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new StockPolicy;
    }

    public function test_view_any_returns_true(): void
    {
        $user = User::factory()->make();
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_only_allows_owner(): void
    {
        $owner = User::factory()->create(['id' => 10]);
        $otherUser = User::factory()->create(['id' => 20]);
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->view($owner, $stock));
        $this->assertFalse($this->policy->view($otherUser, $stock));
    }

    public function test_create_only_allows_member(): void
    {
        $member = User::factory()->member()->make();
        $admin = User::factory()->admin()->make();

        $this->assertTrue($this->policy->create($member));
        $this->assertFalse($this->policy->create($admin));
    }

    public function test_update_only_allows_owner(): void
    {
        $owner = User::factory()->create(['id' => 10]);
        $admin = User::factory()->admin()->create(['id' => 20]);
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->update($owner, $stock));
        $this->assertFalse($this->policy->update($admin, $stock));
    }

    public function test_delete_only_allows_owner(): void
    {
        $owner = User::factory()->create(['id' => 10]);
        $admin = User::factory()->admin()->create(['id' => 20]);
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->delete($owner, $stock));
        $this->assertFalse($this->policy->delete($admin, $stock));
    }
}
