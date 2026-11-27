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

            <!-- Page Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-tags-fill text-primary"></i> Temizlik Malzemesi Kategorileri
                    </h4>
                    <p class="text-muted mb-0 fs-7">Sistemdeki temizlik kimyasalları, kağıt grubu, ekipman ve atık torbası kategorileri</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <button class="btn btn-primary btn-sm rounded-2 d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-lg"></i> Yeni Kategori Ekle
                    </button>
                </div>
            </div>

            <!-- Categories Grid -->
            <div class="row g-3">
                <?php foreach ($categories as $cat): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-3 bg-<?= e($cat['color'] ?? 'primary') ?>-subtle text-<?= e($cat['color'] ?? 'primary') ?> rounded-3 fs-3">
                                            <i class="bi <?= e($cat['icon'] ?? 'bi-tag') ?>"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0 fs-6"><?= e($cat['name']) ?></h6>
                                            <span class="badge bg-secondary-subtle text-dark font-monospace fs-8"><?= e($cat['code']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted fs-7 mb-0"><?= e($cat['description']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
    </main>
</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-plus-circle me-2"></i>Yeni Temizlik Kategorisi Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('categories/store') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Kategori Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Örn: Hijyen Ekipmanları" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Kategori Kodu</label>
                        <input type="text" name="code" class="form-control form-control-sm" placeholder="Örn: KAT-HJG">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 text-muted">Açıklama</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Kategori tanımı..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
