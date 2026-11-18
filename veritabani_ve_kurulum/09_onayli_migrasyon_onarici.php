<?php
/**
 * Veritabanı Migrasyon Scripti
 * ============================
 * Bu dosyayı sunucuya yükleyin ve tarayıcıdan çalıştırın:
 *   https://site-adresiniz.com/migrate_fix.php?confirm=evet
 * 
 * İşlem tamamlandıktan sonra GÜVENLİK İÇİN bu dosyayı sunucudan SİLİN!
 */

// Güvenlik: Basit doğrulama
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'evet') {
    echo '<div style="font-family: Inter, sans-serif; max-width: 600px; margin: 80px auto; padding: 30px; background: #FFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); text-align: center; border-top: 4px solid #F59E0B;">';
    echo '<h3 style="color: #0F172A;">Veritabani Migrasyon Scripti</h3>';
    echo '<p style="color: #64748B;">Bu script eksik tablolari olusturur ve mevcut tablolari gunceller.</p>';
    echo '<p style="color: #DC2626; font-weight: 600;">Islem tamamlandiktan sonra bu dosyayi sunucudan silin!</p>';
    echo '<a href="?confirm=evet" style="display: inline-block; padding: 12px 24px; background: #2563EB; color: #FFF; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 10px;">Migrasyonu Calistir</a>';
    echo '</div>';
    exit;
}

// Autoloader ve App init
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) require_once $file;
    });
    $helpersFile = __DIR__ . '/app/Helpers/functions.php';
    if (file_exists($helpersFile)) require_once $helpersFile;
}

\App\Config\App::init();

$db = \App\Config\Database::getConnection();
if (!$db) {
    die('<p style="color:red; font-family:sans-serif;">Veritabani baglantisi kurulamadi!</p>');
}

$results = [];

// 1. stock_counts tablosu
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS stock_counts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            count_no VARCHAR(100) UNIQUE NOT NULL,
            counted_by INT NOT NULL,
            approved_by INT,
            status VARCHAR(50) DEFAULT 'pending_approval',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            approved_at DATETIME,
            INDEX idx_stock_counts_counted_by (counted_by),
            INDEX idx_stock_counts_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $results[] = ['OK', 'stock_counts tablosu olusturuldu/kontrol edildi'];
} catch (Exception $e) {
    $results[] = ['HATA', 'stock_counts: ' . $e->getMessage()];
}

// 2. stock_count_items tablosu
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS stock_count_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stock_count_id INT NOT NULL,
            product_id INT NOT NULL,
            system_stock DECIMAL(10,2) NOT NULL,
            physical_stock DECIMAL(10,2) NOT NULL,
            difference DECIMAL(10,2) NOT NULL,
            reason TEXT,
            INDEX idx_count_items_count (stock_count_id),
            INDEX idx_count_items_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $results[] = ['OK', 'stock_count_items tablosu olusturuldu/kontrol edildi'];
} catch (Exception $e) {
    $results[] = ['HATA', 'stock_count_items: ' . $e->getMessage()];
}

// 3. audit_logs tablosuna eksik kolonlari ekle
try {
    $columns = array_column(
        $db->query('SHOW COLUMNS FROM audit_logs')->fetchAll(PDO::FETCH_ASSOC),
        'Field'
    );

    $alterations = [];
    if (!in_array('entity_type', $columns)) $alterations[] = 'ADD COLUMN entity_type VARCHAR(100) NULL AFTER action';
    if (!in_array('entity_id', $columns)) $alterations[] = 'ADD COLUMN entity_id INT NULL AFTER entity_type';
    if (!in_array('old_values', $columns)) $alterations[] = 'ADD COLUMN old_values JSON NULL';
    if (!in_array('new_values', $columns)) $alterations[] = 'ADD COLUMN new_values JSON NULL';
    if (!in_array('module', $columns)) $alterations[] = 'ADD COLUMN module VARCHAR(100) NULL';
    if (!in_array('description', $columns)) $alterations[] = 'ADD COLUMN description TEXT NULL';
    if (!in_array('ip_address', $columns)) $alterations[] = 'ADD COLUMN ip_address VARCHAR(45) NULL';
    if (!in_array('user_agent', $columns)) $alterations[] = 'ADD COLUMN user_agent TEXT NULL';

    if (!empty($alterations)) {
        $sql = 'ALTER TABLE audit_logs ' . implode(', ', $alterations);
        $db->exec($sql);
        $results[] = ['OK', 'audit_logs tablosuna ' . count($alterations) . ' kolon eklendi'];
    } else {
        $results[] = ['BILGI', 'audit_logs tablosu zaten guncel'];
    }
} catch (Exception $e) {
    $results[] = ['HATA', 'audit_logs: ' . $e->getMessage()];
}

// Sonuclari goster
echo '<div style="font-family: Inter, sans-serif; max-width: 700px; margin: 40px auto; padding: 30px; background: #FFF; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-top: 4px solid #10B981;">';
echo '<h3 style="color: #0F172A; margin-bottom: 20px;">Migrasyon Sonuclari</h3>';
echo '<table style="width: 100%; border-collapse: collapse;">';
foreach ($results as $r) {
    $color = $r[0] === 'HATA' ? '#DC2626' : ($r[0] === 'OK' ? '#10B981' : '#3B82F6');
    echo '<tr style="border-bottom: 1px solid #E2E8F0;">';
    echo '<td style="padding: 10px; width: 60px; font-weight: bold; color: ' . $color . ';">' . $r[0] . '</td>';
    echo '<td style="padding: 10px; color: #334155;">' . htmlspecialchars($r[1]) . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '<p style="margin-top: 20px; padding: 12px; background: #FEF3C7; border-radius: 8px; color: #92400E; font-weight: 500;">Bu dosyayi sunucudan silmeyi unutmayin!</p>';
echo '</div>';
