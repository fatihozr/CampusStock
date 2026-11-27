<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\University;
use App\Models\Store;

class StoreWebController
{
    private University $universityModel;
    private Store $storeModel;

    public function __construct()
    {
        Auth::requireAuth();
        $this->universityModel = new University();
        $this->storeModel = new Store();
    }

    public function index(): void
    {
        $universities = $this->universityModel->getAll(false);
        $stores = $this->storeModel->getAll(null, false);

        view('stores.index', [
            'pageTitle'    => 'Üniversiteler & Kırtasiye Şubeleri',
            'universities' => $universities,
            'stores'       => $stores
        ]);
    }

    public function storeUniversity(): void
    {
        if (!empty($_POST['name']) && !empty($_POST['city'])) {
            $this->universityModel->store($_POST);
            $_SESSION['flash_message'] = 'Yeni üniversite başarıyla eklendi.';
            $_SESSION['flash_type'] = 'success';
        }
        redirect('/stores');
    }

    public function storeBranch(): void
    {
        if (!empty($_POST['name']) && !empty($_POST['university_id'])) {
            $this->storeModel->store($_POST);
            $_SESSION['flash_message'] = 'Yeni kırtasiye şubesi başarıyla eklendi.';
            $_SESSION['flash_type'] = 'success';
        }
        redirect('/stores');
    }
}
