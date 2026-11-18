<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Department;
use App\Models\Personnel;
use Exception;

class PersonnelController
{
    private Personnel $personnelModel;
    private Department $departmentModel;

    public function __construct()
    {
        $this->personnelModel  = new Personnel();
        $this->departmentModel = new Department();
    }

    /**
     * Show Personnel Management List Page (Professional Directory)
     */
    public function index(): void
    {
        Auth::requirePermission('personnel');

        $search     = trim($_GET['search'] ?? '');
        $department = trim($_GET['department'] ?? '');
        $status     = trim($_GET['status'] ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));

        $filters = [
            'search'     => $search,
            'department' => $department,
            'status'     => $status
        ];

        $paginatedData = $this->personnelModel->getFilteredPaginated($filters, $page, 8);
        $filterOptions = $this->personnelModel->getFilterOptions();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('personnel.index', [
            'pageTitle'     => 'Personel Rehberi | Stationery Flow E-Ticaret ve Depo',
            'currentUser'   => Auth::user(),
            'personnel'     => $paginatedData['data'],
            'totalItems'    => $paginatedData['total_items'],
            'currentPage'   => $paginatedData['current_page'],
            'totalPages'    => $paginatedData['total_pages'],
            'perPage'       => $paginatedData['per_page'],
            'filters'       => $filters,
            'filterOptions' => $filterOptions,
            'flashSuccess'  => $flashSuccess,
            'flashError'    => $flashError
        ]);
    }

    /**
     * Show Hierarchical Department & Organization Tree Page (Birim & Müdürlükler)
     */
    public function departments(): void
    {
        Auth::requirePermission('personnel');

        $allPersonnel = $this->personnelModel->getAll();
        $departments  = $this->departmentModel->getAll();
        $stats        = $this->departmentModel->getSummaryStats($allPersonnel);

        // Group personnel dynamically by department
        $deptPersonnelMap = [];
        foreach ($departments as $d) {
            $deptPersonnelMap[$d['name']] = [];
        }

        foreach ($allPersonnel as $p) {
            $deptName = $p['department'] ?? 'Bilgi İşlem Müdürlüğü';
            if (!isset($deptPersonnelMap[$deptName])) {
                $deptPersonnelMap[$deptName] = [];
            }
            $deptPersonnelMap[$deptName][] = $p;
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError   = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('personnel.departments', [
            'pageTitle'        => 'Organizasyon ve Departman Yönetimi | Stationery Flow',
            'currentUser'      => Auth::user(),
            'departments'      => $departments,
            'deptPersonnelMap' => $deptPersonnelMap,
            'allPersonnel'     => $allPersonnel,
            'stats'            => $stats,
            'flashSuccess'     => $flashSuccess,
            'flashError'       => $flashError
        ]);
    }

    /**
     * Store new department
     */
    public function storeDepartment(): void
    {
        Auth::requirePermission('personnel');

        $redirect = $_POST['redirect_to'] ?? '/departments';

        try {
            $newDept = $this->departmentModel->store($_POST);
            $_SESSION['flash_success'] = 'Müdürlük kaydı başarıyla eklendi: ' . htmlspecialchars($newDept['name']);
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect($redirect);
    }

    /**
     * Update existing department
     */
    public function updateDepartment(): void
    {
        Auth::requirePermission('personnel');

        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect_to'] ?? '/departments';

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz müdürlük kaydı!';
            redirect($redirect);
            return;
        }

        try {
            $updated = $this->departmentModel->update($id, $_POST);
            if ($updated) {
                $_SESSION['flash_success'] = 'Daire Başkanlığı bilgileri başarıyla güncellendi.';
            } else {
                $_SESSION['flash_error'] = 'Daire Başkanlığı kaydı bulunamadı!';
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect($redirect);
    }

    /**
     * Delete department
     */
    public function deleteDepartment(): void
    {
        Auth::requirePermission('admin'); // Using 'admin' as per requirement ("envanter isimli kullanıcı yani ana admin")

        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect_to'] ?? '/departments';

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz Daire Başkanlığı kaydı!';
            redirect($redirect);
            return;
        }

        try {
            $deleted = $this->departmentModel->delete($id);
            if ($deleted) {
                $_SESSION['flash_success'] = 'Daire Başkanlığı başarıyla silindi.';
            } else {
                $_SESSION['flash_error'] = 'Daire Başkanlığı kaydı bulunamadı veya silinemedi!';
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }

        redirect($redirect);
    }

    /**
     * Store new personnel member
     */
    public function store(): void
    {
        Auth::requirePermission('personnel');

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $redirect  = $_POST['redirect_to'] ?? '/personnel';

        if (empty($firstName) || empty($lastName)) {
            $_SESSION['flash_error'] = 'Lütfen personelin adını ve soyadını giriniz.';
            redirect($redirect);
            return;
        }

        $newPerson = $this->personnelModel->store($_POST);
        $_SESSION['flash_success'] = 'Personel kaydı oluşturuldu! Sicil No: ' . htmlspecialchars($newPerson['sicil_no']);
        redirect($redirect);
    }

    /**
     * Update existing personnel member
     */
    public function update(): void
    {
        Auth::requirePermission('personnel');

        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect_to'] ?? '/personnel';

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz personel kaydı!';
            redirect($redirect);
            return;
        }

        $updated = $this->personnelModel->update($id, $_POST);
        if ($updated) {
            $_SESSION['flash_success'] = 'Personel bilgileri başarıyla güncellendi.';
        } else {
            $_SESSION['flash_error'] = 'Personel kaydı bulunamadı!';
        }

        redirect($redirect);
    }

    /**
     * Delete personnel member
     */
    public function delete(): void
    {
        Auth::requirePermission('personnel');

        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect_to'] ?? '/personnel';

        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Geçersiz personel kaydı!';
            redirect($redirect);
            return;
        }

        $person = $this->personnelModel->find($id);
        if (!$person) {
            $_SESSION['flash_error'] = 'Personel bulunamadı!';
            redirect($redirect);
            return;
        }

        $deleted = $this->personnelModel->delete($id);
        if ($deleted) {
            $_SESSION['flash_success'] = 'Personel kaydı başarıyla silindi.';
        } else {
            $_SESSION['flash_error'] = 'Personel kaydı silinemedi!';
        }

        redirect($redirect);
    }

    /**
     * AJAX Endpoint: Get single personnel details (JSON) with active assigned devices
     */
    public function detail(): void
    {
        Auth::requirePermission('personnel');

        $id = (int)($_GET['id'] ?? 0);
        $person = $this->personnelModel->find($id);

        header('Content-Type: application/json; charset=utf-8');
        if ($person) {
            echo json_encode(['success' => true, 'data' => $person]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Personel bulunamadı.']);
        }
        exit;
    }
}
