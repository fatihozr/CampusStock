<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\ApiToken;
use Throwable;

final class KimlikController
{
    private User $userModel;
    private ApiToken $tokenModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->tokenModel = new ApiToken();
    }

    public function giris(Request $istek): void
    {
        $kimlik = trim((string)($istek->girdi('email') ?? $istek->girdi('kullanici_adi') ?? $istek->girdi('username') ?? ''));
        $sifre  = trim((string)($istek->girdi('sifre') ?? $istek->girdi('password') ?? ''));

        if (empty($kimlik) || empty($sifre)) {
            Response::hata('Kullanıcı adı / e-posta ve şifre zorunludur.', 422);
        }

        $kullanici = $this->userModel->findByUsernameOrEmail($kimlik);
        if (!$kullanici || !$this->userModel->verifyPassword($sifre, $kullanici['password_hash'])) {
            Response::hata('Geçersiz e-posta veya şifre.', 401);
        }

        $tokenBilgi = $this->tokenModel->createToken((int)$kullanici['id'], 'campus_app', 30);

        Response::json([
            'basarili' => true,
            'mesaj'    => 'Giriş başarılı',
            'token'    => $tokenBilgi['token'],
            'kullanici'=> [
                'id'       => (int)$kullanici['id'],
                'ad'       => $kullanici['first_name'],
                'soyad'    => $kullanici['last_name'],
                'email'    => $kullanici['email'],
                'rol'      => $kullanici['role_slug'] ?? 'uye',
                'telefon'  => $kullanici['phone'] ?? null
            ]
        ]);
    }

    public function kayit(Request $istek): void
    {
        $ad        = trim((string)($istek->girdi('ad') ?? $istek->girdi('first_name') ?? ''));
        $soyad     = trim((string)($istek->girdi('soyad') ?? $istek->girdi('last_name') ?? ''));
        $email     = trim((string)$istek->girdi('email'));
        $sifre     = trim((string)($istek->girdi('sifre') ?? $istek->girdi('password') ?? ''));
        $telefon   = trim((string)($istek->girdi('telefon') ?? $istek->girdi('phone') ?? ''));
        $kullaniciAdi = trim((string)($istek->girdi('kullanici_adi') ?? explode('@', $email)[0]));

        if (empty($ad) || empty($email) || empty($sifre)) {
            Response::hata('Ad, e-posta ve şifre alanları zorunludur.', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::hata('Geçerli bir e-posta adresi giriniz.', 422);
        }

        if (strlen($sifre) < 6) {
            Response::hata('Şifre en az 6 karakter olmalıdır.', 422);
        }

        if ($this->userModel->findByUsernameOrEmail($email)) {
            Response::hata('Bu e-posta adresi zaten kayıtlı.', 409);
        }

        try {
            $yeni = $this->userModel->storeUser([
                'username'   => $kullaniciAdi,
                'email'      => $email,
                'password'   => $sifre,
                'first_name' => $ad,
                'last_name'  => $soyad,
                'phone'      => $telefon,
                'role_id'    => 4,
                'title'      => 'Öğrenci',
                'status'     => 'active'
            ]);

            $tokenBilgi = $this->tokenModel->createToken((int)$yeni['id'], 'campus_app', 30);

            Response::json([
                'basarili' => true,
                'mesaj'    => 'Kayıt başarıyla oluşturuldu.',
                'token'    => $tokenBilgi['token'],
                'kullanici'=> [
                    'id'    => (int)$yeni['id'],
                    'ad'    => $yeni['first_name'],
                    'soyad' => $yeni['last_name'],
                    'email' => $yeni['email']
                ]
            ], 201);
        } catch (Throwable $e) {
            Response::hata('Kayıt oluşturulamadı: ' . $e->getMessage(), 500);
        }
    }

    public function profil(Request $istek): void
    {
        $authHeader = $istek->baslik('authorization') ?? '';
        if (preg_match('/Bearer\s(\S+)/i', $authHeader, $eslesme)) {
            $user = $this->tokenModel->findUserByToken($eslesme[1]);
            if ($user) {
                Response::json(['basarili' => true, 'kullanici' => $user]);
            }
        }

        Response::hata('Yetkisiz erişim. Lütfen giriş yapınız.', 401);
    }
}
