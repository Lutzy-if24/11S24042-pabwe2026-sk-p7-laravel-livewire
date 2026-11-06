<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class EnsureStockOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private EnsureStockOwnership $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new EnsureStockOwnership;
    }

    public function test_aborts_with_401_when_user_is_not_authenticated(): void
    {
        $request = Request::create('/stocks/1');

        try {
            $this->middleware->handle($request, fn () => new Response);
            $this->fail('Expected HttpException 401 was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(401, $e->getStatusCode());
        }
    }

    public function test_passes_when_no_stock_route_parameter(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/stocks');
        $request->setUserResolver(fn () => $user);

        $response = $this->middleware->handle($request, fn () => new Response('OK'));

        $this->assertSame('OK', $response->getContent());
    }

    public function test_aborts_with_403_when_stock_belongs_to_another_user(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $request = Request::create("/stocks/{$stock->id}");
        $request->setUserResolver(fn () => $attacker);
        $request->setRouteResolver(fn () => new class($stock) {
            public function __construct(private Stock $stock) {}
            public function parameter(string $name, $default = null) { return $this->stock; }
        });

        // Test with stock instance
        $requestWithInstance = Request::create("/stocks/{$stock->id}");
        $requestWithInstance->setUserResolver(fn () => $attacker);
        $route = new \Illuminate\Routing\Route('GET', '/stocks/{stock}', []);
        $route->bind($requestWithInstance);
        $route->setParameter('stock', $stock);
        $requestWithInstance->setRouteResolver(fn () => $route);

        try {
            $this->middleware->handle($requestWithInstance, fn () => new Response);
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame('Akses ditolak: Anda bukan pemilik stok ini.', $e->getMessage());
        }
    }

    public function test_passes_when_user_owns_the_stock_instance(): void
    {
        $owner = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $request = Request::create("/stocks/{$stock->id}");
        $request->setUserResolver(fn () => $owner);
        $route = new \Illuminate\Routing\Route('GET', '/stocks/{stock}', []);
        $route->bind($request);
        $route->setParameter('stock', $stock);
        $request->setRouteResolver(fn () => $route);

        $response = $this->middleware->handle($request, fn () => new Response('OK'));

        $this->assertSame('OK', $response->getContent());
    }

    public function test_passes_when_user_owns_the_stock_id(): void
    {
        $owner = User::factory()->create();
        $stock = Stock::factory()->create(['user_id' => $owner->id]);

        $request = Request::create("/stocks/{$stock->id}");
        $request->setUserResolver(fn () => $owner);
        $route = new \Illuminate\Routing\Route('GET', '/stocks/{stock}', []);
        $route->bind($request);
        $route->setParameter('stock', $stock->id);
        $request->setRouteResolver(fn () => $route);

        $response = $this->middleware->handle($request, fn () => new Response('OK'));

        $this->assertSame('OK', $response->getContent());
    }
}
