<?php

/**
 * CampusStock Kırtasiye API, Güvenlik & Mock Veri Test Süiti
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

use App\Helpers\Csrf;
use App\Models\Product;
use App\Models\Category;
use App\Models\University;
use App\Models\Store;
use App\Models\Sale;
use App\Models\User;
use App\Services\PosService;
use App\Services\MockDataService;

MockDataService::reset();

echo "===============================================\n";
echo "  CAMPUSSTOCK KIRTASİYE KAPSAMLI TEST SUITE\n";
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

// 1. Mock Data & Ürünler (Read)
$productModel = new Product();
$allProds = $productModel->getAll();
assertTest("Kırtasiye Mock Ürün Listesi", count($allProds) === 10, "Toplam: " . count($allProds));

// 2. Barkod Arama & Olmayan Barkod Hata Kontrolü
$prod = $productModel->findByBarcode('8690846001015');
assertTest("Barkod ile Ürün Arama (8690846001015)", $prod !== null && str_contains($prod['name'], 'Faber-Castell'), $prod['name'] ?? '');
assertTest("Geçersiz Barkod Kontrolü (Null Dönüşü)", $productModel->findByBarcode('0000000000000') === null);

// 3. Ürün CRUD (Create -> Update -> Stock Adjust -> Delete)
$newProdId = $productModel->create(['name' => 'Test Kalem', 'category_id' => 1, 'sale_price' => 50, 'current_stock' => 10]);
$updated = $productModel->update($newProdId, ['sale_price' => 60]);
$foundNew = $productModel->find($newProdId);
assertTest("Ürün CRUD (Ekleme & Güncelleme)", $updated && (float)$foundNew['sale_price'] === 60.0);
$productModel->delete($newProdId, false);
assertTest("Ürün CRUD (Silme)", $productModel->find($newProdId) === null);

// 4. Yetersiz Stok Hata Yönetimi (Negative Case)
$insufficientCaught = false;
try {
    $productModel->adjustStock(1, -9999, 'sale');
} catch (Throwable $e) {
    $insufficientCaught = str_contains($e->getMessage(), 'Yetersiz stok');
}
assertTest("Yetersiz Stok Hata Yönetimi", $insufficientCaught);

// 5. Kategoriler
$cats = (new Category())->getAll();
assertTest("Kırtasiye Kategorileri", count($cats) === 5, "Toplam: " . count($cats));

// 6. POS Sepet & Boş Sepet Hata Kontrolü
$posService = new PosService();
$cart = $posService->calculateCart([['product_id' => 1, 'quantity' => 2, 'unit_price' => 100.00]], null, 10.0);
assertTest("POS Sepet Hesaplama (%10 İndirim)", $cart['subtotal'] == 200.0 && $cart['grand_total'] == 180.0, "Toplam: {$cart['grand_total']} TL");

$emptyCartCaught = false;
try {
    $posService->checkout(['items' => []], 1);
} catch (Throwable $e) {
    $emptyCartCaught = true;
}
assertTest("Boş Sepet Checkout Engelleme", $emptyCartCaught);

// 7. Üniversite & Kırtasiye Şubeleri
$univs = (new University())->getAll();
$stores = (new Store())->getAll();
assertTest("Üniversite & Kırtasiye Şubeleri", count($univs) === 3 && count($stores) === 3, count($univs) . " Üniversite / " . count($stores) . " Şube");

// 8. Kimlik Doğrulama & Parola Hash Kontrolü
$userModel = new User();
$admin = $userModel->findByUsernameOrEmail('admin');
assertTest("Admin Doğru Şifre Doğrulama", $admin !== null && $userModel->verifyPassword('admin123', $admin['password_hash']));
assertTest("Admin Yanlış Şifre Reddi (123456 dahil)", $admin !== null && !$userModel->verifyPassword('123456', $admin['password_hash']));

// 9. CSRF Token Doğrulama
$csrfToken = Csrf::token();
assertTest("CSRF Token Üretme & Doğrulama", Csrf::verify($csrfToken) && !Csrf::verify('gecersiz-token'));

// 10. İadeler & Siparişler
$returns = MockDataService::all('returns');
$sales = (new Sale())->getAll();
assertTest("Mock İade & Sipariş Kayıtları", count($returns) >= 1 && count($sales) >= 3);

echo "\n-----------------------------------------------\n";
echo "SONUÇ: {$testsPassed} BAŞARILI, {$testsFailed} HATALI\n";
echo "===============================================\n";

exit($testsFailed > 0 ? 1 : 0);
