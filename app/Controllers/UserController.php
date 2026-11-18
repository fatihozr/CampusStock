<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Personnel;
use App\Models\Role;
use App\Models\User;

class UserController
{
    private User $userModel;
    private Role $roleModel;
    private Personnel $personnelModel;

    public function __construct()
    {
        $this->userModel      = new User();
        $this->roleModel      = new Role();
        $this->personnelModel = new Personnel();
    }

    /**
     * Show Users & Roles Management Page
     */
    public function index(): void
    {
        Auth::requirePermission('users');

        $activeTab     = $_GET['tab'] ?? 'users';
        $usersList     = $this->userModel->getAllUsers();
        $rolesList     = $this->roleModel->getAll();
        $allModules    = Role::getAvailableModules();
        $personnelList = $this->personnelModel->getAll();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $smtpModel     = new \App\Models\SmtpSetting();
        $smtpSettings  = $smtpModel->get();

        view('user.index', [
            'pageTitle'     => 'Kullanıcı, Yetki ve Ayarlar | Stationery Flow E-Ticaret ve Depo',
            'currentUser'   => Auth::user(),
            'activeTab'     => $activeTab,
            'usersList'     => $usersList,
            'rolesList'     => $rolesList,
            'allModules'    => $allModules,
            'personnelList' => $personnelList,
            'smtpSettings'  => $smtpSettings,
            'flashSuccess'  => $flashSuccess,
            'flashError'    => $flashError
        ]);
    }

    /**
     * Store SMTP settings securely
     */
    public function storeSmtpSettings(): void
    {
        Auth::requirePermission('users');

        $smtpModel = new \App\Models\SmtpSetting();
        $smtpModel->store($_POST);

        $_SESSION['flash_success'] = 'E-posta (SMTP) ayarları başarıyla veritabanına kaydedildi.';
        redirect('/users?tab=smtp');
    }

    /**
     * Test SMTP socket connection AJAX endpoint
     */
    public function testSmtpConnection(): void
    {
        Auth::requirePermission('users');

        header('Content-Type: application/json; charset=utf-8');

        $mailService = new \App\Services\MailService();
        $result = $mailService->testSmtpConnection($_POST);

        echo json_encode($result);
        exit;
    }

    /**
     * Store new user account
     */
    public function storeUser(): void
    {
        Auth::requirePermission('users');

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Lütfen kullanıcı adı, e-posta ve şifre alanlarını eksiksiz doldurunuz!';
            redirect('/users');
            return;
        }

        $newUser = $this->userModel->storeUser($_POST);
        $_SESSION['flash_success'] = 'Kullanıcı hesabı oluşturuldu! Kullanıcı Adı: ' . htmlspecialchars($newUser['username']);
        redirect('/users');
    }

    /**
     * Update user account & role
     */
    public function updateUser(): void
    {
        Auth::requirePermission('users');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz kullanıcı hesabı!';
            redirect('/users');
            return;
        }

        $updated = $this->userModel->updateUser($id, $_POST);
        if ($updated) {
            $_SESSION['flash_success'] = 'Kullanıcı hesabı ve yetki rolü güncellendi.';
        } else {
            $_SESSION['flash_error'] = 'Kullanıcı hesabı bulunamadı!';
        }

        redirect('/users');
    }

    /**
     * Delete user account
     */
    public function deleteUser(): void
    {
        Auth::requirePermission('users');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz kullanıcı hesabı!';
            redirect('/users');
            return;
        }

        if ($id === Auth::user()['id']) {
            $_SESSION['flash_error'] = 'Kendi aktif kullanıcı hesabınızı silemezsiniz!';
            redirect('/users');
            return;
        }

        $deleted = $this->userModel->deleteUser($id);
        if ($deleted) {
            $_SESSION['flash_success'] = 'Kullanıcı hesabı silindi.';
        } else {
            $_SESSION['flash_error'] = 'Kullanıcı hesabı silinemedi!';
        }

        redirect('/users');
    }

    /**
     * Update role permission matrix
     */
    public function updateRolePermissions(): void
    {
        Auth::requirePermission('users');

        $roleId      = (int)($_POST['role_id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];

        if ($roleId <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz rol seçimi!';
            redirect('/users?tab=roles');
            return;
        }

        $updated = $this->roleModel->updatePermissions($roleId, $permissions);
        if ($updated) {
            $_SESSION['flash_success'] = 'Rol yetki matrisi başarıyla güncellendi.';
        } else {
            $_SESSION['flash_error'] = 'Rol yetkileri güncellenemedi!';
        }

        redirect('/users?tab=roles');
    }
}
