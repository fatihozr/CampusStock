<?php

namespace App\Controllers\Api;

use App\Models\Category;
use Exception;

class CategoryApiController extends BaseApiController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    /**
     * GET /api/v1/categories
     */
    public function index(): void
    {
        $asTree = isset($_GET['tree']) ? filter_var($_GET['tree'], FILTER_VALIDATE_BOOLEAN) : false;

        $categories = $asTree 
            ? $this->categoryModel->getTree()
            : $this->categoryModel->getAll(true);

        $this->success($categories, 'Kategoriler listelendi');
    }

    /**
     * POST /api/v1/categories
     */
    public function store(): void
    {
        $body = $this->getJsonBody();
        if (empty($body['name'])) {
            $this->error('Kategori adı zorunludur', 422);
        }

        try {
            $newId = $this->categoryModel->create($body);
            $cat = $this->categoryModel->find($newId);
            $this->success($cat, 'Kategori başarıyla oluşturuldu', 201);
        } catch (Exception $e) {
            $this->error('Kategori oluşturulamadı: ' . $e->getMessage(), 500);
        }
    }
}
