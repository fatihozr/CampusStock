<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\User;

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Show login page
     */
    public function login(): void
    {
        Auth::requireGuest();

        $flashError = $_SESSION['flash_error'] ?? null;
        $flashSuccess = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        view('auth.login', [
            'pageTitle'    => 'Giriş Yap | CampusStock Kırtasiye',
            'flashError'   => $flashError,
            'flashSuccess' => $flashSuccess
        ]);
    }

    /**
     * Handle login submission
     */
    public function handleLogin(): void
    {
        Auth::requireGuest();

        if (!Csrf::verify($_POST['_csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Güvenlik doğrulaması (CSRF) geçersiz. Lütfen sayfayı yenileyip tekrar deneyin.';
            redirect('/login');
            return;
        }

        $identifier = trim($_POST['username_or_email'] ?? $_POST['email'] ?? '');
        $password   = trim($_POST['password'] ?? '');
        $remember   = isset($_POST['remember']);

        if (empty($identifier) || empty($password)) {
            $_SESSION['flash_error'] = 'Lütfen kullanıcı adı / e-posta ve şifrenizi giriniz.';
            redirect('/login');
            return;
        }

        $user = $this->userModel->findByUsernameOrEmail($identifier);
        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Girdiğiniz kullanıcı adı / e-posta veya şifre hatalı!';
            redirect('/login');
            return;
        }

        if (($user['status'] ?? 'active') !== 'active') {
            $_SESSION['flash_error'] = 'Hesabınız pasif durumdadır. Sistem yöneticisi ile iletişime geçiniz.';
            redirect('/login');
            return;
        }

        Auth::login($user, $remember);
        $this->userModel->updateLastLogin($user['id']);

        $_SESSION['flash_success'] = 'Giriş başarılı. Hoş geldiniz, ' . htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8');
        redirect('/dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        Auth::logout();
        $_SESSION['flash_success'] = 'Güvenli çıkış yapıldı.';
        redirect('/login');
    }
}
