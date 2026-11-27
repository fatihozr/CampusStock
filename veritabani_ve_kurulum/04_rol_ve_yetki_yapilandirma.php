<?php
require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';

\App\Config\App::init();
$db = \App\Config\Database::getConnection();

if (!$db) {
    die("Database connection failed!");
}

try {

    // 1. Alter stock_movements
    try {
        $db->exec("ALTER TABLE stock_movements ADD COLUMN delivered_by TEXT DEFAULT NULL");
        echo "stock_movements: delivered_by added.\n";
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    try {
        $db->exec("ALTER TABLE stock_movements ADD COLUMN received_by TEXT DEFAULT NULL");
        echo "stock_movements: received_by added.\n";
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    // 2. Alter roles to add permissions
    try {
        $db->exec("ALTER TABLE roles ADD COLUMN permissions TEXT DEFAULT NULL");
        echo "roles: permissions added.\n";
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    // 3. Create new roles
    $stmt = $db->prepare("INSERT INTO roles (slug, name, description, permissions) VALUES (:slug, :name, :description, :permissions)");
    
    // Temizlik Rolü
    try {
        $stmt->execute([
            'slug' => 'temizlik_role',
            'name' => 'Temizlik Görevlisi',
            'description' => 'Sadece stok çıkışı yapabilir',
            'permissions' => json_encode(['inventory', 'stock_out'])
        ]);
        $temizlikRoleId = $db->lastInsertId();
        echo "Created role: temizlik_role\n";
    } catch (Exception $e) { 
        echo $e->getMessage() . "\n"; 
        $stmtGet = $db->prepare("SELECT id FROM roles WHERE slug = 'temizlik_role'");
        $stmtGet->execute();
        $temizlikRoleId = $stmtGet->fetchColumn();
    }

    // Envanter Rolü
    try {
        $stmt->execute([
            'slug' => 'envanter_role',
            'name' => 'Envanter Yöneticisi',
            'description' => 'Sistemin genel yöneticisi',
            'permissions' => json_encode(['dashboard', 'inventory', 'stock_in', 'stock_out', 'reports', 'personnel', 'users', 'categories'])
        ]);
        $envanterRoleId = $db->lastInsertId();
        echo "Created role: envanter_role\n";
    } catch (Exception $e) { 
        echo $e->getMessage() . "\n"; 
        $stmtGet = $db->prepare("SELECT id FROM roles WHERE slug = 'envanter_role'");
        $stmtGet->execute();
        $envanterRoleId = $stmtGet->fetchColumn();
    }

    // 4. Create Users
    $stmtUser = $db->prepare("INSERT INTO users (role_id, department_id, username, email, password_hash, first_name, last_name, title, status) VALUES (:role_id, 1, :username, :email, :password, :fname, :lname, :title, 'active')");
    $password = password_hash('123456', PASSWORD_DEFAULT);

    try {
        $stmtUser->execute([
            'role_id' => $temizlikRoleId,
            'username' => 'temizlik',
            'email' => 'temizlik@belediye.gov.tr',
            'password' => $password,
            'fname' => 'Temizlik',
            'lname' => 'Personeli',
            'title' => 'Görevli'
        ]);
        echo "Created user: temizlik\n";
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    try {
        $stmtUser->execute([
            'role_id' => $envanterRoleId,
            'username' => 'envanter',
            'email' => 'envanter@belediye.gov.tr',
            'password' => $password,
            'fname' => 'Envanter',
            'lname' => 'Yöneticisi',
            'title' => 'Yönetici'
        ]);
        echo "Created user: envanter\n";
    } catch (Exception $e) { echo $e->getMessage() . "\n"; }

    echo "Done.\n";
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage();
}
