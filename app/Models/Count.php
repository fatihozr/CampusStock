<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use RuntimeException;
use Throwable;

final class Count
{
    private PDO $db;
    private static bool $schemaEnsured = false;

    public function __construct()
    {
        $connection = Database::getConnection();
        if (!$connection instanceof PDO) {
            throw new RuntimeException('Veritabanı bağlantısı kurulamadı.');
        }

        $this->db = $connection;
        $this->ensureSchema();
    }

    /**
     * Eksik fiziksel sayım tablolarını MySQL üzerinde oluşturur.
     */
    private function ensureSchema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS stock_counts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                count_no VARCHAR(100) UNIQUE NOT NULL,
                counted_by INT NOT NULL,
                approved_by INT,
                status VARCHAR(50) DEFAULT 'pending_approval',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                approved_at DATETIME,
                INDEX idx_stock_counts_counted_by (counted_by),
                INDEX idx_stock_counts_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS stock_count_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                stock_count_id INT NOT NULL,
                product_id INT NOT NULL,
                system_stock DECIMAL(10,2) NOT NULL,
                physical_stock DECIMAL(10,2) NOT NULL,
                difference DECIMAL(10,2) NOT NULL,
                reason TEXT,
                INDEX idx_count_items_count (stock_count_id),
                INDEX idx_count_items_product (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$schemaEnsured = true;
    }

    /**
     * Tüm fiziksel sayımları getirir.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT
                c.*,
                c.count_no AS document_no,
                c.created_at AS count_date,
                NULL AS notes,
                u.first_name,
                u.last_name
            FROM stock_counts c
            INNER JOIN users u ON u.id = c.counted_by
            ORDER BY c.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Yeni fiziksel sayım kaydeder.
     */
    public function store(array $data, array $items): array
    {
        try {
            $this->db->beginTransaction();

            $countNo = trim((string)($data['document_no'] ?? $data['count_no'] ?? ''));
            if ($countNo === '') {
                $countNo = 'SAYIM-' . date('Ymd-His');
            }

            $countDate = trim((string)($data['count_date'] ?? date('Y-m-d H:i:s')));
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $countDate)) {
                $countDate .= ' ' . date('H:i:s');
            }

            $stmt = $this->db->prepare("
                INSERT INTO stock_counts (count_no, counted_by, status, created_at)
                VALUES (:count_no, :counted_by, :status, :created_at)
            ");
            $stmt->execute([
                'count_no' => $countNo,
                'counted_by' => (int)$data['counted_by'],
                'status' => 'pending_approval',
                'created_at' => $countDate,
            ]);

            $countId = (int)$this->db->lastInsertId();
            $itemStmt = $this->db->prepare("
                INSERT INTO stock_count_items (
                    stock_count_id, product_id, system_stock,
                    physical_stock, difference, reason
                ) VALUES (
                    :stock_count_id, :product_id, :system_stock,
                    :physical_stock, :difference, :reason
                )
            ");

            foreach ($items as $item) {
                $systemStock = (float)($item['expected_quantity'] ?? $item['system_stock'] ?? 0);
                $physicalStock = (float)($item['counted_quantity'] ?? $item['physical_stock'] ?? 0);

                $itemStmt->execute([
                    'stock_count_id' => $countId,
                    'product_id' => (int)$item['product_id'],
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $physicalStock - $systemStock,
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            $this->db->commit();

            return [
                'id' => $countId,
                'count_no' => $countNo,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
