<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Models\Department;
use App\Models\StockMovement;
use App\Models\AuditLog;
use App\Models\Personnel;
use Exception;

class StockController
{
    private Product $productModel;
    private Category $categoryModel;
    private Department $departmentModel;
    private StockMovement $movementModel;
    private Personnel $personnelModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->departmentModel = new Department();
        $this->movementModel = new StockMovement();
        $this->personnelModel = new Personnel();
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
        
        $allProducts = $this->productModel->getAll();
        $products = $allProducts;
        $totalProductCount = count($allProducts);
        $outOfStockCount = count(array_filter(
            $allProducts,
            static fn(array $product): bool => (float)($product['current_stock'] ?? 0) <= 0
        ));
        
        // Apply filters in PHP (since we fetched all active ones)
        if ($search !== '') {
            $q = mb_strtolower($search, 'UTF-8');
            $products = array_filter($products, function($p) use ($q) {
                return str_contains(mb_strtolower($p['name'], 'UTF-8'), $q) || 
                       str_contains(mb_strtolower($p['code'], 'UTF-8'), $q);
            });
        }
        
        if ($category !== '') {
            $products = array_filter($products, function($p) use ($category) {
                return $p['category_id'] == $category;
            });
        }
        
        if ($statusTab === 'critical') {
            $products = array_filter($products, function($p) {
                return $p['current_stock'] <= $p['min_stock'];
            });
        } elseif ($statusTab === 'out_of_stock') {
            $products = array_filter($products, function($p) {
                return (float)($p['current_stock'] ?? 0) <= 0;
            });
        }

        $perPage = 15;
        $totalItems = count($products);
        $totalPages = max(1, (int)ceil($totalItems / $perPage));
        $currentPage = min($totalPages, max(1, (int)($_GET['page'] ?? 1)));
        $products = array_slice(array_values($products), ($currentPage - 1) * $perPage, $perPage);

        $pagination = [
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'per_page' => $perPage,
        ];
        
        $categories = $this->categoryModel->getAll();
        $departments = $this->departmentModel->getAll();
        $critCount  = count($this->productModel->getCriticalStock());

        $unitChoices = [
            'Adet', 'Bidon', 'Koli', 'Paket', 'Rulo', 'Çift', 'Makara', 'Litre', 'Kg'
        ];

        view('stock.index', [
            'pageTitle'   => 'Kırtasiye Ürünleri & Stok Yönetimi',
            'currentUser' => Auth::user(),
            'stocks'      => $products,
            'filters'     => ['search' => $search, 'category' => $category, 'status_tab' => $statusTab],
            'categories'  => $categories,
            'departments' => $departments,
            'unitChoices' => $unitChoices,
            'critCount'   => $critCount,
            'outOfStockCount' => $outOfStockCount,
            'totalProductCount' => $totalProductCount,
            'pagination'  => $pagination,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Show Create Product Form
     */
    public function create(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        $categories = $this->categoryModel->getAll();
        
        view('stock.create', [
            'pageTitle'   => 'Yeni Malzeme Ekle',
            'currentUser' => Auth::user(),
            'categories'  => $categories,
            'unitChoices' => ['Adet', 'Bidon', 'Koli', 'Paket', 'Rulo', 'Çift', 'Makara', 'Litre', 'Kg'],
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_error']);
    }

    /**
     * Display Single Product Detail View
     */
    public function detail(): void
    {
        Auth::requireAuth();

        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);

        if (!$product) {
            $_SESSION['flash_error'] = 'Ürün bulunamadı!';
            redirect('/products');
            return;
        }
        
        $categories = $this->categoryModel->getAll();
        $departments = $this->departmentModel->getAll();
        $movements = $this->productModel->getProductMovements($id);
        $personnel = $this->personnelModel->getAll();

        view('stock.detail', [
            'pageTitle'   => 'Ürün Detayı: ' . $product['name'],
            'currentUser' => Auth::user(),
            'product'     => $product,
            'movements'   => $movements,
            'categories'  => $categories,
            'departments' => $departments,
            'personnel'   => $personnel,
            'unitChoices' => ['Adet', 'Bidon', 'Koli', 'Paket', 'Rulo', 'Çift', 'Makara', 'Litre', 'Kg'],
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

            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__, 2) . '/public/storage/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('prod_') . '.' . $ext;
                $dest = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest)) {
                    $data['image_path'] = 'storage/uploads/products/' . $filename;
                }
            }

            $product = $this->productModel->store($data);
            
            AuditLog::log('CREATE', 'products', $product['id'], null, $data);

            $_SESSION['flash_success'] = "{$product['name']} ürünü kaydedildi.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/products');
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
            $oldProduct = $this->productModel->find($id);

            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__, 2) . '/public/storage/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('prod_') . '.' . $ext;
                $dest = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest)) {
                    $data['image_path'] = 'storage/uploads/products/' . $filename;
                }
            }

            $this->productModel->update($id, $data);
            
            AuditLog::log('UPDATE', 'products', $id, $oldProduct, $data);
            
            $_SESSION['flash_success'] = 'Ürün güncellendi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        $redirectUrl = $_POST['redirect_back'] ?? '/products';
        redirect($redirectUrl);
    }

    /**
     * Stock In
     */
    public function stockIn(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('stock_in');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products');
            return;
        }

        try {
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity  = (int)($_POST['quantity'] ?? 0);
            $unitCost  = (float)($_POST['unit_cost'] ?? 0);
            $desc      = trim($_POST['description'] ?? '');
            $deliveredBy = '';
            $receivedBy  = '';

            if ($quantity <= 0) {
                throw new Exception("Miktar 0'dan büyük olmalıdır.");
            }

            $product = $this->productModel->find($productId);
            if (!$product) throw new Exception("Ürün bulunamadı.");

            // Handle optional image upload (will overwrite existing if provided)
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__, 2) . '/public/storage/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('prod_') . '.' . $ext;
                $dest = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $dest)) {
                    $newImagePath = 'storage/uploads/products/' . $filename;
                    $this->productModel->updateImage($productId, $newImagePath);
                    $product['image_path'] = $newImagePath; // Update local variable just in case
                }
            }

            // Update unit if changed by user during entry
            if (!empty($_POST['unit']) && $_POST['unit'] !== $product['unit']) {
                $this->productModel->updateUnit($productId, $_POST['unit']);
            }

            $prevStock = (int)$product['current_stock'];
            $nextStock = $prevStock + $quantity;

            $isManager = Auth::isManager();
            $status = 'pending';

            // Log movement
            $this->movementModel->logMovement([
                'product_id'   => $productId,
                'type'         => 'in',
                'status'       => $status,
                'quantity'     => $quantity,
                'prev_stock'   => $prevStock,
                'next_stock'   => $nextStock,
                'unit_cost'    => $unitCost,
                'total_cost'   => $unitCost * $quantity,
                'performed_by' => Auth::user()['id'],
                'description'  => $desc,
                'delivered_by' => $deliveredBy,
                'received_by'  => $receivedBy
            ]);

            // Update product only if approved
            if ($status === 'approved') {
                $this->productModel->updateStock($productId, $nextStock);
                $_SESSION['flash_success'] = "Stok girişi başarıyla yapıldı.";
            } else {
                $_SESSION['flash_success'] = "Stok girişi onay için yöneticiye gönderildi.";
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/products';
        redirect($redirectUrl);
    }

    /**
     * Stock Out
     */
    public function stockOut(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('stock_out');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products');
            return;
        }

        try {
            $productId    = (int)($_POST['product_id'] ?? 0);
            $departmentId = (int)($_POST['department_id'] ?? 0);
            $quantity     = (int)($_POST['quantity'] ?? 0);
            $desc         = trim($_POST['description'] ?? '');
            $deliveredBy  = '';
            $receivedBy   = '';

            if ($quantity <= 0) {
                throw new Exception("Miktar 0'dan büyük olmalıdır.");
            }
            if ($departmentId <= 0) {
                throw new Exception("Lütfen teslim alacak birimi seçiniz.");
            }

            $product = $this->productModel->find($productId);
            if (!$product) throw new Exception("Ürün bulunamadı.");
            
            // Update unit if changed by user during exit
            if (!empty($_POST['unit']) && $_POST['unit'] !== $product['unit']) {
                $this->productModel->updateUnit($productId, $_POST['unit']);
            }

            $prevStock = (int)$product['current_stock'];
            if ($prevStock < $quantity) {
                throw new Exception("Yetersiz stok! Mevcut: " . $prevStock);
            }

            $nextStock = $prevStock - $quantity;
            $unitCost  = (float)$product['unit_price'];

            $isManager = Auth::isManager();
            $status = 'pending';

            // Log movement
            $this->movementModel->logMovement([
                'product_id'    => $productId,
                'type'          => 'out',
                'status'        => $status,
                'quantity'      => $quantity,
                'prev_stock'    => $prevStock,
                'next_stock'    => $nextStock,
                'unit_cost'     => $unitCost,
                'total_cost'    => $unitCost * $quantity,
                'department_id' => $departmentId,
                'performed_by'  => Auth::user()['id'],
                'description'   => $desc,
                'delivered_by'  => $deliveredBy,
                'received_by'   => $receivedBy
            ]);

            // Update product only if approved
            if ($status === 'approved') {
                $this->productModel->updateStock($productId, $nextStock);
                $_SESSION['flash_success'] = "Stok çıkışı başarıyla yapıldı.";
            } else {
                $_SESSION['flash_success'] = "Stok çıkışı onay için yöneticiye gönderildi.";
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? '/products';
        redirect($redirectUrl);
    }

    /**
     * Display All Stock Movement Logs History Page
     */
    public function movements(): void
    {
        Auth::requireAuth();

        $movements = $this->movementModel->getRecent(100);
        
        view('stock.movements', [
            'pageTitle'   => 'Stok Hareketleri',
            'currentUser' => Auth::user(),
            'movements'   => $movements
        ]);
    }

    /**
     * Display Pending Approvals Page
     */
    public function approvals(): void
    {
        Auth::requireAuth();
        if (!Auth::isManager()) {
            $_SESSION['flash_error'] = 'Yetkisiz işlem!';
            redirect('/products');
            return;
        }

        $pendingMovements = $this->movementModel->getPendingMovements();

        view('stock.approvals', [
            'pageTitle'   => 'Onay Bekleyen İşlemler',
            'currentUser' => Auth::user(),
            'movements'   => $pendingMovements,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Approve Pending Movement
     */
    public function approveMovement(): void
    {
        Auth::requireAuth();
        if (!Auth::isManager()) {
            $_SESSION['flash_error'] = 'Yetkisiz işlem!';
            redirect('/products');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products/approvals');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $movement = $this->movementModel->find($id);

        if (!$movement || $movement['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Geçersiz işlem veya zaten onaylanmış/reddedilmiş.';
            redirect('/products/approvals');
            return;
        }

        try {
            $product = $this->productModel->find((int)$movement['product_id']);
            if (!$product) throw new Exception("Ürün bulunamadı.");

            $currentStock = (float)$product['current_stock'];
            $qty = (float)$movement['quantity'];

            if ($movement['type'] === 'in') {
                $nextStock = $currentStock + $qty;
            } else {
                if ($currentStock < $qty) {
                    throw new Exception("Yetersiz stok! Mevcut: " . $currentStock);
                }
                $nextStock = $currentStock - $qty;
            }

            // Update movement status and re-calculate prev/next
            $this->movementModel->updateStatus($id, 'approved', Auth::user()['id'], null, $currentStock, $nextStock);

            // Update product stock
            $this->productModel->updateStock($product['id'], $nextStock);

            $_SESSION['flash_success'] = 'İşlem başarıyla onaylandı.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/products/approvals');
    }

    /**
     * Reject Pending Movement
     */
    public function rejectMovement(): void
    {
        Auth::requireAuth();
        if (!Auth::isManager()) {
            $_SESSION['flash_error'] = 'Yetkisiz işlem!';
            redirect('/products');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products/approvals');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['rejection_reason'] ?? '');
        $movement = $this->movementModel->find($id);

        if (!$movement || $movement['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Geçersiz işlem veya zaten onaylanmış/reddedilmiş.';
            redirect('/products/approvals');
            return;
        }

        try {
            $this->movementModel->updateStatus($id, 'rejected', Auth::user()['id'], $reason);
            $_SESSION['flash_success'] = 'İşlem reddedildi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/products/approvals');
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
            $oldProduct = $this->productModel->find($id);
            $this->productModel->delete($id);
            
            AuditLog::log('DELETE', 'products', $id, $oldProduct, ['status' => 'passive']);
            
            $_SESSION['flash_success'] = 'Ürün pasife alındı.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/products');
    }

    /**
     * AJAX Check Code
     */
    public function ajaxCheckCode(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json; charset=utf-8');
        
        $code = trim($_GET['code'] ?? '');
        $excludeId = (int)($_GET['exclude_id'] ?? 0);
        
        if (empty($code)) {
            echo json_encode(['exists' => false]);
            exit;
        }

        $stmt = \App\Config\Database::getConnection()->prepare("SELECT id FROM products WHERE code = :code AND id != :exclude_id");
        $stmt->execute(['code' => $code, 'exclude_id' => $excludeId]);
        $exists = $stmt->fetch() !== false;

        echo json_encode(['exists' => $exists]);
        exit;
    }

    /**
     * AJAX Check Barcode
     */
    public function ajaxCheckBarcode(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json; charset=utf-8');
        
        $barcode = trim($_GET['barcode'] ?? '');
        $excludeId = (int)($_GET['exclude_id'] ?? 0);
        
        if (empty($barcode)) {
            echo json_encode(['exists' => false]);
            exit;
        }

        $stmt = \App\Config\Database::getConnection()->prepare("SELECT id FROM products WHERE barcode = :barcode AND id != :exclude_id");
        $stmt->execute(['barcode' => $barcode, 'exclude_id' => $excludeId]);
        $exists = $stmt->fetch() !== false;

        echo json_encode(['exists' => $exists]);
        exit;
    }
}

