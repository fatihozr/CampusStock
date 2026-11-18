<?php

namespace App\Models;

use Exception;

class Category
{
    public function __construct()
    {
        $this->initMockSessionData();
    }

    /**
     * Initialize standard cleaning product categories
     */
    private function initMockSessionData(): void
    {
        if (!isset($_SESSION['mock_categories'])) {
            $_SESSION['mock_categories'] = [
                ['id' => 1, 'name' => 'Temizlik Kimyasalları', 'code' => 'KAT-KIM', 'icon' => 'bi-droplet-half', 'color' => 'primary', 'description' => 'Çamaşır suyu, deterjan, dezenfektan, yüzey ve cam temizleyicileri'],
                ['id' => 2, 'name' => 'Kağıt Ürünleri', 'code' => 'KAT-KGT', 'icon' => 'bi-journal-text', 'color' => 'warning', 'description' => 'Tuvalet kağıdı, Z-katlama ve rulo kağıt havlu, peçete grubu'],
                ['id' => 3, 'name' => 'Temizlik Ekipmanları', 'code' => 'KAT-EKP', 'icon' => 'bi-tools', 'color' => 'success', 'description' => 'Mop, kova, fırça, sünger, mikrofiber bez ve aparatlar'],
                ['id' => 4, 'name' => 'Çöp ve Atık Grubu', 'code' => 'KAT-COP', 'icon' => 'bi-trash', 'color' => 'danger', 'description' => 'Battal boy, dökme ve tıbbi atık çöp poşetleri'],
                ['id' => 5, 'name' => 'Kişisel Koruyucu & Sarf', 'code' => 'KAT-KSR', 'icon' => 'bi-shield-check', 'color' => 'info', 'description' => 'Bulaşık ve muayene eldivenleri, maske ve koruyucu hijyen gereçleri']
            ];
        }
    }

    /**
     * Get all categories sorted alphabetically
     */
    public function getAll(): array
    {
        $items = $_SESSION['mock_categories'] ?? [];
        usort($items, function ($a, $b) {
            return turkish_strcmp($a['name'] ?? '', $b['name'] ?? '');
        });
        return $items;
    }

    /**
     * Find category by ID
     */
    public function find(int $id): ?array
    {
        foreach ($_SESSION['mock_categories'] as $c) {
            if ((int)$c['id'] === $id) return $c;
        }
        return null;
    }

    /**
     * Find category by Name
     */
    public function findByName(string $name): ?array
    {
        $search = mb_strtolower(trim($name), 'UTF-8');
        foreach ($_SESSION['mock_categories'] as $c) {
            if (mb_strtolower($c['name'], 'UTF-8') === $search) return $c;
        }
        return null;
    }

    /**
     * Add new category
     */
    public function store(array $data): array
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new Exception('Kategori adı boş olamaz!');
        }

        if ($this->findByName($name)) {
            throw new Exception('Bu isimde bir kategori zaten mevcut!');
        }

        $newId = count($_SESSION['mock_categories']) > 0
            ? max(array_column($_SESSION['mock_categories'], 'id')) + 1
            : 1;

        $category = [
            'id'          => $newId,
            'name'        => $name,
            'code'        => strtoupper(trim($data['code'] ?? ('KAT-' . $newId))),
            'icon'        => trim($data['icon'] ?? 'bi-tag'),
            'color'       => trim($data['color'] ?? 'primary'),
            'description' => trim($data['description'] ?? '')
        ];

        $_SESSION['mock_categories'][] = $category;

        if (function_exists('log_audit')) {
            log_audit('Kategori Eklendi', 'Kategoriler', "Yeni kategori '{$name}' oluşturuldu.");
        }

        return $category;
    }
}
