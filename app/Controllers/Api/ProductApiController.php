<?php

namespace App\Controllers\Api;

use App\Models\Product;
use Exception;

class ProductApiController extends BaseApiController
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /**
     * GET /api/v1/products
     * Filter by search (q), category_id, brand_id, low_stock, status
     */
    public function index(): void
    {
        $filters = [
            'search'      => $_GET['q'] ?? ($_GET['search'] ?? null),
            'category_id' => !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null,
            'brand_id'    => !empty($_GET['brand_id']) ? (int)$_GET['brand_id'] : null,
            'status'      => $_GET['status'] ?? 'active',
            'low_stock'   => isset($_GET['low_stock']) ? filter_var($_GET['low_stock'], FILTER_VALIDATE_BOOLEAN) : false,
            'limit'       => !empty($_GET['limit']) ? (int)$_GET['limit'] : 100,
            'offset'      => !empty($_GET['offset']) ? (int)$_GET['offset'] : 0,
        ];

        $products = $this->productModel->getAll($filters);
        $totalCount = $this->productModel->count($filters);

        $this->success([
            'products' => $products,
            'total'    => $totalCount,
            'limit'    => $filters['limit'],
            'offset'   => $filters['offset']
        ], 'Ürünler başarıyla listelendi');
    }

    /**
     * GET /api/v1/products/{id}
     */
    public function show(?string $id = null): void
    {
        $productId = $this->getParamInt($id, 'id');
        if (!$productId) {
            $this->error('Geçersiz ürün kimliği (ID)', 400);
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            $this->error('Ürün bulunamadı', 404);
        }

        $this->success($product, 'Ürün detayları getirildi');
    }

    /**
     * GET /api/v1/products/barcode/{barcode}
     * Instant lookup for POS barcode scanners
     */
    public function barcode(?string $barcode = null): void
    {
        $barcode = $barcode ?: ($_GET['barcode'] ?? null);
        if (empty($barcode)) {
            $this->error('Barkod bilgisi eksik', 400);
        }

        $product = $this->productModel->findByBarcode($barcode);
        if (!$product) {
            $this->error('Bu barkoda ait ürün bulunamadı (' . htmlspecialchars($barcode) . ')', 404);
        }

        $this->success($product, 'Ürün barkod ile bulundu');
    }

    /**
     * POST /api/v1/products
     * Add new stationery item
     */
    public function store(): void
    {
        $body = $this->getJsonBody();

        if (empty($body['name'])) {
            $this->error('Ürün adı zorunludur', 422);
        }

        try {
            $newId = $this->productModel->create($body);
            $product = $this->productModel->find($newId);
            $this->success($product, 'Ürün başarıyla eklendi', 201);
        } catch (Exception $e) {
            $this->error('Ürün eklenemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PUT /api/v1/products/{id} or POST /api/v1/products/update
     */
    public function update(?string $id = null): void
    {
        $body = $this->getJsonBody();
        $productId = $this->getParamInt($id, 'id');
        if (!$productId && !empty($body['id'])) {
            $productId = (int)$body['id'];
        }

        if (!$productId) {
            $this->error('Geçersiz ürün kimliği', 400);
        }

        try {
            $success = $this->productModel->update($productId, $body);
            if ($success) {
                $product = $this->productModel->find($productId);
                $this->success($product, 'Ürün başarıyla güncellendi');
            } else {
                $this->error('Güncellenecek alan bulunamadı veya işlem başarısız oldu', 400);
            }
        } catch (Exception $e) {
            $this->error('Ürün güncellenemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /api/v1/products/{id}
     */
    public function destroy(?string $id = null): void
    {
        $productId = $this->getParamInt($id, 'id');
        if (!$productId) {
            $this->error('Geçersiz ürün kimliği', 400);
        }

        try {
            $this->productModel->delete($productId, true);
            $this->success(null, 'Ürün başarıyla pasife alındı');
        } catch (Exception $e) {
            $this->error('Ürün silinemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/products/adjust-stock
     * Manual stock increment/decrement
     */
    public function adjustStock(): void
    {
        $body = $this->getJsonBody();

        $productId = (int)($body['product_id'] ?? 0);
        $quantity = (float)($body['quantity'] ?? 0.0);
        $type = $body['movement_type'] ?? 'adjustment'; // purchase, sale, waste, adjustment
        $notes = $body['notes'] ?? 'Manuel stok güncellemesi';
        $userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

        if (!$productId || $quantity == 0) {
            $this->error('Ürün ID ve miktar zorunludur', 422);
        }

        try {
            $this->productModel->adjustStock($productId, $quantity, $type, 'manual', null, $notes, $userId);
            $updatedProduct = $this->productModel->find($productId);
            $this->success($updatedProduct, 'Stok başarıyla güncellendi');
        } catch (Exception $e) {
            $this->error('Stok güncellenemedi: ' . $e->getMessage(), 400);
        }
    }
}

