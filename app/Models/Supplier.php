<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Supplier
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM suppliers ORDER BY company_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM suppliers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO suppliers (company_name, contact_person, phone, email, address, tax_office, tax_number, balance)
            VALUES (:company_name, :contact_person, :phone, :email, :address, :tax_office, :tax_number, :balance)
        ");
        $stmt->execute([
            'company_name'   => trim($data['company_name']),
            'contact_person' => $data['contact_person'] ?? null,
            'phone'          => $data['phone'] ?? null,
            'email'          => $data['email'] ?? null,
            'address'        => $data['address'] ?? null,
            'tax_office'     => $data['tax_office'] ?? null,
            'tax_number'     => $data['tax_number'] ?? null,
            'balance'        => (float)($data['balance'] ?? 0.0)
        ]);
        return (int)$this->db->lastInsertId();
    }
}
