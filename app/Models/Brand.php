<?php

namespace App\Models;

use App\Services\MockDataService;

class Brand
{
    private const TABLE = 'brands';

    public function getAll(): array
    {
        return MockDataService::all(self::TABLE);
    }

    public function find(int $id): ?array
    {
        return MockDataService::find(self::TABLE, $id);
    }

    public function create(array $data): int
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);
        $rows[] = [
            'id'        => $newId,
            'name'      => trim($data['name'] ?? 'Yeni Marka'),
            'country'   => trim($data['country'] ?? 'Türkiye'),
            'is_active' => 1
        ];
        MockDataService::saveAll(self::TABLE, $rows);
        return $newId;
    }
}
