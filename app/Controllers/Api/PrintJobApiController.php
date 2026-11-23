<?php

namespace App\Controllers\Api;

use App\Services\PrintJobService;
use App\Models\PrintJob;
use Throwable;

class PrintJobApiController extends BaseApiController
{
    private PrintJobService $printService;
    private PrintJob $printJobModel;

    public function __construct()
    {
        $this->printService = new PrintJobService();
        $this->printJobModel = new PrintJob();
    }

    /**
     * GET /api/v1/print-jobs/pricing
     */
    public function pricing(): void
    {
        $pricing = $this->printService->getPricingTable();
        $this->success($pricing, 'Baskı ve fotokopi güncel fiyat tarifesi.');
    }

    /**
     * POST /api/v1/print-jobs/calculate
     */
    public function calculate(): void
    {
        $body = $this->getJsonBody();
        $calc = $this->printService->calculateCost($body);

        $this->success($calc, 'Baskı maliyeti hesaplandı.');
    }

    /**
     * GET /api/v1/print-jobs
     */
    public function index(): void
    {
        $authUser = $this->getAuthUser();

        $filters = [
            'store_id' => !empty($_GET['store_id']) ? (int)$_GET['store_id'] : null,
            'status'   => $_GET['status'] ?? null
        ];

        if ($authUser && in_array($authUser['role_slug'] ?? '', ['student', 'customer'])) {
            $filters['student_id'] = (int)$authUser['id'];
        } elseif (!empty($_GET['student_id'])) {
            $filters['student_id'] = (int)$_GET['student_id'];
        }

        $jobs = $this->printJobModel->getAll($filters);
        $this->success($jobs, 'Baskı talepleri listelendi.');
    }

    /**
     * POST /api/v1/print-jobs
     */
    public function store(): void
    {
        $authUser = $this->getAuthUser();
        $studentId = $authUser ? (int)$authUser['id'] : 1;

        $body = $this->getJsonBody();

        try {
            $job = $this->printService->submitJob($studentId, $body);
            $this->success($job, 'Baskı talebiniz oluşturuldu. Gel-Al kodunuzla teslim alabilirsiniz.', 201);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/v1/print-jobs/{id}
     */
    public function show(mixed $id = null): void
    {
        $jobId = $this->getParamInt($id, 'id');
        if ($jobId <= 0) {
            $this->error('Geçersiz baskı talep kimliği.', 400);
        }

        $job = $this->printJobModel->find($jobId);
        if (!$job) {
            $this->error('Baskı talebi bulunamadı.', 404);
        }

        $this->success($job, 'Baskı talebi detayları.');
    }

    /**
     * PUT /api/v1/print-jobs/{id}/status
     */
    public function updateStatus(mixed $id = null): void
    {
        $this->requireAuthUser();

        $jobId = $this->getParamInt($id, 'id');
        if ($jobId <= 0) {
            $this->error('Geçersiz baskı talep kimliği.', 400);
        }

        $body = $this->getJsonBody();
        $status = $body['status'] ?? '';

        $allowedStatuses = ['pending', 'printing', 'ready', 'delivered', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            $this->error("Geçersiz baskı durumu. İzin verilenler: " . implode(', ', $allowedStatuses), 422);
        }

        $this->printJobModel->updateStatus($jobId, $status);
        $job = $this->printJobModel->find($jobId);

        $this->success($job, "Baskı durumu '{$status}' olarak güncellendi.");
    }
}
