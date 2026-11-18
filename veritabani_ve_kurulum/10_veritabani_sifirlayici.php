<?php
/**
 * Veritabanı Temizlik Aracı (Production'a Geçiş İçin)
 * UYARI: Bu betik sadece test aşamasındaki hareket verilerini temizler.
 */

// Sadece komut satırından çalıştırılabilsin (güvenlik)
if (php_sapi_name() !== 'cli') {
    die("Bu betik sadece komut satırından (CLI) calistirilabilir.");
}

require_once __DIR__ . '/app/Config/App.php';
require_once __DIR__ . '/app/Config/Database.php';

\App\Config\App::init();
$db = \App\Config\Database::getConnection();

if (!$db) {
    die("Veritabanı baglantisi kurulamadi!\n");
}

echo "DİKKAT: Bu islem veritabanindaki tum STOK HAREKETLERİNİ, TALEPLERİ, SAYIMLARI ve LOGLARI silecek!\n";
echo "Devam etmek istiyor musunuz? (evet/hayir): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));

if (strtolower($line) !== 'evet') {
    die("Islem iptal edildi.\n");
}

try {
    $db->beginTransaction();

    // 1. İşlem Kayıtlarını ve Günlükleri (Logları) Sil
    $db->exec("DELETE FROM audit_logs");
    $db->exec("DELETE FROM stock_movements");
    
    // 2. Sayım Verilerini Sil
    $db->exec("DELETE FROM stock_count_items");
    $db->exec("DELETE FROM stock_counts");

    // 3. Talep Verilerini Sil
    $db->exec("DELETE FROM stock_request_items");
    $db->exec("DELETE FROM stock_requests");

    // 4. Teslimat ve Satın Alma Verilerini Sil
    $db->exec("DELETE FROM delivery_items");
    $db->exec("DELETE FROM deliveries");
    $db->exec("DELETE FROM procurement_items");
    $db->exec("DELETE FROM procurements");

    // 5. Ürünlerin (Stok Kalemlerinin) Mevcut Miktarını Sıfırla (Master Datayı tutarız, miktarını sıfırlarız)
    $db->exec("UPDATE products SET current_stock = 0");

    $tablesToReset = [
        'audit_logs', 'stock_movements', 'stock_count_items', 'stock_counts',
        'stock_request_items', 'stock_requests', 'delivery_items', 'deliveries',
        'procurement_items', 'procurements'
    ];

    $db->commit();

    // MySQL DDL komutları implicit commit yaptığı için sayaçları transaction sonrasında sıfırla.
    foreach ($tablesToReset as $table) {
        $db->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
    }

    echo "\nBASARILI: Test verileri tamamen temizlendi. Stok mevcutlari 0 (sifir) olarak guncellendi.\n";
    echo "Kullanicilar, Roller, Departmanlar ve Urun Tanimlamalari korundu.\n";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "\nHATA OLUSTU: " . $e->getMessage() . "\n";
}
