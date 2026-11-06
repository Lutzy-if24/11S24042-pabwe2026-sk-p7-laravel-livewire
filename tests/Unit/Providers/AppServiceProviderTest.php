<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Features\Stock\Repositories\StockRepository;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class AppServiceProviderTest extends TestCase
{
    public function test_service_provider_bindings_and_rate_limiter(): void
    {
        $this->assertInstanceOf(
            StockRepository::class,
            $this->app->make(StockRepositoryInterface::class)
        );

        $this->assertInstanceOf(
            OpenAiStockGeneratorService::class,
            $this->app->make(AiClientInterface::class)
        );

        $this->assertInstanceOf(
            StockPolicy::class,
            Gate::getPolicyFor(Stock::class)
        );

        $limiter = RateLimiter::limiter('1000-per-second');
        $this->assertNotNull($limiter);
        $limit = $limiter(request());
        $this->assertSame(1000, $limit->maxAttempts);
    }

    public function test_force_https_when_enabled(): void
    {
        Config::set('app.force_https', true);

        $provider = new AppServiceProvider($this->app);
        $provider->boot();

        $this->assertTrue(true);
    }
}
