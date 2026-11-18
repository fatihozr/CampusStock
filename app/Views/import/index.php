<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-3 p-md-4">

            <!-- Flash Messages -->
            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 p-3 mb-4 border-0 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-4 text-success flex-shrink-0"></i>
                    <div><?= e($flashSuccess) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 p-3 mb-4 border-0 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-danger flex-shrink-0"></i>
                    <div><?= e($flashError) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>

            <!-- Page Title -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-excel-fill text-success fs-3"></i>
                        Excel İçe Aktarma
                    </h4>
                    <p class="text-muted mb-0 fs-7">Excel dosyanızı yükleyin. Sistem sütunları ve verileri otomatik olarak analiz eder.</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="<?= base_url('import/template?type=stock&format=xlsx') ?>" class="btn btn-outline-warning text-dark btn-sm rounded-2 d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-excel me-1"></i> Stok Şablonu (.XLSX)
                    </a>
                    <a href="<?= base_url('import/template?type=personnel&format=xlsx') ?>" class="btn btn-outline-primary btn-sm rounded-2 d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-excel me-1"></i> Personel Şablonu (.XLSX)
                    </a>
                </div>
            </div>

            <!-- STEP 1: TEK AKIŞ DOSYA YÜKLEME & ANALİZ -->
            <?php if ($step === 1): ?>
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-9 col-xl-8">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <form action="<?= base_url('import/upload') ?>" method="POST" enctype="multipart/form-data">
                                <div class="card-body p-4 p-md-5">
                                    
                                    <!-- Veri Türü Seçimi (Pill Badges) -->
                                    <div class="mb-4 text-center">
                                        <label class="form-label fw-bold text-dark fs-6 d-block mb-3">Aktarılacak Veri Türü</label>
                                        <div class="d-inline-flex gap-2 p-1 bg-light rounded-pill border">
                                            <input type="radio" class="btn-check" name="import_type" id="type_stk" value="stock" checked>
                                            <label class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold fs-7" for="type_stk">
                                                <i class="bi bi-boxes me-1"></i> Stok Kalemleri
                                            </label>

                                            <input type="radio" class="btn-check" name="import_type" id="type_pers" value="personnel">
                                            <label class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold fs-7" for="type_pers">
                                                <i class="bi bi-people me-1"></i> Personel
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Sürükle Bırak / Dosya Yükleme Alanı -->
                                    <div class="drop-zone p-5 text-center border-2 border-dashed rounded-4 mb-4 bg-light transition-all cursor-pointer position-relative" 
                                         id="dropZone"
                                         style="border-color: #cbd5e1; background-color: #f8fafc;"
                                         onclick="document.getElementById('excel_file').click();">
                                        
                                        <div class="mb-3">
                                            <i class="bi bi-cloud-arrow-up-fill display-3 text-primary"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-2">Excel dosyanızı buraya sürükleyin</h5>
                                        <p class="text-muted fs-7 mb-3">veya bilgisayarınızdan bir dosya seçin</p>

                                        <label for="excel_file" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm cursor-pointer" onclick="event.stopPropagation();">
                                            <i class="bi bi-folder2-open me-1"></i> Dosya Seç
                                        </label>

                                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" class="d-none" required onchange="handleFileSelected(this)">

                                        <div class="mt-3 text-muted fs-8">
                                            Desteklenen formatlar: <strong>.xlsx, .xls, .csv</strong>
                                        </div>
                                    </div>

                                    <!-- Seçilen Dosya Detayı -->
                                    <div id="fileDetailsCard" class="card border border-success-subtle bg-success-subtle rounded-3 p-3 mb-4 d-none">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <i class="bi bi-file-earmark-excel-fill text-success fs-2"></i>
                                                <div>
                                                    <h6 class="fw-bold text-dark mb-0" id="fileNameText">-</h6>
                                                    <small class="text-muted" id="fileSizeText">0 KB</small>
                                                </div>
                                            </div>
                                            <span class="badge bg-success rounded-pill px-3 py-2 fs-8"><i class="bi bi-check-lg me-1"></i> Dosya Hazır</span>
                                        </div>
                                    </div>

                                    <!-- Gönder Butonu -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill fw-bold shadow-sm py-3 fs-6">
                                            <i class="bi bi-lightning-charge-fill me-1"></i> Dosyayı Otomatik Analiz Et
                                        </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <!-- STEP 2: OTOMATİK DOSYA ANALİZİ VE AKTARIM ONAYI -->
            <?php elseif ($step === 2): ?>
                <?php 
                    $validCount   = count($validationResult['valid'] ?? []);
                    $invalidCount = count($validationResult['invalid'] ?? []);
                    $mappedCount  = count(array_filter($userMapping ?? []));
                    $totalTarget  = count($targetFields ?? []);
                    
                    // Mandatory fields check (e.g. 'code', 'name' for stock)
                    $requiredFields = \App\Models\Import::getRequiredFields($type ?? 'stock');
                    $hasAllRequiredMapped = true;
                    foreach ($requiredFields as $reqKey) {
                        if (!isset($userMapping[$reqKey]) || $userMapping[$reqKey] === '') {
                            $hasAllRequiredMapped = false;
                            break;
                        }
                    }

                    // A file is ready for smooth import if all valid rows pass and mandatory fields are mapped
                    $isFullyReady = ($invalidCount === 0 && $hasAllRequiredMapped);
                ?>

                <!-- 1. DOSYA ANALİZ ÖZET KARTI -->
                <?php if ($isFullyReady): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-success-subtle border-start border-success border-4">
                        <div class="card-body p-4 d-md-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                <div class="bg-success text-white p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-check-circle-fill fs-2"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-success mb-1">Dosyanız Başarıyla Analiz Edildi</h5>
                                    <div class="d-flex flex-wrap gap-3 text-secondary fs-7 fw-medium">
                                        <span>🟢 <strong><?= $totalRows ?> kayıttan <?= $validCount ?> kayıt hazır</strong></span>
                                        <span>&bull;</span>
                                        <span>🟢 <strong>Tüm sütunlar otomatik eşleştirildi</strong></span>
                                    </div>
                                </div>
                            </div>
                            <form action="<?= base_url('import/process') ?>" method="POST" class="m-0">
                                <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill fw-bold shadow">
                                    <i class="bi bi-rocket-takeoff-fill me-2"></i> <?= $validCount ?> Kaydı Sisteme Aktar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-warning-subtle border-start border-warning border-4">
                        <div class="card-body p-4 d-md-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
                                <div class="bg-warning text-dark p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                    <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">Dosya Analizi Tamamlandı</h5>
                                    <div class="d-flex flex-wrap gap-3 text-dark fs-7 fw-medium">
                                        <span>🟢 <strong><?= $totalRows ?> kayıttan <?= $validCount ?> kayıt hazır</strong></span>
                                        <?php if ($invalidCount > 0): ?>
                                            <span>&bull;</span>
                                            <span>🔴 <strong class="text-danger"><?= $invalidCount ?> kayıtta eksik/hatalı bilgi var</strong></span>
                                        <?php endif; ?>
                                        <?php if (!$hasAllRequiredMapped): ?>
                                            <span>&bull;</span>
                                            <span>⚠️ <strong class="text-warning-emphasis">Zorunlu sütun eşleşmesi için kontrol gerekebilir</strong></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($validCount > 0): ?>
                                <form action="<?= base_url('import/process') ?>" method="POST" class="m-0">
                                    <button type="submit" class="btn btn-success btn-lg px-4 rounded-pill fw-bold shadow">
                                        <i class="bi bi-rocket-takeoff-fill me-2"></i> <?= $validCount ?> Geçerli Kaydı Aktar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2. HATALI SATIR DETAYLARI (Varsa) -->
                <?php if ($invalidCount > 0): ?>
                    <div class="card border border-danger-subtle rounded-4 shadow-sm mb-4">
                        <div class="card-header bg-danger text-white py-3 rounded-top-4 d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-octagon-fill me-2"></i>İnceleme Gerektiren Kayıtlar (<?= $invalidCount ?> Kayıt)</h6>
                            <span class="badge bg-white text-danger fw-bold">Bu satırlar aktarılmayacaktır</span>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0 fs-7">
                                <thead class="table-danger">
                                    <tr>
                                        <th style="width: 90px;" class="ps-3">Satır No</th>
                                        <th style="width: 320px;">Açıklama</th>
                                        <th>Satır Verisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($validationResult['invalid'] as $inv): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-danger">Satır <?= $inv['row_number'] ?></td>
                                            <td class="text-danger fw-semibold"><?= implode(', ', $inv['errors']) ?></td>
                                            <td class="text-muted font-monospace fs-8"><?= implode(' | ', array_filter($inv['data'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3. İLK 10 KAYIT ÖNİZLEMESİ -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-eye-fill text-primary fs-5"></i>
                            Veri Önizleme
                        </h6>
                        <span class="badge bg-light text-secondary border px-3 py-2 fs-8">Toplam <?= $totalRows ?> Kayıt</span>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover align-middle mb-0 fs-7">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 50px;">#</th>
                                    <?php foreach ($targetFields as $key => $label): ?>
                                        <th><?= e(explode(' ', $label)[0]) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($validationResult['valid'])): ?>
                                    <?php foreach (array_slice($validationResult['valid'], 0, 10) as $idx => $valItem): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-muted"><?= $idx + 1 ?></td>
                                            <?php foreach ($targetFields as $key => $label): ?>
                                                <td class="text-dark fw-medium"><?= e($valItem['data'][$key] ?? '-') ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">Geçerli önizleme kaydı bulunamadı.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light py-2 text-center text-muted fs-8">
                        🟢 <?= $validCount ?> kaydın verileri kendi sistem formatımıza başarıyla dönüştürüldü.
                    </div>
                </div>

                <!-- 4. GİZLENEBİLİR MANUEL KOLON EŞLEŞTİRME ACCORDION -->
                <div class="accordion mb-4" id="accordionMapping">
                    <div class="accordion-item border-0 shadow-sm rounded-4 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?= $isFullyReady ? 'collapsed bg-white' : 'bg-warning-subtle text-dark fw-bold' ?> py-3" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseMapping">
                                <i class="bi bi-sliders me-2 text-primary"></i>
                                <span>Gelişmiş Sütun Ayarları <?= $isFullyReady ? '(🟢 Tüm Sütunlar Otomatik Eşleşti)' : '(⚠️ Sütun Seçimi Gerekebilir)' ?></span>
                            </button>
                        </h2>
                        <div id="collapseMapping" class="accordion-collapse collapse <?= $allColsRecognized ? '' : 'show' ?>" data-bs-parent="#accordionMapping">
                            <div class="accordion-body p-4 bg-light">
                                <form action="<?= base_url('import/preview') ?>" method="POST">
                                    <p class="text-muted fs-7 mb-3">Sistem Excel'deki sütunları otomatik eşleştirmiştir. Farklı bir sütun seçmek isterseniz aşağıdan değiştirebilirsiniz:</p>
                                    
                                    <div class="row g-3 mb-3">
                                        <?php foreach ($targetFields as $fieldKey => $fieldLabel): ?>
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <label class="form-label fw-semibold text-secondary fs-8 mb-1"><?= e($fieldLabel) ?></label>
                                                <select name="mapping[<?= $fieldKey ?>]" class="form-select form-select-sm border-secondary-subtle">
                                                    <option value="">-- Otomatik Seçim / Eşleştirilmedi --</option>
                                                    <?php foreach ($headers as $hIdx => $hName): ?>
                                                        <?php 
                                                            $selectedIdx = $userMapping[$fieldKey] ?? \App\Models\Import::guessMapping($fieldKey, $headers);
                                                            $isSelected = ((string)$selectedIdx === (string)$hIdx);
                                                        ?>
                                                        <option value="<?= $hIdx ?>" <?= $isSelected ? 'selected' : '' ?>>
                                                            "<?= e($hName) ?>"
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">
                                            <i class="bi bi-arrow-repeat me-1"></i> Ayarları Yeniden Uygula
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- STEP 4: AKTARIM BAŞARILI SONUÇ EKRANI -->
            <?php elseif ($step === 4): ?>
                <div class="row justify-content-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="card border-0 shadow rounded-4 text-center p-5">
                            <div class="display-1 text-success mb-3">🎉</div>
                            <h3 class="fw-bold text-dark mb-2">Excel Aktarımı Başarılı</h3>
                            <p class="text-secondary fs-6 mb-4">
                                <strong><?= $importedCount ?></strong> adet <?= e($type === 'stock' ? 'stok' : ($type === 'inventory' ? 'envanter' : 'personel')) ?> kaydı başarıyla sisteme aktarıldı.
                            </p>

                            <div class="d-inline-flex flex-wrap gap-2 justify-content-center mb-4 p-3 bg-light rounded-pill border">
                                <span class="badge bg-success-subtle text-success fs-7 px-3 py-2 rounded-pill">Eklenen: <?= $importedCount ?></span>
                                <span class="badge bg-secondary-subtle text-secondary fs-7 px-3 py-2 rounded-pill">Atlanan: <?= $invalidCount ?></span>
                                <span class="badge bg-danger-subtle text-danger fs-7 px-3 py-2 rounded-pill">Hatalı: <?= $invalidCount ?></span>
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <?php if ($type === 'stock'): ?>
                                    <a href="<?= base_url('products') ?>" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow">
                                        <i class="bi bi-boxes me-1"></i> Stokları Görüntüle
                                    </a>
                                <?php elseif ($type === 'inventory'): ?>
                                    <a href="<?= base_url('inventory') ?>" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow">
                                        <i class="bi bi-laptop me-1"></i> Envanteri Görüntüle
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url('personnel') ?>" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold shadow">
                                        <i class="bi bi-people me-1"></i> Personelleri Görüntüle
                                    </a>
                                <?php endif; ?>

                                <a href="<?= base_url('import') ?>" class="btn btn-outline-secondary btn-lg rounded-pill px-4 fw-bold">
                                    <i class="bi bi-arrow-repeat me-1"></i> Yeni Excel Yükle
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<script>
function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileNameText = document.getElementById('fileNameText');
        const fileSizeText = document.getElementById('fileSizeText');
        const fileDetailsCard = document.getElementById('fileDetailsCard');

        if (fileNameText) fileNameText.innerText = file.name;
        if (fileSizeText) fileSizeText.innerText = (file.size / 1024).toFixed(1) + ' KB';
        if (fileDetailsCard) fileDetailsCard.classList.remove('d-none');
    }
}
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
