<?php

namespace App\Models;

use App\Services\MockDataService;

class University
{
    private const TABLE = 'universities';

    public function getAll(bool $onlyActive = true): array
    {
        $rows = MockDataService::all(self::TABLE);
        if ($onlyActive) {
            return array_values(array_filter($rows, fn($r) => (int)($r['is_active'] ?? 1) === 1));
        }
        return $rows;
    }

    public function find(int $id): ?array
    {
        return MockDataService::find(self::TABLE, $id);
    }

    public function store(array $data): array
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);
        $new = [
            'id'        => $newId,
            'name'      => trim($data['name'] ?? ''),
            'city'      => trim($data['city'] ?? ''),
            'address'   => trim($data['address'] ?? ''),
            'phone'     => trim($data['phone'] ?? ''),
            'code'      => strtoupper(trim($data['code'] ?? 'UNV')),
            'is_active' => 1
        ];
        $rows[] = $new;
        MockDataService::saveAll(self::TABLE, $rows);
        return $new;
    }
}
