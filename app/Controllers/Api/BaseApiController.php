<?php

namespace App\Controllers\Api;

class BaseApiController
{
    /**
     * Send standard JSON response
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Standard Success Response
     */
    protected function success(mixed $data = null, string $message = 'İşlem başarılı', int $statusCode = 200): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'timestamp' => date('c')
        ];

        $this->json($response, $statusCode);
    }

    /**
     * Standard Error Response
     */
    protected function error(string $message = 'Bir hata oluştu', int $statusCode = 400, mixed $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'timestamp' => date('c')
        ];

        $this->json($response, $statusCode);
    }

    /**
     * Parse incoming JSON request body or fallback to $_POST
     */
    protected function getJsonBody(): array
    {
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $_POST ?? [];
    }

    /**
     * Get validated integer from parameter or $_GET
     */
    protected function getParamInt(mixed $val, ?string $key = null): int
    {
        if ($val !== null && is_numeric($val)) {
            return (int)$val;
        }
        if ($key !== null && isset($_GET[$key]) && is_numeric($_GET[$key])) {
            return (int)$_GET[$key];
        }
        return 0;
    }

    /**
     * Extract Bearer token from HTTP Authorization header
     */
    protected function getBearerToken(): ?string
    {
        $headers = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        if (!empty($headers) && preg_match('/Bearer\s(\S+)/i', $headers, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get authenticated user via Token or Session
     */
    protected function getAuthUser(): ?array
    {
        $token = $this->getBearerToken();
        if ($token) {
            $tokenModel = new \App\Models\ApiToken();
            $user = $tokenModel->findUserByToken($token);
            if ($user) {
                return $user;
            }
        }

        // Fallback to active PHP Session
        if (\App\Helpers\Auth::check()) {
            return \App\Helpers\Auth::user();
        }

        return null;
    }

    /**
     * Require authenticated user, exit with 401 if unauthorized
     */
    protected function requireAuthUser(): array
    {
        $user = $this->getAuthUser();
        if (!$user) {
            $this->error('Yetkisiz erişim. Lütfen geçerli bir Bearer token veya oturum ile giriş yapınız.', 401);
        }
        return $user;
    }
}

