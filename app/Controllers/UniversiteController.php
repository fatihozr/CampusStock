<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\University;
use App\Models\Store;

final class UniversiteController
{
    private University $model;
    private Store $storeModel;

    public function __construct()
    {
        $this->model = new University();
        $this->storeModel = new Store();
    }

    public function liste(Request $istek): void
    {
        $universiteler = $this->model->getAll();
        Response::json([
            'basarili' => true,
            'sayi'     => count($universiteler),
            'veri'     => $universiteler
        ]);
    }

    public function detay(Request $istek): void
    {
        $id = (int)$istek->parametre('id');
        $universite = $this->model->find($id);

        if (!$universite) {
            Response::hata('Üniversite bulunamadı.', 404);
        }

        Response::json([
            'basarili' => true,
            'veri'     => $universite
        ]);
    }

    public function kirtasiyeler(Request $istek): void
    {
        $id = (int)$istek->parametre('id');
        $universite = $this->model->find($id);

        if (!$universite) {
            Response::hata('Üniversite bulunamadı.', 404);
        }

        $kirtasiyeler = $this->storeModel->getAll($id);
        Response::json([
            'basarili'   => true,
            'universite' => $universite['name'] ?? $universite['ad'] ?? '',
            'veri'       => $kirtasiyeler
        ]);
    }
}
