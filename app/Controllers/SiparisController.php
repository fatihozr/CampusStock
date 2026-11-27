<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\OrderService;
use App\Models\Order;
use Throwable;

final class SiparisController
{
    private OrderService $orderService;
    private Order $orderModel;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->orderModel = new Order();
    }

    public function olustur(Request $istek): void
    {
        $kalemler = $istek->girdi('kalemler') ?? $istek->girdi('items');
        if (empty($kalemler) || !is_array($kalemler)) {
            Response::hata('Siparişte en az bir ürün bulunmalıdır.', 422);
        }

        $ogrenciId = (int)($istek->girdi('ogrenci_id') ?? $istek->girdi('student_id') ?? 1);
        $kirtasiyeId = (int)($istek->girdi('kirtasiye_id') ?? $istek->girdi('store_id') ?? 1);

        try {
            $siparis = $this->orderService->createOrder($ogrenciId, [
                'store_id'       => $kirtasiyeId,
                'items'          => $kalemler,
                'payment_method' => (string)($istek->girdi('odeme_yontemi') ?? $istek->girdi('payment_method') ?? 'cash_pickup'),
                'notes'          => (string)($istek->girdi('notlar') ?? $istek->girdi('notes') ?? '')
            ]);

            Response::json([
                'basarili' => true,
                'mesaj'    => 'Siparişiniz başarıyla alındı. Kampüs kırtasiyesinden Gel-Al koduyla teslim alabilirsiniz.',
                'siparis'  => $siparis
            ], 201);
        } catch (Throwable $e) {
            Response::hata($e->getMessage(), 400);
        }
    }

    public function liste(Request $istek): void
    {
        $filtreler = [
            'store_id'     => $istek->sorgu('kirtasiye_id') ? (int)$istek->sorgu('kirtasiye_id') : null,
            'order_status' => (string)($istek->sorgu('durum') ?? $istek->sorgu('status') ?? ''),
            'search'       => (string)($istek->sorgu('ara') ?? $istek->sorgu('search') ?? ''),
            'page'         => max(1, (int)($istek->sorgu('sayfa') ?? 1)),
            'per_page'     => min(100, max(1, (int)($istek->sorgu('limit') ?? 30)))
        ];

        $siparisler = $this->orderModel->getAll($filtreler);

        Response::json([
            'basarili'   => true,
            'sayi'       => count($siparisler),
            'siparisler' => $siparisler
        ]);
    }

    public function detay(Request $istek): void
    {
        $id = (int)$istek->parametre('id');
        $siparis = $this->orderModel->findById($id);

        if (!$siparis) {
            Response::hata('Sipariş bulunamadı.', 404);
        }

        Response::json([
            'basarili' => true,
            'siparis'  => $siparis
        ]);
    }

    public function kodDogrula(Request $istek): void
    {
        $kod = trim((string)($istek->girdi('kod') ?? $istek->girdi('pickup_code') ?? $istek->sorgu('kod') ?? ''));

        if (empty($kod)) {
            Response::hata('Teslimat kodu girilmelidir.', 422);
        }

        $sonuc = $this->orderService->verifyPickup($kod);
        if (!$sonuc) {
            Response::hata('Bu koda ait aktif bir Gel-Al siparişi bulunamadı.', 404);
        }

        Response::json([
            'basarili' => true,
            'sonuc'    => $sonuc
        ]);
    }

    public function durumGuncelle(Request $istek): void
    {
        $id = (int)$istek->parametre('id');
        $durum = (string)($istek->girdi('durum') ?? $istek->girdi('status') ?? '');

        $durumHaritasi = [
            'hazirlaniyor'   => 'preparing',
            'hazir'          => 'ready',
            'teslim_edildi'  => 'delivered',
            'iptal'          => 'cancelled',
            'received'       => 'received',
            'preparing'      => 'preparing',
            'ready'          => 'ready',
            'delivered'      => 'delivered',
            'cancelled'      => 'cancelled'
        ];

        if (!isset($durumHaritasi[$durum])) {
            Response::hata('Geçersiz durum. Geçerli durumlar: hazirlaniyor, hazir, teslim_edildi, iptal', 422);
        }

        $hedefDurum = $durumHaritasi[$durum];

        try {
            if ($hedefDurum === 'delivered') {
                $guncel = $this->orderService->deliverOrder($id);
            } elseif ($hedefDurum === 'cancelled') {
                $guncel = $this->orderService->cancelOrder($id);
            } else {
                $this->orderModel->updateStatus($id, $hedefDurum);
                $guncel = $this->orderModel->findById($id);
            }

            Response::json([
                'basarili' => true,
                'mesaj'    => "Sipariş durumu '{$hedefDurum}' olarak güncellendi.",
                'siparis'  => $guncel
            ]);
        } catch (Throwable $e) {
            Response::hata($e->getMessage(), 400);
        }
    }
}
