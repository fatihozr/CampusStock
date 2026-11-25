<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Role;
use App\Models\User;

class UserController
{
    private User $userModel;
    private Role $roleModel;

    public function __construct()
    {
        Auth::requireAuth();
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    public function index(): void
    {
        $activeTab  = $_GET['tab'] ?? 'users';
        $usersList  = $this->userModel->getAllUsers();
        $rolesList  = $this->roleModel->getAll();
        $allModules = Role::getAvailableModules();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('user.index', [
            'pageTitle'     => 'Yöneticiler & Kullanıcı Rolleri | CampusStock',
            'currentUser'   => Auth::user(),
            'activeTab'     => $activeTab,
            'usersList'     => $usersList,
            'rolesList'     => $rolesList,
            'allModules'    => $allModules,
            'personnelList' => [],
            'smtpSettings'  => [],
            'flashSuccess'  => $flashSuccess,
            'flashError'    => $flashError
        ]);
    }

    public function storeUser(): void
    {
        if (!empty($_POST['username']) && !empty($_POST['email'])) {
            $this->userModel->storeUser($_POST);
            $_SESSION['flash_success'] = 'Yeni kullanıcı başarıyla oluşturuldu.';
        }
        redirect('/users');
    }

    public function updateUser(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->userModel->updateUser($id, $_POST);
            $_SESSION['flash_success'] = 'Kullanıcı bilgileri güncellendi.';
        }
        redirect('/users');
    }

    public function deleteUser(): void
    {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id > 0 && $id !== (int)(Auth::user()['id'] ?? 0)) {
            $this->userModel->deleteUser($id);
            $_SESSION['flash_success'] = 'Kullanıcı silindi.';
        }
        redirect('/users');
    }
}
