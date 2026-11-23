<?php

declare(strict_types=1);

namespace App\Core;

/**
 * .env dosyasını kütüphane kullanmadan okur.
 *
 * Her "AD=deger" satırı $_ENV dizisine yazılır. Boş satırlar ve # ile
 * başlayan yorumlar atlanır. Sunucuda (örn. Render) ortam değişkeni zaten
 * tanımlıysa .env'deki değer onun üzerine yazılmaz.
 */
final class Env
{
    public static function yukle(string $dosya): void
    {
        if (!is_file($dosya)) {
            return; // Canlıda değerler .env yerine sunucunun ortam değişkenlerinden gelir
        }

        $satirlar = file($dosya, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($satirlar as $satir) {
            $satir = trim($satir);
            if ($satir === '' || str_starts_with($satir, '#') || !str_contains($satir, '=')) {
                continue;
            }

            [$ad, $deger] = explode('=', $satir, 2);
            $ad = trim($ad);
            $deger = trim(trim($deger), "\"'");

            if (getenv($ad) === false && !array_key_exists($ad, $_ENV)) {
                $_ENV[$ad] = $deger;
            }
        }
    }

    /** Değişkeni önce $_ENV'den, yoksa sunucu ortamından okur; boşsa varsayılanı döner. */
    public static function al(string $ad, ?string $varsayilan = null): ?string
    {
        $deger = $_ENV[$ad] ?? getenv($ad);

        return ($deger === false || $deger === '') ? $varsayilan : (string) $deger;
    }
}
