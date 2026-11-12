<?php

/**
 * Front controller: uygulamanın tek giriş noktası.
 *
 * Tarayıcıdan gelen her istek (statik dosyalar hariç) bu dosyaya düşer.
 * app/ klasörü public/ dışında olduğu için dışarıdan doğrudan açılamaz.
 */

declare(strict_types=1);

define('KOK', dirname(__DIR__));

// Hata ayrıntısı kullanıcıya gösterilmez, yalnızca sunucu günlüğüne yazılır
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// App\Core\Env -> app/Core/Env.php
spl_autoload_register(static function (string $sinif): void {
    $onek = 'App\\';
    if (!str_starts_with($sinif, $onek)) {
        return;
    }
    $dosya = KOK . '/app/' . str_replace('\\', '/', substr($sinif, strlen($onek))) . '.php';
    if (is_file($dosya)) {
        require $dosya;
    }
});

App\Core\Env::yukle(KOK . '/.env');

$router = new App\Core\Router();
(require KOK . '/config/routes.php')($router);
$router->calistir(App\Core\Request::yakala());
