<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Throwable;

class PrintJob
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
                    CREATE TABLE IF NOT EXISTS print_jobs (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        student_id INTEGER NOT NULL,
                        store_id INTEGER NOT NULL DEFAULT 1,
                        document_name TEXT NOT NULL,
                        file_path TEXT,
                        page_count INTEGER NOT NULL DEFAULT 1,
                        color_mode TEXT DEFAULT 'bw',
                        double_sided INTEGER DEFAULT 1,
                        binding TEXT DEFAULT 'none',
                        copies INTEGER NOT NULL DEFAULT 1,
                        total_price REAL NOT NULL DEFAULT 0.0,
                        status TEXT DEFAULT 'pending',
                        pickup_code TEXT NOT NULL,
                        notes TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS print_jobs (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        student_id INT NOT NULL,
                        store_id INT NOT NULL DEFAULT 1,
                        document_name VARCHAR(255) NOT NULL,
                        file_path VARCHAR(255) NULL,
                        page_count INT NOT NULL DEFAULT 1,
                        color_mode ENUM('bw', 'color') DEFAULT 'bw',
                        double_sided TINYINT(1) DEFAULT 1,
                        binding ENUM('none', 'staple', 'spiral') DEFAULT 'none',
                        copies INT NOT NULL DEFAULT 1,
                        total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                        status ENUM('pending', 'printing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
                        pickup_code VARCHAR(10) NOT NULL,
                        notes TEXT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_print_student (student_id),
                        INDEX idx_print_store (store_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch (Throwable) {}
    }

    public function create(array $data): array
    {
        $pickupCode = 'PRN-' . rand(1000, 9999);

        $stmt = $this->db->prepare("
            INSERT INTO print_jobs (
                student_id, store_id, document_name, file_path, page_count,
                color_mode, double_sided, binding, copies, total_price,
                status, pickup_code, notes
            ) VALUES (
                :student_id, :store_id, :document_name, :file_path, :page_count,
                :color_mode, :double_sided, :binding, :copies, :total_price,
                :status, :pickup_code, :notes
            )
        ");

        $stmt->execute([
            'student_id'    => (int)$data['student_id'],
            'store_id'      => (int)($data['store_id'] ?? 1),
            'document_name' => $data['document_name'],
            'file_path'     => $data['file_path'] ?? null,
            'page_count'    => (int)($data['page_count'] ?? 1),
            'color_mode'    => $data['color_mode'] ?? 'bw',
            'double_sided'  => (int)($data['double_sided'] ?? 1),
            'binding'       => $data['binding'] ?? 'none',
            'copies'        => (int)($data['copies'] ?? 1),
            'total_price'   => (float)$data['total_price'],
            'status'        => 'pending',
            'pickup_code'   => $pickupCode,
            'notes'         => $data['notes'] ?? null
        ]);

        return $this->find((int)$this->db->lastInsertId());
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT pj.*, 
                       u.first_name || ' ' || u.last_name as student_name,
                       u.phone as student_phone,
                       s.name as store_name
                FROM print_jobs pj
                LEFT JOIN users u ON pj.student_id = u.id
                LEFT JOIN stores s ON pj.store_id = s.id
                WHERE pj.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($res) return $res;
        } catch (Throwable) {}

        return null;
    }

    public function getAll(array $filters = []): array
    {
        try {
            $sql = "
                SELECT pj.*, 
                       u.first_name || ' ' || u.last_name as student_name,
                       u.phone as student_phone,
                       s.name as store_name
                FROM print_jobs pj
                LEFT JOIN users u ON pj.student_id = u.id
                LEFT JOIN stores s ON pj.store_id = s.id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($filters['student_id'])) {
                $sql .= " AND pj.student_id = :student_id";
                $params['student_id'] = (int)$filters['student_id'];
            }

            if (!empty($filters['store_id'])) {
                $sql .= " AND pj.store_id = :store_id";
                $params['store_id'] = (int)$filters['store_id'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND pj.status = :status";
                $params['status'] = $filters['status'];
            }

            $sql .= " ORDER BY pj.id DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable) {
            return [];
        }
    }

    public function updateStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE print_jobs SET status = :status, updated_at = :now WHERE id = :id");
            return $stmt->execute([
                'id'     => $id,
                'status' => $status,
                'now'    => date('Y-m-d H:i:s')
            ]);
        } catch (Throwable) {
            return false;
        }
    }
}
