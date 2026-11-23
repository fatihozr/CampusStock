<?php

namespace App\Controllers\Api;

use App\Models\Store;
use App\Models\Product;

class StoreApiController extends BaseApiController
{
    private Store $storeModel;
    private Product $productModel;

    public function __construct()
    {
        $this->storeModel = new Store();
        $this->productModel = new Product();
    }

    /**
     * GET /api/v1/stores
     */
    public function index(): void
    {
        $universityId = isset($_GET['university_id']) ? (int)$_GET['university_id'] : null;
        $stores = $this->storeModel->getAll($universityId);

        $this->success($stores, 'Kampüs mağazaları başarıyla listelendi.');
    }

    /**
     * GET /api/v1/stores/{id}
     */
    public function show(mixed $id = null): void
    {
        $storeId = $this->getParamInt($id, 'id');
        if ($storeId <= 0) {
            $this->error('Geçersiz mağaza kimliği.', 400);
        }

        $store = $this->storeModel->find($storeId);
        if (!$store) {
            $this->error('Mağaza bulunamadı.', 404);
        }

        $this->success($store, 'Mağaza detayları.');
    }

    /**
     * GET /api/v1/stores/{id}/products
     */
    public function products(mixed $id = null): void
    {
        $storeId = $this->getParamInt($id, 'id');
        if ($storeId <= 0) {
            $this->error('Geçersiz mağaza kimliği.', 400);
        }

        $store = $this->storeModel->find($storeId);
        if (!$store) {
            $this->error('Mağaza bulunamadı.', 404);
        }

        $filters = [
            'search'      => $_GET['search'] ?? '',
            'category_id' => !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null,
            'brand_id'    => !empty($_GET['brand_id']) ? (int)$_GET['brand_id'] : null,
            'page'        => max(1, (int)($_GET['page'] ?? 1)),
            'per_page'    => min(100, max(1, (int)($_GET['per_page'] ?? 20)))
        ];

        $products = $this->storeModel->getStoreProducts($storeId, $filters);

        $this->success([
            'store'    => [
                'id'   => $store['id'],
                'name' => $store['name'],
                'slug' => $store['slug']
            ],
            'products' => $products
        ], 'Mağaza ürünleri listelendi.');
    }
}
