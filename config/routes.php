<?php

declare(strict_types=1);

/**
 * Tüm adreslerin listesi. Her satır: metot, yol, [Controller, metot], [ara katmanlar].
 */

use App\Controllers\SaglikController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/api/saglik', [SaglikController::class, 'goster']);
};
