<?php

namespace App\Controllers;

use App\Models\Brand;
use App\Helpers\Auth;
use Exception;

class BrandController
{
    private Brand $brandModel;

    public function __construct()
    {
        Auth::requireAuth();
        $this->brandModel = new Brand();
    }

    public function index(): void
    {
        $brands = $this->brandModel->getAll();

        view('brand.index', [
            'pageTitle' => 'Kırtasiye Markaları',
            'brands'    => $brands
        ]);
    }

    public function store(): void
    {
        try {
            $this->brandModel->create($_POST);
            $_SESSION['flash_message'] = 'Marka başarıyla eklendi.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        redirect('/brands');
    }
}

