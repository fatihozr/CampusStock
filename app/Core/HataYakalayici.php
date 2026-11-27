<?php

declare(strict_types=1);

namespace App\Core;

use ErrorException;
use Throwable;

/**
 * Yakalanmamış her hatayı tek yerde karşılar.
 *
 * Ayrıntı (sınıf, mesaj, dosya, satır) yalnızca sunucu günlüğüne yazılır;
 * kullanıcı her zaman aynı genel mesajı görür. Böylece veritabanı adı,
 * dosya yolu veya anahtar gibi bilgiler dışarı sızmaz (OWASP: güvenlik
 * yanlış yapılandırması).
 */
final class HataYakalayici
{
    public const GENEL_MESAJ = 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.';

    public static function kaydet(): void
    {
        // Uyarı ve bildirimler de hata sayılır; sessizce yanlış çalışmaya devam edilmez
        set_error_handler(static function (int $seviye, string $mesaj, string $dosya, int $satir): bool {
            if (!(error_reporting() & $seviye)) {
                return false; // @ ile susturulmuş satırlar
            }
            throw new ErrorException($mesaj, 0, $seviye, $dosya, $satir);
        });

        set_exception_handler(static function (Throwable $hata): void {
            self::gunlugeYaz($hata);
            self::yanitla();
        });

        // Bellek aşımı gibi ölümcül hatalar exception handler'a düşmez
        register_shutdown_function(static function (): void {
            $son = error_get_last();
            if ($son !== null && in_array($son['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                error_log(sprintf('[ÖLÜMCÜL] %s (%s:%d)', $son['message'], $son['file'], $son['line']));
                self::yanitla();
            }
        });
    }

    private static function gunlugeYaz(Throwable $hata): void
    {
        // İstek gövdesi bilerek yazılmaz: parola veya kart bilgisi içerebilir
        error_log(sprintf(
            '[HATA] %s %s | %s: %s (%s:%d)',
            $_SERVER['REQUEST_METHOD'] ?? '-',
            parse_url($_SERVER['REQUEST_URI'] ?? '-', PHP_URL_PATH),
            $hata::class,
            $hata->getMessage(),
            $hata->getFile(),
            $hata->getLine()
        ));
    }

    private static function yanitla(): void
    {
        if (headers_sent()) {
            return; // Yanıtın bir kısmı zaten gitti; üzerine başlık yazılamaz
        }
        while (ob_get_level() > 0) {
            ob_end_clean(); // Yarım kalmış çıktıyı at
        }

        $yol = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($yol === '/api' || str_starts_with($yol, '/api/')) {
            Response::hata(self::GENEL_MESAJ, 500);
        }

        // Sayfa istekleri için geçici sade sayfa; ana yerleşim gelince errors/500.php kullanılacak
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        echo '<!doctype html><html lang="tr"><meta charset="utf-8"><title>Hata</title><h1>'
            . self::GENEL_MESAJ . '</h1></html>';
        exit;
    }
}
