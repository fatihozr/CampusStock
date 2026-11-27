<?php
// PHP Built-in Server Router for Local Testing
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Yolların doğru çalışması için
    if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
        return false; // Statik dosyaları (css, js, resim vs.) doğrudan sun
    }
}

// Tüm diğer istekleri index.php'ye yönlendir
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_GET['r'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
require_once __DIR__ . '/index.php';
