<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Count;
use App\Models\Product;
use Exception;

class CountController
{
    private Count $countModel;
    private Product $productModel;

    public function __construct()
    {
        $this->countModel = new Count();
        $this->productModel = new Product();
    }

    public function index(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $counts = $this->countModel->getAll();
        
        view('counts.index', [
            'pageTitle' => 'Fiziksel Sayım Tutanakları',
            'currentUser' => Auth::user(),
            'counts' => $counts,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    public function create(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $products = $this->productModel->getAll();
        
        view('counts.create', [
            'pageTitle' => 'Yeni Sayım Tutanağı',
            'currentUser' => Auth::user(),
            'products' => $products
        ]);
    }

    public function store(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        try {
            $data = [
                'document_no' => $_POST['document_no'] ?? 'SYM-'.date('YmdHis'),
                'count_date' => $_POST['count_date'] ?? date('Y-m-d'),
                'counted_by' => Auth::user('id'),
                'notes' => $_POST['notes'] ?? ''
            ];
            
            $items = [];
            foreach ($_POST['counted'] as $productId => $countedQty) {
                if ($countedQty !== '') {
                    $items[] = [
                        'product_id' => $productId,
                        'expected_quantity' => $_POST['expected'][$productId] ?? 0,
                        'counted_quantity' => (float)$countedQty,
                        'reason' => $_POST['reasons'][$productId] ?? ''
                    ];
                }
            }
            
            if (empty($items)) {
                throw new Exception("En az bir ürün için sayım girmelisiniz.");
            }
            
            $this->countModel->store($data, $items);
            $_SESSION['flash_success'] = 'Sayım tutanağı başarıyla kaydedildi.';
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        redirect('/counts');
    }

    public function approve(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        try {
            $id = (int)$_POST['id'];
            $this->countModel->approve($id, Auth::user('id'));
            $_SESSION['flash_success'] = 'Sayım tutanağı onaylandı ve stoklar denkleştirildi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/counts');
    }
}
