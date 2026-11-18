<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Brand
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
    }

    /**
     * Get all stationery brands with product counts
     */
    public function getAll(bool $activeOnly = false): array
    {
        $sql = "SELECT b.*, 
                       (SELECT COUNT(*) FROM products p WHERE p.brand_id = b.id AND p.status = 'active') AS product_count
                FROM brands b
                WHERE 1=1";

        if ($activeOnly) {
            $sql .= " AND b.is_active = 1";
        }

        $sql .= " ORDER BY b.name ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find brand by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM brands WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create brand
     */
    public function create(array $data): int
    {
        $slug = !empty($data['slug']) ? $this->slugify($data['slug']) : $this->slugify($data['name']);

        $stmt = $this->db->prepare("
            INSERT INTO brands (name, slug, country, website, is_active)
            VALUES (:name, :slug, :country, :website, :is_active)
        ");

        $stmt->execute([
            'name'      => trim($data['name']),
            'slug'      => $slug,
            'country'   => $data['country'] ?? null,
            'website'   => $data['website'] ?? null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update brand
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['name', 'slug', 'country', 'website', 'is_active'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[$col] = $col === 'slug' ? $this->slugify($data[$col]) : $data[$col];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE brands SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete brand
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM brands WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $tr = ['ı' => 'i', 'ğ' => 'g', 'ü' => 'u', 'ş' => 's', 'ö' => 'o', 'ç' => 'c'];
        $text = strtr($text, $tr);
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        return trim(preg_replace('/-+/', '-', $text), '-');
    }
}
