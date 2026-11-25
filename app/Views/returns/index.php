<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid p-0">
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">İade Talepleri & İşlemleri</h4>
                    <p class="text-muted mb-0 fs-7">Sipariş ve satışlardan gelen ürün iade kayıtları</p>
                </div>
                <div>
                    <a href="<?= base_url('sales') ?>" class="ds-btn ds-btn-outline">
                        <i class="bi bi-receipt"></i> Satışlara Dön
                    </a>
                </div>
            </div>

            <?php if (!empty($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'success' ?> alert-dismissible fade show">
                    <?= e($_SESSION['flash_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <div class="ds-card">
                <div class="ds-card-header">
                    <h5 class="ds-card-title mb-0"><i class="bi bi-arrow-return-left me-2 text-warning"></i>İade Kayıtları</h5>
                    <span class="ds-badge ds-badge-warning"><?= count($returns) ?> Kayıt</span>
                </div>
                <div class="ds-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fiş / Sipariş No</th>
                                    <th>Ürün</th>
                                    <th>Müşteri</th>
                                    <th>İade Nedeni</th>
                                    <th>İade Tutarı</th>
                                    <th>Tarih</th>
                                    <th>Durum</th>
                                    <th class="text-end">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($returns)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">Henüz iade kaydı bulunmuyor.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($returns as $ret): ?>
                                        <tr>
                                            <td class="fw-bold"><?= e($ret['receipt_no']) ?></td>
                                            <td><?= e($ret['product_name']) ?></td>
                                            <td><?= e($ret['customer_name'] ?? '-') ?></td>
                                            <td><small class="text-muted"><?= e($ret['reason']) ?></small></td>
                                            <td class="fw-bold text-danger"><?= number_format((float)$ret['return_amount'], 2, ',', '.') ?> ₺</td>
                                            <td class="fs-7"><?= e($ret['created_at']) ?></td>
                                            <td>
                                                <?php
                                                $st = $ret['status'] ?? 'Beklemede';
                                                $cls = $st === 'Onaylandı' ? 'bg-success-subtle text-success' : ($st === 'Reddedildi' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
                                                ?>
                                                <span class="badge <?= $cls ?>"><?= e($st) ?></span>
                                            </td>
                                            <td class="text-end">
                                                <form action="<?= base_url('returns/status') ?>" method="POST" class="d-inline-flex gap-1">
                                                    <input type="hidden" name="id" value="<?= $ret['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                                        <option value="Beklemede" <?= $st === 'Beklemede' ? 'selected' : '' ?>>Beklemede</option>
                                                        <option value="Onaylandı" <?= $st === 'Onaylandı' ? 'selected' : '' ?>>Onaylandı</option>
                                                        <option value="Reddedildi" <?= $st === 'Reddedildi' ? 'selected' : '' ?>>Reddedildi</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
