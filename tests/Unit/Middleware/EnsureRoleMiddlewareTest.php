<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class EnsureRoleMiddlewareTest extends TestCase
{
    private EnsureRole $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new EnsureRole;
    }

    public function test_aborts_with_401_when_user_is_not_authenticated(): void
    {
        $request = Request::create('/test');

        try {
            $this->middleware->handle($request, fn () => new Response, 'admin');
            $this->fail('Expected HttpException 401 was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
        }
    }

    public function test_aborts_with_403_when_user_has_wrong_role(): void
    {
        $user = User::factory()->member()->make();
        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);

        try {
            $this->middleware->handle($request, fn () => new Response, 'admin');
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_passes_when_user_has_allowed_role(): void
    {
        $user = User::factory()->admin()->make();
        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, fn () => new Response('OK'), 'admin');

        $this->assertSame('OK', $response->getContent());
    }
}
