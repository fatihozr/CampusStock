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
    <div class="report-header d-flex align-items-center justify-content-between mb-4">
        <!-- Logo -->
        <div style="width: 150px; text-align: left; padding-left: 10px;">
            <img src="<?= base_url('public/assets/img/logo.jpg') ?>" alt="Stationery Flow" style="max-height: 85px; width: auto; object-fit: contain;">
        </div>
        <!-- Title Text -->
        <div class="text-center flex-grow-1 px-3">
            <h5 class="fw-bold mb-1" style="letter-spacing: 1px;">STATIONERY FLOW KURUMSAL ENVANTER YÖNETİMİ</h5>
            <h6 class="fw-semibold text-secondary mb-2">Destek ve İdari İşler Yönetimi</h6>
            <div class="bg-dark text-white d-inline-block px-4 py-1 rounded-pill mt-2">
                <h5 class="fw-bold mb-0" style="font-size: 1.1rem; letter-spacing: 0.5px;"><?= e($reportTitle ?? $title ?? 'SİSTEM RAPORU') ?></h5>
            </div>
        </div>
        <!-- Meta Data -->
        <div class="text-end text-muted small" style="width: 150px; font-size: 0.75rem;">
            <div><strong>Tarih:</strong> <?= e($generatedAt ?? date('d.m.Y')) ?></div>
            <div><strong>Saat:</strong> <?= date('H:i') ?></div>
            <div class="mt-2"><strong>Düzenleyen:</strong><br><?= e(($currentUser['first_name'] ?? 'Sistem') . ' ' . ($currentUser['last_name'] ?? 'Yöneticisi')) ?></div>
        </div>
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
                    <th>Daire Başkanlığı</th>
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
    <?php elseif ($activeModule === 'costs'): ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Sıra</th>
                    <th>Stok Kodu</th>
                    <th>Malzeme Adı</th>
                    <th>Kategori</th>
                    <th class="text-end">Mevcut Miktar</th>
                    <th class="text-end">Birim Fiyat</th>
                    <th class="text-end">Toplam Maliyet</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalVal = 0;
                if (!empty($stkList)): foreach ($stkList as $idx => $stk): 
                    $lineTotal = ($stk['current_stock'] ?? 0) * ($stk['unit_price'] ?? 0);
                    $totalVal += $lineTotal;
                ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="fw-bold"><?= e($stk['code'] ?? '') ?></td>
                        <td><?= e($stk['name'] ?? '') ?></td>
                        <td><?= e($stk['category_name'] ?? 'Genel') ?></td>
                        <td class="text-end fw-bold"><?= e(($stk['current_stock'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td class="text-end"><?= e(number_format((float)($stk['unit_price'] ?? 0), 2)) ?> TL</td>
                        <td class="text-end fw-bold text-primary"><?= e(number_format($lineTotal, 2)) ?> TL</td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="table-light fw-bold">
                        <td colspan="6" class="text-end">GENEL TOPLAM YATIRIM:</td>
                        <td class="text-end text-danger"><?= e(number_format($totalVal, 2)) ?> TL</td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted p-4">Kayıtlı stok verisi bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php elseif ($activeModule === 'consumption'): ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Sıra</th>
                    <th>Birim / Daire Başkanlığı</th>
                    <th>Stok Kodu</th>
                    <th>Malzeme Adı</th>
                    <th class="text-end">Toplam Tüketim</th>
                    <th class="text-end">Toplam Maliyet</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalConsumptionCost = 0;
                if (!empty($stkList)): foreach ($stkList as $idx => $stk): 
                    $totalConsumptionCost += ($stk['total_cost'] ?? 0);
                ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="fw-bold"><?= e($stk['department_name'] ?? 'Bilinmiyor') ?></td>
                        <td><?= e($stk['product_code'] ?? '') ?></td>
                        <td><?= e($stk['product_name'] ?? '') ?></td>
                        <td class="text-end fw-bold text-primary"><?= e(($stk['total_quantity'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td class="text-end fw-bold text-danger"><?= e(number_format((float)($stk['total_cost'] ?? 0), 2)) ?> TL</td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="table-light fw-bold">
                        <td colspan="5" class="text-end">GENEL TOPLAM TÜKETİM MALİYETİ:</td>
                        <td class="text-end text-danger"><?= e(number_format($totalConsumptionCost, 2)) ?> TL</td>
                    </tr>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted p-4">Tüketim kaydı bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php elseif ($activeModule === 'critical'): ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Sıra</th>
                    <th>Stok Kodu</th>
                    <th>Malzeme Adı</th>
                    <th>Kategori</th>
                    <th class="text-end">Mevcut Miktar</th>
                    <th class="text-end">Kritik Eşik</th>
                    <th class="text-end">Eksik Miktar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stkList)): foreach ($stkList as $idx => $stk): 
                    $fark = (int)($stk['min_stock'] ?? 0) - (int)($stk['current_stock'] ?? 0);
                ?>
                    <tr class="table-danger">
                        <td><?= $idx + 1 ?></td>
                        <td class="fw-bold"><?= e($stk['code'] ?? '') ?></td>
                        <td><?= e($stk['name'] ?? '') ?></td>
                        <td><?= e($stk['category_name'] ?? 'Genel') ?></td>
                        <td class="text-end fw-bold text-danger"><?= e(($stk['current_stock'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td class="text-end text-dark"><?= e(($stk['min_stock'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td class="text-end fw-bold text-danger">-<?= $fark ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted p-4">Kritik seviyede stok bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php elseif ($activeModule === 'movements'): ?>
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Sıra</th>
                    <th>Tarih</th>
                    <th>İşlem Tipi</th>
                    <th>Stok Kodu</th>
                    <th>Malzeme Adı</th>
                    <th>Teslim Eden</th>
                    <th>Teslim Alan</th>
                    <th class="text-end">Miktar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stkList)): foreach ($stkList as $idx => $stk): 
                    $performedBy = trim(($stk['first_name'] ?? '') . ' ' . ($stk['last_name'] ?? ''));
                    $deptName = $stk['department_name'] ?? '-';
                    if ($stk['type'] === 'in') {
                        $teslimEden = '-';
                        $teslimAlan = $performedBy;
                    } else {
                        $teslimEden = $performedBy;
                        $teslimAlan = $deptName;
                    }
                ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($stk['created_at'])) ?></td>
                        <td class="fw-bold <?= $stk['type'] === 'in' ? 'text-success' : 'text-danger' ?>">
                            <?= $stk['type'] === 'in' ? 'GİRİŞ' : 'ÇIKIŞ' ?>
                        </td>
                        <td><?= e($stk['product_code'] ?? '') ?></td>
                        <td><?= e($stk['product_name'] ?? '') ?></td>
                        <td><?= e($teslimEden) ?></td>
                        <td><?= e($teslimAlan) ?></td>
                        <td class="text-end fw-bold <?= $stk['type'] === 'in' ? 'text-success' : 'text-danger' ?>">
                            <?= $stk['type'] === 'in' ? '+' : '-' ?><?= e($stk['quantity'] ?? 0) ?>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center text-muted p-4">Hareket kaydı bulunamadı.</td></tr>
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
                    <th>Birim Fiyat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stkList)): foreach ($stkList as $idx => $stk): ?>
                    <tr>
                        <td><?= $idx + 1 ?></td>
                        <td class="fw-bold"><?= e($stk['code'] ?? '') ?></td>
                        <td><?= e($stk['name'] ?? '') ?></td>
                        <td><?= e($stk['category_name'] ?? 'Genel') ?></td>
                        <td class="fw-bold"><?= e(($stk['current_stock'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td><?= e(($stk['min_stock'] ?? 0) . ' ' . ($stk['unit'] ?? '')) ?></td>
                        <td><?= e(number_format((float)($stk['unit_price'] ?? 0), 2)) ?> TL</td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted p-4">Kayıtlı stok verisi bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Signature Footer -->
    <div class="row mt-5 pt-4">
        <div class="col-6 ps-4 text-center" style="max-width: 300px;">
            <p class="mb-1 fw-bold text-dark" style="font-size: 0.95rem;">Destek ve İdari İşler Yöneticisi</p>
            <p class="text-muted mb-5" style="font-size: 0.8rem;">Onay</p>
            <div class="border-top pt-2 text-muted mx-auto" style="width: 180px; font-size: 0.8rem; letter-spacing: 0.5px;">Mühür / İmza</div>
        </div>
    </div>

</body>
</html>
