<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class StockMovement
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
     * Record a stock movement
     */
    public function record(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO stock_movements (
                product_id, movement_type, quantity, prev_stock, next_stock,
                unit_cost, total_cost, reference_type, reference_id, notes, created_by
            ) VALUES (
                :product_id, :movement_type, :quantity, :prev_stock, :next_stock,
                :unit_cost, :total_cost, :reference_type, :reference_id, :notes, :created_by
            )
        ");

        $stmt->execute([
            'product_id'     => (int)$data['product_id'],
            'movement_type'  => $data['movement_type'], // purchase, sale, sale_return, adjustment, waste, count
            'quantity'       => (float)$data['quantity'],
            'prev_stock'     => (float)$data['prev_stock'],
            'next_stock'     => (float)$data['next_stock'],
            'unit_cost'      => (float)($data['unit_cost'] ?? 0.0),
            'total_cost'     => (float)($data['total_cost'] ?? 0.0),
            'reference_type' => $data['reference_type'] ?? 'manual',
            'reference_id'   => $data['reference_id'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'created_by'     => !empty($data['created_by']) ? (int)$data['created_by'] : null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get recent movements
     */
    public function getAll(int $limit = 100, ?int $productId = null): array
    {
        $sql = "
            SELECT m.*, 
                   p.name AS product_name, 
                   p.code AS product_code, 
                   p.barcode AS product_barcode,
                   p.unit AS product_unit,
                   u.username AS user_name,
                   u.first_name, u.last_name
            FROM stock_movements m
            JOIN products p ON m.product_id = p.id
            LEFT JOIN users u ON m.created_by = u.id
            WHERE 1=1
        ";

        $params = [];
        if ($productId) {
            $sql .= " AND m.product_id = :product_id";
            $params['product_id'] = $productId;
        }

        $sql .= " ORDER BY m.id DESC LIMIT {$limit}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($r) {
            $r['type_label'] = match($r['movement_type']) {
                'purchase'        => 'Satın Alma (Giriş)',
                'sale'            => 'Satış (Çıkış)',
                'sale_return'     => 'Müşteri İadesi (Giriş)',
                'purchase_return' => 'Tedarikçi İadesi (Çıkış)',
                'adjustment'      => 'Stok Düzeltme',
                'waste'           => 'Zayiat / Fire (Çıkış)',
                'count'           => 'Sayım Güncellemesi',
                default           => $r['movement_type']
            };

            $r['badge_class'] = match($r['movement_type']) {
                'purchase', 'sale_return' => 'bg-success text-white',
                'sale', 'waste'           => 'bg-danger text-white',
                'adjustment', 'count'     => 'bg-warning text-dark',
                default                   => 'bg-secondary text-white'
            };

            return $r;
        }, $rows);
    }

    /**
     * Get recent movements (Alias for dashboard)
     */
    public function getRecent(int $limit = 5): array
    {
        return $this->getAll($limit);
    }

    /**
     * Legacy method fallback for views that call getPendingMovements()
     */
    public function getPendingMovements(): array
    {
        return [];
    }
}
