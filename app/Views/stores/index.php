<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid p-0">
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Üniversiteler & Kırtasiye Şubeleri</h4>
                    <p class="text-muted mb-0 fs-7">CampusStock sistemine bağlı üniversiteler ve kampüs kırtasiyeleri</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <button class="ds-btn ds-btn-outline" data-bs-toggle="modal" data-bs-target="#addUniversityModal">
                        <i class="bi bi-building-add"></i> Üniversite Ekle
                    </button>
                    <button class="ds-btn ds-btn-primary" data-bs-toggle="modal" data-bs-target="#addStoreModal">
                        <i class="bi bi-shop"></i> Kırtasiye Şubesi Ekle
                    </button>
                </div>
            </div>

            <?php if (!empty($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                    <?= e($_SESSION['flash_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Kırtasiye Şubeleri -->
                <div class="col-12 col-xl-7">
                    <div class="ds-card h-100">
                        <div class="ds-card-header">
                            <h5 class="ds-card-title mb-0"><i class="bi bi-shop me-2 text-primary"></i>Kampüs Kırtasiyeleri</h5>
                            <span class="ds-badge ds-badge-primary"><?= count($stores) ?> Şube</span>
                        </div>
                        <div class="ds-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kırtasiye Adı</th>
                                            <th>Üniversite</th>
                                            <th>Çalışma Saatleri</th>
                                            <th>Telefon / Vergi No</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stores as $st): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= e($st['name']) ?></div>
                                                    <small class="text-muted"><?= e($st['address'] ?? '') ?></small>
                                                </td>
                                                <td><span class="badge bg-primary-subtle text-primary"><?= e($st['university_name'] ?? '-') ?></span></td>
                                                <td><i class="bi bi-clock me-1 text-muted"></i><?= e($st['opens_at'] ?? '08:30') ?> - <?= e($st['closes_at'] ?? '19:00') ?></td>
                                                <td>
                                                    <div class="fs-7"><?= e($st['phone'] ?? '-') ?></div>
                                                    <small class="text-muted">VKN: <?= e($st['tax_number'] ?? '-') ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Üniversiteler -->
                <div class="col-12 col-xl-5">
                    <div class="ds-card h-100">
                        <div class="ds-card-header">
                            <h5 class="ds-card-title mb-0"><i class="bi bi-mortarboard me-2 text-success"></i>Kayıtlı Üniversiteler</h5>
                            <span class="ds-badge ds-badge-success"><?= count($universities) ?> Üniversite</span>
                        </div>
                        <div class="ds-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Üniversite</th>
                                            <th>İl</th>
                                            <th>İletişim</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($universities as $u): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= e($u['name']) ?></div>
                                                    <small class="text-muted"><?= e($u['address'] ?? '') ?></small>
                                                </td>
                                                <td><span class="badge bg-light text-dark border"><?= e($u['city'] ?? '') ?></span></td>
                                                <td class="fs-7"><?= e($u['phone'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Üniversite Ekle Modal -->
<div class="modal fade" id="addUniversityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('universities/store') ?>" method="POST" class="modal-content ds-card border-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Yeni Üniversite Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="ds-label">Üniversite Adı *</label>
                    <input type="text" name="name" class="ds-input" required placeholder="Örn: Yıldız Teknik Üniversitesi">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="ds-label">İl *</label>
                        <input type="text" name="city" class="ds-input" required placeholder="İstanbul">
                    </div>
                    <div class="col-6">
                        <label class="ds-label">Telefon</label>
                        <input type="text" name="phone" class="ds-input" placeholder="0212 383 00 00">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="ds-label">Adres</label>
                    <input type="text" name="address" class="ds-input" placeholder="Davutpaşa Kampüsü, Esenler">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ds-btn ds-btn-outline" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="ds-btn ds-btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Kırtasiye Şubesi Ekle Modal -->
<div class="modal fade" id="addStoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('stores/store') ?>" method="POST" class="modal-content ds-card border-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Yeni Kırtasiye Şubesi Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="ds-label">Bağlı Üniversite *</label>
                    <select name="university_id" class="ds-select" required>
                        <?php foreach ($universities as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="ds-label">Kırtasiye Adı *</label>
                    <input type="text" name="name" class="ds-input" required placeholder="Örn: YTÜ Davutpaşa Kırtasiye">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="ds-label">Telefon</label>
                        <input type="text" name="phone" class="ds-input" placeholder="0212 383 10 10">
                    </div>
                    <div class="col-6">
                        <label class="ds-label">Vergi No</label>
                        <input type="text" name="tax_number" class="ds-input" placeholder="1112223334">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="ds-label">Açılış Saati</label>
                        <input type="text" name="opens_at" class="ds-input" value="08:30">
                    </div>
                    <div class="col-6">
                        <label class="ds-label">Kapanış Saati</label>
                        <input type="text" name="closes_at" class="ds-input" value="19:00">
                    </div>
                </div>
                <div class="mb-0">
                    <label class="ds-label">Adres</label>
                    <input type="text" name="address" class="ds-input" placeholder="Öğrenci Merkezi Zemin Kat">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="ds-btn ds-btn-outline" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="ds-btn ds-btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
