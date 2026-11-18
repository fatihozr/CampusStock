<?php
require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';

\App\Config\App::init();
$db = \App\Config\Database::getConnection();

if ($db) {
    $db->exec("UPDATE departments SET name = REPLACE(name, 'Müdürlüğü', 'Daire Başkanlığı')");
    $db->exec("UPDATE users SET title = REPLACE(title, 'Müdürlüğü', 'Daire Başkanlığı')");
    echo 'Updated terminology in database.';
} else {
    echo 'Database connection failed.';
}
