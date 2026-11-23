<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Throwable;

class University
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
                    CREATE TABLE IF NOT EXISTS universities (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        slug TEXT UNIQUE NOT NULL,
                        city TEXT NOT NULL,
                        domain TEXT,
                        code TEXT UNIQUE,
                        is_active INTEGER DEFAULT 1,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS universities (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(200) NOT NULL,
                        slug VARCHAR(200) UNIQUE NOT NULL,
                        city VARCHAR(80) NOT NULL,
                        domain VARCHAR(100) NULL,
                        code VARCHAR(50) UNIQUE NULL,
                        is_active TINYINT(1) DEFAULT 1,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch (Throwable) {}
    }

    public function getAll(bool $onlyActive = true): array
    {
        try {
            $sql = "SELECT * FROM universities";
            if ($onlyActive) {
                $sql .= " WHERE is_active = 1";
            }
            $sql .= " ORDER BY name ASC";
            $stmt = $this->db->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Default fallback if table empty
            if (empty($results)) {
                return [
                    [
                        'id' => 1,
                        'name' => 'İstanbul Teknik Üniversitesi (İTÜ)',
                        'slug' => 'itu',
                        'city' => 'İstanbul',
                        'domain' => 'itu.edu.tr',
                        'code' => 'ITU',
                        'is_active' => 1
                    ],
                    [
                        'id' => 2,
                        'name' => 'Orta Doğu Teknik Üniversitesi (ODTÜ)',
                        'slug' => 'odtu',
                        'city' => 'Ankara',
                        'domain' => 'metu.edu.tr',
                        'code' => 'ODTU',
                        'is_active' => 1
                    ],
                    [
                        'id' => 3,
                        'name' => 'Boğaziçi Üniversitesi',
                        'slug' => 'bogazici',
                        'city' => 'İstanbul',
                        'domain' => 'boun.edu.tr',
                        'code' => 'BOUN',
                        'is_active' => 1
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
            $stmt = $this->db->prepare("SELECT * FROM universities WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (Throwable) {}

        // Fallback for default ID 1
        if ($id === 1) {
            return [
                'id' => 1,
                'name' => 'İstanbul Teknik Üniversitesi (İTÜ)',
                'slug' => 'itu',
                'city' => 'İstanbul',
                'domain' => 'itu.edu.tr',
                'code' => 'ITU',
                'is_active' => 1
            ];
        }

        return null;
    }

    public function findBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM universities WHERE slug = :slug LIMIT 1");
            $stmt->execute(['slug' => $slug]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (Throwable) {}

        return null;
    }

    public function store(array $data): array
    {
        $name = trim($data['name'] ?? '');
        $slug = trim($data['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)));
        $city = trim($data['city'] ?? '');
        $domain = trim($data['domain'] ?? '');
        $code = strtoupper(trim($data['code'] ?? ''));

        $stmt = $this->db->prepare("
            INSERT INTO universities (name, slug, city, domain, code, is_active)
            VALUES (:name, :slug, :city, :domain, :code, :is_active)
        ");
        $stmt->execute([
            'name'      => $name,
            'slug'      => $slug,
            'city'      => $city,
            'domain'    => $domain,
            'code'      => $code,
            'is_active' => (int)($data['is_active'] ?? 1)
        ]);

        return $this->find((int)$this->db->lastInsertId()) ?? [];
    }
}
