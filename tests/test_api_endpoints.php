<?php

/**
 * CampusStock Kırtasiye API & Mock Veri Test Süiti
 * (CampusStockDb.drawio sınırlarıyla %100 uyumlu)
 */

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = dirname(__DIR__) . '/app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
        if (file_exists($file)) require_once $file;
    });

    $helpersFile = dirname(__DIR__) . '/app/Helpers/functions.php';
    if (file_exists($helpersFile)) require_once $helpersFile;
}

use App\Models\Product;
use App\Models\Category;
use App\Models\University;
use App\Models\Store;
use App\Models\Sale;
use App\Models\User;
use App\Services\PosService;
use App\Services\OrderService;
use App\Services\MockDataService;

MockDataService::reset();

echo "===============================================\n";
echo "  CAMPUSSTOCK KIRTASİYE MOCK TEST SUITE\n";
echo "===============================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $name, bool $condition, ?string $extra = null): void
{
    global $testsPassed, $testsFailed;
    if ($condition) {
        $testsPassed++;
        echo " [✓ PASS] {$name}" . ($extra ? " ({$extra})" : "") . "\n";
    } else {
        $testsFailed++;
        echo " [✗ FAIL] {$name}" . ($extra ? " - {$extra}" : "") . "\n";
    }
}

// 1. Mock Data & Ürünler
$productModel = new Product();
$allProds = $productModel->getAll();
assertTest("Kırtasiye Mock Ürün Listesi", count($allProds) === 10, "Toplam: " . count($allProds));

// 2. Barkod Arama
$prod = $productModel->findByBarcode('8690846001015');
assertTest("Barkod ile Ürün Arama (8690846001015)", $prod !== null && str_contains($prod['name'], 'Faber-Castell'), $prod['name'] ?? '');

// 3. Kategoriler
$catModel = new Category();
$cats = $catModel->getAll();
assertTest("Kırtasiye Kategorileri", count($cats) === 5, "Toplam: " . count($cats));

// 4. POS Sepet & Checkout
$posService = new PosService();
$cart = $posService->calculateCart([['product_id' => 1, 'quantity' => 2, 'unit_price' => 100.00]], null, 10.0);
assertTest("POS Sepet Hesaplama (%10 İndirim)", $cart['subtotal'] == 200.0 && $cart['grand_total'] == 180.0, "Toplam: {$cart['grand_total']} TL");

// 5. Üniversite & Kırtasiye Şubeleri
$univs = (new University())->getAll();
$stores = (new Store())->getAll();
assertTest("Üniversite & Kırtasiye Şubeleri", count($univs) === 3 && count($stores) === 3, count($univs) . " Üniversite / " . count($stores) . " Şube");

// 6. Kullanıcılar & Roller
$userModel = new User();
$admin = $userModel->findByUsernameOrEmail('admin');
assertTest("Admin Kullanıcı Doğrulama", $admin !== null && $userModel->verifyPassword('admin123', $admin['password_hash']));

// 7. İadeler
$returns = MockDataService::all('returns');
assertTest("Mock İade Kayıtları", count($returns) >= 1, "Toplam: " . count($returns));

// 8. Siparişler / Satışlar
$sales = (new Sale())->getAll();
assertTest("Mock Sipariş & Satış Kayıtları", count($sales) >= 3, "Toplam: " . count($sales));

echo "\n-----------------------------------------------\n";
echo "SONUÇ: {$testsPassed} BAŞARILI, {$testsFailed} HATALI\n";
echo "===============================================\n";

exit($testsFailed > 0 ? 1 : 0);
