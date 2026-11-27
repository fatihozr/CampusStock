<?php

declare(strict_types=1);

/**
 * CampusStock API & Web Rotaları
 * 
 * GitHub mikro-framework Router için kayıtlı tüm uç noktalar.
 */

use App\Core\Router;
use App\Controllers\SaglikController;
use App\Controllers\UniversiteController;
use App\Controllers\KirtasiyeController;
use App\Controllers\UrunController;
use App\Controllers\SepetController;
use App\Controllers\SiparisController;
use App\Controllers\KimlikController;

return static function (Router $router): void {
    // 1. Sistem Sağlık Kontrolü
    $router->get('/api/saglik', [SaglikController::class, 'goster']);

    // 2. Üniversiteler & Kampüsler
    $router->get('/api/universiteler', [UniversiteController::class, 'liste']);
    $router->get('/api/universiteler/{id}', [UniversiteController::class, 'detay']);
    $router->get('/api/universiteler/{id}/kirtasiyeler', [UniversiteController::class, 'kirtasiyeler']);

    // 3. Kampüs Kırtasiyeleri (Mağazalar)
    $router->get('/api/kirtasiyeler', [KirtasiyeController::class, 'liste']);
    $router->get('/api/kirtasiyeler/{id}', [KirtasiyeController::class, 'detay']);
    $router->get('/api/kirtasiyeler/{id}/urunler', [KirtasiyeController::class, 'urunler']);

    // 4. Ürünler & Stok
    $router->get('/api/urunler', [UrunController::class, 'liste']);
    $router->get('/api/urunler/{id}', [UrunController::class, 'detay']);
    $router->get('/api/urunler/barkod/{barkod}', [UrunController::class, 'barkod']);

    // 5. Sepet Yönetimi
    $router->get('/api/sepet', [SepetController::class, 'goster']);
    $router->post('/api/sepet/ekle', [SepetController::class, 'ekle']);
    $router->delete('/api/sepet/{id}', [SepetController::class, 'sil']);

    // 6. Sipariş & Gel-Al Motoru
    $router->get('/api/siparisler', [SiparisController::class, 'liste']);
    $router->post('/api/siparisler', [SiparisController::class, 'olustur']);
    $router->get('/api/siparisler/{id}', [SiparisController::class, 'detay']);
    $router->put('/api/siparisler/{id}/durum', [SiparisController::class, 'durumGuncelle']);
    $router->post('/api/siparisler/dogrula', [SiparisController::class, 'kodDogrula']);

    // 7. Kimlik Doğrulama & Kullanıcı
    $router->post('/api/auth/giris', [KimlikController::class, 'giris']);
    $router->post('/api/auth/kayit', [KimlikController::class, 'kayit']);
    $router->get('/api/auth/profil', [KimlikController::class, 'profil']);
};
