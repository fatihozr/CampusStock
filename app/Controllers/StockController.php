<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\StockMovement;
use Exception;

class StockController
{
    private Product $productModel;
    private Category $categoryModel;
    private Brand $brandModel;
    private StockMovement $movementModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->brandModel = new Brand();
        $this->movementModel = new StockMovement();
    }

    public function index(): void
    {
        Auth::requireAuth();

        $search    = trim($_GET['search'] ?? '');
        $category  = trim($_GET['category'] ?? '');
        $statusTab = trim($_GET['tab'] ?? 'all');

        $allProducts = $this->productModel->getAll();
        $totalProductCount = count($allProducts);
        $outOfStockCount = count(array_filter($allProducts, fn($p) => (float)$p['current_stock'] <= 0));
        $criticalCount = count(array_filter($allProducts, fn($p) => (float)$p['current_stock'] <= (float)$p['min_stock']));

        $filters = [];
        if ($search !== '') $filters['search'] = $search;
        if ($category !== '') $filters['category_id'] = (int)$category;
        if ($statusTab === 'critical') $filters['low_stock'] = true;

        $products = $this->productModel->getAll($filters);
        $categories = $this->categoryModel->getAll();

        view('stock.index', [
            'pageTitle'         => 'Kırtasiye Ürünleri & Stok',
            'currentUser'       => Auth::user(),
            'products'          => $products,
            'categories'        => $categories,
            'departments'       => [],
            'search'            => $search,
            'selectedCategory'  => $category,
            'statusTab'         => $statusTab,
            'totalProductCount' => $totalProductCount,
            'outOfStockCount'   => $outOfStockCount,
            'criticalCount'     => $criticalCount,
            'flashSuccess'      => $_SESSION['flash_success'] ?? null,
            'flashError'        => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function create(): void
    {
        Auth::requireAuth();

        view('stock.create', [
            'pageTitle'   => 'Yeni Kırtasiye Ürünü Ekle',
            'currentUser' => Auth::user(),
            'categories'  => $this->categoryModel->getAll(),
            'brands'      => $this->brandModel->getAll(),
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        unset($_SESSION['flash_error']);
    }

    public function store(): void
    {
        Auth::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products');
            return;
        }

        try {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                throw new Exception("Ürün adı zorunludur.");
            }

            $this->productModel->create([
                'name'           => $name,
                'category_id'    => (int)($_POST['category_id'] ?? 1),
                'brand_id'       => (int)($_POST['brand_id'] ?? 1),
                'barcode'        => trim($_POST['barcode'] ?? ''),
                'code'           => trim($_POST['code'] ?? ''),
                'unit'           => trim($_POST['unit'] ?? 'Adet'),
                'purchase_price' => (float)($_POST['purchase_price'] ?? $_POST['unit_price'] ?? 0),
                'sale_price'     => (float)($_POST['sale_price'] ?? $_POST['unit_price'] ?? 0),
                'current_stock'  => (float)($_POST['current_stock'] ?? $_POST['quantity'] ?? 0),
                'min_stock'      => (float)($_POST['min_stock'] ?? 5),
                'shelf_location' => trim($_POST['shelf_location'] ?? 'Reyon A-1'),
                'description'    => trim($_POST['description'] ?? '')
            ]);

            $_SESSION['flash_success'] = "Kırtasiye ürünü '{$name}' başarıyla eklendi.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/products');
    }

    public function detail(): void
    {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->find($id);

        if (!$product) {
            redirect('/products');
            return;
        }

        $movements = $this->movementModel->getAll(['product_id' => $id]);

        view('stock.detail', [
            'pageTitle'    => $product['name'],
            'currentUser'  => Auth::user(),
            'product'      => $product,
            'categories'   => $this->categoryModel->getAll(),
            'brands'       => $this->brandModel->getAll(),
            'movements'    => $movements,
            'departments'  => [],
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function update(): void
    {
        Auth::requireAuth();
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->productModel->update($id, [
                'name'           => trim($_POST['name'] ?? ''),
                'category_id'    => (int)($_POST['category_id'] ?? 1),
                'barcode'        => trim($_POST['barcode'] ?? ''),
                'unit'           => trim($_POST['unit'] ?? 'Adet'),
                'purchase_price' => (float)($_POST['purchase_price'] ?? 0),
                'sale_price'     => (float)($_POST['sale_price'] ?? $_POST['unit_price'] ?? 0),
                'min_stock'      => (float)($_POST['min_stock'] ?? 5),
                'shelf_location' => trim($_POST['shelf_location'] ?? ''),
                'description'    => trim($_POST['description'] ?? '')
            ]);
            $_SESSION['flash_success'] = "Ürün bilgileri güncellendi.";
        }

        redirect('/products/detail?id=' . $id);
    }

    public function delete(): void
    {
        Auth::requireAuth();
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            $this->productModel->delete($id, false);
            $_SESSION['flash_success'] = "Ürün sistemden silindi.";
        }
        redirect('/products');
    }

    public function stockIn(): void
    {
        Auth::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products');
            return;
        }

        try {
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity  = (float)($_POST['quantity'] ?? 0);
            $notes     = trim($_POST['notes'] ?? $_POST['description'] ?? 'Stok Girişi');

            if ($quantity <= 0) {
                throw new Exception("Miktar 0'dan büyük olmalıdır.");
            }

            $this->productModel->adjustStock($productId, $quantity, 'purchase', 'manual', null, $notes, Auth::user()['id'] ?? 1);
            $_SESSION['flash_success'] = "Stok girişi başarıyla kaydedildi.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect($_SERVER['HTTP_REFERER'] ?? '/products');
    }

    public function stockOut(): void
    {
        Auth::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/products');
            return;
        }

        try {
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity  = (float)($_POST['quantity'] ?? 0);
            $notes     = trim($_POST['notes'] ?? $_POST['description'] ?? 'Stok Çıkışı');

            if ($quantity <= 0) {
                throw new Exception("Miktar 0'dan büyük olmalıdır.");
            }

            $this->productModel->adjustStock($productId, -$quantity, 'sale', 'manual', null, $notes, Auth::user()['id'] ?? 1);
            $_SESSION['flash_success'] = "Stok çıkışı başarıyla kaydedildi.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect($_SERVER['HTTP_REFERER'] ?? '/products');
    }

    public function movements(): void
    {
        Auth::requireAuth();
        $movements = $this->movementModel->getAll();

        view('stock.movements', [
            'pageTitle'   => 'Kırtasiye Stok Hareketleri',
            'currentUser' => Auth::user(),
            'movements'   => $movements,
            'products'    => $this->productModel->getAll()
        ]);
    }
}
