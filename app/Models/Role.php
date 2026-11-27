<?php

namespace App\Models;

class Role
{
    private array $roles = [
        [
            'id'          => 1,
            'slug'        => 'admin',
            'name'        => 'Genel Admin',
            'description' => 'Tüm üniversiteler, kırtasiyeler, kullanıcılar ve sistem ayarları üzerinde tam yetki.',
            'permissions' => ['dashboard', 'inventory', 'stocks', 'stock_in', 'stock_out', 'reports', 'users', 'admin']
        ],
        [
            'id'          => 2,
            'slug'        => 'kadmin',
            'name'        => 'Kırtasiye Yöneticisi (KAdmin)',
            'description' => 'Kendi kırtasiye şubesinin ürünlerini, stoklarını, siparişlerini ve iadelerini yönetir.',
            'permissions' => ['dashboard', 'inventory', 'stocks', 'stock_in', 'stock_out', 'reports']
        ],
        [
            'id'          => 3,
            'slug'        => 'uye',
            'name'        => 'Üye (Öğrenci / Müşteri)',
            'description' => 'Kırtasiye ürünlerini görüntüler, sepete ekler, sipariş verir ve iade talebi oluşturur.',
            'permissions' => ['dashboard']
        ]
    ];

    public function getAll(): array
    {
        return $this->roles;
    }

    public function find(int $id): ?array
    {
        foreach ($this->roles as $role) {
            if ($role['id'] === $id) {
                return $role;
            }
        }
        return null;
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->roles as $role) {
            if ($role['slug'] === $slug) {
                return $role;
            }
        }
        return $this->roles[0];
    }

    public static function getAvailableModules(): array
    {
        return [
            'dashboard' => 'Kontrol Paneli',
            'inventory' => 'Ürün & Stok Yönetimi',
            'stock_in'  => 'Stok Girişi',
            'stock_out' => 'Stok Çıkışı',
            'reports'   => 'Raporlar',
            'users'     => 'Kullanıcı & Rol Yönetimi',
            'admin'     => 'Tam Yönetici Yetkisi'
        ];
    }
}
