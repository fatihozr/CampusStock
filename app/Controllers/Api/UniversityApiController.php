<?php

namespace App\Controllers\Api;

use App\Models\University;
use App\Models\Store;

class UniversityApiController extends BaseApiController
{
    private University $universityModel;
    private Store $storeModel;

    public function __construct()
    {
        $this->universityModel = new University();
        $this->storeModel = new Store();
    }

    /**
     * GET /api/v1/universities
     */
    public function index(): void
    {
        $onlyActive = isset($_GET['all']) ? false : true;
        $universities = $this->universityModel->getAll($onlyActive);

        $this->success($universities, 'Üniversite listesi başarıyla getirildi.');
    }

    /**
     * GET /api/v1/universities/{id}
     */
    public function show(mixed $id = null): void
    {
        $univId = $this->getParamInt($id, 'id');
        if ($univId <= 0) {
            $this->error('Geçersiz üniversite kimliği.', 400);
        }

        $university = $this->universityModel->find($univId);
        if (!$university) {
            $this->error('Üniversite bulunamadı.', 404);
        }

        $this->success($university, 'Üniversite detayları.');
    }

    /**
     * GET /api/v1/universities/{id}/stores
     */
    public function stores(mixed $id = null): void
    {
        $univId = $this->getParamInt($id, 'id');
        if ($univId <= 0) {
            $this->error('Geçersiz üniversite kimliği.', 400);
        }

        $stores = $this->storeModel->getAll($univId);
        $this->success($stores, 'Kampüs kırtasiye mağazaları listesi.');
    }

    /**
     * POST /api/v1/universities
     */
    public function store(): void
    {
        $this->requireAuthUser();
        $body = $this->getJsonBody();

        if (empty($body['name']) || empty($body['city'])) {
            $this->error('Üniversite adı ve şehir alanları zorunludur.', 422);
        }

        $created = $this->universityModel->store($body);
        $this->success($created, 'Üniversite başarıyla eklendi.', 201);
    }
}
