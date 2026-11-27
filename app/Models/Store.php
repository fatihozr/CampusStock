<?php

namespace App\Models;

use App\Services\MockDataService;

class Store
{
    private const TABLE = 'stores';

    public function getAll(?int $universityId = null, bool $onlyActive = true): array
    {
        $rows = MockDataService::all(self::TABLE);
        $filtered = [];

        foreach ($rows as $row) {
            if ($universityId !== null && (int)($row['university_id'] ?? 0) !== $universityId) {
                continue;
            }
            if ($onlyActive && (int)($row['is_active'] ?? 1) !== 1) {
                continue;
            }
            $filtered[] = $row;
        }

        return $filtered;
    }

    public function find(int $id): ?array
    {
        return MockDataService::find(self::TABLE, $id);
    }

    public function store(array $data): array
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);
        $univId = (int)($data['university_id'] ?? 1);
        $univ = MockDataService::find('universities', $univId);

        $new = [
            'id'              => $newId,
            'university_id'   => $univId,
            'university_name' => $univ['name'] ?? 'Üniversite',
            'name'            => trim($data['name'] ?? ''),
            'address'         => trim($data['address'] ?? ''),
            'phone'           => trim($data['phone'] ?? ''),
            'tax_number'      => trim($data['tax_number'] ?? ''),
            'opens_at'        => $data['opens_at'] ?? '08:30',
            'closes_at'       => $data['closes_at'] ?? '19:00',
            'is_active'       => 1
        ];
        $rows[] = $new;
        MockDataService::saveAll(self::TABLE, $rows);
        return $new;
    }

    public function getStoreProducts(int $storeId, array $filters = []): array
    {
        return (new Product())->getAll($filters);
    }
}
