<?php

namespace App\Models;

use Exception;

class Department
{
    public function __construct()
    {
        $this->initMockSessionData();
    }

    /**
     * Initialize department dataset in session if not present
     */
    private function initMockSessionData(): void
    {
        if (!isset($_SESSION['mock_departments']) || count($_SESSION['mock_departments']) < 5) {
            $_SESSION['mock_departments'] = [
                ['id' => 1, 'code' => 'MDR-101', 'name' => 'Bilgi İşlem Müdürlüğü', 'description' => 'BT Altyapı, Ağ, Donanım ve Yazılım Geliştirme Hizmetleri', 'status' => 'Aktif'],
                ['id' => 2, 'code' => 'MDR-102', 'name' => 'Yazı İşleri Müdürlüğü', 'description' => 'Genel Evrak, Kararlar, Meclis ve Kurumsal Arşiv İşleri', 'status' => 'Aktif'],
                ['id' => 3, 'code' => 'MDR-103', 'name' => 'İmar ve Şehircilik Müdürlüğü', 'description' => 'Proje Onay, Yapı Ruhsatı ve Coğrafi Bilgi Sistemleri', 'status' => 'Aktif'],
                ['id' => 4, 'code' => 'MDR-104', 'name' => 'Fen İşleri Müdürlüğü', 'description' => 'Altyapı, Yol Yapım, Şantiye ve Saha Çalışmaları', 'status' => 'Aktif'],
                ['id' => 5, 'code' => 'MDR-105', 'name' => 'Destek Hizmetleri', 'description' => 'Satınalma, İhale, Ambar ve Ulaşım Hizmetleri', 'status' => 'Aktif'],
                ['id' => 6, 'code' => 'MDR-106', 'name' => 'Zabıta Müdürlüğü', 'description' => 'Ruhsat Denetim, Çevre ve Şehir Düzeni Denetimi', 'status' => 'Aktif'],
                ['id' => 7, 'code' => 'MDR-107', 'name' => 'Kültür ve Sosyal İşler', 'description' => 'Sosyal Yardım, Kütüphane ve Gençlik Merkezi Etkinlikleri', 'status' => 'Aktif'],
                ['id' => 8, 'code' => 'MDR-108', 'name' => 'Mali Hizmetler', 'description' => 'Bütçe, Muhasebe ve Tahsilat İşlemleri', 'status' => 'Aktif'],
                ['id' => 9, 'code' => 'MDR-109', 'name' => 'İnsan Kaynakları', 'description' => 'Özlük İşleri, Eğitim ve İşe Alım Süreçleri', 'status' => 'Aktif']
            ];
        }
    }

    /**
     * Get all departments sorted alphabetically
     */
    public function getAll(): array
    {
        $items = $_SESSION['mock_departments'];
        usort($items, fn($a, $b) => turkish_strcmp($a['name'], $b['name']));
        return $items;
    }

    /**
     * Find single department by ID
     */
    public function find(int $id): ?array
    {
        foreach ($_SESSION['mock_departments'] as $d) {
            if ((int)$d['id'] === $id) return $d;
        }
        return null;
    }

    /**
     * Find single department by Name
     */
    public function findByName(string $name): ?array
    {
        $search = mb_strtolower(trim($name), 'UTF-8');
        foreach ($_SESSION['mock_departments'] as $d) {
            if (mb_strtolower($d['name'], 'UTF-8') === $search) return $d;
        }
        return null;
    }

    /**
     * Store new department
     */
    public function store(array $data): array
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new Exception('Müdürlük adı boş bırakılamaz!');
        }

        if ($this->findByName($name) !== null) {
            throw new Exception('Aynı isimde bir müdürlük zaten mevcut!');
        }

        $newId = count($_SESSION['mock_departments']) > 0
            ? max(array_column($_SESSION['mock_departments'], 'id')) + 1
            : 1;

        $code = !empty($data['code']) ? trim($data['code']) : 'MDR-' . (100 + $newId);

        $newDept = [
            'id'          => $newId,
            'code'        => $code,
            'name'        => $name,
            'description' => trim($data['description'] ?? ''),
            'status'      => $data['status'] ?? 'Aktif'
        ];

        array_unshift($_SESSION['mock_departments'], $newDept);
        return $newDept;
    }

    /**
     * Update existing department
     */
    public function update(int $id, array $data): bool
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new Exception('Müdürlük adı boş bırakılamaz!');
        }

        $existing = $this->findByName($name);
        if ($existing !== null && (int)$existing['id'] !== $id) {
            throw new Exception('Aynı isimde başka bir müdürlük zaten mevcut!');
        }

        foreach ($_SESSION['mock_departments'] as &$d) {
            if ((int)$d['id'] === $id) {
                $oldName = $d['name'];
                $d['name']        = $name;
                $d['code']        = trim($data['code'] ?? $d['code']);
                $d['description'] = trim($data['description'] ?? $d['description']);
                $d['status']      = $data['status'] ?? $d['status'];

                // Dynamically update personnel department names if department name was renamed
                if ($oldName !== $name && isset($_SESSION['mock_personnel'])) {
                    foreach ($_SESSION['mock_personnel'] as &$p) {
                        if (($p['department'] ?? '') === $oldName) {
                            $p['department'] = $name;
                        }
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate summary KPI stats dynamically
     */
    public function getSummaryStats(array $allPersonnel): array
    {
        $allDepts        = $this->getAll();
        $totalDepts      = count($allDepts);
        $totalPersonnel  = count($allPersonnel);
        $activePersonnel = count(array_filter($allPersonnel, fn($p) => ($p['status'] ?? '') === 'Aktif'));
        $passivePersonnel= count(array_filter($allPersonnel, fn($p) => ($p['status'] ?? '') === 'Pasif'));

        $emptyDeptCount = 0;
        foreach ($allDepts as $dept) {
            $count = 0;
            foreach ($allPersonnel as $p) {
                if (($p['department'] ?? '') === $dept['name']) {
                    $count++;
                }
            }
            if ($count === 0) $emptyDeptCount++;
        }

        return [
            'total_departments' => $totalDepts,
            'total_personnel'   => $totalPersonnel,
            'active_personnel'  => $activePersonnel,
            'passive_personnel' => $passivePersonnel,
            'empty_departments' => $emptyDeptCount
        ];
    }
}
