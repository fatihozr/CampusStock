<?php

namespace App\Controllers\Api;

use App\Models\Customer;
use App\Models\CashMovement;
use Exception;

class CustomerApiController extends BaseApiController
{
    private Customer $customerModel;

    public function __construct()
    {
        $this->customerModel = new Customer();
    }

    /**
     * GET /api/v1/customers
     */
    public function index(): void
    {
        $filters = [
            'search'        => $_GET['q'] ?? ($_GET['search'] ?? null),
            'customer_type' => $_GET['type'] ?? ($_GET['customer_type'] ?? null),
            'has_balance'   => isset($_GET['has_balance']) ? filter_var($_GET['has_balance'], FILTER_VALIDATE_BOOLEAN) : false
        ];

        $customers = $this->customerModel->getAll($filters);
        $this->success($customers, 'Müşteriler / Cariler listelendi');
    }

    /**
     * GET /api/v1/customers/{id}
     */
    public function show(?string $id = null): void
    {
        $customerId = $this->getParamInt($id, 'id');
        if (!$customerId) {
            $this->error('Geçersiz müşteri ID', 400);
        }

        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            $this->error('Müşteri bulunamadı', 404);
        }

        $this->success($customer, 'Müşteri detayları getirildi');
    }

    /**
     * POST /api/v1/customers
     */
    public function store(): void
    {
        $body = $this->getJsonBody();

        if (empty($body['first_name'])) {
            $this->error('Müşteri adı / Yetkili adı zorunludur', 422);
        }

        try {
            $newId = $this->customerModel->create($body);
            $customer = $this->customerModel->find($newId);
            $this->success($customer, 'Müşteri başarıyla kaydedildi', 201);
        } catch (Exception $e) {
            $this->error('Müşteri kaydedilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/v1/customers/{id}/payment
     * Debt / Veresiye collection payment
     */
    public function payment(?string $id = null): void
    {
        $customerId = $this->getParamInt($id, 'id');
        $body = $this->getJsonBody();
        $amount = (float)($body['amount'] ?? 0.0);
        $paymentMethod = $body['payment_method'] ?? 'cash';
        $notes = $body['notes'] ?? 'Cari bakiye tahsilatı';

        if (!$customerId || $amount <= 0) {
            $this->error('Geçerli müşteri ID ve tahsilat tutarı zorunludur', 422);
        }

        $customer = $this->customerModel->find($customerId);
        if (!$customer) {
            $this->error('Müşteri bulunamadı', 404);
        }

        try {
            // Deduct balance
            $this->customerModel->adjustBalance($customerId, -$amount);

            // Record cash movement
            $userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 1;
            $cashModel = new CashMovement();
            $cashModel->record([
                'user_id'        => $userId,
                'movement_type'  => 'customer_payment',
                'amount'         => $amount,
                'payment_method' => $paymentMethod,
                'reference_id'   => 'CAR-' . $customerId,
                'description'    => "Müşteri Tahsilatı: {$customer['full_name']} - {$notes}"
            ]);

            $updatedCustomer = $this->customerModel->find($customerId);
            $this->success([
                'customer'         => $updatedCustomer,
                'collected_amount' => $amount,
                'new_balance'      => $updatedCustomer['balance']
            ], 'Tahsilat başarıyla işlendi');

        } catch (Exception $e) {
            $this->error('Tahsilat kaydedilemedi: ' . $e->getMessage(), 500);
        }
    }
}
