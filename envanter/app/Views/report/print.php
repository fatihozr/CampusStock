<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= e($reportTitle ?? $title ?? 'Sistem Raporu') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; color: #1e293b; background: #fff; }
        .report-header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .table th { background-color: #f1f5f9 !important; font-size: 0.8rem; text-transform: uppercase; }
        .table td { font-size: 0.825rem; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body class="p-4" onload="window.print()">

    <!-- Printable Header Bar -->
    <div class="no-print d-flex justify-content-between align-items-center bg-light p-3 rounded mb-4 border">
        <div>
            <strong>Resmi Yazıcı Çıktı Görünümü</strong> (PDF olarak kaydetmek için Ctrl+P basınız)
        </div>
        <div>
            <button class="btn btn-primary btn-sm me-2" onclick="window.print()"><i class="bi bi-printer"></i> Yazdır / PDF İndir</button>
            <button class="btn btn-secondary btn-sm" onclick="window.close()"><i class="bi bi-x-lg"></i> Pencereyi Kapat</button>
        </div>
    </div>

    <!-- Official Document Header -->
    <div class="report-header text-center">
        <h5 class="fw-bold mb-1">T.C. BELEDİYE BAŞKANLIĞI</h5>
        <h6 class="fw-semibold text-secondary mb-2">Destek Hizmetleri Birimi - Stok ve Dağıtım Yönetim Sistemi</h6>
        <h5 class="fw-bold text-primary mb-0 mt-3"><?= e($reportTitle ?? $title ?? 'Sistem Raporu') ?></h5>
        <div class="text-muted small mt-1">Rapor Tarihi: <strong><?= e($generatedAt ?? date('d.m.Y H:i')) ?></strong> &middot; Düzenleyen: <strong><?= e(($currentUser['first_name'] ?? 'Sistem') . ' ' . ($currentUser['last_name'] ?? 'Yöneticisi')) ?> (<?= e($currentUser['title'] ?? 'Yetkili') ?>)</strong></div>
    </div>

    <!-- Content Table Based on Module -->
    <?php 
    $activeModule = $module ?? $type ?? 'stocks';
    $stkList = $stocks ?? (is_array($reportData ?? null) ? $reportData : []);
    $prsList = $personnel ?? (is_array($reportData ?? null) ? $reportData : []);
    ?>

    <?php if ($activeModule === 'personnel'): ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Sıra</th>
                    <th>Sicil No</th>
                    <th>Ad Soyad</th>
                    <th>Unvan</th>
                    <th>Müdürlük</th>
                    <th>Birim</th>
                    <th>E-Posta</th>
                    <th>Telefon</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prsList)): foreach ($prsList as $idx => $prs): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="fw-bold"><?= e($prs['sicil_no'] ?? '') ?></td>
                        <td><?= e($prs['full_name'] ?? $prs['name'] ?? '') ?></td>
                        <td><?= e($prs['title'] ?? '') ?></td>
                        <td><?= e($prs['department'] ?? '') ?></td>
                        <td><?= e($prs['unit'] ?? '') ?></td>
                        <td><?= e($prs['email'] ?? '') ?></td>
                        <td><?= e($prs['phone'] ?? '') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center text-muted p-4">Kayıtlı personel verisi bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php else: ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Sıra</th>
                    <th>Stok Kodu</th>
                    <th>Malzeme Adı</th>
                    <th>Kategori</th>
                    <th>Mevcut Miktar</th>
                    <th>Kritik Eşik</th>
                    <th>Depo</th>
                    <th>Raf / Lokasyon</th>
                    <th>Birim Fiyat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stkList)): foreach ($stkList as $idx => $stk): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="fw-bold"><?= e($stk['code'] ?? '') ?></td>
                        <td><?= e($stk['name'] ?? '') ?></td>
                        <td><?= e($stk['category'] ?? '') ?></td>
                        <td class="fw-bold"><?= e(($stk['quantity'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td><?= e(($stk['min_threshold'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td><?= e($stk['warehouse'] ?? '') ?></td>
                        <td><strong><?= e($stk['rack_location'] ?? 'C31') ?></strong></td>
                        <td><?= e(number_format((float)($stk['unit_price'] ?? 0), 2)) ?> TL</td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="9" class="text-center text-muted p-4">Kayıtlı stok verisi bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Signature Footer -->
    <div class="row mt-5 pt-4 text-center">
        <div class="col-4">
            <p class="mb-1 fw-bold">Düzenleyen</p>
            <p class="text-muted small"><?= e(($currentUser['first_name'] ?? 'Sistem') . ' ' . ($currentUser['last_name'] ?? 'Yöneticisi')) ?></p>
            <div class="mt-4 border-top pt-1 text-muted small">İmza</div>
        </div>
        <div class="col-4">
            <p class="mb-1 fw-bold">Depo / Ambar Sorumlusu</p>
            <p class="text-muted small">Ayşe KAYA</p>
            <div class="mt-4 border-top pt-1 text-muted small">İmza</div>
        </div>
        <div class="col-4">
            <p class="mb-1 fw-bold">Destek Hizmetleri Müdürü</p>
            <p class="text-muted small">Onay</p>
            <div class="mt-4 border-top pt-1 text-muted small">Mühür / İmza</div>
        </div>
    </div>

</body>
</html>
