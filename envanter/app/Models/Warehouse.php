<?php

namespace App\Models;

class Warehouse
{
    public function __construct()
    {
        $this->initMockSessionData();
    }

    /**
     * Initialize warehouses for cleaning supplies storage
     */
    private function initMockSessionData(): void
    {
        if (!isset($_SESSION['mock_warehouses'])) {
            $_SESSION['mock_warehouses'] = [
                [
                    'id'          => 1,
                    'code'        => 'DEP-01',
                    'name'        => 'Destek Hizmetleri Ana Deposu',
                    'location'    => 'Ana Hizmet Binası Zemin Kat Ambar B-01',
                    'manager'     => 'Ayşe Kaya (Ambar Sorumlusu)',
                    'capacity'    => '800 m²',
                    'status'      => 'Aktif'
                ],
                [
                    'id'          => 2,
                    'code'        => 'DEP-02',
                    'name'        => 'Ek Hizmet Binası Yardımcı Ambar',
                    'location'    => 'Ek Hizmet Binası Bodrum Kat A-04',
                    'manager'     => 'Mehmet Demir (Depo Görevlisi)',
                    'capacity'    => '300 m²',
                    'status'      => 'Aktif'
                ]
            ];
        }
    }

    /**
     * Get all active warehouses
     */
    public function getAll(): array
    {
        return $_SESSION['mock_warehouses'] ?? [];
    }

    /**
     * Find warehouse by ID
     */
    public function find(int $id): ?array
    {
        foreach ($_SESSION['mock_warehouses'] as $w) {
            if ($w['id'] === $id) return $w;
        }
        return null;
    }

    /**
     * Find default primary warehouse
     */
    public function getDefaultWarehouse(): array
    {
        return $_SESSION['mock_warehouses'][0] ?? [
            'id'       => 1,
            'code'     => 'DEP-01',
            'name'     => 'Destek Hizmetleri Ana Deposu',
            'location' => 'Ana Hizmet Binası Zemin Kat Ambar B-01'
        ];
    }
}
