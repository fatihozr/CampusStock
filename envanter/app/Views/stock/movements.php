<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-3 p-md-4">

            <!-- Page Header -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-primary fs-3"></i>
                        Stok Hareketleri ve Ambar Tarihçesi
                    </h4>
                    <p class="text-muted mb-0 fs-7">Sistemdeki tüm temizlik malzemesi stok giriş ve çıkış işlemlerinin log kayıtları</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="<?= base_url('stocks') ?>" class="btn btn-outline-primary btn-sm rounded-2">
                        <i class="bi bi-boxes me-1"></i> Ürün Kataloğuna Dön
                    </a>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="<?= base_url('stocks/movements') ?>" class="row g-2 align-items-center">
                        <div class="col-12 col-md-6 col-lg-5">
                            <select name="stock_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tüm Ürünlerin Hareketlerini Göster --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($selectedId === (int)$p['id']) ? 'selected' : '' ?>>
                                        [<?= e($p['code']) ?>] <?= e($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2">
                            <?php if ($selectedId): ?>
                                <a href="<?= base_url('stocks/movements') ?>" class="btn btn-outline-secondary btn-sm w-100">Filtreyi Sıfırla</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Movements Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white p-3 border-bottom-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0">Stok İşlem Kayıtları</h6>
                    <span class="badge bg-primary rounded-pill"><?= count($movements) ?> Kayıt</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-8 text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">EVRAK NO</th>
                                    <th>ÜRÜN KODU</th>
                                    <th>ÜRÜN ADI</th>
                                    <th>KATEGORİ</th>
                                    <th>İŞLEM TÜRÜ</th>
                                    <th class="text-center">MİKTAR</th>
                                    <th class="text-center">ÖNCEKİ STOK</th>
                                    <th class="text-center">SONRAKİ STOK</th>
                                    <th class="text-end">BİRİM MALİYET</th>
                                    <th class="text-end">TOPLAM MALİYET</th>
                                    <th>İŞLEM YAPAN</th>
                                    <th>TARİH</th>
                                    <th class="pe-3">AÇIKLAMA</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($movements)): ?>
                                    <?php foreach ($movements as $m): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <span class="badge bg-dark font-monospace text-white px-2 py-1"><?= e($m['document_no']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary font-monospace"><?= e($m['product_code']) ?></span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('stocks/detail?id=' . $m['stock_id']) ?>" class="fw-bold text-dark text-decoration-none hover-primary">
                                                    <?= e($m['item_name']) ?>
                                                </a>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?= e($m['category']) ?></span></td>
                                            <td>
                                                <?php if ($m['movement_type'] === 'STOK_GIRIS'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                                        <i class="bi bi-arrow-down-circle me-1"></i> STOK GİRİŞİ
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                                        <i class="bi bi-arrow-up-circle me-1"></i> STOK ÇIKIŞI
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-bold fs-6 <?= ($m['movement_type'] === 'STOK_GIRIS') ? 'text-success' : 'text-danger' ?>">
                                                <?= ($m['movement_type'] === 'STOK_GIRIS' ? '+' : '-') ?><?= e($m['quantity']) ?>
                                            </td>
                                            <td class="text-center text-muted"><?= e($m['prev_stock']) ?></td>
                                            <td class="text-center fw-bold text-dark"><?= e($m['next_stock']) ?></td>
                                            <td class="text-end fw-semibold"><?= number_format((float)($m['unit_cost'] ?? 0), 2, ',', '.') ?> TL</td>
                                            <td class="text-end fw-bold text-dark"><?= number_format((float)($m['total_cost'] ?? 0), 2, ',', '.') ?> TL</td>
                                            <td><?= e($m['performed_by']) ?></td>
                                            <td class="text-muted fs-8"><?= e($m['date']) ?></td>
                                            <td class="text-secondary pe-3"><?= e($m['description']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="13" class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x fs-1 d-block text-muted mb-2"></i>
                                            Kayıtlı stok hareketi bulunamadı.
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
