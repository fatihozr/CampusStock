<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Models\ApiToken;
use App\Helpers\Auth;
use Throwable;

class AuthApiController extends BaseApiController
{
    private User $userModel;
    private ApiToken $tokenModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->tokenModel = new ApiToken();
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(): void
    {
        $body = $this->getJsonBody();
        $identifier = trim($body['username'] ?? $body['email'] ?? '');
        $password = trim($body['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            $this->error('Kullanıcı adı/e-posta ve şifre zorunludur.', 422);
        }

        $user = $this->userModel->findByUsernameOrEmail($identifier);
        if (!$user) {
            $this->error('Geçersiz kullanıcı adı veya şifre.', 401);
        }

        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->error('Geçersiz kullanıcı adı veya şifre.', 401);
        }

        // Update last login
        try {
            $this->userModel->updateLastLogin($user['id']);
        } catch (Throwable) {}

        // Create API Bearer Token
        $tokenData = $this->tokenModel->createToken($user['id'], 'mobile_or_web_app', 30);

        // Also initiate session for seamless hybrid use
        try {
            Auth::login($user);
        } catch (Throwable) {}

        $userData = [
            'id'         => (int)$user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'phone'      => $user['phone'] ?? null,
            'title'      => $user['title'] ?? 'Kullanıcı',
            'role_id'    => (int)($user['role_id'] ?? 4),
            'role_slug'  => $user['role_slug'] ?? 'cashier',
            'role_name'  => $user['role_name'] ?? 'Kasiyer'
        ];

        $this->success([
            'token'      => $tokenData['token'],
            'token_type' => 'Bearer',
            'expires_at' => $tokenData['expires_at'],
            'user'       => $userData
        ], 'Giriş başarılı');
    }

    /**
     * POST /api/v1/auth/register
     */
    public function register(): void
    {
        $body = $this->getJsonBody();

        $username   = trim($body['username'] ?? '');
        $email      = trim($body['email'] ?? '');
        $password   = trim($body['password'] ?? '');
        $firstName  = trim($body['first_name'] ?? '');
        $lastName   = trim($body['last_name'] ?? '');
        $phone      = trim($body['phone'] ?? '');

        if (empty($username) || empty($email) || empty($password) || empty($firstName)) {
            $this->error('Kullanıcı adı, e-posta, şifre ve ad alanları zorunludur.', 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Geçerli bir e-posta adresi giriniz.', 422);
        }

        if (strlen($password) < 6) {
            $this->error('Şifre en az 6 karakter olmalıdır.', 422);
        }

        // Check unique
        $existing = $this->userModel->findByUsernameOrEmail($username);
        if ($existing) {
            $this->error('Bu kullanıcı adı zaten kullanımda.', 409);
        }

        $existingEmail = $this->userModel->findByUsernameOrEmail($email);
        if ($existingEmail) {
            $this->error('Bu e-posta adresi zaten kayıtlı.', 409);
        }

        try {
            $newUser = $this->userModel->storeUser([
                'username'   => $username,
                'email'      => $email,
                'password'   => $password,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $phone,
                'role_id'    => 4, // standard customer/student
                'title'      => 'Öğrenci / Müşteri',
                'status'     => 'active'
            ]);

            if (!$newUser) {
                $this->error('Kullanıcı kaydı oluşturulamadı.', 500);
            }

            $tokenData = $this->tokenModel->createToken($newUser['id'], 'registered_app', 30);

            $this->success([
                'token'      => $tokenData['token'],
                'token_type' => 'Bearer',
                'expires_at' => $tokenData['expires_at'],
                'user'       => [
                    'id'         => (int)$newUser['id'],
                    'username'   => $newUser['username'],
                    'email'      => $newUser['email'],
                    'first_name' => $newUser['first_name'],
                    'last_name'  => $newUser['last_name'],
                    'role_slug'  => $newUser['role_slug'] ?? 'customer'
                ]
            ], 'Kayıt başarıyla tamamlandı', 201);
        } catch (Throwable $e) {
            $this->error('Kayıt oluşturulurken bir hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(): void
    {
        $user = $this->requireAuthUser();
        $this->success($user, 'Kullanıcı profili');
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(): void
    {
        $token = $this->getBearerToken();
        if ($token) {
            $this->tokenModel->revokeToken($token);
        }

        Auth::logout();

        $this->success(null, 'Çıkış yapıldı');
    }
}
