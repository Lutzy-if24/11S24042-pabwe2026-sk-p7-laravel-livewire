<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Features\Auth\Actions\LoginUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LoginUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_user_action_with_session(): void
    {
        $user = User::factory()->create([
            'username' => 'actionuser',
            'password' => Hash::make('secret'),
        ]);

        $request = Request::create('/test');
        $session = $this->app['session']->driver('array');
        $session->start();
        $request->setLaravelSession($session);
        $this->app->instance('request', $request);

        $action = new LoginUserAction;
        $result = $action->execute('actionuser', 'secret');

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($user);
    }
}
