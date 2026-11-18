<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-3 p-md-4">
            
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <a href="<?= base_url('procurements/detail?id=' . $procurement['id']) ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
                        <i class="bi bi-arrow-left"></i> İhale Detayına Dön
                    </a>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-box-seam text-success me-2"></i> Mal Kabul (Tesellüm) İşlemi
                    </h4>
                    <p class="text-muted mb-0 fs-7">
                        <span class="fw-bold text-dark"><?= e($procurement['procurement_no']) ?></span> numaralı ihale için tedarikçiden 
                        (<span class="fw-medium"><?= e($procurement['supplier']) ?></span>) gelen ürünleri kaydedin ve stoğa aktarın.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <form action="<?= base_url('deliveries/store') ?>" method="POST">
                    <input type="hidden" name="procurement_id" value="<?= $procurement['id'] ?>">
                    
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-12 col-md-4">
                                <label class="form-label text-dark fw-semibold">Teslimat Belge / İrsaliye No</label>
                                <input type="text" name="document_no" class="form-control form-control-sm text-uppercase font-monospace" placeholder="Örn: IRS-2026-98765">
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Teslim Edilen Malzemeler ve Kalite Kontrol</h6>
                        
                        <div class="table-responsive">
                            <table class="table align-middle table-hover border">
                                <thead class="table-light fs-7 text-muted text-uppercase">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>ÜRÜN ADI</th>
                                        <th class="text-center" style="width: 130px;">BEKLENEN KALAN</th>
                                        <th class="text-center" style="width: 150px;">GELEN MİKTAR</th>
                                        <th class="text-center" style="width: 140px;">ŞARTNAMEYE UYGUN MU?</th>
                                        <th>UYGUNSUZLUK NEDENİ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    foreach ($procurement['items'] as $item): 
                                        $remaining = max(0, $item['quantity'] - $item['received_quantity']);
                                        if ($remaining <= 0) continue; 
                                    ?>
                                        <tr>
                                            <td class="text-muted"><?= $i++ ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= e($item['product_name']) ?></div>
                                                <?php if (!empty($item['expected_specifications'])): ?>
                                                    <div class="fs-8 text-muted mt-1"><i class="bi bi-info-circle"></i> Şart: <?= e($item['expected_specifications']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-bold text-primary"><?= $remaining ?></td>
                                            <td>
                                                <input type="number" name="delivered[<?= $item['id'] ?>]" class="form-control text-center form-control-sm" min="0" max="<?= $remaining ?>" value="0">
                                            </td>
                                            <td class="text-center">
                                                <select name="compliance[<?= $item['id'] ?>]" class="form-select form-select-sm compliance-select" data-id="<?= $item['id'] ?>">
                                                    <option value="yes">Evet (Uygun)</option>
                                                    <option value="no">Hayır (Hatalı/Eksik)</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="non_compliance_reason[<?= $item['id'] ?>]" id="reason_<?= $item['id'] ?>" class="form-control form-control-sm" placeholder="Uygunsuzluk varsa belirtin..." disabled>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($i === 1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-success fw-bold">
                                                <i class="bi bi-check-circle-fill d-block fs-3 mb-2"></i>
                                                Bu ihaleye ait tüm ürünler daha önce eksiksiz teslim alınmıştır.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning py-2 px-3 fs-7 mt-3 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>
                                Uygun olmayan (hatalı, bozuk veya yanlış ambalajlı) ürünleri seçtiğinizde bu ürünler sisteme "Onay Bekleyen Hatalı Ürün" olarak düşer, stoğa yansımaz.
                            </div>
                        </div>

                    </div>
                    <div class="card-footer bg-light p-3 text-end">
                        <?php if ($i > 1): ?>
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> Tesellüm İşlemini Kaydet</button>
                        <?php else: ?>
                            <a href="<?= base_url('procurements/detail?id='.$procurement['id']) ?>" class="btn btn-secondary px-4">Geri Dön</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Şartname uygunluk durumuna göre açıklama alanını aktif/pasif etme
    document.querySelectorAll('.compliance-select').forEach(select => {
        select.addEventListener('change', function() {
            const itemId = this.dataset.id;
            const reasonInput = document.getElementById('reason_' + itemId);
            if (this.value === 'no') {
                reasonInput.disabled = false;
                reasonInput.required = true;
                reasonInput.focus();
            } else {
                reasonInput.disabled = true;
                reasonInput.required = false;
                reasonInput.value = '';
            }
        });
    });
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
