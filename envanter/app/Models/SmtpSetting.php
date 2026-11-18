<?php

namespace App\Models;

class SmtpSetting
{
    public function __construct()
    {
        if (!isset($_SESSION['mock_smtp_settings'])) {
            $_SESSION['mock_smtp_settings'] = [];
        }
    }

    /**
     * Get saved SMTP configuration from session database
     */
    public function get(): array
    {
        return $_SESSION['mock_smtp_settings'] ?? [];
    }

    /**
     * Store or update SMTP configuration securely in session database
     */
    public function store(array $data): array
    {
        $existingPassword = $_SESSION['mock_smtp_settings']['smtp_password'] ?? '';
        $newPassword = trim($data['smtp_password'] ?? '');

        $_SESSION['mock_smtp_settings'] = [
            'smtp_host'         => trim($data['smtp_host'] ?? ''),
            'smtp_port'         => (int)($data['smtp_port'] ?? 587),
            'smtp_username'     => trim($data['smtp_username'] ?? ''),
            // Preserve existing password if user submits blank/stars
            'smtp_password'     => (!empty($newPassword) && $newPassword !== '********') ? $newPassword : $existingPassword,
            'smtp_encryption'   => strtolower(trim($data['smtp_encryption'] ?? 'tls')),
            'smtp_from_address' => trim($data['smtp_from_address'] ?? 'envanter@belediye.bel.tr'),
            'smtp_from_name'    => trim($data['smtp_from_name'] ?? 'T.C. Belediye Envanter Sistem Raporu'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        return $_SESSION['mock_smtp_settings'];
    }
}
