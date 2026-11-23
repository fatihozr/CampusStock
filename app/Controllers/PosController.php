<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Customer;
use App\Helpers\Auth;

class PosController
{
    private Product $productModel;
    private Category $categoryModel;
    private Brand $brandModel;
    private Customer $customerModel;

    public function __construct()
    {
        Auth::requireAuth();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->brandModel = new Brand();
        $this->customerModel = new Customer();
    }

    /**
     * Display POS Quick Cashier Terminal
     */
    public function index(): void
    {
        $categories = $this->categoryModel->getAll(true);
        $brands = $this->brandModel->getAll(true);
        $customers = $this->customerModel->getAll();
        $featuredProducts = $this->productModel->getAll(['status' => 'active', 'limit' => 24]);

        view('pos.index', [
            'pageTitle'        => 'Kırtasiye Hızlı Satış (POS)',
            'categories'       => $categories,
            'brands'           => $brands,
            'customers'        => $customers,
            'featuredProducts' => $featuredProducts
        ]);
    }
}

