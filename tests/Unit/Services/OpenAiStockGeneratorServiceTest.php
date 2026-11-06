<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class OpenAiStockGeneratorServiceTest extends TestCase
{
    private OpenAiStockGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OpenAiStockGeneratorService;
    }

    public function test_generate_stocks_successfully(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'stocks' => [
                                    [
                                        'name' => 'Kertas HVS A4',
                                        'sku' => 'SKU-HVS-01',
                                        'description' => 'Kertas 80gr',
                                        'quantity' => 20,
                                        'unit' => 'rim',
                                        'price' => 45000,
                                        'min_stock' => 5,
                                    ],
                                    [
                                        'name' => 'Pulpen Gel',
                                        'sku' => 'SKU-PLP-02',
                                        'description' => 'Tinta hitam',
                                        'quantity' => 50,
                                        'unit' => 'pcs',
                                        'price' => 3500,
                                        'min_stock' => 10,
                                    ],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generateStocks('Peralatan Kantor');

        $this->assertCount(2, $result);
        $this->assertSame('Kertas HVS A4', $result[0]->name);
        $this->assertSame('SKU-HVS-01', $result[0]->sku);
        $this->assertSame('Kertas 80gr', $result[0]->description);
        $this->assertSame(20, $result[0]->quantity);
        $this->assertSame('rim', $result[0]->unit);
        $this->assertSame(45000.0, $result[0]->price);
        $this->assertSame(5, $result[0]->min_stock);
    }

    public function test_generate_stocks_with_api_error_status(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'error' => ['message' => 'Rate limit exceeded'],
            ], 429),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->service->generateStocks('Peralatan Kantor');
    }

    public function test_generate_stocks_with_empty_or_invalid_json_content(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'not a json',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Gagal memproses format JSON');

        $this->service->generateStocks('Peralatan Kantor');
    }

    public function test_generate_stocks_with_empty_content(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Respon dari OpenAI kosong atau tidak valid.');

        $this->service->generateStocks('Peralatan Kantor');
    }

    public function test_generate_stocks_with_connection_failure(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => fn () => throw new \Exception('Network connection failed'),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Koneksi ke OpenAI API gagal');

        $this->service->generateStocks('Peralatan Kantor');
    }
}
