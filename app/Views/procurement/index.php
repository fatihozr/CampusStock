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

            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-receipt text-success me-2"></i> İhale ve Satın Alma Takibi
                    </h4>
                    <p class="text-muted mb-0 fs-7">Tedarik ve sipariş süreçleri</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="<?= base_url('procurements/create') ?>" class="btn btn-success btn-sm rounded-2 shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Yeni İhale Kaydı Oluştur
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white p-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Satın Alma İşlemleri</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-8 text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">İHALE NO / KAYIT</th>
                                    <th>TARİH</th>
                                    <th>TEDARİKÇİ FİRMA</th>
                                    <th>OLUŞTURAN</th>
                                    <th>DURUM</th>
                                    <th class="text-end pe-3">İŞLEMLER</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($procurements)): ?>
                                    <?php foreach ($procurements as $proc): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold font-monospace text-primary">
                                                <a href="<?= base_url('procurements/detail?id='.$proc['id']) ?>" class="text-decoration-none">
                                                    <?= e($proc['procurement_no']) ?>
                                                </a>
                                            </td>
                                            <td><?= date('d.m.Y', strtotime($proc['created_at'])) ?></td>
                                            <td class="fw-semibold text-dark"><?= e($proc['supplier']) ?></td>
                                            <td><?= e($proc['first_name'] . ' ' . $proc['last_name']) ?></td>
                                            <td>
                                                <?php if ($proc['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1">Beklemede (Sipariş Verildi)</span>
                                                <?php elseif ($proc['status'] === 'partial'): ?>
                                                    <span class="badge bg-info text-dark px-2 py-1">Kısmi Teslimat</span>
                                                <?php elseif ($proc['status'] === 'completed'): ?>
                                                    <span class="badge bg-success px-2 py-1">Tamamlandı</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="<?= base_url('procurements/detail?id='.$proc['id']) ?>" class="btn btn-sm btn-outline-primary" title="İhale Detayları">
                                                    <i class="bi bi-eye"></i> İncele
                                                </a>
                                                <?php if ($proc['status'] !== 'completed'): ?>
                                                    <a href="<?= base_url('deliveries/create?procurement_id='.$proc['id']) ?>" class="btn btn-sm btn-outline-success" title="Tesellüm / Alım Yap">
                                                        <i class="bi bi-box-seam"></i> Tesellüm Yap
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-receipt fs-1 d-block mb-2 opacity-50"></i>
                                            Kayıtlı satın alma / ihale bulunmamaktadır.
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

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
