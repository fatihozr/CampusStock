<?php

namespace App\Models;

use App\Services\MockDataService;
use Exception;

class Product
{
    private const TABLE = 'products';

    public function getAll(array $filters = []): array
    {
        $rows = MockDataService::all(self::TABLE);
        $filtered = [];

        foreach ($rows as $row) {
            if (!empty($filters['status']) && ($row['status'] ?? 'active') !== $filters['status']) {
                continue;
            }
            if (!empty($filters['category_id']) && (int)($row['category_id'] ?? 0) !== (int)$filters['category_id']) {
                continue;
            }
            if (!empty($filters['brand_id']) && (int)($row['brand_id'] ?? 0) !== (int)$filters['brand_id']) {
                continue;
            }
            if (!empty($filters['low_stock']) && (float)($row['current_stock'] ?? 0) > (float)($row['min_stock'] ?? 5)) {
                continue;
            }
            if (!empty($filters['search'])) {
                $q = mb_strtolower(trim($filters['search']), 'UTF-8');
                $haystack = mb_strtolower(
                    ($row['name'] ?? '') . ' ' . ($row['barcode'] ?? '') . ' ' . ($row['code'] ?? '') . ' ' . ($row['brand_name'] ?? '') . ' ' . ($row['category_name'] ?? ''),
                    'UTF-8'
                );
                if (!str_contains($haystack, $q)) {
                    continue;
                }
            }
            $filtered[] = $this->formatProduct($row);
        }

        usort($filtered, fn($a, $b) => $b['id'] <=> $a['id']);

        if (!empty($filters['limit'])) {
            $offset = (int)($filters['offset'] ?? 0);
            $filtered = array_slice($filtered, $offset, (int)$filters['limit']);
        }

        return $filtered;
    }

    public function find(int $id): ?array
    {
        $row = MockDataService::find(self::TABLE, $id);
        return $row ? $this->formatProduct($row) : null;
    }

    public function findByBarcode(string $barcode): ?array
    {
        $barcode = trim($barcode);
        foreach (MockDataService::all(self::TABLE) as $row) {
            if (($row['barcode'] ?? '') === $barcode || ($row['code'] ?? '') === $barcode) {
                return $this->formatProduct($row);
            }
        }
        return null;
    }

    public function findByCode(string $code): ?array
    {
        return $this->findByBarcode($code);
    }

    public function getCriticalStock(int $limit = 50): array
    {
        return $this->getAll(['low_stock' => true, 'limit' => $limit]);
    }

    public function getCriticalStockCount(): int
    {
        return count($this->getAll(['low_stock' => true]));
    }

    public function create(array $data): int
    {
        $rows = MockDataService::all(self::TABLE);
        $newId = MockDataService::nextId(self::TABLE);

        $catName = 'Genel Kırtasiye';
        if (!empty($data['category_id'])) {
            $cat = MockDataService::find('categories', (int)$data['category_id']);
            if ($cat) $catName = $cat['name'];
        }

        $brandName = 'Diğer';
        if (!empty($data['brand_id'])) {
            $brand = MockDataService::find('brands', (int)$data['brand_id']);
            if ($brand) $brandName = $brand['name'];
        }

        $stock = (float)($data['current_stock'] ?? $data['quantity'] ?? 0);
        $newRow = [
            'id'              => $newId,
            'category_id'     => (int)($data['category_id'] ?? 1),
            'brand_id'        => !empty($data['brand_id']) ? (int)$data['brand_id'] : 1,
            'store_id'        => (int)($data['store_id'] ?? 1),
            'category_name'   => $catName,
            'brand_name'      => $brandName,
            'code'            => !empty($data['code']) ? $data['code'] : $this->generateCode(),
            'barcode'         => $data['barcode'] ?? ('869084600' . rand(1000, 9999)),
            'name'            => trim($data['name'] ?? 'Yeni Kırtasiye Ürünü'),
            'description'     => $data['description'] ?? '',
            'unit'            => $data['unit'] ?? 'Adet',
            'purchase_price'  => (float)($data['purchase_price'] ?? $data['unit_price'] ?? 0),
            'sale_price'      => (float)($data['sale_price'] ?? $data['unit_price'] ?? 0),
            'wholesale_price' => (float)($data['wholesale_price'] ?? 0),
            'tax_rate'        => (float)($data['tax_rate'] ?? 20.0),
            'current_stock'   => $stock,
            'min_stock'       => (float)($data['min_stock'] ?? 5),
            'shelf_location'  => $data['shelf_location'] ?? 'Reyon A-1',
            'image_url'       => $data['image_url'] ?? '',
            'status'          => $data['status'] ?? 'active'
        ];

        $rows[] = $newRow;
        MockDataService::saveAll(self::TABLE, $rows);

        if ($stock > 0) {
            (new StockMovement())->record([
                'product_id'    => $newId,
                'product_name'  => $newRow['name'],
                'movement_type' => 'purchase',
                'quantity'      => $stock,
                'prev_stock'    => 0,
                'next_stock'    => $stock,
                'unit_cost'     => $newRow['purchase_price'],
                'notes'         => 'Yeni ürün açılış stoğu'
            ]);
        }

        return $newId;
    }

    public function update(int $id, array $data): bool
    {
        $rows = MockDataService::all(self::TABLE);
        foreach ($rows as &$row) {
            if ((int)$row['id'] === $id) {
                foreach (['category_id', 'brand_id', 'code', 'barcode', 'name', 'description', 'unit', 'purchase_price', 'sale_price', 'wholesale_price', 'tax_rate', 'min_stock', 'shelf_location', 'image_url', 'status'] as $f) {
                    if (array_key_exists($f, $data)) {
                        $row[$f] = $data[$f];
                    }
                }
                if (isset($data['category_id'])) {
                    $cat = MockDataService::find('categories', (int)$data['category_id']);
                    if ($cat) $row['category_name'] = $cat['name'];
                }
                MockDataService::saveAll(self::TABLE, $rows);
                return true;
            }
        }
        return false;
    }

    public function delete(int $id, bool $softDelete = true): bool
    {
        $rows = MockDataService::all(self::TABLE);
        if ($softDelete) {
            foreach ($rows as &$row) {
                if ((int)$row['id'] === $id) {
                    $row['status'] = 'inactive';
                    MockDataService::saveAll(self::TABLE, $rows);
                    return true;
                }
            }
            return false;
        }
        $rows = array_filter($rows, fn($r) => (int)$r['id'] !== $id);
        MockDataService::saveAll(self::TABLE, $rows);
        return true;
    }

    public function adjustStock(int $productId, float $quantityChange, string $type, string $refType = 'manual', ?string $refId = null, ?string $notes = null, ?int $userId = null): bool
    {
        $rows = MockDataService::all(self::TABLE);
        foreach ($rows as &$row) {
            if ((int)$row['id'] === $productId) {
                $prev = (float)$row['current_stock'];
                $next = $prev + $quantityChange;
                if ($next < 0 && $type === 'sale') {
                    throw new Exception("Yetersiz stok! Mevcut: {$prev}, İstenen çıkış: " . abs($quantityChange));
                }
                $row['current_stock'] = max(0, $next);
                MockDataService::saveAll(self::TABLE, $rows);

                (new StockMovement())->record([
                    'product_id'     => $productId,
                    'product_name'   => $row['name'],
                    'movement_type'  => $type,
                    'quantity'       => $quantityChange,
                    'prev_stock'     => $prev,
                    'next_stock'     => $row['current_stock'],
                    'unit_cost'      => (float)($row['purchase_price'] ?? 0),
                    'reference_type' => $refType,
                    'reference_id'   => $refId,
                    'notes'          => $notes ?? ($quantityChange > 0 ? 'Stok girişi' : 'Stok çıkışı'),
                    'created_by'     => $userId
                ]);
                return true;
            }
        }
        throw new Exception("Ürün bulunamadı (ID: {$productId})");
    }

    public function decreaseStock(int $productId, float $quantity, string $refType = 'order', ?string $refId = null): bool
    {
        return $this->adjustStock($productId, -$quantity, 'sale', $refType, $refId);
    }

    public function increaseStock(int $productId, float $quantity, string $refType = 'cancel', ?string $refId = null): bool
    {
        return $this->adjustStock($productId, $quantity, 'adjustment', $refType, $refId);
    }

    public function generateCode(): string
    {
        return 'KRT-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    public function count(array $filters = []): int
    {
        return count($this->getAll($filters));
    }

    private function formatProduct(array $row): array
    {
        $stock = (float)($row['current_stock'] ?? 0);
        $min   = (float)($row['min_stock'] ?? 5);

        $row['current_stock']      = $stock;
        $row['quantity']           = $stock;
        $row['min_stock']          = $min;
        $row['purchase_price']     = (float)($row['purchase_price'] ?? 0);
        $row['sale_price']         = (float)($row['sale_price'] ?? 0);
        $row['unit_price']         = $row['sale_price'];
        $row['wholesale_price']    = (float)($row['wholesale_price'] ?? 0);
        $row['tax_rate']           = (float)($row['tax_rate'] ?? 20.0);
        $row['image_path']         = $row['image_url'] ?? '';
        $row['stock_status_label'] = $stock <= 0 ? 'Tükendi' : ($stock <= $min ? 'Kritik' : 'Yeterli');
        $row['badge_class']        = $stock <= 0 ? 'bg-danger-subtle text-danger' : ($stock <= $min ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success');

        return $row;
    }
}
