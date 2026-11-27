<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\Auth;
use PDO;
use Exception;

class AuditLog
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
     * @param string $action 'CREATE', 'UPDATE', 'DELETE' vb.
     * @param string $entityType Tablo adı (örn: 'products')
     * @param int|null $entityId Etkilenen satır ID
     * @param array|null $oldValues Eski veri dizisi
     * @param array|null $newValues Yeni veri dizisi
     */
    public static function log(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        try {
            $db = Database::getConnection();
            $user = Auth::user();
            if (!$db || !$user) return;

            $columnRows = $db->query('SHOW COLUMNS FROM audit_logs')->fetchAll(PDO::FETCH_ASSOC);
            $columns = array_column($columnRows, 'Field');

            $oldJson = $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            if (!in_array('entity_type', $columns, true)) {
                $description = json_encode([
                    'entity_id' => $entityId,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $stmt = $db->prepare("
                    INSERT INTO audit_logs (user_id, action, module, description, ip_address, user_agent)
                    VALUES (:user_id, :action, :module, :description, :ip_address, :user_agent)
                ");
                $stmt->execute([
                    'user_id' => $user['id'],
                    'action' => $action,
                    'module' => $entityType,
                    'description' => $description ?: '{}',
                    'ip_address' => $ipAddress,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ]);
                return;
            }

            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address)
                VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address)
            ");
            
            $stmt->execute([
                'user_id' => $user['id'],
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_values' => $oldJson,
                'new_values' => $newJson,
                'ip_address' => $ipAddress
            ]);
        } catch (Exception $e) {
            // Sessizce hatayı yut (Loglama hatası sistemi durdurmamalı)
            error_log("AuditLog Error: " . $e->getMessage());
        }
    }
}
