<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class CashMovement
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
    }

    public function record(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO cash_movements (user_id, movement_type, amount, payment_method, reference_id, description)
            VALUES (:user_id, :movement_type, :amount, :payment_method, :reference_id, :description)
        ");

        $stmt->execute([
            'user_id'        => (int)$data['user_id'],
            'movement_type'  => $data['movement_type'], // sale_cash, sale_card, return_cash, refund_card, customer_payment, expense, opening_balance, closing_balance
            'amount'         => (float)$data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference_id'   => $data['reference_id'] ?? null,
            'description'    => $data['description'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getAll(int $limit = 50): array
    {
        $sql = "
            SELECT cm.*, u.username, u.first_name, u.last_name
            FROM cash_movements cm
            LEFT JOIN users u ON cm.user_id = u.id
            ORDER BY cm.id DESC
            LIMIT {$limit}
        ";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
