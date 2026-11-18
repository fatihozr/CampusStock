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
        $stmt = $this->db->query("SELECT * FROM departments ORDER BY name ASC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map([$this, 'formatDepartment'], $results);
    }

    /**
     * Find single department by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dept ? $this->formatDepartment($dept) : null;
    }

    /**
     * Format department array to include virtual fields
     */
    private function formatDepartment(array $dept): array
    {
        $dept['status'] = (isset($dept['is_active']) && (int)$dept['is_active'] === 1) ? 'Aktif' : 'Pasif';
        $dept['code'] = 'MDR-' . str_pad($dept['id'] ?? 0, 3, '0', STR_PAD_LEFT);
        return $dept;
    }

    /**
     * Find single department by Name
     */
    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => trim($name)]);
        $dept = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dept ? $this->formatDepartment($dept) : null;
    }

    /**
     * Store new department
     */
    public function store(array $data): array
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new Exception('Daire Başkanlığı adı boş bırakılamaz!');
        }

        if ($this->findByName($name) !== null) {
            throw new Exception('Aynı isimde bir Daire Başkanlığı zaten mevcut!');
        }

        $stmt = $this->db->prepare("INSERT INTO departments (name, description, is_active) VALUES (:name, :description, :is_active)");
        $stmt->execute([
            'name' => $name,
            'description' => trim($data['description'] ?? ''),
            'is_active' => ($data['status'] ?? 'Aktif') === 'Aktif' ? 1 : 0
        ]);

        return $this->find((int)$this->db->lastInsertId());
    }

    /**
     * Update existing department
     */
    public function update(int $id, array $data): bool
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new Exception('Daire Başkanlığı adı boş bırakılamaz!');
        }

        $existing = $this->findByName($name);
        if ($existing !== null && (int)$existing['id'] !== $id) {
            throw new Exception('Aynı isimde başka bir Daire Başkanlığı zaten mevcut!');
        }

        $stmt = $this->db->prepare("UPDATE departments SET name = :name, description = :description, is_active = :is_active WHERE id = :id");
        return $stmt->execute([
            'name' => $name,
            'description' => trim($data['description'] ?? ''),
            'is_active' => ($data['status'] ?? 'Aktif') === 'Aktif' ? 1 : 0,
            'id' => $id
        ]);
    }

    /**
     * Delete department if no personnel assigned
     */
    public function delete(int $id): bool
    {
        // Check if there are users attached to this department
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE department_id = :id");
        $stmt->execute(['id' => $id]);
        $hasUsers = ($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0) > 0;

        if ($hasUsers) {
            throw new Exception('Bu Daire Başkanlığına bağlı personeller bulunduğu için silinemez. Önce personelleri başka birime aktarın.');
        }

        $stmt = $this->db->prepare("DELETE FROM departments WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Calculate summary KPI stats dynamically
     */
    public function getSummaryStats(array $allPersonnel): array
    {
        // Simple counts
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM departments");
        $totalDepts = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return [
            'total_departments' => $totalDepts,
            'total_personnel'   => count($allPersonnel),
            'active_personnel'  => 0,
            'passive_personnel' => 0,
            'empty_departments' => 0
        ];
    }
}
