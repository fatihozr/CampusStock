<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class User
{
    private ?PDO $db;
    private Role $roleModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->roleModel = new Role();
        if (!$this->db) {
            throw new Exception("Veritabanı bağlantısı kurulamadı. Lütfen ayarları kontrol edin.");
        }
    }

    /**
     * Find user by username or email
     */
    public function findByUsernameOrEmail(string $identifier): ?array
    {
        $identifier = trim($identifier);

        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name, r.slug as role_slug
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE (u.username = :ident1 OR u.email = :ident2) AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->execute(['ident1' => $identifier, 'ident2' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Verify user password using PHP native password_verify
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, r.name as role_name, COALESCE(u.title, 'Mağaza') as department
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name, COALESCE(u.title, 'Mağaza') as department
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Store new user account
     */
    public function storeUser(array $data): array
    {
        $plainPassword = !empty($data['password']) ? trim($data['password']) : 'admin123';
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO users (role_id, department_id, username, email, password_hash, first_name, last_name, title, status)
            VALUES (:role_id, :department_id, :username, :email, :password_hash, :first_name, :last_name, :title, :status)
        ");
        
        $stmt->execute([
            'role_id'       => (int)($data['role_id'] ?? 4),
            'department_id' => (int)($data['department_id'] ?? 1),
            'username'      => trim($data['username'] ?? ''),
            'email'         => trim($data['email'] ?? ''),
            'password_hash' => $hash,
            'first_name'    => trim($data['first_name'] ?? ''),
            'last_name'     => trim($data['last_name'] ?? ''),
            'title'         => trim($data['title'] ?? ''),
            'status'        => $data['status'] ?? 'active'
        ]);

        return $this->find((int)$this->db->lastInsertId());
    }

    /**
     * Update user account
     */
    public function updateUser(int $id, array $data): bool
    {
        $query = "UPDATE users SET 
            role_id = :role_id, 
            department_id = :department_id,
            username = :username,
            email = :email,
            first_name = :first_name,
            last_name = :last_name,
            title = :title,
            status = :status";
            
        $params = [
            'role_id'       => (int)($data['role_id'] ?? 4),
            'department_id' => (int)($data['department_id'] ?? 1),
            'username'      => trim($data['username'] ?? ''),
            'email'         => trim($data['email'] ?? ''),
            'first_name'    => trim($data['first_name'] ?? ''),
            'last_name'     => trim($data['last_name'] ?? ''),
            'title'         => trim($data['title'] ?? ''),
            'status'        => $data['status'] ?? 'active',
            'id'            => $id
        ];

        if (!empty($data['password'])) {
            $query .= ", password_hash = :password_hash";
            $params['password_hash'] = password_hash(trim($data['password']), PASSWORD_DEFAULT);
        }

        $query .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Delete user account
     */
    public function deleteUser(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(int $userId): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = :now WHERE id = :id");
        $stmt->execute(['id' => $userId, 'now' => $now]);
    }
}

