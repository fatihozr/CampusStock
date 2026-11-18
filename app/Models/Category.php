<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Category
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
     * Get all categories with product count
     */
    public function getAll(bool $activeOnly = false): array
    {
        $sql = "SELECT c.*, 
                       p.name AS parent_name,
                       (SELECT COUNT(*) FROM products pr WHERE pr.category_id = c.id AND pr.status = 'active') AS product_count
                FROM categories c
                LEFT JOIN categories p ON c.parent_id = p.id
                WHERE 1=1";

        if ($activeOnly) {
            $sql .= " AND c.is_active = 1";
        }

        $sql .= " ORDER BY c.sort_order ASC, c.name ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get category tree (parent categories with children)
     */
    public function getTree(): array
    {
        $all = $this->getAll(true);
        $tree = [];
        $children = [];

        foreach ($all as $cat) {
            if ($cat['parent_id']) {
                $children[$cat['parent_id']][] = $cat;
            } else {
                $tree[$cat['id']] = $cat;
                $tree[$cat['id']]['subcategories'] = [];
            }
        }

        foreach ($children as $parentId => $subs) {
            if (isset($tree[$parentId])) {
                $tree[$parentId]['subcategories'] = $subs;
            }
        }

        return array_values($tree);
    }

    /**
     * Find category by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, p.name AS parent_name
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            WHERE c.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Create category
     */
    public function create(array $data): int
    {
        $slug = !empty($data['slug']) ? $this->slugify($data['slug']) : $this->slugify($data['name']);

        $stmt = $this->db->prepare("
            INSERT INTO categories (parent_id, name, slug, description, icon, is_active, sort_order)
            VALUES (:parent_id, :name, :slug, :description, :icon, :is_active, :sort_order)
        ");

        $stmt->execute([
            'parent_id'   => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            'name'        => trim($data['name']),
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? 'bi-bookmark',
            'is_active'   => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'sort_order'  => (int)($data['sort_order'] ?? 0)
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update category
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['parent_id', 'name', 'slug', 'description', 'icon', 'is_active', 'sort_order'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[$col] = $col === 'slug' ? $this->slugify($data[$col]) : $data[$col];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE categories SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete category
     */
    public function delete(int $id): bool
    {
        // Check if has products
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
        $stmt->execute(['id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception("Bu kategoriye ait ürünler bulunmaktadır. Önce ürünleri başka kategoriye taşıyın.");
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
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
