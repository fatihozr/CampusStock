<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class StockRequest
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
     * Get all requests for approval center or all requests
     */
    public function getAll(string $status = ''): array
    {
        $query = "
            SELECT sr.*, d.name as department_name, 
                   u.first_name as req_first_name, u.last_name as req_last_name
            FROM stock_requests sr
            JOIN departments d ON sr.department_id = d.id
            JOIN users u ON sr.requested_by = u.id
        ";
        
        if ($status !== '') {
            $query .= " WHERE sr.status = :status";
        }
        
        $query .= " ORDER BY sr.id DESC";
        
        $stmt = $this->db->prepare($query);
        
        if ($status !== '') {
            $stmt->bindValue(':status', $status);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get requests by specific user
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT sr.*, d.name as department_name
            FROM stock_requests sr
            JOIN departments d ON sr.department_id = d.id
            WHERE sr.requested_by = :user_id
            ORDER BY sr.id DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find single request with its items
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT sr.*, d.name as department_name, 
                   u.first_name as req_first_name, u.last_name as req_last_name,
                   a.first_name as app_first_name, a.last_name as app_last_name
            FROM stock_requests sr
            JOIN departments d ON sr.department_id = d.id
            JOIN users u ON sr.requested_by = u.id
            LEFT JOIN users a ON sr.approved_by = a.id
            WHERE sr.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($request) {
            $itemStmt = $this->db->prepare("
                SELECT sri.*, p.name as product_name, p.code as product_code, p.unit
                FROM stock_request_items sri
                JOIN products p ON sri.product_id = p.id
                WHERE sri.stock_request_id = :req_id
            ");
            $itemStmt->execute(['req_id' => $id]);
            $request['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $request ?: null;
    }

    /**
     * Store new request (Transactions)
     */
    public function store(array $data, array $items): array
    {
        try {
            $this->db->beginTransaction();
            
            $requestNo = 'TLP-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            
            $stmt = $this->db->prepare("
                INSERT INTO stock_requests (request_no, department_id, requested_by, status)
                VALUES (:request_no, :department_id, :requested_by, 'pending')
            ");
            
            $stmt->execute([
                'request_no' => $requestNo,
                'department_id' => $data['department_id'],
                'requested_by' => $data['requested_by']
            ]);
            
            $requestId = $this->db->lastInsertId();
            
            $itemStmt = $this->db->prepare("
                INSERT INTO stock_request_items (stock_request_id, product_id, quantity, description)
                VALUES (:stock_request_id, :product_id, :quantity, :description)
            ");
            
            foreach ($items as $item) {
                if ($item['quantity'] > 0) {
                    $itemStmt->execute([
                        'stock_request_id' => $requestId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'description' => $item['description'] ?? null
                    ]);
                }
            }
            
            $this->db->commit();
            return $this->find((int)$requestId);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Approve request and decrease stock (Transaction with FOR UPDATE)
     */
    public function approve(int $requestId, int $approvedBy, string $deliveredTo): bool
    {
        try {
            $this->db->beginTransaction();
            
            $request = $this->find($requestId);
            if (!$request || $request['status'] !== 'pending') {
                throw new Exception("Geçersiz veya zaten işlenmiş talep.");
            }
            
            foreach ($request['items'] as $item) {
                // Lock row
                $stockStmt = $this->db->prepare("SELECT * FROM products WHERE id = :id FOR UPDATE");
                $stockStmt->execute(['id' => $item['product_id']]);
                $product = $stockStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product['current_stock'] < $item['quantity']) {
                    throw new Exception("Yetersiz stok: " . $product['name'] . " (Mevcut: " . $product['current_stock'] . ")");
                }
                
                $nextStock = $product['current_stock'] - $item['quantity'];
                $updateStmt = $this->db->prepare("UPDATE products SET current_stock = :stock WHERE id = :id");
                $updateStmt->execute(['stock' => $nextStock, 'id' => $product['id']]);
                
                // Log movement
                $movModel = new StockMovement();
                $movModel->logMovement([
                    'product_id' => $product['id'],
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'prev_stock' => $product['current_stock'],
                    'next_stock' => $nextStock,
                    'unit_cost' => $product['unit_price'],
                    'total_cost' => $item['quantity'] * $product['unit_price'],
                    'department_id' => $request['department_id'],
                    'stock_request_id' => $requestId,
                    'performed_by' => $approvedBy,
                    'description' => 'Malzeme talebi onayı: ' . $request['request_no']
                ]);
            }
            
            $now = date('Y-m-d H:i:s');
            $updReq = $this->db->prepare("
                UPDATE stock_requests 
                SET status = 'approved', approved_by = :approved_by, delivered_to = :delivered_to, approved_at = :now, delivered_at = :now
                WHERE id = :id
            ");
            $updReq->execute([
                'approved_by' => $approvedBy,
                'delivered_to' => $deliveredTo,
                'now' => $now,
                'id' => $requestId
            ]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reject request
     */
    public function reject(int $requestId, int $approvedBy, string $reason): bool
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            UPDATE stock_requests 
            SET status = 'rejected', approved_by = :approved_by, rejection_reason = :reason, approved_at = :now
            WHERE id = :id AND status = 'pending'
        ");
        return $stmt->execute([
            'approved_by' => $approvedBy,
            'reason' => $reason,
            'now' => $now,
            'id' => $requestId
        ]);
    }
}
