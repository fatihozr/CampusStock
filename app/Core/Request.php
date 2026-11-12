<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Gelen HTTP isteğini tek nesnede toplar: metot, yol, sorgu parametreleri,
 * gövde (JSON veya form), başlıklar ve router'ın yakaladığı {id} değerleri.
 */
final class Request
{
    /** @var array<string, int> */
    private array $parametreler = [];

    /**
     * @param array<string, mixed>  $sorgu
     * @param array<string, mixed>  $govde
     * @param array<string, string> $basliklar
     */
    public function __construct(
        public readonly string $metot,
        public readonly string $yol,
        private readonly array $sorgu = [],
        private readonly array $govde = [],
        private readonly array $basliklar = [],
        public readonly bool $govdeHatali = false,
    ) {
    }

    public static function yakala(): self
    {
        $metot = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // "/api/cart/" ile "/api/cart" aynı yol sayılır
        $yol = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $yol = '/' . trim($yol, '/');

        $govde = $_POST;
        $govdeHatali = false;
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $ham = (string) file_get_contents('php://input');
            $cozulmus = json_decode($ham, true);
            $govde = is_array($cozulmus) ? $cozulmus : [];
            $govdeHatali = $ham !== '' && !is_array($cozulmus);
        }

        // HTTP_X_CSRF_TOKEN -> x-csrf-token
        $basliklar = [];
        foreach ($_SERVER as $ad => $deger) {
            if (str_starts_with($ad, 'HTTP_')) {
                $basliklar[strtolower(str_replace('_', '-', substr($ad, 5)))] = (string) $deger;
            }
        }

        return new self($metot, $yol, $_GET, $govde, $basliklar, $govdeHatali);
    }

    public function sorgu(string $ad, mixed $varsayilan = null): mixed
    {
        return $this->sorgu[$ad] ?? $varsayilan;
    }

    public function girdi(string $ad, mixed $varsayilan = null): mixed
    {
        return $this->govde[$ad] ?? $varsayilan;
    }

    /** @return array<string, mixed> */
    public function tumGirdi(): array
    {
        return $this->govde;
    }

    public function baslik(string $ad): ?string
    {
        return $this->basliklar[strtolower($ad)] ?? null;
    }

    public function parametre(string $ad): ?int
    {
        return $this->parametreler[$ad] ?? null;
    }

    /** @param array<string, int> $parametreler */
    public function parametreleriAyarla(array $parametreler): void
    {
        $this->parametreler = $parametreler;
    }

    public function apiMi(): bool
    {
        return $this->yol === '/api' || str_starts_with($this->yol, '/api/');
    }
}
