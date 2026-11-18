<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class Role
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get all defined roles
     */
    public function getAll(): array
    {
        if (!$this->db) return [];
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY id ASC");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($roles as &$role) {
            if (isset($role['permissions']) && is_string($role['permissions'])) {
                $role['permissions'] = json_decode($role['permissions'], true) ?? [];
            } else {
                $role['permissions'] = [];
            }
        }
        return $roles;
    }

    /**
     * Find role by ID
     */
    public function find(int $id): ?array
    {
        if (!$this->db) return null;
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ?: null;
    }

    /**
     * Find role by slug
     */
    public function findBySlug(string $slug): ?array
    {
        if (!$this->db) return null;
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($role && isset($role['permissions']) && is_string($role['permissions'])) {
            $role['permissions'] = json_decode($role['permissions'], true) ?? [];
        }
        return $role ?: null;
    }

    public static function getAvailableModules(): array
    {
        return [
            'dashboard' => 'Gösterge Paneli',
            'inventory' => 'Stok ve Envanter Yönetimi',
            'requests'  => 'Malzeme Talepleri',
            'approvals' => 'Talep Onaylama',
            'procurement' => 'Satın Alma ve İhale',
            'delivery'  => 'Mal Kabul ve Kalite',
            'counts'    => 'Fiziksel Sayımlar',
            'reports'   => 'Raporlar',
            'users'     => 'Kullanıcı ve Ayarlar'
        ];
    }

    public function updatePermissions(int $roleId, array $permissions): bool
    {
        if (!$this->db) return false;
        $json = json_encode($permissions);
        $stmt = $this->db->prepare("UPDATE roles SET permissions = :permissions WHERE id = :id");
        return $stmt->execute(['permissions' => $json, 'id' => $roleId]);
    }
}
