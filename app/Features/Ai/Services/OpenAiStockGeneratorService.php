<?php

declare(strict_types=1);

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class OpenAiStockGeneratorService implements AiClientInterface
{
    /**
     * Generate stock items using OpenAI Chat Completions API.
     *
     * @return list<GeneratedStockItem>
     *
     * @throws RuntimeException
     */
    public function generateStocks(string $prompt): array
    {
        $apiKey = (string) config('openai.api_key', '');
        $baseUrl = rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/');
        $model = (string) config('openai.model', 'gpt-4o-mini');
        $timeout = (int) config('openai.timeout', 30);

        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->baseUrl($baseUrl)
                ->post('/chat/completions', [
                    'model' => $model,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an intelligent inventory management assistant. Return a valid JSON object in Indonesian language with a "stocks" array containing objects with "name" (string, product name), "sku" (string, realistic SKU code), "description" (string, markdown formatted details), "quantity" (integer >= 0), "unit" (string, e.g. pcs, box, unit, rim), "price" (number or null), and "min_stock" (integer >= 0). Format: {"stocks": [{"name": "...", "sku": "...", "description": "...", "quantity": 10, "unit": "pcs", "price": 50000, "min_stock": 5}]}',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Buatkan rekomendasi daftar stok inventaris realistis untuk kategori/topik berikut: {$prompt}",
                        ],
                    ],
                    'temperature' => 0.7,
                ]);
        } catch (Throwable $e) {
            throw new RuntimeException('Koneksi ke OpenAI API gagal: ' . $e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new RuntimeException("OpenAI API merespons dengan status {$response->status()}: {$errorMsg}");
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('Respon dari OpenAI kosong atau tidak valid.');
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Gagal memproses format JSON dari respon OpenAI.');
        }

        /** @var list<array<string, mixed>> $items */
        $items = $decoded['stocks'] ?? $decoded['items'] ?? (isset($decoded[0]) ? $decoded : []);

        $result = [];
        foreach ($items as $item) {
            if (is_array($item) && ! empty($item['name'])) {
                $result[] = new GeneratedStockItem(
                    name: (string) $item['name'],
                    sku: ! empty($item['sku']) ? (string) $item['sku'] : null,
                    description: (string) ($item['description'] ?? ''),
                    quantity: max(0, (int) ($item['quantity'] ?? $item['qty'] ?? 0)),
                    unit: ! empty($item['unit']) ? (string) $item['unit'] : 'pcs',
                    price: isset($item['price']) && is_numeric($item['price']) ? (float) $item['price'] : null,
                    min_stock: max(0, (int) ($item['min_stock'] ?? 5)),
                );
            }
        }

        return $result;
    }
}
