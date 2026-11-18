<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid p-3 p-md-4">
            
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
                        <i class="bi bi-clipboard-check text-info me-2"></i> Fiziksel Sayım Tutanakları
                    </h4>
                    <p class="text-muted mb-0 fs-7">Depodaki mevcut stok ile sistem verilerini karşılaştırma ve sayım kayıtları</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="<?= base_url('counts/create') ?>" class="btn btn-info text-white btn-sm rounded-2 shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Yeni Sayım Tutanağı Oluştur
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white p-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Geçmiş Sayımlar</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-8 text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">TUTANAK NO</th>
                                    <th>SAYIM TARİHİ</th>
                                    <th>SAYIMI YAPAN (GÖREVLİ)</th>
                                    <th>DURUM</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($counts)): ?>
                                    <?php foreach ($counts as $cnt): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold font-monospace text-dark"><?= e($cnt['document_no']) ?></td>
                                            <td><?= date('d.m.Y', strtotime($cnt['count_date'])) ?></td>
                                            <td class="fw-medium"><?= e($cnt['first_name'] . ' ' . $cnt['last_name']) ?></td>
                                            <td>
                                                <?php if ($cnt['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1">Onay Bekliyor</span>
                                                <?php elseif ($cnt['status'] === 'approved'): ?>
                                                    <span class="badge bg-success px-2 py-1">Onaylandı / Eşitlendi</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-clipboard fs-1 d-block mb-2 opacity-50"></i>
                                            Kayıtlı fiziksel sayım tutanağı bulunmuyor.
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
