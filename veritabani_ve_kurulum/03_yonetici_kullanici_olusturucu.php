<?php
require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';

\App\Config\App::init();
$db = \App\Config\Database::getConnection();

if (!$db) {
    die("Database connection failed!");
}

try {
    // Get envanter_role id
    $stmtGet = $db->prepare("SELECT id FROM roles WHERE slug = 'envanter_role'");
    $stmtGet->execute();
    $envanterRoleId = $stmtGet->fetchColumn();

    if (!$envanterRoleId) {
        // Fallback to inserting the role
        $db->exec("INSERT INTO roles (slug, name, description, permissions) VALUES ('envanter_role', 'Envanter Yöneticisi', 'Sistemin genel yöneticisi', '[\"dashboard\", \"inventory\", \"stock_in\", \"stock_out\", \"reports\", \"personnel\", \"users\", \"categories\"]')");
        $envanterRoleId = $db->lastInsertId();
    }

    $password = password_hash('123', PASSWORD_DEFAULT);
    $stmtUser = $db->prepare("INSERT INTO users (role_id, department_id, username, email, password_hash, first_name, last_name, title, status) VALUES (:role_id, 1, :username, :email, :password, :fname, :lname, :title, 'active')");
    
    // Check if user exists
    $stmtCheck = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn()) {
        // Update password if exists
        $stmtUpdate = $db->prepare("UPDATE users SET password_hash = :password WHERE username = 'admin'");
        $stmtUpdate->execute(['password' => $password]);
        echo "Admin password updated successfully!\n";
    } else {
        // Insert new user
        $stmtUser->execute([
            'role_id' => $envanterRoleId,
            'username' => 'admin',
            'email' => 'admin@belediye.gov.tr',
            'password' => $password,
            'fname' => 'Sistem',
            'lname' => 'Yöneticisi',
            'title' => 'Admin'
        ]);
        echo "Admin user created successfully!\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
