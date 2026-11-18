<?php
require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';

\App\Config\App::init();
$db = \App\Config\Database::getConnection();

if ($db) {
    $result = $db->query("DESCRIBE stock_movements");
    print_r($result->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "Database connection failed.\n";
}
