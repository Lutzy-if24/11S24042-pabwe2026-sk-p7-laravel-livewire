<?php

declare(strict_types=1);

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedStockItem;

interface AiClientInterface
{
    /**
     * Generate stock items based on a prompt or topic.
     *
     * @return list<GeneratedStockItem>
     */
    public function generateStocks(string $prompt): array;
}
