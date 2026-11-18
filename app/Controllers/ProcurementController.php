<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Procurement;
use App\Models\Product;
use Exception;

class ProcurementController
{
    private Procurement $procurementModel;
    private Product $productModel;

    public function __construct()
    {
        $this->procurementModel = new Procurement();
        $this->productModel = new Product();
    }

    /**
     * Display all procurements (tenders)
     */
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $procurements = $this->procurementModel->getAll();
        
        view('procurement.index', [
            'pageTitle' => 'İhale ve Satın Alma Takibi',
            'currentUser' => Auth::user(),
            'procurements' => $procurements,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Show create procurement form
     */
    public function create(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $products = $this->productModel->getAll();
        
        view('procurement.create', [
            'pageTitle' => 'Yeni İhale Kaydı',
            'currentUser' => Auth::user(),
            'products' => $products
        ]);
    }

    /**
     * Store new procurement
     */
    public function store(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        try {
            $data = [
                'procurement_no' => $_POST['procurement_no'],
                'supplier' => $_POST['supplier'],
                'created_by' => Auth::user('id')
            ];
            
            $items = [];
            if (!empty($_POST['products']) && is_array($_POST['products'])) {
                foreach ($_POST['products'] as $productId => $quantity) {
                    if ($quantity > 0) {
                        $items[] = [
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'unit_price' => $_POST['unit_prices'][$productId] ?? 0,
                            'expected_specifications' => $_POST['specifications'][$productId] ?? ''
                        ];
                    }
                }
            }
            
            if (empty($items)) {
                throw new Exception("En az bir ürün seçmelisiniz.");
            }
            
            $this->procurementModel->store($data, $items);
            $_SESSION['flash_success'] = 'İhale kaydı başarıyla oluşturuldu.';
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        redirect('/procurements');
    }

    /**
     * Display Single Procurement Detail
     */
    public function detail(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $id = (int)($_GET['id'] ?? 0);
        $procurement = $this->procurementModel->find($id);
        
        if (!$procurement) {
            $_SESSION['flash_error'] = 'İhale kaydı bulunamadı.';
            redirect('/procurements');
            return;
        }
        
        view('procurement.detail', [
            'pageTitle' => 'İhale Detayı: ' . $procurement['procurement_no'],
            'currentUser' => Auth::user(),
            'procurement' => $procurement,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }
}
