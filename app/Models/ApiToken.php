<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Throwable;

class ApiToken
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureTableExists();
    }

    /**
     * Ensure api_tokens table exists without crashing
     */
    private function ensureTableExists(): void
    {
        if (!$this->db) {
            return;
        }

        try {
            $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS api_tokens (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER NOT NULL,
                        token TEXT UNIQUE NOT NULL,
                        name TEXT DEFAULT 'auth_token',
                        last_used_at DATETIME,
                        expires_at DATETIME,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS api_tokens (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        token VARCHAR(128) UNIQUE NOT NULL,
                        name VARCHAR(100) DEFAULT 'auth_token',
                        last_used_at DATETIME NULL,
                        expires_at DATETIME NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_token (token)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch (Throwable) {
            // Silently ignore if already exists or permission restricted
        }
    }

    /**
     * Create a new secure token for user
     */
    public function createToken(int $userId, string $name = 'auth_token', int $lifetimeDays = 30): array
    {
        $token = 'cs_' . bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + ($lifetimeDays * 86400));

        try {
            $stmt = $this->db->prepare("
                INSERT INTO api_tokens (user_id, token, name, expires_at)
                VALUES (:user_id, :token, :name, :expires_at)
            ");
            $stmt->execute([
                'user_id'    => $userId,
                'token'      => $token,
                'name'       => $name,
                'expires_at' => $expiresAt
            ]);

            return [
                'token'      => $token,
                'name'       => $name,
                'expires_at' => $expiresAt
            ];
        } catch (Throwable $e) {
            // Fallback token format if DB insertion fails
            return [
                'token'      => $token,
                'name'       => $name,
                'expires_at' => $expiresAt
            ];
        }
    }

    /**
     * Find user associated with bearer token
     */
    public function findUserByToken(string $token): ?array
    {
        $token = trim($token);
        if (empty($token)) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT t.id as token_id, t.user_id, t.expires_at,
                       u.id, u.username, u.email, u.first_name, u.last_name, u.title, u.status, u.role_id,
                       COALESCE(r.slug, 'cashier') as role_slug,
                       COALESCE(r.name, 'Kullanıcı') as role_name
                FROM api_tokens t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE t.token = :token AND (t.expires_at IS NULL OR t.expires_at > :now)
                LIMIT 1
            ");
            $stmt->execute([
                'token' => $token,
                'now'   => date('Y-m-d H:i:s')
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Update last_used_at timestamp
                try {
                    $upd = $this->db->prepare("UPDATE api_tokens SET last_used_at = :now WHERE id = :id");
                    $upd->execute(['now' => date('Y-m-d H:i:s'), 'id' => $user['token_id']]);
                } catch (Throwable) {}

                return $user;
            }
        } catch (Throwable) {
            // Table might not exist or error occurred
        }

        return null;
    }

    /**
     * Revoke / delete token
     */
    public function revokeToken(string $token): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM api_tokens WHERE token = :token");
            return $stmt->execute(['token' => trim($token)]);
        } catch (Throwable) {
            return false;
        }
    }
}
