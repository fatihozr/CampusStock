<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Trash;
use Exception;

class TrashController
{
    private Trash $trashModel;

    public function __construct()
    {
        $this->trashModel = new Trash();
    }

    /**
     * Display Trash Bin (Çöp Kutusu) Page
     */
    public function index(): void
    {
        Auth::requireLogin();

        $items = $this->trashModel->getAll();

        view('trash.index', [
            'pageTitle'    => 'Çöp Kutusu / Silinen Kayıtlar',
            'trashItems'   => $items,
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ]);

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
    }

    /**
     * POST: Restore soft-deleted record back to active list
     */
    public function restore(): void
    {
        Auth::requireLogin();

        $trashId = (int)($_POST['trash_id'] ?? 0);

        if ($trashId <= 0) {
            $_SESSION['flash_error'] = 'Lütfen geri yüklenecek geçerli bir kayıt seçiniz!';
            redirect('/trash');
            return;
        }

        try {
            $this->trashModel->restore($trashId);
            $_SESSION['flash_success'] = 'Kayıt başarıyla aktif listeye geri yüklendi!';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/trash');
    }

    /**
     * POST: Permanently Purge soft-deleted record (Admin Only)
     */
    public function purge(): void
    {
        Auth::requirePermission('admin');

        $trashId = (int)($_POST['trash_id'] ?? 0);

        if ($trashId <= 0) {
            $_SESSION['flash_error'] = 'Lütfen kalıcı silinecek geçerli bir kayıt seçiniz!';
            redirect('/trash');
            return;
        }

        try {
            $this->trashModel->purge($trashId);
            $_SESSION['flash_success'] = 'Kayıt veritabanından kalıcı olarak silindi.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect('/trash');
    }
}
