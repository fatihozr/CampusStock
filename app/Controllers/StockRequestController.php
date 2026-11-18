<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\StockRequest;
use App\Models\Product;
use App\Models\Department;
use Exception;

class StockRequestController
{
    private StockRequest $requestModel;
    private Product $productModel;
    private Department $departmentModel;

    public function __construct()
    {
        $this->requestModel = new StockRequest();
        $this->productModel = new Product();
        $this->departmentModel = new Department();
    }

    /**
     * Display all requests
     */
    public function index(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        
        if (Auth::hasPermission('admin')) {
            $requests = $this->requestModel->getAll();
        } else {
            $requests = $this->requestModel->getByUser($user['id']);
        }
        
        view('requests.index', [
            'pageTitle' => 'Malzeme Talepleri',
            'currentUser' => $user,
            'requests' => $requests,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Show pending approvals for admin
     */
    public function approvals(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');

        $requests = $this->requestModel->getAll(); // In a real app, maybe filter by status='pending'
        $requests = array_filter($requests, function($r) {
            return $r['status'] === 'pending';
        });
        
        view('requests.approvals', [
            'pageTitle'   => 'Bekleyen Talepler',
            'currentUser' => Auth::user(),
            'requests'    => $requests,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Show create request form
     */
    public function create(): void
    {
        Auth::requireAuth();
        
        $products = $this->productModel->getAll();
        $departments = $this->departmentModel->getAll();
        
        view('requests.create', [
            'pageTitle' => 'Yeni Malzeme Talebi',
            'currentUser' => Auth::user(),
            'products' => $products,
            'departments' => $departments
        ]);
    }

    /**
     * Store new request
     */
    public function store(): void
    {
        Auth::requireAuth();
        $user = Auth::user();
        
        try {
            $departmentId = $_POST['department_id'] ?? null;
            if (!$departmentId) {
                throw new Exception("Birim seçimi zorunludur.");
            }
            
            $items = [];
            foreach ($_POST['products'] as $productId => $quantity) {
                if ($quantity > 0) {
                    $items[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'description' => $_POST['descriptions'][$productId] ?? ''
                    ];
                }
            }
            
            if (empty($items)) {
                throw new Exception("En az bir ürün talep etmelisiniz.");
            }
            
            $data = [
                'department_id' => $departmentId,
                'requested_by' => $user['id']
            ];
            
            $this->requestModel->store($data, $items);
            $_SESSION['flash_success'] = 'Talebiniz başarıyla oluşturuldu ve onay bekliyor.';
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        redirect('/requests');
    }

    /**
     * Approve a request (Admin)
     */
    public function approve(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        try {
            $id = (int)$_POST['id'];
            $deliveredTo = $_POST['delivered_to'] ?? '';
            
            if (empty($deliveredTo)) {
                throw new Exception("Teslim alan kişi bilgisi zorunludur.");
            }
            
            $this->requestModel->approve($id, Auth::user('id'), $deliveredTo);
            $_SESSION['flash_success'] = 'Talep onaylandı ve stoktan düşüldü.';
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        redirect('/requests');
    }

    /**
     * Reject a request (Admin)
     */
    public function reject(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        try {
            $id = (int)$_POST['id'];
            $reason = $_POST['rejection_reason'] ?? '';
            
            $this->requestModel->reject($id, Auth::user('id'), $reason);
            $_SESSION['flash_success'] = 'Talep reddedildi.';
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        redirect('/requests');
    }
}
