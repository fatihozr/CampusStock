<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Store;
use App\Models\Product;

final class KirtasiyeController
{
    private Store $storeModel;
    private Product $productModel;

    public function __construct()
    {
        $this->storeModel = new Store();
        $this->productModel = new Product();
    }

    public function liste(Request $istek): void
    {
        $universiteId = $istek->sorgu('universite_id') !== null ? (int)$istek->sorgu('universite_id') : null;
        $kirtasiyeler = $this->storeModel->getAll($universiteId);

        Response::json([
            'basarili' => true,
            'sayi'     => count($kirtasiyeler),
            'veri'     => $kirtasiyeler
        ]);
    }

    public function detay(Request $istek): void
    {
        $id = (int)$istek->parametre('id');
        $kirtasiye = $this->storeModel->find($id);

        if (!$kirtasiye) {
            Response::hata('Kırtasiye mağazası bulunamadı.', 404);
        }

        Response::json([
            'basarili' => true,
            'veri'     => $kirtasiye
        ]);
    }

    public function urunler(Request $istek): void
    {
        $id = (int)$istek->parametre('id');
        $kirtasiye = $this->storeModel->find($id);

        if (!$kirtasiye) {
            Response::hata('Kırtasiye mağazası bulunamadı.', 404);
        }

        $filtreler = [
            'search'      => (string)($istek->sorgu('q') ?? $istek->sorgu('search') ?? ''),
            'category_id' => $istek->sorgu('kategori_id') ? (int)$istek->sorgu('kategori_id') : null,
            'limit'       => min(100, max(1, (int)($istek->sorgu('limit') ?? 50)))
        ];

        $urunler = $this->storeModel->getStoreProducts($id, $filtreler);

        Response::json([
            'basarili'  => true,
            'kirtasiye' => [
                'id' => $kirtasiye['id'],
                'ad' => $kirtasiye['name'] ?? $kirtasiye['ad'] ?? ''
            ],
            'urunler'   => $urunler
        ]);
    }
}
