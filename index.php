<?php

/**
 * CampusStock — Üniversite Kırtasiye Stok & Satış Platformu
 * Front Controller (Giriş Noktası)
 */

// OWASP Temel Güvenlik Başlıkları
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

// Autoloader setup
$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    // PSR-4 Autoload Fallback when vendor directory is not generated yet via composer
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/app/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });

    // Helper functions fallback load
    $helpersFile = __DIR__ . '/app/Helpers/functions.php';
    if (file_exists($helpersFile)) {
        require_once $helpersFile;
    }
}

// Initialize Application Configuration & Environment
\App\Config\App::init();

$secure = filter_var(\App\Config\App::get('SESSION_SECURE', false), FILTER_VALIDATE_BOOLEAN) 
          || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => \App\Config\App::get('SESSION_LIFETIME', 7200),
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['active_module'])) {
    $_SESSION['active_module'] = 'stationery';
}

// Load Web Routes
require_once __DIR__ . '/routes/web.php';

// Dispatch Requested Route
\App\Config\Router::dispatch();
