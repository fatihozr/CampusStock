<?php
namespace App\Controllers;

use App\Helpers\Auth;

class ModuleController
{
    public function switch(): void
    {
        Auth::requireAuth();

        $module = $_GET['m'] ?? 'cleaning';
        $allowedModules = ['cleaning', 'furniture'];

        if (in_array($module, $allowedModules, true)) {
            $_SESSION['active_module'] = $module;
        }

        // Redirect back or to dashboard
        $referer = $_SERVER['HTTP_REFERER'] ?? base_url('dashboard');
        if (!headers_sent()) {
            header('Location: ' . $referer);
        }
        exit;
    }
}
