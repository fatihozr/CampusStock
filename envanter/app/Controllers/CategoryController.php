<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Category;
use Exception;

class CategoryController
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    /**
     * Display Categories Page
     */
    public function index(): void
    {
        Auth::requireAuth();

        $categories = $this->categoryModel->getAll();

        view('category.index', [
            'pageTitle'    => 'Temizlik Malzemesi Kategorileri',
            'currentUser'  => Auth::user(),
            'categories'   => $categories,
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Store new category
     */
    public function store(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        try {
            $this->categoryModel->store($_POST);
            $_SESSION['flash_success'] = 'Yeni kategori başarıyla eklendi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/categories');
    }
}
