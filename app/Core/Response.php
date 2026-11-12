<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Tüm JSON yanıtları buradan çıkar: içerik türü, durum kodu ve önbellek
 * başlığı tek yerde yazılır. Hatalar her zaman {"hata": "..."} biçimindedir.
 */
final class Response
{
    public static function json(mixed $veri, int $kod = 200): never
    {
        http_response_code($kod);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store'); // kişisel veri tarayıcı önbelleğinde kalmasın

        echo json_encode(
            $veri,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
        );
        exit;
    }

    /** @param array<string, mixed> $ek Örn. ['alanlar' => [...]] veya ['kod' => 'FARKLI_KIRTASIYE'] */
    public static function hata(string $mesaj, int $kod, array $ek = []): never
    {
        self::json(['hata' => $mesaj] + $ek, $kod);
    }

    /** Gövdesiz yanıt; örn. silme işlemlerinde 204. */
    public static function bos(int $kod = 204): never
    {
        http_response_code($kod);
        header('Cache-Control: no-store');
        exit;
    }
}
