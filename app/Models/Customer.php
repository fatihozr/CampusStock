<?php

namespace App\Models;

use App\Services\MockDataService;

class Customer
{
    private const TABLE = 'customers';

    public function getAll(array $filters = []): array
    {
        $rows = MockDataService::all(self::TABLE);
        $filtered = [];

        foreach ($rows as $row) {
            if (!empty($filters['customer_type']) && ($row['customer_type'] ?? '') !== $filters['customer_type']) {
                continue;
            }
            if (!empty($filters['has_balance']) && (float)($row['balance'] ?? 0) <= 0) {
                continue;
            }
            if (!empty($filters['search'])) {
                $q = mb_strtolower(trim($filters['search']), 'UTF-8');
                $haystack = mb_strtolower(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '') . ' ' . ($row['email'] ?? '') . ' ' . ($row['phone'] ?? ''), 'UTF-8');
                if (!str_contains($haystack, $q)) {
                    continue;
                }
            }
            $filtered[] = $row;
        }
        return $filtered;
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
            'id'            => $newId,
            'customer_type' => $data['customer_type'] ?? 'student',
            'first_name'    => trim($data['first_name'] ?? ''),
            'last_name'     => trim($data['last_name'] ?? ''),
            'company_name'  => trim($data['company_name'] ?? ''),
            'email'         => trim($data['email'] ?? ''),
            'phone'         => trim($data['phone'] ?? ''),
            'city'          => trim($data['city'] ?? 'İstanbul'),
            'district'      => trim($data['district'] ?? ''),
            'neighborhood'  => trim($data['neighborhood'] ?? ''),
            'street'        => trim($data['street'] ?? ''),
            'zip_code'      => trim($data['zip_code'] ?? ''),
            'discount_rate' => (float)($data['discount_rate'] ?? 0),
            'credit_limit'  => (float)($data['credit_limit'] ?? 0),
            'balance'       => 0.0,
            'status'        => 'active'
        ];
        MockDataService::saveAll(self::TABLE, $rows);
        return $newId;
    }

    public function adjustBalance(int $id, float $amount): bool
    {
        $rows = MockDataService::all(self::TABLE);
        foreach ($rows as &$row) {
            if ((int)$row['id'] === $id) {
                $row['balance'] = max(0, (float)$row['balance'] + $amount);
                MockDataService::saveAll(self::TABLE, $rows);
                return true;
            }
        }
        return false;
    }
}
