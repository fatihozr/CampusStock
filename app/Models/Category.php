<?php

namespace App\Models;

use App\Services\MockDataService;

class Category
{
    private const TABLE = 'categories';

    public function getAll(): array
    {
        $cats = MockDataService::all(self::TABLE);
        $products = MockDataService::all('products');

        foreach ($cats as &$c) {
            $count = 0;
            foreach ($products as $p) {
                if ((int)($p['category_id'] ?? 0) === (int)$c['id']) {
                    $count++;
                }
            }
            $c['product_count'] = $count;
        }
        return $cats;
    }

    public function find(int $id): ?array
    {
        return MockDataService::find(self::TABLE, $id);
    }

    public function store(array $data): int
    {
        return $this->create($data);
    }

    public function create(array $data): int
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);
        $name = trim($data['name'] ?? 'Yeni Kategori');

        $rows[] = [
            'id'            => $newId,
            'name'          => $name,
            'slug'          => strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)),
            'description'   => trim($data['description'] ?? ''),
            'product_count' => 0
        ];

        MockDataService::saveAll(self::TABLE, $rows);
        return $newId;
    }
}
