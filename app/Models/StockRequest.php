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
        try {
            $query = "
                SELECT sr.*, d.name as department_name, 
                       u.first_name as req_first_name, u.last_name as req_last_name
                FROM stock_requests sr
                LEFT JOIN departments d ON sr.department_id = d.id
                LEFT JOIN users u ON sr.requested_by = u.id
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
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get requests by specific user
     */
    public function getByUser(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT sr.*, d.name as department_name
                FROM stock_requests sr
                LEFT JOIN departments d ON sr.department_id = d.id
                WHERE sr.requested_by = :user_id
                ORDER BY sr.id DESC
            ");
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function find(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM stock_requests WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
