<?php

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("Bu migration yalnızca komut satırından çalıştırılabilir.\n");
}

require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';

\App\Config\App::init();
$db = \App\Config\Database::getConnection();

if (!$db instanceof PDO) {
    exit("MySQL bağlantısı kurulamadı.\n");
}

$columnExists = static function (string $table, string $column) use ($db): bool {
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :table_name
          AND COLUMN_NAME = :column_name
    ");
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);

    return (int)$stmt->fetchColumn() > 0;
};

$addColumn = static function (string $table, string $column, string $definition) use ($db, $columnExists): void {
    if ($columnExists($table, $column)) {
        echo "[ATLANDI] {$table}.{$column} zaten mevcut.\n";
        return;
    }

    $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    echo "[EKLENDİ] {$table}.{$column}\n";
};

try {
    if (!$columnExists('stock_movements', 'type') && $columnExists('stock_movements', 'movement_type')) {
        $db->exec("
            ALTER TABLE stock_movements
            CHANGE COLUMN movement_type type VARCHAR(50) NOT NULL
        ");
        echo "[YENİDEN ADLANDIRILDI] stock_movements.movement_type -> type\n";
    }

    $addColumn('stock_movements', 'status', "VARCHAR(50) NOT NULL DEFAULT 'approved' AFTER `type`");
    $addColumn('stock_movements', 'approved_by', 'INT NULL AFTER `performed_by`');
    $addColumn('stock_movements', 'rejection_reason', 'TEXT NULL AFTER `approved_by`');
    $addColumn('stock_movements', 'approved_at', 'DATETIME NULL AFTER `created_at`');
    $addColumn('stock_movements', 'module', "VARCHAR(50) NOT NULL DEFAULT 'cleaning' AFTER `approved_at`");
    $addColumn('stock_movements', 'delivered_by', 'VARCHAR(255) NULL AFTER `module`');
    $addColumn('stock_movements', 'received_by', 'VARCHAR(255) NULL AFTER `delivered_by`');

    $addColumn('products', 'module', "VARCHAR(50) NOT NULL DEFAULT 'cleaning' AFTER `updated_at`");
    $addColumn('categories', 'module', "VARCHAR(50) NOT NULL DEFAULT 'cleaning' AFTER `created_at`");
    $addColumn('roles', 'permissions', 'TEXT NULL AFTER `description`');

    $db->exec("
        CREATE TABLE IF NOT EXISTS stock_counts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            count_no VARCHAR(100) UNIQUE NOT NULL,
            counted_by INT NOT NULL,
            approved_by INT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending_approval',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            approved_at DATETIME NULL,
            INDEX idx_stock_counts_counted_by (counted_by),
            INDEX idx_stock_counts_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS stock_count_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stock_count_id INT NOT NULL,
            product_id INT NOT NULL,
            system_stock DECIMAL(10,2) NOT NULL,
            physical_stock DECIMAL(10,2) NOT NULL,
            difference DECIMAL(10,2) NOT NULL,
            reason TEXT NULL,
            INDEX idx_count_items_count (stock_count_id),
            INDEX idx_count_items_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "[HAZIR] MySQL şema güncellemesi tamamlandı.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "[HATA] {$e->getMessage()}\n");
    exit(1);
}
