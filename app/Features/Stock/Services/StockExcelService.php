<?php

declare(strict_types=1);

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class StockExcelService
{
    public function __construct(
        private readonly StockRepositoryInterface $stockRepository
    ) {}

    /**
     * Export all stocks for a user to an Excel (.xlsx) file as a download stream.
     */
    public function export(int $userId): StreamedResponse
    {
        $stocks = $this->stockRepository->getAllForUser($userId);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Set Headers
        $headers = ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status'];
        $sheet->fromArray($headers, null, 'A1', true);

        // Style header row
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $rowNumber = 2;
        foreach ($stocks as $stock) {
            $sheet->setCellValue("A{$rowNumber}", $stock->name);
            $sheet->setCellValue("B{$rowNumber}", $stock->sku ?? '');
            $sheet->setCellValue("C{$rowNumber}", $stock->quantity);
            $sheet->setCellValue("D{$rowNumber}", $stock->unit);
            $sheet->setCellValue("E{$rowNumber}", $stock->price !== null ? (float) $stock->price : '');
            $sheet->setCellValue("F{$rowNumber}", $stock->min_stock);
            $sheet->setCellValue("G{$rowNumber}", $stock->status->value);
            $rowNumber++;
        }

        // Auto-fit column widths
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'stocks_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import stocks from an Excel file for the specified user.
     *
     * @throws InvalidArgumentException
     */
    public function import(int $userId, string $filePath): int
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $firstRow = array_shift($rows);
        if ($firstRow === null || empty(array_filter($firstRow, fn ($v) => $v !== null && trim((string) $v) !== ''))) {
            throw new InvalidArgumentException('Header Excel tidak ditemukan.');
        }

        // Normalize headers
        $headerMap = [];
        foreach ($firstRow as $colLetter => $colValue) {
            if ($colValue !== null) {
                $headerMap[strtolower(trim((string) $colValue))] = $colLetter;
            }
        }

        $nameCol = $headerMap['name'] ?? $headerMap['nama'] ?? null;
        $qtyCol = $headerMap['qty'] ?? $headerMap['quantity'] ?? $headerMap['jumlah'] ?? null;

        if ($nameCol === null || $qtyCol === null) {
            throw new InvalidArgumentException('Header Excel tidak valid: kolom wajib "name" dan "qty" tidak ditemukan.');
        }

        $skuCol = $headerMap['sku'] ?? null;
        $unitCol = $headerMap['unit'] ?? $headerMap['satuan'] ?? null;
        $priceCol = $headerMap['price'] ?? $headerMap['harga'] ?? null;
        $minStockCol = $headerMap['min_stock'] ?? $headerMap['min stock'] ?? $headerMap['stok_minimum'] ?? null;
        $statusCol = $headerMap['status'] ?? null;

        $importedCount = 0;
        $rowIdx = 1; // header was row 1

        foreach ($rows as $row) {
            $rowIdx++;

            // Skip empty rows
            if (empty(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== ''))) {
                continue;
            }

            $rawName = $row[$nameCol] ?? null;
            if ($rawName === null || trim((string) $rawName) === '') {
                throw new InvalidArgumentException("Nama barang pada baris {$rowIdx} tidak boleh kosong.");
            }
            $name = trim((string) $rawName);

            $rawQty = $row[$qtyCol] ?? null;
            if ($rawQty === null || ! is_numeric(trim((string) $rawQty)) || (int) trim((string) $rawQty) < 0) {
                throw new InvalidArgumentException("Jumlah (qty) pada baris {$rowIdx} harus berupa angka >= 0.");
            }
            $quantity = (int) trim((string) $rawQty);

            $minStock = 5;
            if ($minStockCol && isset($row[$minStockCol]) && trim((string) $row[$minStockCol]) !== '') {
                $rawMin = trim((string) $row[$minStockCol]);
                if (! is_numeric($rawMin) || (int) $rawMin < 0) {
                    throw new InvalidArgumentException("Batas minimum stok pada baris {$rowIdx} harus berupa angka >= 0.");
                }
                $minStock = (int) $rawMin;
            }

            $price = null;
            if ($priceCol && isset($row[$priceCol]) && trim((string) $row[$priceCol]) !== '') {
                $rawPrice = trim((string) $row[$priceCol]);
                if (! is_numeric($rawPrice) || (float) $rawPrice < 0) {
                    throw new InvalidArgumentException("Harga pada baris {$rowIdx} harus berupa angka >= 0.");
                }
                $price = (float) $rawPrice;
            }

            $unit = 'pcs';
            if ($unitCol && isset($row[$unitCol]) && trim((string) $row[$unitCol]) !== '') {
                $unit = trim((string) $row[$unitCol]);
            }

            $sku = null;
            if ($skuCol && isset($row[$skuCol]) && trim((string) $row[$skuCol]) !== '') {
                $sku = trim((string) $row[$skuCol]);
            }

            $calculatedStatus = StockStatus::fromQuantity($quantity, $minStock);

            if ($statusCol && isset($row[$statusCol]) && trim((string) $row[$statusCol]) !== '') {
                $rawStatus = strtolower(trim((string) $row[$statusCol]));
                $statusEnum = match ($rawStatus) {
                    'available', 'tersedia' => StockStatus::AVAILABLE,
                    'low_stock', 'stok menipis' => StockStatus::LOW_STOCK,
                    'out_of_stock', 'habis' => StockStatus::OUT_OF_STOCK,
                    default => throw new InvalidArgumentException("Status '{$rawStatus}' pada baris {$rowIdx} tidak valid."),
                };

                if ($statusEnum !== $calculatedStatus) {
                    throw new InvalidArgumentException("Status '{$rawStatus}' tidak sesuai dengan quantity ({$quantity}) dan min_stock ({$minStock}) pada baris {$rowIdx}.");
                }
                $finalStatus = $statusEnum;
            } else {
                $finalStatus = $calculatedStatus;
            }

            $this->stockRepository->create([
                'user_id' => $userId,
                'name' => $name,
                'sku' => $sku,
                'description' => null,
                'quantity' => $quantity,
                'unit' => $unit,
                'price' => $price,
                'min_stock' => $minStock,
                'status' => $finalStatus->value,
            ]);

            $importedCount++;
        }

        return $importedCount;
    }
}
