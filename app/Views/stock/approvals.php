<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-3 p-md-4">
            
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

            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-check2-square text-primary fs-3"></i>
                        Onay Bekleyen Stok İşlemleri
                    </h4>
                    <p class="text-muted mb-0 fs-7">Görevli personel tarafından yapılan ve onayınızı bekleyen stok giriş/çıkış kayıtları.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-8 text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">TARİH</th>
                                    <th>PERSONEL</th>
                                    <th>İŞLEM</th>
                                    <th>ÜRÜN</th>
                                    <th class="text-center">MİKTAR</th>
                                    <th>BİRİM (ÇIKIŞ İSE)</th>
                                    <th>AÇIKLAMA</th>
                                    <th class="text-end pe-3">İŞLEMLER</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($movements)): ?>
                                    <?php foreach ($movements as $m): ?>
                                        <tr>
                                            <td class="ps-3 text-muted"><?= e($m['created_at']) ?></td>
                                            <td>
                                                <span class="fw-semibold text-dark"><?= e($m['first_name'] . ' ' . $m['last_name']) ?></span>
                                                <small class="d-block text-muted">@<?= e($m['username']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($m['type'] === 'in'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">STOK GİRİŞİ</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">STOK ÇIKIŞI</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark d-block"><?= e($m['product_name']) ?></span>
                                                <small class="text-muted font-monospace"><?= e($m['product_code']) ?></small>
                                            </td>
                                            <td class="text-center fw-bold fs-6 <?= ($m['type'] === 'in') ? 'text-success' : 'text-danger' ?>">
                                                <?= ($m['type'] === 'in' ? '+' : '-') ?><?= e($m['quantity']) ?> <small class="text-muted fs-8 fw-normal"><?= e($m['unit']) ?></small>
                                            </td>
                                            <td><?= e($m['department_name'] ?: '-') ?></td>
                                            <td><?= e($m['description'] ?: '-') ?></td>
                                            <td class="text-end pe-3">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <form action="<?= base_url('products/approve-movement') ?>" method="POST" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                        <button type="submit" class="btn btn-success btn-sm px-2" title="Onayla"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger btn-sm px-2 btn-reject-movement" data-id="<?= $m['id'] ?>" data-bs-toggle="modal" data-bs-target="#rejectModal" title="Reddet">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                            Onay bekleyen işlem bulunmuyor.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- MODAL: REDDETME SEBEBİ                     -->
<!-- ========================================== -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-x-circle me-2"></i>İşlemi Reddet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/reject-movement') ?>" method="POST">
                <input type="hidden" name="id" id="reject_movement_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Reddetme Sebebi (İsteğe bağlı)</label>
                        <textarea name="rejection_reason" class="form-control form-control-sm" rows="3" placeholder="Lütfen reddetme sebebini yazınız..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">İşlemi Reddet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-reject-movement').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('reject_movement_id').value = this.dataset.id;
        });
    });
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
