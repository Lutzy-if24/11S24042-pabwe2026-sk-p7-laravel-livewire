<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_guest_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_home_redirects_admin_to_admin_users(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/');
        $response->assertRedirect(route('admin.users'));
    }

    public function test_home_redirects_member_to_stocks(): void
    {
        $member = User::factory()->member()->create();

        $response = $this->actingAs($member)->get('/');
        $response->assertRedirect(route('stocks.index'));
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/stocks')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_member_cannot_access_admin_routes(): void
    {
        $member = User::factory()->member()->create();

        $this->actingAs($member)->get('/admin/users')->assertStatus(403);
        $this->actingAs($member)->get("/admin/users/{$member->id}/stocks")->assertStatus(403);
    }

    public function test_admin_cannot_access_member_stocks_route(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/stocks')->assertStatus(403);
    }

    public function test_ownership_middleware_blocks_access_to_others_stock(): void
    {
        $owner = User::factory()->member()->create();
        $otherMember = User::factory()->member()->create();

        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherMember)->get("/stocks/{$stock->id}")->assertStatus(403);
        $this->actingAs($owner)->get("/stocks/{$stock->id}")->assertStatus(200);
    }
}
