<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Department
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı. Lütfen ayarları kontrol edin.");
        }
    }

    /**
     * Get all departments sorted alphabetically
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM departments ORDER BY name ASC");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'formatDepartment'], $results);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Find single department by ID
     */
    public function find(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $dept = $stmt->fetch(PDO::FETCH_ASSOC);
            return $dept ? $this->formatDepartment($dept) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Format department array to include virtual fields
     */
    private function formatDepartment(array $dept): array
    {
        $dept['status'] = (isset($dept['is_active']) && (int)$dept['is_active'] === 1) ? 'Aktif' : 'Pasif';
        $dept['code'] = 'MDR-' . str_pad((string)($dept['id'] ?? 0), 3, '0', STR_PAD_LEFT);
        return $dept;
    }

    public function create(array $data): int
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO departments (name, description, is_active) VALUES (:name, :desc, :act)");
            $stmt->execute([
                'name' => trim($data['name'] ?? ''),
                'desc' => $data['description'] ?? null,
                'act'  => (int)($data['is_active'] ?? 1)
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE departments SET name = :name, description = :desc, is_active = :act, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            return $stmt->execute([
                'id'   => $id,
                'name' => trim($data['name'] ?? ''),
                'desc' => $data['description'] ?? null,
                'act'  => (int)($data['is_active'] ?? 1)
            ]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM departments WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
