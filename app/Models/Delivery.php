<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Delivery
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
     * Find single delivery with its checked items
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, u.first_name, u.last_name, p.procurement_no, p.supplier
            FROM deliveries d
            JOIN users u ON d.received_by = u.id
            JOIN procurements p ON d.procurement_id = p.id
            WHERE d.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $del = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($del) {
            $itemStmt = $this->db->prepare("
                SELECT di.*, pi.product_id, pi.quantity as expected_quantity,
                       pr.name as product_name, pr.code as product_code
                FROM delivery_items di
                JOIN procurement_items pi ON di.procurement_item_id = pi.id
                JOIN products pr ON pi.product_id = pr.id
                WHERE di.delivery_id = :id
            ");
            $itemStmt->execute(['id' => $id]);
            $del['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $del ?: null;
    }

    /**
     * Store new delivery and update stock if fully compliant
     */
    public function store(array $data, array $items): array
    {
        try {
            $this->db->beginTransaction();
            
            // Check if there are non-compliant items
            $hasNonCompliant = false;
            foreach ($items as $item) {
                if ($item['is_compliant'] !== 'yes') {
                    $hasNonCompliant = true;
                    break;
                }
            }
            
            $status = $hasNonCompliant ? 'pending_approval' : 'approved';
            
            $stmt = $this->db->prepare("
                INSERT INTO deliveries (procurement_id, document_no, received_by, status)
                VALUES (:procurement_id, :document_no, :received_by, :status)
            ");
            
            $stmt->execute([
                'procurement_id' => $data['procurement_id'],
                'document_no' => $data['document_no'] ?? null,
                'received_by' => $data['received_by'],
                'status' => $status
            ]);
            
            $deliveryId = $this->db->lastInsertId();
            
            $itemStmt = $this->db->prepare("
                INSERT INTO delivery_items (delivery_id, procurement_item_id, quantity, is_compliant, non_compliance_reason)
                VALUES (:delivery_id, :procurement_item_id, :quantity, :is_compliant, :non_compliance_reason)
            ");
            
            foreach ($items as $item) {
                $itemStmt->execute([
                    'delivery_id' => $deliveryId,
                    'procurement_item_id' => $item['procurement_item_id'],
                    'quantity' => $item['quantity'],
                    'is_compliant' => $item['is_compliant'],
                    'non_compliance_reason' => $item['non_compliance_reason'] ?? null
                ]);
                
                // If compliant, update the delivered quantity in procurement
                // and update the stock directly
                if ($item['is_compliant'] === 'yes') {
                    $updProc = $this->db->prepare("
                        UPDATE procurement_items 
                        SET delivered_quantity = delivered_quantity + :qty
                        WHERE id = :id
                    ");
                    $updProc->execute([
                        'qty' => $item['quantity'],
                        'id' => $item['procurement_item_id']
                    ]);
                    
                    // Update Stock
                    $prodStmt = $this->db->prepare("
                        SELECT product_id, unit_price FROM procurement_items WHERE id = :id
                    ");
                    $prodStmt->execute(['id' => $item['procurement_item_id']]);
                    $piInfo = $prodStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($piInfo) {
                        $lockStmt = $this->db->prepare("SELECT * FROM products WHERE id = :id FOR UPDATE");
                        $lockStmt->execute(['id' => $piInfo['product_id']]);
                        $product = $lockStmt->fetch(PDO::FETCH_ASSOC);
                        
                        $nextStock = $product['current_stock'] + $item['quantity'];
                        $this->db->prepare("UPDATE products SET current_stock = :stock WHERE id = :id")
                                 ->execute(['stock' => $nextStock, 'id' => $product['id']]);
                                 
                        // Log Movement
                        $movModel = new StockMovement();
                        $movModel->logMovement([
                            'product_id' => $product['id'],
                            'type' => 'in',
                            'quantity' => $item['quantity'],
                            'prev_stock' => $product['current_stock'],
                            'next_stock' => $nextStock,
                            'unit_cost' => $piInfo['unit_price'],
                            'total_cost' => $item['quantity'] * $piInfo['unit_price'],
                            'procurement_id' => $data['procurement_id'],
                            'performed_by' => $data['received_by'],
                            'description' => 'İhale tesellüm işlemi (İrsaliye: ' . ($data['document_no'] ?? 'Yok') . ')'
                        ]);
                    }
                }
            }
            
            // Update Procurement Status
            $procStmt = $this->db->prepare("
                SELECT SUM(quantity) as total_qty, SUM(delivered_quantity) as del_qty
                FROM procurement_items
                WHERE procurement_id = :id
            ");
            $procStmt->execute(['id' => $data['procurement_id']]);
            $procStats = $procStmt->fetch(PDO::FETCH_ASSOC);
            
            $procStatus = 'pending';
            if ($procStats['del_qty'] > 0 && $procStats['del_qty'] < $procStats['total_qty']) {
                $procStatus = 'partial';
            } elseif ($procStats['del_qty'] >= $procStats['total_qty']) {
                $procStatus = 'completed';
            }
            
            $this->db->prepare("UPDATE procurements SET status = :status WHERE id = :id")
                     ->execute(['status' => $procStatus, 'id' => $data['procurement_id']]);
                     
            $this->db->commit();
            return $this->find((int)$deliveryId);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
