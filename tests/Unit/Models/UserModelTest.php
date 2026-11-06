<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_casts_attributes(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $this->assertNotNull($user->email_verified_at);
        $this->assertNotSame('secret123', $user->password);
    }

    public function test_user_has_many_stocks(): void
    {
        $user = User::factory()->create();
        Stock::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->stocks);
        $this->assertInstanceOf(Stock::class, $user->stocks->first());
    }

    public function test_is_admin_check(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($member->isAdmin());
    }

    public function test_unverified_factory_state(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }
}
