<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

/** Uygulamanın ayakta olduğunu gösterir; canlıda sağlık kontrolü adresi olarak kullanılır. */
final class SaglikController
{
    public function goster(Request $istek): void
    {
        Response::json(['durum' => 'çalışıyor']);
    }
}
