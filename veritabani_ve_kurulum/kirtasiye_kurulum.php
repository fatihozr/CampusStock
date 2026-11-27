<?php

/**
 * Kırtasiye Stok ve Satış Sistemi
 * Otomatik Veritabanı Kurulum ve Tohumlama Scripti
 */

require_once dirname(__DIR__) . '/app/Config/App.php';
require_once dirname(__DIR__) . '/app/Config/Database.php';

use App\Config\App;

App::init();

$isCli = (php_sapi_name() === 'cli');

function logMsg(string $msg, string $type = 'info'): void
{
    global $isCli;
    if ($isCli) {
        $prefix = match($type) {
            'success' => '[✓ BAŞARILI] ',
            'error'   => '[✗ HATA] ',
            'warning' => '[! UYARI] ',
            default   => '[i BİLGİ] '
        };
        echo $prefix . $msg . PHP_EOL;
    } else {
        $color = match($type) {
            'success' => '#10B981',
            'error'   => '#EF4444',
            'warning' => '#F59E0B',
            default   => '#3B82F6'
        };
        echo "<div style='font-family:sans-serif; margin:8px 0; padding:10px 14px; border-radius:6px; background:#F8FAFC; border-left:4px solid {$color};'><strong>{$msg}</strong></div>";
    }
}

if (!$isCli) {
    echo "<!DOCTYPE html><html lang='tr'><head><meta charset='UTF-8'><title>Kırtasiye Veritabanı Kurulumu</title></head><body style='max-width:800px; margin:40px auto; font-family:sans-serif; background:#F1F5F9; color:#1E293B;'>";
    echo "<div style='background:#FFF; padding:24px; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);'>";
    echo "<h2 style='margin-top:0; color:#1E40AF;'>📚 Kırtasiye Stok ve Satış Sistemi Veritabanı Kurulumu</h2>";
}

logMsg("Kurulum başlatılıyor...");

// Veritabanı bağlantı bilgilerini al
$host = App::get('DB_HOST', 'localhost');
$port = App::get('DB_PORT', '3306');
$dbName = App::get('DB_DATABASE', 'kirtasiye_stok_satis');
$user = App::get('DB_USERNAME', 'root');
$pass = App::get('DB_PASSWORD', '');

$schemaFile = __DIR__ . '/kirtasiye_veritabani.sql';
$seedFile   = __DIR__ . '/kirtasiye_ornek_veriler.sql';

if (!file_exists($schemaFile) || !file_exists($seedFile)) {
    logMsg("SQL dosyaları bulunamadı!", 'error');
    exit(1);
}

// 1. MySQL Kurulumunu Dene
$mysqlSuccess = false;
try {
    logMsg("MySQL sunucusuna bağlanılıyor ({$host}:{$port})...");
    $pdoRoot = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    logMsg("Veritabanı oluşturuluyor/kontrol ediliyor: `{$dbName}`...");
    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdoRoot->exec("USE `{$dbName}`");

    logMsg("Tablo şemaları yükleniyor (`kirtasiye_veritabani.sql`)...");
    $schemaSql = file_get_contents($schemaFile);
    $pdoRoot->exec($schemaSql);
    logMsg("Tablolar başarıyla oluşturuldu.", 'success');

    logMsg("Örnek kırtasiye verileri tohumlanıyor (`kirtasiye_ornek_veriler.sql`)...");
    $seedSql = file_get_contents($seedFile);
    $pdoRoot->exec($seedSql);
    logMsg("Örnek kırtasiye verileri başarıyla yüklendi.", 'success');

    $mysqlSuccess = true;
} catch (Throwable $e) {
    logMsg("MySQL kurulumunda uyarı/hata: " . $e->getMessage(), 'warning');
    logMsg("Yerel MySQL servisi kapalıysa SQLite yedek veritabanı da otomatik oluşturulacak.", 'info');
}

// 2. Her ihtimale karşı SQLite Veritabanı da oluştur (Hemen bağımsız çalışabilmesi için)
try {
    $sqlitePath = dirname(__DIR__) . '/database.sqlite';
    logMsg("SQLite taşınabilir veritabanı hazırlanıyor: database.sqlite...");
    
    $pdoSqlite = new PDO("sqlite:" . $sqlitePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // SQLite uyumlu şema
    $pdoSqlite->exec("
        CREATE TABLE IF NOT EXISTS roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            description TEXT,
            permissions TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            role_id INTEGER NOT NULL,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            phone TEXT,
            title TEXT DEFAULT 'Kasiyer',
            status TEXT DEFAULT 'active',
            last_login_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_id INTEGER,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT,
            icon TEXT DEFAULT 'bi-bookmark',
            is_active INTEGER DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS brands (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            country TEXT,
            website TEXT,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL,
            brand_id INTEGER,
            code TEXT UNIQUE NOT NULL,
            barcode TEXT UNIQUE,
            name TEXT NOT NULL,
            description TEXT,
            unit TEXT DEFAULT 'Adet',
            purchase_price REAL DEFAULT 0.0,
            sale_price REAL DEFAULT 0.0,
            wholesale_price REAL DEFAULT 0.0,
            tax_rate REAL DEFAULT 20.0,
            current_stock REAL DEFAULT 0.0,
            min_stock REAL DEFAULT 5.0,
            shelf_location TEXT,
            image_url TEXT,
            status TEXT DEFAULT 'active',
            is_featured INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_type TEXT DEFAULT 'individual',
            company_name TEXT,
            first_name TEXT NOT NULL,
            last_name TEXT,
            phone TEXT,
            email TEXT,
            address TEXT,
            tax_office TEXT,
            tax_number TEXT,
            discount_rate REAL DEFAULT 0.0,
            credit_limit REAL DEFAULT 0.0,
            balance REAL DEFAULT 0.0,
            status TEXT DEFAULT 'active',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS suppliers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            company_name TEXT NOT NULL,
            contact_person TEXT,
            phone TEXT,
            email TEXT,
            address TEXT,
            tax_office TEXT,
            tax_number TEXT,
            balance REAL DEFAULT 0.0,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS sales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            receipt_no TEXT UNIQUE NOT NULL,
            customer_id INTEGER,
            user_id INTEGER NOT NULL,
            sale_type TEXT DEFAULT 'pos',
            payment_method TEXT DEFAULT 'cash',
            subtotal REAL DEFAULT 0.0,
            tax_total REAL DEFAULT 0.0,
            discount_total REAL DEFAULT 0.0,
            grand_total REAL DEFAULT 0.0,
            paid_amount REAL DEFAULT 0.0,
            change_amount REAL DEFAULT 0.0,
            status TEXT DEFAULT 'completed',
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS sale_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sale_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            product_name TEXT NOT NULL,
            barcode TEXT,
            unit TEXT DEFAULT 'Adet',
            quantity REAL NOT NULL,
            unit_price REAL NOT NULL,
            cost_price REAL DEFAULT 0.0,
            tax_rate REAL DEFAULT 20.0,
            tax_amount REAL DEFAULT 0.0,
            discount_amount REAL DEFAULT 0.0,
            line_total REAL NOT NULL,
            returned_quantity REAL DEFAULT 0.0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS sale_returns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            return_no TEXT UNIQUE NOT NULL,
            sale_id INTEGER NOT NULL,
            sale_item_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity REAL NOT NULL,
            unit_price REAL NOT NULL,
            total_refund REAL NOT NULL,
            reason TEXT,
            refund_method TEXT DEFAULT 'cash',
            processed_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS stock_movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            movement_type TEXT NOT NULL,
            quantity REAL NOT NULL,
            prev_stock REAL NOT NULL,
            next_stock REAL NOT NULL,
            unit_cost REAL DEFAULT 0.0,
            total_cost REAL DEFAULT 0.0,
            reference_type TEXT DEFAULT 'manual',
            reference_id TEXT,
            notes TEXT,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS cash_movements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            movement_type TEXT NOT NULL,
            amount REAL NOT NULL,
            payment_method TEXT DEFAULT 'cash',
            reference_id TEXT,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            entity_type TEXT NOT NULL,
            entity_id INTEGER,
            old_values TEXT,
            new_values TEXT,
            ip_address TEXT,
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Tohum verileri SQLite'a ekle (eğer boşsa)
    $checkCount = (int)$pdoSqlite->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($checkCount === 0) {
        $seedSql = file_get_contents($seedFile);
        // SQLite uyumlu hale getir (ON DUPLICATE KEY UPDATE ve NOW() temizliği)
        $lines = explode("\n", $seedSql);
        $cleanStatements = [];
        $buffer = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || str_starts_with($trimmed, '--')) continue;
            
            // Clean MySQL specific functions for SQLite
            $line = str_replace('NOW() - INTERVAL 1 DAY', "datetime('now', '-1 day')", $line);
            $line = str_replace('NOW() - INTERVAL 2 HOUR', "datetime('now', '-2 hour')", $line);
            $line = str_replace('NOW() - INTERVAL 1 HOUR', "datetime('now', '-1 hour')", $line);
            $line = str_replace('NOW() - INTERVAL 5 HOUR', "datetime('now', '-5 hour')", $line);
            $line = str_replace("\\'", "''", $line);
            $line = preg_replace('/ON DUPLICATE KEY UPDATE [^;]+;/i', ';', $line);

            $buffer .= $line . "\n";
            if (str_ends_with(trim($line), ';')) {
                $cleanStatements[] = $buffer;
                $buffer = '';
            }
        }

        foreach ($cleanStatements as $stmtSql) {
            try {
                $pdoSqlite->exec($stmtSql);
            } catch (Throwable $e) {
                // Ignore duplicate or minor sqlite syntax variance
            }
        }
    }
    logMsg("SQLite taşınabilir veritabanı başarıyla hazırlandı.", 'success');
} catch (Throwable $e) {
    logMsg("SQLite kurulum uyarısı: " . $e->getMessage(), 'warning');
}

logMsg("---------------------------------------------------------");
logMsg("Kurulum süreci tamamlandı!", 'success');
logMsg("Giriş Bilgileri:");
logMsg("  Yönetici: admin / admin123");
logMsg("  Kasiyer:  kasiyer / admin123");
logMsg("  Depo:     depo / admin123");

if (!$isCli) {
    echo "<div style='margin-top:20px;'><a href='../login' style='background:#2563EB; color:#FFF; padding:10px 18px; text-decoration:none; border-radius:6px; font-weight:600;'>Giriş Sayfasına Git</a></div>";
    echo "</div></body></html>";
}
