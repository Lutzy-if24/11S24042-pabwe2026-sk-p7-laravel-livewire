<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Features\Auth\Actions\RegisterUserAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user_action_with_session(): void
    {
        $request = Request::create('/test');
        $session = $this->app['session']->driver('array');
        $session->start();
        $request->setLaravelSession($session);
        $this->app->instance('request', $request);

        $action = new RegisterUserAction;
        $user = $action->execute([
            'name' => 'Action User',
            'username' => 'action_reg_user',
            'email' => 'actionreg@example.com',
            'password' => 'secret123',
        ]);

        $this->assertSame('Action User', $user->name);
        $this->assertSame('member', $user->role);
        $this->assertAuthenticatedAs($user);
    }
}
