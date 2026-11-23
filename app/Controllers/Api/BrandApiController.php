<?php

namespace App\Controllers\Api;

use App\Models\Brand;
use Exception;

class BrandApiController extends BaseApiController
{
    private Brand $brandModel;

    public function __construct()
    {
        $this->brandModel = new Brand();
    }

    /**
     * GET /api/v1/brands
     */
    public function index(): void
    {
        $brands = $this->brandModel->getAll(true);
        $this->success($brands, 'Markalar listelendi');
    }

    /**
     * POST /api/v1/brands
     */
    public function store(): void
    {
        $body = $this->getJsonBody();
        if (empty($body['name'])) {
            $this->error('Marka adı zorunludur', 422);
        }

        try {
            $newId = $this->brandModel->create($body);
            $brand = $this->brandModel->find($newId);
            $this->success($brand, 'Marka başarıyla oluşturuldu', 201);
        } catch (Exception $e) {
            $this->error('Marka oluşturulamadı: ' . $e->getMessage(), 500);
        }
    }
}

