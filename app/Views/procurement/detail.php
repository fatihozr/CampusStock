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
                    <a href="<?= base_url('procurements') ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
                        <i class="bi bi-arrow-left"></i> İhalelere Dön
                    </a>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-card-text text-primary me-2"></i> İhale Detayı: <?= e($procurement['procurement_no']) ?>
                    </h4>
                    <p class="text-muted mb-0 fs-7">
                        <span class="fw-semibold text-dark">Tedarikçi:</span> <?= e($procurement['supplier']) ?>
                    </p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <?php if ($procurement['status'] !== 'completed'): ?>
                        <a href="<?= base_url('deliveries/create?procurement_id='.$procurement['id']) ?>" class="btn btn-success btn-sm rounded-2 shadow-sm d-flex align-items-center gap-1">
                            <i class="bi bi-box-seam"></i> Tesellüm (Mal Kabul) Yap
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4">
                <!-- Sipariş Edilen Ürünler (Sol Taraf) -->
                <div class="col-12 col-xl-7">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white p-3 border-bottom-0">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-cart me-2"></i>İhale Kapsamındaki Ürünler</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover border-top mb-0">
                                    <thead class="table-light fs-8 text-muted">
                                        <tr>
                                            <th>ÜRÜN ADI</th>
                                            <th class="text-center">SİPARİŞ MİKTARI</th>
                                            <th class="text-center">TESLİM ALINAN</th>
                                            <th class="text-center">KALAN</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-7">
                                        <?php foreach ($procurement['items'] as $item): ?>
                                            <?php 
                                            $remaining = max(0, $item['quantity'] - $item['received_quantity']);
                                            $hasFaulty = false;
                                            if (!empty($procurement['deliveries'])) {
                                                foreach ($procurement['deliveries'] as $del) {
                                                    foreach ($del['items'] as $dItem) {
                                                        if ($dItem['product_id'] == $item['product_id'] && $dItem['is_compliant'] === 'no') {
                                                            $hasFaulty = true;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= e($item['product_name']) ?></div>
                                                    <small class="text-muted"><?= e($item['product_code']) ?></small>
                                                    <?php if ($item['received_quantity'] > 0 && $remaining > 0): ?>
                                                        <div class="mt-1 fs-8 text-warning fw-bold">
                                                            <i class="bi bi-exclamation-triangle"></i> Eksik Teslimat (<?= $remaining ?> adet daha gelecek)
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($hasFaulty): ?>
                                                        <div class="mt-1 fs-8 text-danger fw-bold">
                                                            <i class="bi bi-x-circle"></i> Hatalı ürün teslimatı uyarısı!
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center text-primary fw-bold"><?= $item['quantity'] ?></td>
                                                <td class="text-center text-success fw-bold"><?= $item['received_quantity'] ?></td>
                                                <td class="text-center <?= $remaining > 0 ? 'text-danger' : 'text-success' ?> fw-bold">
                                                    <?= $remaining ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tesellüm Geçmişi (Sağ Taraf) -->
                <div class="col-12 col-xl-5">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white p-3 border-bottom-0">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Tesellüm & Kabul Geçmişi</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($procurement['deliveries'])): ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($procurement['deliveries'] as $del): ?>
                                        <li class="list-group-item p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="badge bg-light text-dark border"><i class="bi bi-file-earmark-text"></i> <?= e($del['document_no'] ?: 'Belge No Yok') ?></span>
                                                </div>
                                                <div class="text-muted fs-8">
                                                    <i class="bi bi-calendar"></i> <?= date('d.m.Y', strtotime($del['created_at'])) ?>
                                                </div>
                                            </div>
                                            <div class="fs-7 mb-2">
                                                <span class="text-muted">Teslim Alan:</span> 
                                                <span class="fw-medium"><?= e($del['first_name'] . ' ' . $del['last_name']) ?></span>
                                            </div>
                                            <!-- Teslimat Detayları Listesi -->
                                            <div class="bg-light rounded-2 p-2">
                                                <div class="fs-8 fw-bold text-uppercase text-muted mb-1 border-bottom pb-1">Teslim Edilen Ürünler:</div>
                                                <?php foreach ($del['items'] as $dItem): ?>
                                                    <div class="d-flex justify-content-between align-items-center fs-8 py-1">
                                                        <span><?= e($dItem['product_name']) ?></span>
                                                        <span class="fw-bold text-dark">
                                                            <?= $dItem['quantity'] ?> Adet
                                                            <?php if ($dItem['is_compliant'] !== 'yes'): ?>
                                                                <span class="badge bg-danger ms-1" title="Hatalı / Uygunsuz Ürün">!</span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x fs-1 d-block mb-2 opacity-50"></i>
                                    Henüz bu ihale için mal kabul işlemi yapılmamıştır.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
