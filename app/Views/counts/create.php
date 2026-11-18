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
                    <a href="<?= base_url('counts') ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
                        <i class="bi bi-arrow-left"></i> Sayımlara Dön
                    </a>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-clipboard-plus text-info me-2"></i> Yeni Fiziksel Sayım Tutanağı
                    </h4>
                    <p class="text-muted mb-0 fs-7">Raftaki fiziksel temizlik malzemelerini sayarak sisteme girin.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <form action="<?= base_url('counts/store') ?>" method="POST">
                    <div class="card-body p-4">
                        <div class="row mb-4 g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label text-dark fw-semibold">Tutanak / Belge No <span class="text-danger">*</span></label>
                                <input type="text" name="document_no" class="form-control form-control-sm text-uppercase font-monospace" required value="SYM-<?= date('YmdHis') ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-dark fw-semibold">Sayım Tarihi <span class="text-danger">*</span></label>
                                <input type="date" name="count_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-dark fw-semibold">Sayımı Yapan Personel</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="<?= e($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>" disabled>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Ürün Sayım Listesi</h6>
                        
                        <div class="table-responsive">
                            <table class="table align-middle table-hover border">
                                <thead class="table-light fs-7 text-muted">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>MALZEME</th>
                                        <th class="text-center" style="width: 140px;">SİSTEMDEKİ MİKTAR</th>
                                        <th class="text-center" style="width: 150px;">SAYIM SONUCU (RAFTAKİ)</th>
                                        <th>FARK NEDENİ AÇIKLAMASI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $index => $prod): ?>
                                        <input type="hidden" name="expected[<?= $prod['id'] ?>]" value="<?= $prod['quantity'] ?>">
                                        <tr>
                                            <td class="text-muted"><?= $index + 1 ?></td>
                                            <td>
                                                <div class="fw-bold"><?= e($prod['name']) ?></div>
                                                <small class="text-muted font-monospace fs-8"><?= e($prod['code']) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary px-3 py-2 fs-7"><?= $prod['quantity'] ?> <?= e($prod['unit']) ?></span>
                                            </td>
                                            <td>
                                                <input type="number" name="counted[<?= $prod['id'] ?>]" class="form-control text-center form-control-sm counted-input" data-id="<?= $prod['id'] ?>" data-expected="<?= $prod['quantity'] ?>" min="0" placeholder="Boş Bırakılabilir">
                                            </td>
                                            <td>
                                                <input type="text" name="reasons[<?= $prod['id'] ?>]" id="reason_<?= $prod['id'] ?>" class="form-control form-control-sm" placeholder="Fark çıkarsa zorunludur..." disabled>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3 mt-4">
                            <label class="form-label fs-7 text-muted">Genel Sayım Notları (İsteğe Bağlı)</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Depo genel durumu vb..."></textarea>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                        <div class="fs-8 text-muted"><i class="bi bi-info-circle text-primary"></i> Sayım miktarını boş bıraktığınız ürünler hesaba katılmaz.</div>
                        <button type="submit" class="btn btn-info text-white px-4"><i class="bi bi-save me-1"></i> Sayımı Kaydet</button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.counted-input').forEach(input => {
        input.addEventListener('input', function() {
            const id = this.dataset.id;
            const expected = parseFloat(this.dataset.expected || 0);
            const counted = parseFloat(this.value);
            const reasonInput = document.getElementById('reason_' + id);
            
            if (!isNaN(counted) && counted !== expected) {
                reasonInput.disabled = false;
                reasonInput.required = true;
                reasonInput.style.borderColor = '#dc3545';
            } else {
                reasonInput.disabled = true;
                reasonInput.required = false;
                reasonInput.value = '';
                reasonInput.style.borderColor = '';
            }
        });
    });
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
