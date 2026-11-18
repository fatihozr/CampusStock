<?php

namespace App\Models;

class Role
{
    public function __construct()
    {
        $this->initMockSessionData();
    }

    /**
     * Initialize mock roles and permission matrix
     */
    private function initMockSessionData(): void
    {
        if (!isset($_SESSION['mock_roles'])) {
            $_SESSION['mock_roles'] = [
                [
                    'id'          => 1,
                    'name'        => 'Admin (Sistem Yöneticisi)',
                    'slug'        => 'admin',
                    'description' => 'Tüm modüllere, yetki ayarlarına ve kullanıcı yönetimine tam erişim hakkı.',
                    'permissions' => ['dashboard', 'stocks', 'personnel', 'reports', 'users']
                ],
                [
                    'id'          => 2,
                    'name'        => 'Ambar / Depo Sorumlusu',
                    'slug'        => 'warehouse_manager',
                    'description' => 'Sarf malzeme stok giriş/çıkış ve ambar yönetim yetkileri.',
                    'permissions' => ['dashboard', 'stocks', 'reports']
                ],
                [
                    'id'          => 3,
                    'name'        => 'Müdür (Birim Yöneticisi)',
                    'slug'        => 'director',
                    'description' => 'Birim malzeme stoklarını ve personel listelerini izleme yetkisi.',
                    'permissions' => ['dashboard', 'stocks', 'personnel', 'reports']
                ],
                [
                    'id'          => 4,
                    'name'        => 'Personel (Standart Kullanıcı)',
                    'slug'        => 'personnel',
                    'description' => 'Gösterge paneli ve temel rehber görüntüleme yetkisi.',
                    'permissions' => ['dashboard']
                ]
            ];
        }
    }

    /**
     * Get all defined roles
     */
    public function getAll(): array
    {
        return $_SESSION['mock_roles'];
    }

    /**
     * Find role by ID
     */
    public function find(int $id): ?array
    {
        foreach ($_SESSION['mock_roles'] as $r) {
            if ($r['id'] === $id) return $r;
        }
        return null;
    }

    /**
     * Find role by slug
     */
    public function findBySlug(string $slug): ?array
    {
        foreach ($_SESSION['mock_roles'] as $r) {
            if ($r['slug'] === $slug) return $r;
        }
        return null;
    }

    /**
     * Update permissions array for a role
     */
    public function updatePermissions(int $roleId, array $permissions): bool
    {
        foreach ($_SESSION['mock_roles'] as &$r) {
            if ($r['id'] === $roleId) {
                $r['permissions'] = array_values(array_unique($permissions));
                return true;
            }
        }
        return false;
    }
}
