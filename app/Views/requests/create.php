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
                    <a href="<?= base_url('requests') ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
                        <i class="bi bi-arrow-left"></i> Taleplere Dön
                    </a>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-cart-plus text-primary me-2"></i> Yeni Malzeme Talebi
                    </h4>
                    <p class="text-muted mb-0 fs-7">Biriminiz için temizlik malzemesi ihtiyaç formunu doldurun.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <form action="<?= base_url('requests/store') ?>" method="POST">
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label text-dark fw-semibold">Talep Eden Birim <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select form-select-sm" required>
                                    <option value="">-- Birim Seçiniz --</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 border-bottom pb-2">Talep Edilecek Malzemeler</h6>
                        
                        <div class="table-responsive">
                            <table class="table align-middle table-hover border">
                                <thead class="table-light fs-7 text-muted">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>MALZEME</th>
                                        <th class="text-center" style="width: 120px;">STOK DURUMU</th>
                                        <th class="text-center" style="width: 150px;">TALEP EDİLEN MİKTAR</th>
                                        <th>AÇIKLAMA (İsteğe Bağlı)</th>
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
                                            <td class="text-center">
                                                <?php if ($prod['quantity'] > 0): ?>
                                                    <span class="badge bg-success-subtle text-success fs-8">Mevcut (<?= $prod['quantity'] ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger fs-8">Tükendi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="products[<?= $prod['id'] ?>]" class="form-control text-center" min="0" value="0" <?= $prod['quantity'] <= 0 ? 'disabled' : '' ?>>
                                                    <span class="input-group-text"><?= e($prod['unit']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="descriptions[<?= $prod['id'] ?>]" class="form-control form-control-sm" placeholder="Not..." <?= $prod['quantity'] <= 0 ? 'disabled' : '' ?>>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info py-2 fs-7 mt-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Lütfen sadece ihtiyacınız olan ürünlerin miktarını giriniz. Miktarı 0 olan ürünler talebe eklenmeyecektir.
                        </div>

                    </div>
                    <div class="card-footer bg-light p-3 text-end">
                        <button type="submit" class="btn btn-primary px-4">Talebi Gönder</button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
