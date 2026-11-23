<?php
use App\Helpers\Auth;
require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div class="content-wrapper p-4" style="min-height: calc(100vh - 65px); background: #F8FAFC;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-tag-fill text-primary me-2"></i>Kırtasiye Markaları</h4>
            <p class="text-muted small mb-0">Faber-Castell, Rotring, Adel, Bic ve sisteme kayıtlı tüm kırtasiye üreticileri</p>
        </div>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newBrandModal">
            <i class="bi bi-plus-lg me-1"></i>Yeni Marka Ekle
        </button>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($brands as $b): ?>
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100 p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($b['name']) ?></h5>
                        <span class="badge bg-primary-subtle text-primary"><?= (int)($b['product_count'] ?? 0) ?> Ürün</span>
                    </div>
                    <small class="text-muted mb-2"><i class="bi bi-globe me-1"></i><?= htmlspecialchars($b['country'] ?? 'Menşei Belirtilmedi') ?></small>
                    <?php if (!empty($b['website'])): ?>
                        <div class="mt-auto pt-2 border-top">
                            <a href="<?= htmlspecialchars($b['website']) ?>" target="_blank" class="small text-decoration-none text-primary text-truncate d-block">
                                <?= htmlspecialchars($b['website']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Yeni Marka Modalı -->
<div class="modal fade" id="newBrandModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('brands/store') ?>" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Yeni Kırtasiye Markası Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Marka Adı:</label>
                    <input type="text" name="name" class="form-control" placeholder="Örn: Faber-Castell, Rotring, Adel..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Menşei Ülke:</label>
                    <input type="text" name="country" class="form-control" placeholder="Örn: Almanya, Türkiye, Japonya...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Resmi Web Sitesi:</label>
                    <input type="url" name="website" class="form-control" placeholder="https://...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-primary fw-bold">Markayı Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>

