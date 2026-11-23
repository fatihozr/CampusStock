<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Customer
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
     * Get all customers with search/type filter
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT * FROM customers WHERE 1=1";
        $params = [];

        if (!empty($filters['customer_type'])) {
            $sql .= " AND customer_type = :customer_type";
            $params['customer_type'] = $filters['customer_type'];
        }

        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $sql .= " AND (first_name LIKE :s1 OR last_name LIKE :s2 OR company_name LIKE :s3 OR phone LIKE :s4)";
            $params['s1'] = $s;
            $params['s2'] = $s;
            $params['s3'] = $s;
            $params['s4'] = $s;
        }

        if (isset($filters['has_balance']) && $filters['has_balance']) {
            $sql .= " AND balance > 0";
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'formatCustomer'], $rows);
    }

    public function formatCustomer(array $c): array
    {
        $c['full_name'] = !empty($c['company_name']) 
            ? $c['company_name'] . ' (' . $c['first_name'] . ' ' . ($c['last_name'] ?? '') . ')'
            : trim($c['first_name'] . ' ' . ($c['last_name'] ?? ''));

        $c['type_label'] = match($c['customer_type']) {
            'student'   => 'Öğrenci',
            'teacher'   => 'Öğretmen',
            'school'    => 'Okul / Kurum',
            'corporate' => 'Kurumsal Firma',
            default     => 'Bireysel'
        };

        $c['type_badge'] = match($c['customer_type']) {
            'student'   => 'bg-info text-white',
            'teacher'   => 'bg-success text-white',
            'school'    => 'bg-primary text-white',
            'corporate' => 'bg-dark text-white',
            default     => 'bg-secondary text-white'
        };

        $c['balance'] = (float)$c['balance'];
        $c['credit_limit'] = (float)$c['credit_limit'];
        $c['discount_rate'] = (float)$c['discount_rate'];

        return $c;
    }

    /**
     * Find customer by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->formatCustomer($row) : null;
    }

    /**
     * Create customer
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO customers (
                customer_type, company_name, first_name, last_name, phone, email,
                address, tax_office, tax_number, discount_rate, credit_limit, notes
            ) VALUES (
                :customer_type, :company_name, :first_name, :last_name, :phone, :email,
                :address, :tax_office, :tax_number, :discount_rate, :credit_limit, :notes
            )
        ");

        $stmt->execute([
            'customer_type' => $data['customer_type'] ?? 'individual',
            'company_name'  => $data['company_name'] ?? null,
            'first_name'    => trim($data['first_name']),
            'last_name'     => $data['last_name'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'email'         => $data['email'] ?? null,
            'address'       => $data['address'] ?? null,
            'tax_office'    => $data['tax_office'] ?? null,
            'tax_number'    => $data['tax_number'] ?? null,
            'discount_rate' => (float)($data['discount_rate'] ?? 0.0),
            'credit_limit'  => (float)($data['credit_limit'] ?? 0.0),
            'notes'         => $data['notes'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update customer
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        $allowed = [
            'customer_type', 'company_name', 'first_name', 'last_name', 'phone', 'email',
            'address', 'tax_office', 'tax_number', 'discount_rate', 'credit_limit', 'status', 'notes'
        ];

        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "{$col} = :{$col}";
                $params[$col] = $data[$col];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE customers SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Adjust customer balance (Debt addition or payment deduction)
     */
    public function adjustBalance(int $customerId, float $amountChange): bool
    {
        $stmt = $this->db->prepare("
            UPDATE customers 
            SET balance = balance + :amount, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        return $stmt->execute([
            'amount' => $amountChange,
            'id'     => $customerId
        ]);
    }
}

