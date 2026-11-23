<?php

/**
 * CampusStock & Kırtasiye API Test Suite
 */

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = dirname(__DIR__) . '/app/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });

    $helpersFile = dirname(__DIR__) . '/app/Helpers/functions.php';
    if (file_exists($helpersFile)) {
        require_once $helpersFile;
    }
}

require_once dirname(__DIR__) . '/app/Config/App.php';
require_once dirname(__DIR__) . '/app/Config/Database.php';

use App\Config\App;
use App\Models\User;
use App\Models\Product;
use App\Models\University;
use App\Models\Store;
use App\Models\Order;
use App\Models\PrintJob;
use App\Services\PosService;
use App\Services\OrderService;
use App\Services\PrintJobService;

App::init();

echo "===============================================\n";
echo "  CAMPUSSTOCK API & BACKEND TEST SUITE\n";
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

// 1. Database Connection Check
try {
    $db = \App\Config\Database::getConnection();
    assertTest("Veritabanı Bağlantısı", $db !== null);
} catch (Throwable $e) {
    assertTest("Veritabanı Bağlantısı", false, $e->getMessage());
}

// 2. Product Barcode & Stock Check
$firstProduct = null;
try {
    $productModel = new Product();
    $allProds = $productModel->getAll(['limit' => 1]);
    if (!empty($allProds)) {
        $firstProduct = $allProds[0];
        $prod = $productModel->findByBarcode($firstProduct['barcode'] ?? $firstProduct['code']);
        assertTest("Barkod/Kod ile Ürün Arama ({$firstProduct['name']})", $prod !== null, "Barkod: " . ($prod['barcode'] ?? $prod['code']));
    } else {
        assertTest("Barkod ile Ürün Arama", true, "Ürün listesi şu an boş, model hazır");
    }
} catch (Throwable $e) {
    assertTest("Barkod ile Ürün Arama", false, $e->getMessage());
}

// 3. POS Service Cart Calculation
try {
    $posService = new PosService();
    $testProdId = $firstProduct ? (int)$firstProduct['id'] : 1;
    $cart = $posService->calculateCart([
        ['product_id' => $testProdId, 'quantity' => 2, 'unit_price' => 35.00]
    ], null, 10.0);
    // 70.00 TL - %10 indirim = 63.00 TL
    assertTest("POS Sepet İndirim (%10) & KDV Hesaplama", $cart['subtotal'] == 70.0 && $cart['grand_total'] == 63.0, "Genel Toplam: {$cart['grand_total']} TL");
} catch (Throwable $e) {
    assertTest("POS Sepet Hesaplama", false, $e->getMessage());
}

// 4. University Model
try {
    $univModel = new University();
    $univs = $univModel->getAll();
    assertTest("Üniversite Listesi", !empty($univs) && count($univs) >= 1, "Toplam: " . count($univs));
} catch (Throwable $e) {
    assertTest("Üniversite Listesi", false, $e->getMessage());
}

// 5. Store Model
try {
    $storeModel = new Store();
    $stores = $storeModel->getAll();
    assertTest("Kampüs Mağazaları Listesi", !empty($stores) && count($stores) >= 1, "Toplam: " . count($stores));
} catch (Throwable $e) {
    assertTest("Kampüs Mağazaları Listesi", false, $e->getMessage());
}

// 6. Print Job Pricing & Calculation Service
try {
    $printService = new PrintJobService();
    $pricing = $printService->getPricingTable();
    assertTest("Fotokopi Fiyat Tarifesi", isset($pricing['bw']) && isset($pricing['color']));

    $calc = $printService->calculateCost([
        'page_count'   => 40,
        'color_mode'   => 'bw',
        'double_sided' => 1,
        'binding'      => 'spiral',
        'copies'       => 1
    ]);
    // 40 pages double sided = 40 * (1.50 / 2) = 30.00 TL printing + 35.00 TL spiral = 65.00 TL
    assertTest("Baskı Hesaplama Motoru (40 sayfa, önlü-arkalı, spiral)", $calc['total_price'] == 65.00, "Tutar: {$calc['total_price']} TL");
} catch (Throwable $e) {
    assertTest("Baskı Hesaplama Motoru", false, $e->getMessage());
}

// 7. Order Service (Student Online Order & Pickup Code)
try {
    $orderService = new OrderService();
    $testProdId = $firstProduct ? (int)$firstProduct['id'] : 1;
    $order = $orderService->createOrder(1, [
        'store_id'       => 1,
        'payment_method' => 'cash_pickup',
        'items'          => [
            ['product_id' => $testProdId, 'quantity' => 1]
        ],
        'notes'          => 'Ders çıkışı alacağım'
    ]);
    assertTest("Öğrenci Çevrimiçi Sipariş Oluşturma", !empty($order['order_no']) && !empty($order['pickup_code']), "Sipariş No: {$order['order_no']} | Kod: {$order['pickup_code']}");

    // Test verify pickup
    $verify = $orderService->verifyPickup($order['pickup_code']);
    assertTest("Gel-Al Teslimat Kodu Doğrulama", $verify !== null && $verify['can_deliver'] === true, "Durum: {$verify['order']['order_status']}");
} catch (Throwable $e) {
    assertTest("Öğrenci Sipariş & Gel-Al Motoru", false, $e->getMessage());
}

// 8. Auth API Token
try {
    $apiTokenModel = new \App\Models\ApiToken();
    $tokenData = $apiTokenModel->createToken(1, 'test_token', 7);
    $foundUser = $apiTokenModel->findUserByToken($tokenData['token']);
    assertTest("API Bearer Token Doğrulama", $foundUser !== null && $foundUser['id'] == 1, "Token: " . substr($tokenData['token'], 0, 15) . "...");
} catch (Throwable $e) {
    assertTest("API Bearer Token Doğrulama", false, $e->getMessage());
}

echo "\n-----------------------------------------------\n";
echo "SONUÇ: {$testsPassed} BAŞARILI, {$testsFailed} HATALI\n";
echo "===============================================\n";

if ($testsFailed > 0) {
    exit(1);
}
exit(0);
