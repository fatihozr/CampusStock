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
                    <a href="<?= base_url('procurements') ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
                        <i class="bi bi-arrow-left"></i> İhalelere Dön
                    </a>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-receipt text-success me-2"></i> Yeni İhale Kaydı Oluştur
                    </h4>
                    <p class="text-muted mb-0 fs-7">Sipariş verilecek veya satın alımı yapılan malzemeleri sisteme girin.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <form action="<?= base_url('procurements/store') ?>" method="POST">
                    <div class="card-body p-4">
                        <div class="row mb-4 g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label text-dark fw-semibold">İhale / Sipariş No <span class="text-danger">*</span></label>
                                <input type="text" name="procurement_no" class="form-control form-control-sm text-uppercase font-monospace" required placeholder="Örn: 2026/12345">
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label text-dark fw-semibold">Tedarikçi Firma / Yüklenici <span class="text-danger">*</span></label>
                                <input type="text" name="supplier" class="form-control form-control-sm" required placeholder="Örn: ABC Temizlik A.Ş.">
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">İhale Kapsamındaki Malzemeler</h6>
                        
                        <div class="table-responsive">
                            <table class="table align-middle table-hover border">
                                <thead class="table-light fs-7 text-muted">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>MALZEME</th>
                                        <th class="text-center" style="width: 150px;">SİPARİŞ MİKTARI</th>
                                        <th class="text-center" style="width: 150px;">BİRİM FİYAT (TL)</th>
                                        <th>ŞARTNAME / ÖZELLİK BEKLENTİSİ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $index => $prod): ?>
                                        <tr>
                                            <td class="text-muted"><?= $index + 1 ?></td>
                                            <td>
                                                <div class="fw-bold"><?= e($prod['name']) ?></div>
                                                <small class="text-muted font-monospace fs-8"><?= e($prod['code']) ?></small>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="products[<?= $prod['id'] ?>]" class="form-control text-center" min="0" value="0">
                                                    <span class="input-group-text"><?= e($prod['unit']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="unit_prices[<?= $prod['id'] ?>]" class="form-control form-control-sm text-end" min="0" value="<?= $prod['unit_price'] ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="specifications[<?= $prod['id'] ?>]" class="form-control form-control-sm" placeholder="Örn: En az %5 klor içermeli">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info py-2 fs-7 mt-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Miktarı 0 olarak bırakılan ürünler ihale kaydına eklenmez. Yalnızca tedarik edilecek malzemelerin miktarını giriniz.
                        </div>

                    </div>
                    <div class="card-footer bg-light p-3 text-end">
                        <button type="submit" class="btn btn-success px-4"><i class="bi bi-save me-1"></i> İhaleyi Kaydet</button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
