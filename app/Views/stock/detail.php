<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

            <!-- Page Title & Navigation -->
            <div class="d-md-flex align-items-center justify-content-between mb-4 fade-up delay-1">
                <div>
                    <a href="<?= base_url('products') ?>" class="ds-btn ds-btn-secondary ds-btn-sm rounded-pill mb-2">
                        <i class="bi bi-arrow-left me-1"></i> Ürün Kataloğuna Dön
                    </a>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-3 mt-1">
                        <?= e($product['name']) ?>
                        <?php
                            $bgClass = $product['badge_class']; 
                            if ($bgClass === 'bg-success') echo '<span class="ds-badge ds-badge-success"><i class="bi bi-check-circle"></i> Yeterli Stok</span>';
                            if ($bgClass === 'bg-warning') echo '<span class="ds-badge ds-badge-warning"><i class="bi bi-exclamation-triangle"></i> Kritik Stok</span>';
                            if ($bgClass === 'bg-danger') echo '<span class="ds-badge ds-badge-danger"><i class="bi bi-x-circle"></i> Stok Tükendi</span>';
                        ?>
                    </h4>
                    <p class="text-muted mb-0 fs-7">
                        Katalog Kodu: <strong class="font-monospace text-dark bg-light px-2 py-1 rounded"><?= e($product['code']) ?></strong> 
                        &middot; Kategori: <strong class="text-dark"><?= e($product['category']) ?></strong>
                    </p>
                </div>
                <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                    <button type="button" class="ds-btn ds-btn-primary hover-lift" style="background-color: var(--c-success); border-color: var(--c-success);" data-bs-toggle="modal" data-bs-target="#detailStockInModal">
                        <i class="bi bi-arrow-down-left-square-fill"></i> Stok Girişi
                    </button>
                    <button type="button" class="ds-btn ds-btn-primary hover-lift" style="background-color: var(--c-danger); border-color: var(--c-danger);" data-bs-toggle="modal" data-bs-target="#detailStockOutModal">
                        <i class="bi bi-arrow-up-right-square-fill"></i> Stok Çıkışı
                    </button>
                    <button type="button" class="ds-btn ds-btn-secondary hover-lift" data-bs-toggle="modal" data-bs-target="#detailEditProductModal">
                        <i class="bi bi-pencil-square"></i> Düzenle
                    </button>
                </div>
            </div>

            <!-- Product Overview Cards -->
            <div class="row g-4 mb-4 fade-up delay-2">
                <!-- Card 1: Main Info -->
                <div class="col-12 col-lg-5">
                    <div class="ds-card h-100">
                        <div class="ds-card-body d-flex flex-column h-100">
                            <div class="d-flex align-items-start gap-4 mb-4">
                                <?php if (!empty($product['image_path']) && file_exists(dirname(__DIR__, 3) . '/public/' . $product['image_path'])): ?>
                                    <img src="<?= base_url('public/' . $product['image_path']) ?>" alt="<?= e($product['name']) ?>" class="rounded-4 object-fit-cover shadow-sm image-lightbox" style="width: 120px; height: 120px; cursor: zoom-in; border: 2px solid #fff;" data-src="<?= base_url('public/' . $product['image_path']) ?>">
                                <?php else: ?>
                                    <div class="rounded-4 bg-light text-secondary d-flex align-items-center justify-content-center shadow-sm" style="width: 120px; height: 120px; font-size: 3rem; border: 2px solid #fff;">
                                        <?= $_SESSION['active_module'] === 'furniture' ? '🪑' : '📦' ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="fw-bold text-dark mb-1"><?= e($product['name']) ?></h5>
                                    <p class="text-muted fs-7 mb-3 d-flex align-items-center gap-1"><i class="bi bi-award"></i> <?= e($product['brand'] ?: 'Marka Belirtilmedi') ?></p>
                                    <button class="ds-btn ds-btn-secondary ds-btn-sm rounded-pill shadow-sm hover-lift fw-medium" data-bs-toggle="modal" data-bs-target="#qrCodeModal">
                                        <i class="bi bi-qr-code-scan me-1"></i> QR Kod
                                    </button>
                                </div>
                            </div>
                            <div class="mt-auto bg-light rounded-3 p-3">
                                <table class="table table-sm table-borderless fs-7 mb-0 bg-transparent">
                                    <tr>
                                        <td class="text-secondary ps-0 py-2" style="width: 130px;">Barkod No:</td>
                                        <td class="fw-bold font-monospace text-dark py-2"><?= e($product['barcode'] ?: 'Tanımlanmadı') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0 py-2">Ölçü Birimi:</td>
                                        <td class="fw-bold text-dark py-2"><?= e($product['unit']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0 py-2">Ambar/Raf:</td>
                                        <td class="fw-bold text-dark py-2"><?= e($product['warehouse'] ?? '-') ?> (<?= e($product['rack_location'] ?? '-') ?>)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-secondary ps-0 py-2">Özellikler:</td>
                                        <td class="fw-bold text-dark py-2"><?= e($product['specifications'] ?: 'Belirtilmedi') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Stock & Cost Metrics -->
                <div class="col-12 col-lg-7">
                    <div class="row g-3 h-100">
                        <div class="col-6 col-md-3">
                            <div class="ds-card h-100 text-center hover-lift">
                                <div class="ds-card-body d-flex flex-column justify-content-center align-items-center">
                                    <div class="ds-stat-icon mb-3" style="background: var(--c-primary-subtle); color: var(--c-primary);"><i class="bi bi-stack fs-5"></i></div>
                                    <span class="fs-8 text-muted fw-bold d-block mb-1">MEVCUT STOK</span>
                                    <span class="fs-2 fw-bold <?= ($product['current_stock'] <= $product['min_stock']) ? 'text-danger' : 'text-dark' ?>">
                                        <?= e($product['current_stock']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="ds-card h-100 text-center hover-lift">
                                <div class="ds-card-body d-flex flex-column justify-content-center align-items-center">
                                    <div class="ds-stat-icon mb-3" style="background: var(--c-warning-subtle); color: var(--c-warning);"><i class="bi bi-exclamation-triangle fs-5"></i></div>
                                    <span class="fs-8 text-muted fw-bold d-block mb-1">KRİTİK EŞİK</span>
                                    <span class="fs-2 fw-bold text-secondary"><?= e($product['min_stock']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="ds-card h-100 text-center hover-lift">
                                <div class="ds-card-body d-flex flex-column justify-content-center align-items-center">
                                    <div class="ds-stat-icon mb-3" style="background: var(--c-info-subtle); color: var(--c-info);"><i class="bi bi-tag fs-5"></i></div>
                                    <span class="fs-8 text-muted fw-bold d-block mb-1">BİRİM FİYAT</span>
                                    <span class="fs-4 fw-bold text-dark mt-1"><?= number_format((float)$product['unit_price'], 2, ',', '.') ?> <small class="fs-8 text-muted fw-normal">TL</small></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="ds-card h-100 text-center hover-lift">
                                <div class="ds-card-body d-flex flex-column justify-content-center align-items-center">
                                    <div class="ds-stat-icon mb-3" style="background: var(--c-success-subtle); color: var(--c-success);"><i class="bi bi-cash-stack fs-5"></i></div>
                                    <span class="fs-8 text-muted fw-bold d-block mb-1">TOPLAM DEĞER</span>
                                    <span class="fs-4 fw-bold text-success mt-1"><?= number_format(((float)$product['unit_price'] * (float)$product['current_stock']), 2, ',', '.') ?> <small class="fs-8 fw-normal">TL</small></span>
                                </div>
                            </div>
                        </div>

                        <!-- Description Box -->
                        <div class="col-12">
                            <div class="ds-card">
                                <div class="ds-card-body text-start p-4">
                                    <strong class="fs-7 text-dark mb-2 d-block"><i class="bi bi-info-circle-fill me-2 text-primary"></i> Açıklama ve Notlar</strong>
                                    <p class="text-muted fs-7 mb-0 lh-base"><?= e($product['description'] ?: 'Bu ürün için herhangi bir açıklama girilmedi.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Movements Log Table -->
            <div class="ds-card fade-up delay-3">
                <div class="ds-card-header d-flex align-items-center justify-content-between">
                    <h6 class="ds-card-title mb-0 d-flex align-items-center gap-3">
                        <i class="bi bi-clock-history text-primary"></i> Ürün Stok Hareketleri Geçmişi
                    </h6>
                    <span class="ds-badge ds-badge-neutral shadow-sm"><?= count($movements) ?> İşlem Kaydı</span>
                </div>

                <div class="ds-card-body p-0 pb-2">
                    <div class="ds-table-container">
                        <table class="ds-table">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>İşlem Türü</th>
                                    <th class="text-center">Miktar</th>
                                    <th>Birim / Kişi</th>
                                    <th class="text-center">Önceki</th>
                                    <th class="text-center">Sonraki</th>
                                    <th>İşlemi Yapan</th>
                                    <th>Açıklama</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($movements)): ?>
                                    <?php foreach ($movements as $m): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium text-dark"><?= date('d.m.Y', strtotime($m['created_at'])) ?></span>
                                                    <span class="fs-8 text-muted"><?= date('H:i', strtotime($m['created_at'])) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($m['type'] === 'in'): ?>
                                                    <span class="ds-badge ds-badge-success"><i class="bi bi-arrow-down-left"></i> GİRİŞ</span>
                                                <?php else: ?>
                                                    <span class="ds-badge ds-badge-danger"><i class="bi bi-arrow-up-right"></i> ÇIKIŞ</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-bold fs-6 <?= ($m['type'] === 'in') ? 'text-success' : 'text-danger' ?>">
                                                <?= ($m['type'] === 'in' ? '+' : '-') ?><?= e($m['quantity']) ?>
                                            </td>
                                            <td class="text-dark fw-medium"><?= e($m['department_name'] ?: '-') ?></td>
                                            <td class="text-center text-muted"><?= e($m['prev_stock']) ?></td>
                                            <td class="text-center fw-bold text-dark">
                                                <div class="bg-light rounded-pill d-inline-block px-3 py-1"><?= e($m['next_stock']) ?></div>
                                            </td>
                                            <td class="text-secondary">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($m['user_name']) ?>&background=random&size=24" class="rounded-circle" alt="">
                                                    <?= e($m['user_name']) ?>
                                                </div>
                                            </td>
                                            <td class="text-muted fs-8" style="max-width:200px;">
                                                <div class="text-truncate" title="<?= e($m['description']) ?>"><?= e($m['description']) ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">📋</div>
                                                <h5>Kayıt Yok</h5>
                                                <p>Bu ürüne ait herhangi bir stok giriş veya çıkış hareketi bulunmuyor.</p>
                                            </div>
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
<!-- MODAL: STOK GİRİŞİ (DS)                    -->
<!-- ========================================== -->
<div class="modal fade" id="detailStockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-success-subtle);">
                <h5 class="modal-title fw-bold fs-6 text-success"><i class="bi bi-arrow-down-left-square-fill me-2"></i>Stok Girişi Formu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/stock-in') ?>" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-6">
                            <label class="ds-label">Giriş Miktarı <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="inQuantity" class="ds-input form-control-lg fw-bold text-success" min="1" required placeholder="0">
                        </div>
                        <div class="col-6 text-center d-flex flex-column justify-content-center">
                            <label class="ds-label mb-1">Yeni Stok (Önizleme)</label>
                            <div class="fs-2 fw-bold text-primary bg-light rounded-3 py-1" id="inNewStockPreview"><?= $product['current_stock'] ?></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="ds-label">Birim Maliyet (TL) <span class="text-muted fw-normal" style="font-size: 0.75rem;">(KDV Dahil Değildir)</span></label>
                        <input type="number" step="0.01" name="unit_cost" class="ds-input" value="<?= $product['unit_price'] ?>" min="0">
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-6">
                            <label class="ds-label">Teslim Eden</label>
                            <select name="delivered_by" class="ds-select ds-select-search" required placeholder="Personel seçin veya yazın...">
                                <option value="">-- Personel Seçiniz --</option>
                                <?php if(!empty($personnel)): foreach($personnel as $p): ?>
                                    <option value="<?= e($p['first_name'] . ' ' . $p['last_name']) ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="ds-label">Teslim Alan</label>
                            <select name="received_by" class="ds-select ds-select-search" required placeholder="Personel seçin veya yazın...">
                                <option value="">-- Personel Seçiniz --</option>
                                <?php if(!empty($personnel)): foreach($personnel as $p): ?>
                                    <option value="<?= e($p['first_name'] . ' ' . $p['last_name']) ?>" <?= (e($p['first_name'] . ' ' . $p['last_name']) === e(App\Helpers\Auth::user()['first_name'].' '.App\Helpers\Auth::user()['last_name'])) ? 'selected' : '' ?>><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="ds-label">Açıklama / İrsaliye No</label>
                        <textarea name="description" class="ds-input" rows="2" placeholder="Örn: Fatura ile giriş"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3" style="background: rgba(0,0,0,0.01);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" style="background-color: var(--c-success); border-color: var(--c-success);">Girişi Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: STOK ÇIKIŞI (DS)                    -->
<!-- ========================================== -->
<div class="modal fade" id="detailStockOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-danger-subtle);">
                <h5 class="modal-title fw-bold fs-6 text-danger"><i class="bi bi-arrow-up-right-square-fill me-2"></i>Stok Çıkışı Formu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/stock-out') ?>" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="ds-label">Çıkış Yapılacak Başkanlık <span class="text-danger">*</span></label>
                        <select name="department_id" class="ds-select" required>
                            <option value="">-- Birim Seçiniz --</option>
                            <?php foreach ($departments as $dept): ?>
                                <?php if ($dept['is_active']): ?>
                                    <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-6">
                            <label class="ds-label">Çıkış Miktarı <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="outQuantity" class="ds-input form-control-lg fw-bold text-danger" min="1" max="<?= $product['current_stock'] ?>" required placeholder="0">
                        </div>
                        <div class="col-6 text-center d-flex flex-column justify-content-center">
                            <label class="ds-label mb-1">Kalan Stok (Önizleme)</label>
                            <div class="fs-2 fw-bold text-primary bg-light rounded-3 py-1" id="outNewStockPreview"><?= $product['current_stock'] ?></div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-6">
                            <label class="ds-label">Teslim Eden</label>
                            <select name="delivered_by" class="ds-select ds-select-search" required placeholder="Personel seçin veya yazın...">
                                <option value="">-- Personel Seçiniz --</option>
                                <?php if(!empty($personnel)): foreach($personnel as $p): ?>
                                    <option value="<?= e($p['first_name'] . ' ' . $p['last_name']) ?>" <?= (e($p['first_name'] . ' ' . $p['last_name']) === e(App\Helpers\Auth::user()['first_name'].' '.App\Helpers\Auth::user()['last_name'])) ? 'selected' : '' ?>><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="ds-label">Teslim Alan</label>
                            <select name="received_by" class="ds-select ds-select-search" required placeholder="Personel seçin veya yazın...">
                                <option value="">-- Personel Seçiniz --</option>
                                <?php if(!empty($personnel)): foreach($personnel as $p): ?>
                                    <option value="<?= e($p['first_name'] . ' ' . $p['last_name']) ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="ds-label">Açıklama / Talep No</label>
                        <textarea name="description" class="ds-input" rows="2" placeholder="Hangi işlem için verildi vb."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3" style="background: rgba(0,0,0,0.01);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" id="btnSubmitStockOut" style="background-color: var(--c-danger); border-color: var(--c-danger);">Çıkışı Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: DÜZENLEME MODALI (DS)               -->
<!-- ========================================== -->
<div class="modal fade" id="detailEditProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--bg-surface-hover);">
                <h5 class="modal-title fw-bold fs-6 text-dark"><i class="bi bi-pencil-square me-2"></i>Ürün Bilgilerini Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/update') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Ürün Kodu <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="ds-input font-monospace text-uppercase" value="<?= e($product['code']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Barkod No</label>
                            <input type="text" name="barcode" class="ds-input font-monospace" value="<?= e($product['barcode']) ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="ds-label">Ürün Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="ds-input" value="<?= e($product['name']) ?>" required>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Kategori</label>
                            <select name="category_id" class="ds-select">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Ölçü Birimi</label>
                            <select name="unit" class="ds-select">
                                <?php foreach ($unitChoices as $key => $label): ?>
                                    <option value="<?= e($label) ?>" <?= ($product['unit'] === $label) ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Kritik Stok</label>
                            <input type="number" name="min_stock" class="ds-input" value="<?= $product['min_stock'] ?>" min="0" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Birim Maliyet (TL) <span class="text-muted fw-normal" style="font-size: 0.75rem;">(KDV Dahil Değildir)</span></label>
                            <input type="number" step="0.01" name="unit_price" class="ds-input" value="<?= $product['unit_price'] ?>" min="0">
                        </div>
                    </div>
                    <div class="mb-2">
                         <label class="ds-label">Görsel Yükle / Değiştir (İsteğe Bağlı)</label>
                         <input type="file" name="product_image" class="ds-input" accept="image/jpeg,image/png,image/webp" style="padding: 0.4rem 1rem;">
                    </div>
                </div>
                <div class="modal-footer border-top py-3" style="background: rgba(0,0,0,0.01);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" style="background-color: var(--text-main); border-color: var(--text-main);">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: QR KOD ETIKETI                      -->
<!-- ========================================== -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content ds-card border-0 text-center">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold fs-6 text-dark"><i class="bi bi-qr-code me-2"></i>QR Kod Etiketi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div id="qrcode-container" class="d-inline-block bg-white p-3 border rounded-4 shadow-sm mb-3"></div>
                <h5 class="fw-bold text-dark mb-1 font-monospace"><?= e($product['code']) ?></h5>
                <p class="text-muted fs-7 mb-0"><?= e($product['name']) ?></p>
            </div>
            <div class="modal-footer border-top-0 py-3 justify-content-center bg-light rounded-bottom-4">
                <button type="button" class="ds-btn ds-btn-primary" onclick="printQRCode()">
                    <i class="bi bi-printer me-1"></i> Yazdır
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Overlay Container -->
<div class="lightbox-overlay" id="lightboxOverlay">
    <img src="" alt="" class="lightbox-img" id="lightboxImage">
    <button class="btn btn-dark position-absolute top-0 end-0 m-4 rounded-circle fs-4 p-2 shadow-sm" id="lightboxClose" style="width:50px;height:50px; z-index: 9999;">
        <i class="bi bi-x"></i>
    </button>
</div>
<style>
.lightbox-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.9); z-index: -1; opacity: 0;
    display: flex; align-items: center; justify-content: center;
    transition: var(--trans-normal);
}
.lightbox-overlay.active {
    z-index: 2000; opacity: 1;
}
.lightbox-img {
    max-width: 90%; max-height: 90%; border-radius: var(--radius-lg);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    transform: scale(0.95); transition: var(--trans-normal);
}
.lightbox-overlay.active .lightbox-img { transform: scale(1); }
</style>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Toast Messages for Flash Data
    <?php if (!empty($flashSuccess)): ?>
    if(typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success', title: '<?= e($flashSuccess) ?>',
            showConfirmButton: false, timer: 3000, timerProgressBar: true, customClass: { popup: 'ds-card shadow-lg' }
        });
    }
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
    if(typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'error', title: '<?= e($flashError) ?>',
            showConfirmButton: false, timer: 4000, timerProgressBar: true, customClass: { popup: 'ds-card shadow-lg' }
        });
    }
    <?php endif; ?>

    // Generate QR Code
    if (typeof QRCode !== 'undefined') {
        const qrContainer = document.getElementById('qrcode-container');
        if (qrContainer) {
            new QRCode(qrContainer, {
                text: "<?= e($product['code']) ?>",
                width: 160,
                height: 160,
                colorDark : "#0f172a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    }

    // Lightbox Logic
    const lightboxOverlay = document.getElementById('lightboxOverlay');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxClose = document.getElementById('lightboxClose');

    document.querySelectorAll('.image-lightbox').forEach(img => {
        img.addEventListener('click', function() {
            lightboxImage.src = this.dataset.src;
            lightboxOverlay.classList.add('active');
        });
    });

    const closeLightbox = () => lightboxOverlay.classList.remove('active');
    if(lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
    if(lightboxOverlay) lightboxOverlay.addEventListener('click', function(e) {
        if(e.target === lightboxOverlay) closeLightbox();
    });

    // Live Stock Previews
    const currentStock = <?= (int)$product['current_stock'] ?>;
    
    // In Modal Preview
    const inQty = document.getElementById('inQuantity');
    const inPrev = document.getElementById('inNewStockPreview');
    if(inQty && inPrev) {
        inQty.addEventListener('input', function() {
            const val = parseInt(this.value) || 0;
            inPrev.innerText = currentStock + val;
        });
    }
    
    // Out Modal Preview
    const outQty = document.getElementById('outQuantity');
    const outPrev = document.getElementById('outNewStockPreview');
    const btnOut = document.getElementById('btnSubmitStockOut');
    if(outQty && outPrev) {
        outQty.addEventListener('input', function() {
            const val = parseInt(this.value) || 0;
            const res = currentStock - val;
            outPrev.innerText = res;
            
            if(res < 0) {
                outPrev.classList.replace('text-primary', 'text-danger');
                if(btnOut) btnOut.disabled = true;
            } else {
                outPrev.classList.replace('text-danger', 'text-primary');
                if(btnOut) btnOut.disabled = false;
            }
        });
    }
});

function printQRCode() {
    const printContent = document.getElementById('qrCodeModal').querySelector('.modal-body').innerHTML;
    const printWindow = window.open('', '', 'height=600, width=600');
    printWindow.document.write('<html><head><title>QR Kod Yazdır</title>');
    printWindow.document.write('<style>body { font-family: "Segoe UI", sans-serif; text-align: center; padding: 40px; margin:0;} h5 { font-size: 24px; margin: 15px 0 5px 0; } p { font-size: 16px; margin: 0; color: #555; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => { printWindow.print(); printWindow.close(); }, 300);
}
</script>
