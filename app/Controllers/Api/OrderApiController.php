<?php

namespace App\Controllers\Api;

use App\Services\OrderService;
use App\Models\Order;
use Throwable;

class OrderApiController extends BaseApiController
{
    private OrderService $orderService;
    private Order $orderModel;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->orderModel = new Order();
    }

    /**
     * GET /api/v1/orders
     */
    public function index(): void
    {
        $authUser = $this->getAuthUser();

        $filters = [
            'store_id'     => !empty($_GET['store_id']) ? (int)$_GET['store_id'] : null,
            'order_status' => $_GET['status'] ?? null,
            'search'       => $_GET['search'] ?? null,
            'page'         => max(1, (int)($_GET['page'] ?? 1)),
            'per_page'     => min(100, max(1, (int)($_GET['per_page'] ?? 30)))
        ];

        // If regular student/customer, only list their own orders
        if ($authUser && in_array($authUser['role_slug'] ?? '', ['student', 'customer'])) {
            $filters['student_id'] = (int)$authUser['id'];
        } elseif (!empty($_GET['student_id'])) {
            $filters['student_id'] = (int)$_GET['student_id'];
        }

        $orders = $this->orderModel->getAll($filters);

        $this->success($orders, 'Siparişler başarıyla listelendi.');
    }

    /**
     * POST /api/v1/orders
     */
    public function store(): void
    {
        $authUser = $this->getAuthUser();
        $studentId = $authUser ? (int)$authUser['id'] : 1; // Default student ID fallback

        $body = $this->getJsonBody();

        if (empty($body['items']) || !is_array($body['items'])) {
            $this->error('Siparişte en az bir ürün bulunmalıdır.', 422);
        }

        try {
            $order = $this->orderService->createOrder($studentId, $body);

            $this->success($order, 'Siparişiniz başarıyla alındı. Kampüs kırtasiyesinden Gel-Al koduyla teslim alabilirsiniz.', 201);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/v1/orders/{id}
     */
    public function show(mixed $id = null): void
    {
        $orderId = $this->getParamInt($id, 'id');
        if ($orderId <= 0) {
            $this->error('Geçersiz sipariş kimliği.', 400);
        }

        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            $this->error('Sipariş bulunamadı.', 404);
        }

        $this->success($order, 'Sipariş detayları.');
    }

    /**
     * POST /api/v1/orders/verify-pickup
     */
    public function verifyPickup(): void
    {
        $body = $this->getJsonBody();
        $code = trim($body['pickup_code'] ?? $_GET['code'] ?? '');

        if (empty($code)) {
            $this->error('Gel-Al teslimat kodu girilmelidir.', 422);
        }

        $result = $this->orderService->verifyPickup($code);
        if (!$result) {
            $this->error('Bu koda ait aktif sipariş bulunamadı.', 404);
        }

        $this->success($result, 'Sipariş doğrulandı.');
    }

    /**
     * PUT /api/v1/orders/{id}/status
     */
    public function updateStatus(mixed $id = null): void
    {
        $this->requireAuthUser();
        $orderId = $this->getParamInt($id, 'id');
        if ($orderId <= 0) {
            $this->error('Geçersiz sipariş kimliği.', 400);
        }

        $body = $this->getJsonBody();
        $status = $body['status'] ?? '';

        $allowedStatuses = ['received', 'preparing', 'ready', 'delivered', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            $this->error("Geçersiz sipariş durumu. İzin verilenler: " . implode(', ', $allowedStatuses), 422);
        }

        try {
            if ($status === 'delivered') {
                $order = $this->orderService->deliverOrder($orderId, $body['payment_method'] ?? null);
            } elseif ($status === 'cancelled') {
                $order = $this->orderService->cancelOrder($orderId);
            } else {
                $paymentStatus = $body['payment_status'] ?? null;
                $this->orderModel->updateStatus($orderId, $status, $paymentStatus);
                $order = $this->orderModel->findById($orderId);
            }

            $this->success($order, "Sipariş durumu '{$status}' olarak güncellendi.");
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}
