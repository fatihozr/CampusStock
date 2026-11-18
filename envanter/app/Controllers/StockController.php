<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Warehouse;
use Exception;

class StockController
{
    private Stock $stockModel;
    private Category $categoryModel;
    private Warehouse $warehouseModel;

    public function __construct()
    {
        $this->stockModel     = new Stock();
        $this->categoryModel  = new Category();
        $this->warehouseModel = new Warehouse();
    }

    /**
     * Display Cleaning Products Catalog & Stock Management Page
     */
    public function index(): void
    {
        Auth::requireAuth();

        $search    = trim($_GET['search'] ?? '');
        $category  = trim($_GET['category'] ?? '');
        $statusTab = trim($_GET['tab'] ?? 'all');
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = 10;

        $filters = [
            'search'     => $search,
            'category'   => $category,
            'status_tab' => $statusTab
        ];

        $stockData  = $this->stockModel->getFilteredPaginated($filters, $page, $perPage);
        $categories = $this->categoryModel->getAll();
        $warehouses = $this->warehouseModel->getAll();
        $critCount  = $this->stockModel->getCriticalStockCount();

        view('stock.index', [
            'pageTitle'   => 'Temizlik Malzemeleri Ürün Kataloğu ve Stok Yönetimi',
            'currentUser' => Auth::user(),
            'stocks'      => $stockData['data'],
            'pagination'  => [
                'total_items' => $stockData['total_items'],
                'current_page'=> $stockData['current_page'],
                'total_pages' => $stockData['total_pages'],
                'per_page'    => $stockData['per_page']
            ],
            'filters'     => $filters,
            'categories'  => $categories,
            'warehouses'  => $warehouses,
            'unitChoices' => Stock::getUnitChoices(),
            'critCount'   => $critCount,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Display Single Product Detail View with History and Value Calculations
     */
    public function detail(): void
    {
        Auth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        $product = $this->stockModel->find($id);

        if (!$product) {
            $_SESSION['flash_error'] = 'İstenen temizlik malzemesi ürünü bulunamadı!';
            redirect('/stocks');
            return;
        }

        $movements  = $this->stockModel->getMovements($id);
        $categories = $this->categoryModel->getAll();
        $warehouses = $this->warehouseModel->getAll();

        view('stock.detail', [
            'pageTitle'   => 'Ürün Detayı: ' . ($product['name'] ?? ''),
            'currentUser' => Auth::user(),
            'product'     => $product,
            'movements'   => $movements,
            'categories'  => $categories,
            'warehouses'  => $warehouses,
            'unitChoices' => Stock::getUnitChoices(),
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Store new cleaning product
     */
    public function store(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        try {
            $data = $_POST;

            // Handle Product Image Upload
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $data['image_path'] = $this->stockModel->saveProductImage($_FILES['product_image']);
            }

            $product = $this->stockModel->store($data);

            // Initial stock inflow if quantity > 0 entered during product creation
            $initQty = (int)($data['quantity'] ?? 0);
            if ($initQty > 0) {
                $unitCost = (float)($data['unit_price'] ?? 0.0);
                $this->stockModel->addStockInflow((int)$product['id'], $initQty, $unitCost, 'Ürün kaydı oluşturulurken ilk stok girişi kaydedildi.');
            }

            $_SESSION['flash_success'] = "['{$product['code']}'] {$product['name']} ürünü ve stok ayarları başarıyla kaydedildi.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/stocks');
    }

    /**
     * Update existing cleaning product
     */
    public function update(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        $id = (int)($_POST['id'] ?? 0);

        try {
            $data = $_POST;

            // Handle Product Image Upload on Update
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $data['image_path'] = $this->stockModel->saveProductImage($_FILES['product_image']);
            }

            $this->stockModel->update($id, $data);
            $_SESSION['flash_success'] = 'Temizlik malzemesi ürün bilgileri başarıyla güncellendi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        $redirectUrl = $_POST['redirect_back'] ?? '/stocks';
        redirect($redirectUrl);
    }

    /**
     * POST: Transaction-Safe Stock Inflow (Stok Girişi)
     */
    public function stockIn(): void
    {
        Auth::requireAuth();

        $stockId  = (int)($_POST['stock_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $unitCost = (float)($_POST['unit_cost'] ?? 0.0);
        $notes    = trim($_POST['notes'] ?? '');

        try {
            $this->stockModel->addStockInflow($stockId, $quantity, $unitCost, $notes);
            $_SESSION['flash_success'] = 'Stok girişi başarıyla gerçekleştirildi ve stok hareketi kaydedildi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        $redirectUrl = $_POST['redirect_back'] ?? '/stocks';
        redirect($redirectUrl);
    }

    /**
     * POST: Transaction-Safe Stock Outflow (Stok Çıkışı)
     */
    public function stockOut(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        $stockId  = (int)($_POST['stock_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $notes    = trim($_POST['notes'] ?? '');

        try {
            $this->stockModel->addStockOutflow($stockId, $quantity, $notes);
            $_SESSION['flash_success'] = 'Stok çıkışı başarıyla gerçekleştirildi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        $redirectUrl = $_POST['redirect_back'] ?? '/stocks';
        redirect($redirectUrl);
    }

    /**
     * Display All Stock Movement Logs History Page
     */
    public function movements(): void
    {
        Auth::requireAuth();

        $stockId   = !empty($_GET['stock_id']) ? (int)$_GET['stock_id'] : null;
        $movements = $this->stockModel->getMovements($stockId);
        $products  = $this->stockModel->getAllItems();

        view('stock.movements', [
            'pageTitle'   => 'Stok Hareketleri ve Ambar Tarihçesi',
            'currentUser' => Auth::user(),
            'movements'   => $movements,
            'products'    => $products,
            'selectedId'  => $stockId
        ]);
    }

    /**
     * POST: Soft-Delete Product
     */
    public function delete(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        $id = (int)($_POST['id'] ?? 0);

        try {
            $this->stockModel->softDelete($id);
            $_SESSION['flash_success'] = 'Ürün pasife alındı ve çöp kutusuna taşındı.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/stocks');
    }

    /**
     * AJAX Check Product Code Duplicate API
     */
    public function ajaxCheckCode(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $code      = trim($_GET['code'] ?? '');
        $excludeId = !empty($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;

        $exists = $this->stockModel->checkDuplicateCode($code, $excludeId);
        echo json_encode(['exists' => $exists]);
        exit;
    }

    /**
     * AJAX Check Barcode Duplicate API
     */
    public function ajaxCheckBarcode(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $barcode   = trim($_GET['barcode'] ?? '');
        $excludeId = !empty($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;

        $exists = $this->stockModel->checkDuplicateBarcode($barcode, $excludeId);
        echo json_encode(['exists' => $exists]);
        exit;
    }
}
