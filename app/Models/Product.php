<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Product
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı. Lütfen veritabanı ayarlarını kontrol edin.");
        }
    }

    /**
     * Format product data with computed fields for views and APIs
     */
    public function formatProduct(array $p): array
    {
        $p['category'] = $p['category_name'] ?? 'Genel';
        $p['brand'] = $p['brand_name'] ?? ($p['brand'] ?? '-');
        $p['quantity'] = (float)($p['current_stock'] ?? 0);
        $p['min_threshold'] = (float)($p['min_stock'] ?? 5);
        $p['unit_price'] = (float)($p['sale_price'] ?? ($p['unit_price'] ?? 0));
        $p['purchase_price'] = (float)($p['purchase_price'] ?? 0);
        $p['sale_price'] = (float)($p['sale_price'] ?? $p['unit_price']);
        $p['wholesale_price'] = (float)($p['wholesale_price'] ?? $p['sale_price']);
        $p['tax_rate'] = (float)($p['tax_rate'] ?? 20);
        $p['shelf_location'] = $p['shelf_location'] ?? '-';
        $p['total_val'] = $p['quantity'] * $p['sale_price'];
        
        // Stock status badge
        if ($p['quantity'] <= 0) {
            $p['badge_class'] = 'bg-danger text-white';
            $p['stock_status_label'] = 'Stokta Yok';
        } elseif ($p['quantity'] <= $p['min_threshold']) {
            $p['badge_class'] = 'bg-warning text-dark';
            $p['stock_status_label'] = 'Kritik Stok';
        } else {
            $p['badge_class'] = 'bg-success-subtle text-success';
            $p['stock_status_label'] = 'Yeterli';
        }
        
        return $p;
    }

    /**
     * Get all products with optional filtering
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT p.*, 
                       c.name AS category_name, 
                       b.name AS brand_name 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = :brand_id";
            $params['brand_id'] = (int)$filters['brand_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $sql .= " AND (p.name LIKE :s1 OR p.code LIKE :s2 OR p.barcode LIKE :s3 OR b.name LIKE :s4)";
            $params['s1'] = $search;
            $params['s2'] = $search;
            $params['s3'] = $search;
            $params['s4'] = $search;
        }

        if (!empty($filters['low_stock'])) {
            $sql .= " AND p.current_stock <= p.min_stock";
        }

        $sql .= " ORDER BY p.id DESC";

        if (!empty($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $offset = (int)($filters['offset'] ?? 0);
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'formatProduct'], $rows);
    }

    /**
     * Find product by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, b.name AS brand_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->formatProduct($row) : null;
    }

    /**
     * Find product by Barcode (Crucial for Stationery POS barcode scanners)
     */
    public function findByBarcode(string $barcode): ?array
    {
        $barcode = trim($barcode);
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, b.name AS brand_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.barcode = :barcode OR p.code = :code
            LIMIT 1
        ");
        $stmt->execute(['barcode' => $barcode, 'code' => $barcode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->formatProduct($row) : null;
    }

    /**
     * Find product by SKU / Code
     */
    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name, b.name AS brand_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.code = :code
            LIMIT 1
        ");
        $stmt->execute(['code' => trim($code)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->formatProduct($row) : null;
    }

    /**
     * Get products running low on stock
     */
    public function getCriticalStock(int $limit = 50): array
    {
        return $this->getAll(['low_stock' => true, 'limit' => $limit]);
    }

    /**
     * Create a new stationery product
     */
    public function create(array $data): int
    {
        $code = !empty($data['code']) ? trim($data['code']) : $this->generateCode();
        $barcode = !empty($data['barcode']) ? trim($data['barcode']) : null;

        $stmt = $this->db->prepare("
            INSERT INTO products (
                category_id, brand_id, code, barcode, name, description, unit,
                purchase_price, sale_price, wholesale_price, tax_rate,
                current_stock, min_stock, shelf_location, image_url, status, is_featured
            ) VALUES (
                :category_id, :brand_id, :code, :barcode, :name, :description, :unit,
                :purchase_price, :sale_price, :wholesale_price, :tax_rate,
                :current_stock, :min_stock, :shelf_location, :image_url, :status, :is_featured
            )
        ");

        $stmt->execute([
            'category_id'     => (int)($data['category_id'] ?? 1),
            'brand_id'        => !empty($data['brand_id']) ? (int)$data['brand_id'] : null,
            'code'            => $code,
            'barcode'         => $barcode,
            'name'            => trim($data['name'] ?? 'Yeni Kırtasiye Ürünü'),
            'description'     => $data['description'] ?? null,
            'unit'            => $data['unit'] ?? 'Adet',
            'purchase_price'  => (float)($data['purchase_price'] ?? 0.0),
            'sale_price'      => (float)($data['sale_price'] ?? ($data['unit_price'] ?? 0.0)),
            'wholesale_price' => (float)($data['wholesale_price'] ?? ($data['sale_price'] ?? 0.0)),
            'tax_rate'        => (float)($data['tax_rate'] ?? 20.0),
            'current_stock'   => (float)($data['current_stock'] ?? 0.0),
            'min_stock'       => (float)($data['min_stock'] ?? 5.0),
            'shelf_location'  => $data['shelf_location'] ?? null,
            'image_url'       => $data['image_url'] ?? null,
            'status'          => $data['status'] ?? 'active',
            'is_featured'     => (int)($data['is_featured'] ?? 0)
        ]);

        $newId = (int)$this->db->lastInsertId();

        // Log initial stock movement if starting stock > 0
        if (!empty($data['current_stock']) && (float)$data['current_stock'] > 0) {
            $movementModel = new StockMovement();
            $movementModel->record([
                'product_id'     => $newId,
                'movement_type'  => 'purchase',
                'quantity'       => (float)$data['current_stock'],
                'prev_stock'     => 0.0,
                'next_stock'     => (float)$data['current_stock'],
                'unit_cost'      => (float)($data['purchase_price'] ?? 0.0),
                'total_cost'     => (float)$data['current_stock'] * (float)($data['purchase_price'] ?? 0.0),
                'reference_type' => 'initial_setup',
                'notes'          => 'Ürün ilk açılış stoğu'
            ]);
        }

        return $newId;
    }

    /**
     * Update product
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        $allowedFields = [
            'category_id', 'brand_id', 'code', 'barcode', 'name', 'description', 'unit',
            'purchase_price', 'sale_price', 'wholesale_price', 'tax_rate',
            'min_stock', 'shelf_location', 'image_url', 'status', 'is_featured'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE products SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete product (or deactivate)
     */
    public function delete(int $id, bool $softDelete = true): bool
    {
        if ($softDelete) {
            $stmt = $this->db->prepare("UPDATE products SET status = 'inactive' WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }

        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Atomically adjust stock (increments/decrements) and record movement
     */
    public function adjustStock(int $productId, float $quantityChange, string $type, string $refType = 'manual', ?string $refId = null, ?string $notes = null, ?int $userId = null): bool
    {
        $product = $this->find($productId);
        if (!$product) {
            throw new Exception("Ürün bulunamadı (ID: {$productId})");
        }

        $prevStock = (float)$product['current_stock'];
        $nextStock = $prevStock + $quantityChange;

        if ($nextStock < 0 && $type === 'sale') {
            throw new Exception("Yetersiz stok! Mevcut: {$prevStock}, İstenen çıkış: " . abs($quantityChange));
        }

        $stmt = $this->db->prepare("UPDATE products SET current_stock = :next_stock, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $success = $stmt->execute([
            'next_stock' => $nextStock,
            'id'         => $productId
        ]);

        if ($success) {
            $movementModel = new StockMovement();
            $movementModel->record([
                'product_id'     => $productId,
                'movement_type'  => $type,
                'quantity'       => $quantityChange,
                'prev_stock'     => $prevStock,
                'next_stock'     => $nextStock,
                'unit_cost'      => (float)$product['purchase_price'],
                'total_cost'     => abs($quantityChange) * (float)$product['purchase_price'],
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'notes'          => $notes,
                'created_by'     => $userId
            ]);
        }

        return $success;
    }

    /**
     * Generate unique SKU code
     */
    public function generateCode(): string
    {
        do {
            $code = 'KRT-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE code = :code");
            $stmt->execute(['code' => $code]);
        } while ((int)$stmt->fetchColumn() > 0);

        return $code;
    }

    /**
     * Count products
     */
    public function count(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM products WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['low_stock'])) {
            $sql .= " AND current_stock <= min_stock";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
