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

            <?php if (!empty($flashError)): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 p-3 mb-4 border-0 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-danger flex-shrink-0"></i>
                    <div><?= e($flashError) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
                </div>
            <?php endif; ?>

            <!-- Page Title & Navigation -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <a href="<?= base_url('stocks') ?>" class="btn btn-outline-secondary btn-sm mb-2 text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Ürün Kataloğuna Dön
                    </a>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <?= e($product['name']) ?>
                        <span class="badge <?= e($product['badge_class']) ?> fs-7 rounded-pill"><?= e($product['stock_status_label']) ?></span>
                    </h4>
                    <p class="text-muted mb-0 fs-7">
                        Ürün Kodu: <strong class="font-monospace text-dark"><?= e($product['code']) ?></strong> 
                        &middot; Kategori: <strong><?= e($product['category']) ?></strong>
                    </p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm rounded-2 d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#detailStockInModal">
                        <i class="bi bi-arrow-down-circle-fill"></i> Stok Girişi
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#detailStockOutModal">
                        <i class="bi bi-arrow-up-circle-fill"></i> Stok Çıkışı
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-2 d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#detailEditProductModal">
                        <i class="bi bi-pencil-square"></i> Düzenle
                    </button>
                </div>
            </div>

            <!-- Product Overview Cards -->
            <div class="row g-3 mb-4">
                <!-- Card 1: Main Info -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <?php if (!empty($product['image_path']) && file_exists(BASE_PATH . '/public/' . $product['image_path'])): ?>
                                    <img src="<?= base_url($product['image_path']) ?>" alt="<?= e($product['name']) ?>" class="rounded-3 border object-fit-cover shadow-sm" style="width: 110px; height: 110px;">
                                <?php else: ?>
                                    <div class="rounded-3 border bg-light text-muted d-flex align-items-center justify-content-center shadow-sm" style="width: 110px; height: 110px; font-size: 2.5rem;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="badge bg-dark font-monospace text-white mb-1"><?= e($product['code']) ?></span>
                                    <h5 class="fw-bold text-dark mb-1"><?= e($product['name']) ?></h5>
                                    <p class="text-muted fs-7 mb-0"><?= e($product['brand'] ?: 'Marka Belirtilmedi') ?></p>
                                </div>
                            </div>
                            <table class="table table-sm table-borderless fs-7 mb-0 border-top pt-2">
                                <tr>
                                    <td class="text-secondary ps-0" style="width: 130px;">Barkod No:</td>
                                    <td class="fw-bold font-monospace"><?= e($product['barcode'] ?: 'Tanımlanmadı') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary ps-0">Ölçü Birimi:</td>
                                    <td class="fw-bold"><?= e($product['unit']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-secondary ps-0">Ambar Lokasyonu:</td>
                                    <td><?= e($product['warehouse']) ?> (<?= e($product['rack_location']) ?>)</td>
                                </tr>
                                <tr>
                                    <td class="text-secondary ps-0">Özellikler:</td>
                                    <td><?= e($product['specifications'] ?: 'Belirtilmedi') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Stock & Cost Metrics -->
                <div class="col-12 col-lg-7">
                    <div class="row g-3 h-100">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 text-center p-3 h-100 bg-white">
                                <span class="fs-8 text-muted fw-semibold d-block mb-1">MEVCUT STOK</span>
                                <span class="fs-2 fw-bold <?= ($product['quantity'] <= $product['min_threshold']) ? 'text-danger' : 'text-dark' ?>">
                                    <?= e($product['quantity']) ?>
                                </span>
                                <small class="text-muted fs-8"><?= e($product['unit']) ?></small>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 text-center p-3 h-100 bg-white">
                                <span class="fs-8 text-muted fw-semibold d-block mb-1">KRİTİK EŞİK</span>
                                <span class="fs-2 fw-bold text-secondary"><?= e($product['min_threshold']) ?></span>
                                <small class="text-muted fs-8"><?= e($product['unit']) ?></small>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 text-center p-3 h-100 bg-white">
                                <span class="fs-8 text-muted fw-semibold d-block mb-1">BİRİM MALİYET</span>
                                <span class="fs-4 fw-bold text-primary mt-2"><?= number_format($product['unit_price'], 2, ',', '.') ?> TL</span>
                                <small class="text-muted fs-8">Varsayılan / Güncel</small>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 text-center p-3 h-100 bg-white">
                                <span class="fs-8 text-muted fw-semibold d-block mb-1">TOPLAM DEĞER</span>
                                <span class="fs-4 fw-bold text-success mt-2"><?= number_format($product['total_val'], 2, ',', '.') ?> TL</span>
                                <small class="text-muted fs-8">Mevcut Stok Değeri</small>
                            </div>
                        </div>

                        <!-- Description Box -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3 p-3 bg-light">
                                <strong class="fs-7 text-dark mb-1 d-block"><i class="bi bi-info-circle me-1 text-primary"></i> Ürün Açıklaması ve Kullanım Amacı</strong>
                                <p class="text-muted fs-7 mb-0"><?= e($product['description'] ?: 'Açıklama girilmedi.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Movements Log Table -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white p-3 border-bottom-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-primary fs-5"></i>
                        Ürün Stok Hareketleri Geçmişi
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill"><?= count($movements) ?> İşlem Kaydı</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-8 text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">EVRAK NO</th>
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
                                                <span class="badge bg-secondary font-monospace"><?= e($m['document_no']) ?></span>
                                            </td>
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
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            Bu ürüne ait henüz kayıtlı stok hareketi bulunmuyor.
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
<!-- MODAL: STOK GİRİŞİ MODALI                   -->
<!-- ========================================== -->
<div class="modal fade" id="detailStockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-arrow-down-circle me-2"></i>Stok Girişi Yap</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/stock-in') ?>" method="POST">
                <input type="hidden" name="stock_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="redirect_back" value="/stocks/detail?id=<?= $product['id'] ?>">

                <div class="modal-body p-4">
                    <div class="alert alert-light border py-2 px-3 fs-7 mb-3">
                        Ürün: <strong>[<?= e($product['code']) ?>] <?= e($product['name']) ?></strong><br>
                        Mevcut Stok: <strong><?= $product['quantity'] ?> <?= e($product['unit']) ?></strong>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Giriş Miktarı <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control form-control-sm" min="1" required placeholder="Miktar">
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Giriş Birim Maliyeti (TL)</label>
                            <input type="number" step="0.01" name="unit_cost" class="form-control form-control-sm" value="<?= $product['unit_price'] ?>" min="0">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Açıklama / Evrak No</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Giriş açıklaması..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: STOK ÇIKIŞI MODALI                  -->
<!-- ========================================== -->
<div class="modal fade" id="detailStockOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-arrow-up-circle me-2"></i>Stok Çıkışı Yap</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/stock-out') ?>" method="POST">
                <input type="hidden" name="stock_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="redirect_back" value="/stocks/detail?id=<?= $product['id'] ?>">

                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 px-3 fs-7 mb-3">
                        Ürün: <strong>[<?= e($product['code']) ?>] <?= e($product['name']) ?></strong><br>
                        Mevcut Stok: <strong><?= $product['quantity'] ?> <?= e($product['unit']) ?></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Çıkış Miktarı <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control form-control-sm" min="1" max="<?= $product['quantity'] ?>" required placeholder="Miktar">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Açıklama / Çıkış Sebebi</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Çıkış açıklaması..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Çıkışı Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: ÜRÜN DÜZENLE MODALI                 -->
<!-- ========================================== -->
<div class="modal fade" id="detailEditProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-pencil-square me-2"></i>Temizlik Ürün Bilgilerini Düzenle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/update') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <input type="hidden" name="redirect_back" value="/stocks/detail?id=<?= $product['id'] ?>">

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Ürün Kodu <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control form-control-sm font-monospace text-uppercase" value="<?= e($product['code']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Barkod No</label>
                            <input type="text" name="barcode" class="form-control form-control-sm font-monospace" value="<?= e($product['barcode']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Malzeme Ürün Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?= e($product['name']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Kategori</label>
                            <select name="category" class="form-select form-select-sm">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['name']) ?>" <?= ($product['category'] === $cat['name']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Ölçü Birimi</label>
                            <select name="unit" class="form-select form-select-sm">
                                <?php foreach ($unitChoices as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= ($product['unit'] === $key) ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Kritik Stok Eşiği (Minimum)</label>
                            <input type="number" name="min_threshold" class="form-control form-control-sm" value="<?= $product['min_threshold'] ?>" min="0" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Birim Maliyet (TL)</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control form-control-sm" value="<?= $product['unit_price'] ?>" min="0">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 text-muted">Marka</label>
                            <input type="text" name="brand" class="form-control form-control-sm" value="<?= e($product['brand']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 text-muted">Görsel Yükle / Değiştir</label>
                            <input type="file" name="product_image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 text-muted">Teknik Özellikler</label>
                        <input type="text" name="specifications" class="form-control form-control-sm" value="<?= e($product['specifications']) ?>">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Açıklama</label>
                        <textarea name="description" class="form-control form-control-sm" rows="3"><?= e($product['description']) ?></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-dark btn-sm px-4">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
