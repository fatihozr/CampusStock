<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Throwable;

class Store
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        if (!$this->db) return;

        try {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS stores (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        university_id INTEGER NOT NULL,
                        user_id INTEGER,
                        name TEXT NOT NULL,
                        slug TEXT UNIQUE NOT NULL,
                        phone TEXT,
                        location_desc TEXT,
                        min_order_amount REAL DEFAULT 0.0,
                        is_active INTEGER DEFAULT 1,
                        opens_at TEXT DEFAULT '08:30:00',
                        closes_at TEXT DEFAULT '19:00:00',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS stores (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        university_id INT NOT NULL,
                        user_id INT NULL,
                        name VARCHAR(200) NOT NULL,
                        slug VARCHAR(200) UNIQUE NOT NULL,
                        phone VARCHAR(30) NULL,
                        location_desc VARCHAR(255) NULL,
                        min_order_amount DECIMAL(10,2) DEFAULT 0.00,
                        is_active TINYINT(1) DEFAULT 1,
                        opens_at TIME DEFAULT '08:30:00',
                        closes_at TIME DEFAULT '19:00:00',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_store_univ (university_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch (Throwable) {}
    }

    public function getAll(?int $universityId = null, bool $onlyActive = true): array
    {
        try {
            $sql = "
                SELECT s.*, u.name as university_name, u.city as university_city
                FROM stores s
                LEFT JOIN universities u ON s.university_id = u.id
                WHERE 1=1
            ";
            $params = [];

            if ($universityId !== null) {
                $sql .= " AND s.university_id = :univ_id";
                $params['univ_id'] = $universityId;
            }

            if ($onlyActive) {
                $sql .= " AND s.is_active = 1";
            }

            $sql .= " ORDER BY s.name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Default fallback if table empty
            if (empty($results)) {
                return [
                    [
                        'id' => 1,
                        'university_id' => 1,
                        'name' => 'İTÜ Ayazağa Kampüs Kırtasiyesi (Merkez)',
                        'slug' => 'itu-ayazaga-merkez',
                        'phone' => '0212 285 30 00',
                        'location_desc' => '75. Yıl Öğrenci Merkezi Zemin Kat',
                        'min_order_amount' => 0.0,
                        'is_active' => 1,
                        'opens_at' => '08:30:00',
                        'closes_at' => '19:30:00',
                        'university_name' => 'İstanbul Teknik Üniversitesi (İTÜ)',
                        'university_city' => 'İstanbul'
                    ],
                    [
                        'id' => 2,
                        'university_id' => 1,
                        'name' => 'İTÜ Maçka Kampüs Fotokopi & Kırtasiye',
                        'slug' => 'itu-macka-sube',
                        'phone' => '0212 293 13 00',
                        'location_desc' => 'Yabancı Diller Yüksekokulu Karşısı',
                        'min_order_amount' => 0.0,
                        'is_active' => 1,
                        'opens_at' => '09:00:00',
                        'closes_at' => '18:00:00',
                        'university_name' => 'İstanbul Teknik Üniversitesi (İTÜ)',
                        'university_city' => 'İstanbul'
                    ]
                ];
            }

            return $results;
        } catch (Throwable) {
            return [];
        }
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, u.name as university_name, u.city as university_city
                FROM stores s
                LEFT JOIN universities u ON s.university_id = u.id
                WHERE s.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (Throwable) {}

        if ($id === 1) {
            return [
                'id' => 1,
                'university_id' => 1,
                'name' => 'İTÜ Ayazağa Kampüs Kırtasiyesi (Merkez)',
                'slug' => 'itu-ayazaga-merkez',
                'phone' => '0212 285 30 00',
                'location_desc' => '75. Yıl Öğrenci Merkezi Zemin Kat',
                'min_order_amount' => 0.0,
                'is_active' => 1,
                'opens_at' => '08:30:00',
                'closes_at' => '19:30:00',
                'university_name' => 'İstanbul Teknik Üniversitesi (İTÜ)',
                'university_city' => 'İstanbul'
            ];
        }

        return null;
    }

    public function getStoreProducts(int $storeId, array $filters = []): array
    {
        $productModel = new Product();
        return $productModel->getAll($filters);
    }
}
