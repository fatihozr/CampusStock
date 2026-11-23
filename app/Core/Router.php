<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Yol ve metoda göre isteği doğru controller metoduna yönlendirir.
 *
 * Kayıt: $router->get('/api/orders/{id}', [SiparisController::class, 'detay'], [AuthMiddleware::class]);
 * {ad} biçimindeki parçalar yalnızca pozitif tam sayıyla eşleşir; "abc" veya "0" gelirse 404 olur.
 * Yol var ama metot uymuyorsa 405 ve Allow başlığı döner.
 */
final class Router
{
    /** @var list<array{metot: string, yol: string, hedef: array{0: class-string, 1: string}, ara: list<class-string>}> */
    private array $rotalar = [];

    public function get(string $yol, array $hedef, array $araKatmanlar = []): void
    {
        $this->ekle('GET', $yol, $hedef, $araKatmanlar);
    }

    public function post(string $yol, array $hedef, array $araKatmanlar = []): void
    {
        $this->ekle('POST', $yol, $hedef, $araKatmanlar);
    }

    public function put(string $yol, array $hedef, array $araKatmanlar = []): void
    {
        $this->ekle('PUT', $yol, $hedef, $araKatmanlar);
    }

    public function delete(string $yol, array $hedef, array $araKatmanlar = []): void
    {
        $this->ekle('DELETE', $yol, $hedef, $araKatmanlar);
    }

    /**
     * Saf eşleştirme: yan etkisi yoktur, bu yüzden birim testle denenebilir.
     *
     * @return array{durum: 200, rota: array, parametreler: array<string, int>}|array{durum: 404}|array{durum: 405, izinli: list<string>}
     */
    public function eslestir(string $metot, string $yol): array
    {
        $izinli = [];

        foreach ($this->rotalar as $rota) {
            $parametreler = self::yolEslesir($rota['yol'], $yol);
            if ($parametreler === null) {
                continue;
            }
            if ($rota['metot'] === $metot) {
                return ['durum' => 200, 'rota' => $rota, 'parametreler' => $parametreler];
            }
            $izinli[] = $rota['metot'];
        }

        return $izinli === []
            ? ['durum' => 404]
            : ['durum' => 405, 'izinli' => array_values(array_unique($izinli))];
    }

    public function calistir(Request $istek): void
    {
        $sonuc = $this->eslestir($istek->metot, $istek->yol);

        if ($sonuc['durum'] === 404) {
            Response::hata('Aradığınız sayfa bulunamadı.', 404);
        }
        if ($sonuc['durum'] === 405) {
            header('Allow: ' . implode(', ', $sonuc['izinli']));
            Response::hata('Bu adres ' . $istek->metot . ' isteğini desteklemiyor.', 405);
        }
        if ($istek->govdeHatali) {
            Response::hata('İstek gövdesi geçerli bir JSON değil.', 400);
        }

        $istek->parametreleriAyarla($sonuc['parametreler']);

        // Ara katmanlar (oturum, CSRF) sırayla çalışır; reddederlerse yanıtı kendileri yazar
        foreach ($sonuc['rota']['ara'] as $araKatman) {
            (new $araKatman())->handle($istek);
        }

        [$sinif, $metot] = $sonuc['rota']['hedef'];
        (new $sinif())->$metot($istek);
    }

    private function ekle(string $metot, string $yol, array $hedef, array $araKatmanlar): void
    {
        $this->rotalar[] = [
            'metot' => $metot,
            'yol'   => '/' . trim($yol, '/'),
            'hedef' => $hedef,
            'ara'   => $araKatmanlar,
        ];
    }

    /** @return array<string, int>|null Eşleşmezse null, eşleşirse yakalanan parametreler */
    private static function yolEslesir(string $desen, string $yol): ?array
    {
        $desenParcalari = explode('/', trim($desen, '/'));
        $yolParcalari = explode('/', trim($yol, '/'));

        if (count($desenParcalari) !== count($yolParcalari)) {
            return null;
        }

        $parametreler = [];
        foreach ($desenParcalari as $i => $parca) {
            if (preg_match('/^\{(\w+)\}$/', $parca, $eslesme) === 1) {
                if (preg_match('/^[1-9]\d{0,9}$/', $yolParcalari[$i]) !== 1) {
                    return null;
                }
                $parametreler[$eslesme[1]] = (int) $yolParcalari[$i];
            } elseif ($parca !== $yolParcalari[$i]) {
                return null;
            }
        }

        return $parametreler;
    }
}
