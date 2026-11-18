<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Procurement
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
    }

    /**
     * Get all procurements (tenders)
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT p.*, u.first_name, u.last_name
            FROM procurements p
            JOIN users u ON p.created_by = u.id
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find single procurement with items
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.first_name, u.last_name
            FROM procurements p
            JOIN users u ON p.created_by = u.id
            WHERE p.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $proc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($proc) {
            $itemStmt = $this->db->prepare("
                SELECT pi.*, pr.name as product_name, pr.code as product_code, pr.unit
                FROM procurement_items pi
                JOIN products pr ON pi.product_id = pr.id
                WHERE pi.procurement_id = :id
            ");
            $itemStmt->execute(['id' => $id]);
            $proc['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $proc ?: null;
    }

    /**
     * Store new procurement
     */
    public function store(array $data, array $items): array
    {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO procurements (procurement_no, supplier, status, created_by)
                VALUES (:procurement_no, :supplier, 'pending', :created_by)
            ");
            
            $stmt->execute([
                'procurement_no' => $data['procurement_no'],
                'supplier' => $data['supplier'],
                'created_by' => $data['created_by']
            ]);
            
            $procId = $this->db->lastInsertId();
            
            $itemStmt = $this->db->prepare("
                INSERT INTO procurement_items (procurement_id, product_id, quantity, unit_price, expected_specifications)
                VALUES (:procurement_id, :product_id, :quantity, :unit_price, :expected_specifications)
            ");
            
            foreach ($items as $item) {
                $itemStmt->execute([
                    'procurement_id' => $procId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'expected_specifications' => $item['expected_specifications'] ?? null
                ]);
            }
            
            $this->db->commit();
            return $this->find((int)$procId);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
