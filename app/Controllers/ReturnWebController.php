<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Services\MockDataService;

class ReturnWebController
{
    public function __construct()
    {
        Auth::requireAuth();
    }

    public function index(): void
    {
        $returns = array_reverse(MockDataService::all('returns'));

        view('returns.index', [
            'pageTitle' => 'İade Talepleri & İşlemleri',
            'returns'   => $returns
        ]);
    }

    public function updateStatus(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'Onaylandı');

        $rows = MockDataService::all('returns');
        foreach ($rows as &$row) {
            if ((int)$row['id'] === $id) {
                $row['status'] = $status;
                break;
            }
        }
        MockDataService::saveAll('returns', $rows);

        $_SESSION['flash_message'] = 'İade durumu güncellendi.';
        $_SESSION['flash_type'] = 'success';
        redirect('/returns');
    }
}
