<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Delivery;
use App\Models\Procurement;
use Exception;

class DeliveryController
{
    private Delivery $deliveryModel;
    private Procurement $procurementModel;

    public function __construct()
    {
        $this->deliveryModel = new Delivery();
        $this->procurementModel = new Procurement();
    }

    /**
     * Show all deliveries
     */
    public function index(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $deliveries = $this->deliveryModel->getAll();
        
        view('delivery.index', [
            'pageTitle' => 'Gelen Teslimatlar',
            'currentUser' => Auth::user(),
            'deliveries' => $deliveries,
            'flashSuccess'=> $_SESSION['flash_success'] ?? null,
            'flashError'  => $_SESSION['flash_error'] ?? null
        ]);
        
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * Show delivery receive form for a procurement
     */
    public function create(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $procurementId = (int)($_GET['procurement_id'] ?? 0);
        $procurement = $this->procurementModel->find($procurementId);
        
        if (!$procurement) {
            $_SESSION['flash_error'] = 'İhale kaydı bulunamadı.';
            redirect('/procurements');
            return;
        }
        
        view('delivery.create', [
            'pageTitle' => 'Tesellüm İşlemi: ' . $procurement['procurement_no'],
            'currentUser' => Auth::user(),
            'procurement' => $procurement
        ]);
    }

    /**
     * Store new delivery (Quality control check)
     */
    public function store(): void
    {
        Auth::requireAuth();
        Auth::requirePermission('admin');
        
        $procurementId = (int)($_POST['procurement_id'] ?? 0);
        
        try {
            $data = [
                'procurement_id' => $procurementId,
                'document_no' => $_POST['document_no'] ?? '',
                'received_by' => Auth::user('id')
            ];
            
            $items = [];
            foreach ($_POST['delivered'] as $procItemId => $quantity) {
                if ($quantity > 0) {
                    $isCompliant = $_POST['compliance'][$procItemId] ?? 'yes';
                    $reason = $_POST['non_compliance_reason'][$procItemId] ?? '';
                    
                    $items[] = [
                        'procurement_item_id' => $procItemId,
                        'quantity' => $quantity,
                        'is_compliant' => $isCompliant,
                        'non_compliance_reason' => $reason
                    ];
                }
            }
            
            if (empty($items)) {
                throw new Exception("En az bir teslim edilen ürün girmelisiniz.");
            }
            
            $this->deliveryModel->store($data, $items);
            $_SESSION['flash_success'] = 'Tesellüm işlemi kaydedildi. Hatalı ürünler onay bekliyor, diğerleri stoğa eklendi.';
            
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        
        redirect('/procurements/detail?id=' . $procurementId);
    }
}
