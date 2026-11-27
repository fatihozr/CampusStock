<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Product;

final class UrunController
{
    private Product $model;

    public function __construct()
    {
        $this->model = new Product();
    }

    public function liste(Request $istek): void
    {
        $filtreler = [
            'search'      => (string)($istek->sorgu('q') ?? $istek->sorgu('search') ?? ''),
            'category_id' => $istek->sorgu('kategori_id') ? (int)$istek->sorgu('kategori_id') : null,
            'brand_id'    => $istek->sorgu('marka_id') ? (int)$istek->sorgu('marka_id') : null,
            'low_stock'   => $istek->sorgu('kritik_stok') === '1' || $istek->sorgu('low_stock') === 'true',
            'limit'       => min(100, max(1, (int)($istek->sorgu('limit') ?? 50))),
            'offset'      => max(0, (int)($istek->sorgu('offset') ?? 0))
        ];

        $urunler = $this->model->getAll($filtreler);

        Response::json([
            'basarili' => true,
            'sayi'     => count($urunler),
            'urunler'  => $urunler
        ]);
    }

    public function detay(Request $istek): void
    {
        $id = (int)$istek->parametre('id');
        $urun = $this->model->find($id);

        if (!$urun) {
            Response::hata('Ürün bulunamadı.', 404);
        }

        Response::json([
            'basarili' => true,
            'veri'     => $urun
        ]);
    }

    public function barkod(Request $istek): void
    {
        $barkod = (string)$istek->parametre('barkod');
        if (empty($barkod)) {
            $barkod = (string)($istek->sorgu('barkod') ?? '');
        }

        $urun = $this->model->findByBarcode($barkod);
        if (!$urun) {
            Response::hata("Bu barkoda ait ürün bulunamadı: {$barkod}", 404);
        }

        Response::json([
            'basarili' => true,
            'veri'     => $urun
        ]);
    }
}
