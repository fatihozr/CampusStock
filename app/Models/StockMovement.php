<?php

namespace App\Models;

use App\Services\MockDataService;

class StockMovement
{
    private const TABLE = 'stock_movements';

    public function getRecent(int $limit = 10): array
    {
        $rows = array_reverse(MockDataService::all(self::TABLE));
        return array_slice($rows, 0, $limit);
    }

    public function getAll(array $filters = []): array
    {
        $rows = array_reverse(MockDataService::all(self::TABLE));
        if (!empty($filters['product_id'])) {
            $rows = array_values(array_filter($rows, fn($r) => (int)$r['product_id'] === (int)$filters['product_id']));
        }
        return $rows;
    }

    public function getPendingMovements(): array
    {
        return [];
    }

    public function record(array $data): int
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);
        $rows[] = [
            'id'             => $newId,
            'product_id'     => (int)($data['product_id'] ?? 0),
            'product_name'   => $data['product_name'] ?? 'Kırtasiye Ürünü',
            'movement_type'  => $data['movement_type'] ?? 'adjustment',
            'quantity'       => (float)($data['quantity'] ?? 0),
            'prev_stock'     => (float)($data['prev_stock'] ?? 0),
            'next_stock'     => (float)($data['next_stock'] ?? 0),
            'unit_cost'      => (float)($data['unit_cost'] ?? 0),
            'reference_type' => $data['reference_type'] ?? 'manual',
            'reference_id'   => $data['reference_id'] ?? null,
            'notes'          => $data['notes'] ?? '',
            'created_at'     => date('Y-m-d H:i:s')
        ];
        MockDataService::saveAll(self::TABLE, $rows);
        return $newId;
    }
}
