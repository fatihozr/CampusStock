<?php

/**
 * Belediye Destek Birimi Envanter ve Stok Takip Sistemi
 * Front Controller (Giriş Noktası)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Load Web Routes
require_once __DIR__ . '/routes/web.php';

// Dispatch Requested Route
\App\Config\Router::dispatch();
