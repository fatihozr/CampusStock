<?php

declare(strict_types=1);

require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';

\App\Config\App::init();
$db = \App\Config\Database::getConnection();

if (!$db instanceof PDO) {
    exit("MySQL bağlantısı kurulamadı.\n");
}

$tables = $db->query("
    SELECT TABLE_NAME
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_TYPE = 'BASE TABLE'
    ORDER BY TABLE_NAME
")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    $quotedTable = '`' . str_replace('`', '``', (string)$table) . '`';
    $row = $db->query("SHOW CREATE TABLE {$quotedTable}")->fetch(PDO::FETCH_ASSOC);
    $createSql = $row['Create Table'] ?? '';
    echo "-- {$table}\n{$createSql};\n\n";
}
