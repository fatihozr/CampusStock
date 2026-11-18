<?php

namespace App\Controllers;

use App\Models\Customer;
use App\Models\CashMovement;
use App\Helpers\Auth;
use Exception;

class CustomerController
{
    private Customer $customerModel;

    public function __construct()
    {
        Auth::requireAuth();
        $this->customerModel = new Customer();
    }

    public function index(): void
    {
        $filters = [
            'search'        => $_GET['search'] ?? null,
            'customer_type' => $_GET['type'] ?? null,
            'has_balance'   => isset($_GET['has_balance']) ? (bool)$_GET['has_balance'] : false
        ];

        $customers = $this->customerModel->getAll($filters);

        view('customer.index', [
            'pageTitle' => 'Müşteriler & Cari Hesaplar',
            'customers' => $customers,
            'filters'   => $filters
        ]);
    }

    public function store(): void
    {
        try {
            $this->customerModel->create($_POST);
            $_SESSION['flash_message'] = 'Müşteri başarıyla kaydedildi.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Hata: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }
        redirect('/customers');
    }

    public function handlePayment(): void
    {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'cash';
        $notes = $_POST['notes'] ?? 'Tahsilat';

        if ($customerId && $amount > 0) {
            try {
                $this->customerModel->adjustBalance($customerId, -$amount);
                $cashModel = new CashMovement();
                $cashModel->record([
                    'user_id'        => Auth::user()['id'] ?? 1,
                    'movement_type'  => 'customer_payment',
                    'amount'         => $amount,
                    'payment_method' => $paymentMethod,
                    'reference_id'   => 'CAR-' . $customerId,
                    'description'    => "Cari Tahsilat - {$notes}"
                ]);
                $_SESSION['flash_message'] = number_format($amount, 2, ',', '.') . ' TL tahsilat kaydedildi.';
                $_SESSION['flash_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['flash_message'] = 'Hata: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
        redirect('/customers');
    }
}
