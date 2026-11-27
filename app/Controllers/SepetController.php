<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Product;

final class SepetController
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public function goster(Request $istek): void
    {
        $sepet = $_SESSION['sepet'] ?? [];
        $toplamTutar = 0.0;
        $kalemler = [];

        foreach ($sepet as $urunId => $adet) {
            $urun = $this->productModel->find((int)$urunId);
            if (!$urun) continue;

            $birimFiyat = (float)$urun['sale_price'];
            $kalemTutar = round($birimFiyat * (float)$adet, 2);
            $toplamTutar += $kalemTutar;

            $kalemler[] = [
                'urun_id'     => $urun['id'],
                'ad'          => $urun['name'],
                'barkod'      => $urun['barcode'],
                'birim_fiyat' => $birimFiyat,
                'adet'        => (float)$adet,
                'tutar'       => $kalemTutar,
                'gorsel'      => $urun['image_url'] ?? null
            ];
        }

        Response::json([
            'basarili'     => true,
            'kalem_sayisi' => count($kalemler),
            'toplam_tutar' => round($toplamTutar, 2),
            'sepet'        => $kalemler
        ]);
    }

    public function ekle(Request $istek): void
    {
        $urunId = (int)($istek->girdi('urun_id') ?? 0);
        $adet = max(1, (float)($istek->girdi('adet') ?? 1));

        if ($urunId <= 0) {
            Response::hata('Geçersiz ürün kimliği.', 400);
        }

        $urun = $this->productModel->find($urunId);
        if (!$urun) {
            Response::hata('Ürün bulunamadı.', 404);
        }

        if (!isset($_SESSION['sepet'])) {
            $_SESSION['sepet'] = [];
        }

        $_SESSION['sepet'][$urunId] = ($_SESSION['sepet'][$urunId] ?? 0) + $adet;

        Response::json([
            'basarili' => true,
            'mesaj'    => "{$urun['name']} sepete eklendi.",
            'sepet'    => $_SESSION['sepet']
        ]);
    }

    public function sil(Request $istek): void
    {
        $urunId = (int)$istek->parametre('id');
        if (isset($_SESSION['sepet'][$urunId])) {
            unset($_SESSION['sepet'][$urunId]);
        }

        Response::json([
            'basarili' => true,
            'mesaj'    => 'Ürün sepetten çıkarıldı.'
        ]);
    }
}
