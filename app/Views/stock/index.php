<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

            <!-- Page Title & Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4 fade-up delay-1">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2" style="letter-spacing: -0.5px;">
                        <div class="ds-avatar" style="background: var(--c-primary-subtle); color: var(--c-primary);">
                            <?= $_SESSION['active_module'] === 'furniture' ? '🪑' : '🧼' ?>
                        </div>
                        Ürün Kataloğu ve Stok Takibi
                    </h4>
                    <p class="text-muted mb-0 fs-7 mt-2">Ambar sarf malzemeleri ve demirbaş ürünlerinin merkezi yönetim ekranı</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                    <button type="button" class="ds-btn ds-btn-primary hover-lift" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-lg"></i> Yeni Ürün Ekle
                    </button>
                </div>
            </div>

            <!-- Summary KPI Pill Tabs -->
            <div class="row g-3 mb-4 fade-up delay-2">
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('products?tab=all') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift <?= ($filters['status_tab'] === 'all') ? 'border-primary' : '' ?>" style="<?= ($filters['status_tab'] === 'all') ? 'box-shadow: 0 0 0 2px var(--c-primary-subtle);' : '' ?>">
                            <div class="ds-card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase d-block mb-1 text-muted">TÜM ÜRÜNLER</span>
                                    <span class="fs-3 fw-bold text-dark"><?= $totalProductCount ?></span>
                                </div>
                                <div class="ds-stat-icon" style="background: var(--c-primary-subtle); color: var(--c-primary);"><i class="bi bi-boxes fs-4"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('products?tab=critical') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift <?= ($filters['status_tab'] === 'critical') ? 'border-warning' : '' ?>" style="<?= ($filters['status_tab'] === 'critical') ? 'box-shadow: 0 0 0 2px var(--c-warning-subtle);' : '' ?>">
                            <div class="ds-card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase d-block mb-1 text-warning">KRİTİK STOK</span>
                                    <span class="fs-3 fw-bold text-warning"><?= $critCount ?></span>
                                </div>
                                <div class="ds-stat-icon" style="background: var(--c-warning-subtle); color: var(--c-warning);"><i class="bi bi-exclamation-triangle-fill fs-4"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('products?tab=out_of_stock') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift <?= ($filters['status_tab'] === 'out_of_stock') ? 'border-danger' : '' ?>" style="<?= ($filters['status_tab'] === 'out_of_stock') ? 'box-shadow: 0 0 0 2px var(--c-danger-subtle);' : '' ?>">
                            <div class="ds-card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase d-block mb-1 text-danger">TÜKENENLER</span>
                                    <span class="fs-3 fw-bold text-danger"><?= $outOfStockCount ?></span>
                                </div>
                                <div class="ds-stat-icon" style="background: var(--c-danger-subtle); color: var(--c-danger);"><i class="bi bi-x-circle-fill fs-4"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('products/movements') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift">
                            <div class="ds-card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="fs-8 fw-bold text-uppercase d-block mb-1 text-secondary">HAREKETLER</span>
                                    <span class="fs-6 fw-bold text-dark">Giriş/Çıkışlar</span>
                                </div>
                                <div class="ds-stat-icon" style="background: var(--c-secondary-subtle); color: var(--c-secondary);"><i class="bi bi-journal-text fs-4"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Filter & Table Card -->
            <div class="ds-card fade-up delay-3 mb-4">
                <div class="ds-card-header bg-transparent border-bottom-0 pt-4 px-4 pb-0">
                    <form method="GET" action="<?= base_url('products') ?>" class="row g-3 align-items-center w-100 m-0">
                        <input type="hidden" name="tab" value="<?= e($filters['status_tab']) ?>">
                        
                        <div class="col-12 col-md-5 col-lg-4 px-1">
                            <div class="position-relative">
                                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                                <input type="text" name="search" class="ds-input ps-5" placeholder="Ürün adı veya kod..." value="<?= e($filters['search']) ?>">
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3 px-1">
                            <select name="category" class="ds-select" onchange="this.form.submit()">
                                <option value="">-- Tüm Kategoriler --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['name']) ?>" <?= ($filters['category'] === $cat['name']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-3 col-lg-2 px-1 d-flex gap-2">
                            <button type="submit" class="ds-btn ds-btn-primary w-100">Filtrele</button>
                            <?php if (!empty($filters['search']) || !empty($filters['category']) || $filters['status_tab'] !== 'all'): ?>
                                <a href="<?= base_url('products') ?>" class="ds-btn ds-btn-secondary" title="Temizle"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Products Table -->
                <div class="ds-card-body px-4 pt-3 pb-0">
                    <div class="ds-table-container">
                        <table class="ds-table">
                            <thead>
                                <tr>
                                    <th style="width: 65px;">Görsel</th>
                                    <th>Ürün Bilgisi</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Mevcut Stok</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-end">İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stocks)): ?>
                                    <?php foreach ($stocks as $stk): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($stk['image_path']) && file_exists(dirname(__DIR__, 3) . '/public/' . $stk['image_path'])): ?>
                                                    <img src="<?= base_url('public/' . $stk['image_path']) ?>" alt="<?= e($stk['name']) ?>" class="ds-product-img image-lightbox" data-src="<?= base_url('public/' . $stk['image_path']) ?>" style="cursor: pointer;">
                                                <?php else: ?>
                                                    <div class="ds-product-img d-flex align-items-center justify-content-center text-muted fs-5">
                                                        <?= $_SESSION['active_module'] === 'furniture' ? '🪑' : '📦' ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="ds-product-info">
                                                    <h6><a href="<?= base_url('products/detail?id=' . $stk['id']) ?>" class="text-dark"><?= e($stk['name']) ?></a></h6>
                                                    <span><?= e($stk['code']) ?> <?= !empty($stk['brand']) ? ' • ' . e($stk['brand']) : '' ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ds-badge ds-badge-neutral"><?= e($stk['category']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold fs-6 <?= ($stk['quantity'] <= $stk['min_threshold']) ? 'text-danger' : 'text-dark' ?>">
                                                    <?= e($stk['quantity']) ?> <span class="fs-8 fw-normal text-muted"><?= e($stk['unit']) ?></span>
                                                </div>
                                                <div class="fs-8 text-muted mt-1">Min: <?= e($stk['min_threshold']) ?></div>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    $bgClass = $stk['badge_class'];
                                                    if (str_contains($bgClass, 'success')) echo '<span class="ds-badge ds-badge-success"><i class="bi bi-check-circle"></i> Yeterli</span>';
                                                    if (str_contains($bgClass, 'warning')) echo '<span class="ds-badge ds-badge-warning"><i class="bi bi-exclamation-triangle"></i> Azaldı</span>';
                                                    if (str_contains($bgClass, 'danger')) echo '<span class="ds-badge ds-badge-danger"><i class="bi bi-x-circle"></i> Kritik</span>';
                                                ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group shadow-sm rounded-3">
                                                    <a href="<?= base_url('products/detail?id=' . $stk['id']) ?>" class="btn btn-light border-0 btn-sm text-primary hover-lift" title="İncele"><i class="bi bi-eye"></i></a>
                                                    <button type="button" class="btn btn-light border-0 btn-sm text-secondary hover-lift btn-edit-product" onclick='prepareEditProductModal(<?= json_encode($stk, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' data-bs-toggle="modal" data-bs-target="#editProductModal" title="Düzenle"><i class="bi bi-pencil"></i></button>
                                                    <button type="button" class="btn btn-light border-0 btn-sm text-danger hover-lift btn-delete-product" data-id="<?= $stk['id'] ?>" data-name="<?= e($stk['name']) ?>" title="Sil"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="empty-state-icon"><?= $_SESSION['active_module'] === 'furniture' ? '🛋️' : '🧹' ?></div>
                                                <h5>Ürün Bulunamadı</h5>
                                                <p>Aradığınız kriterlere uygun kayıtlı malzeme bulunmuyor. Yeni bir ürün ekleyerek envanterinizi oluşturmaya başlayabilirsiniz.</p>
                                                <button type="button" class="ds-btn ds-btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="bi bi-plus-lg"></i> Yeni Ürün Ekle</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="ds-card-header border-top bg-transparent py-3 mt-3 d-flex align-items-center justify-content-between px-4">
                        <div class="fs-8 text-muted">
                            Toplam <strong><?= $pagination['total_items'] ?></strong> üründen <strong><?= count($stocks) ?></strong> adet gösteriliyor.
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                    <li class="page-item <?= ($p === $pagination['current_page']) ? 'active' : '' ?>">
                                        <a class="page-link border-0 <?= ($p === $pagination['current_page']) ? 'bg-primary text-white' : 'text-dark bg-light' ?>" href="<?= base_url('products?page=' . $p . '&tab=' . e($filters['status_tab']) . '&search=' . urlencode($filters['search']) . '&category=' . urlencode($filters['category'])) ?>">
                                            <?= $p ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- MODAL: YENİ ÜRÜN EKLE (DS)                 -->
<!-- ========================================== -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-primary-subtle);">
                <h5 class="modal-title fw-bold fs-6 text-primary"><i class="bi bi-box-seam me-2"></i>Yeni Ürün Kaydı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/store') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <p class="text-muted fs-8 mb-4">Tüm alanlar isteğe bağlıdır. Boş bırakılan kod, ad, kategori ve ölçü birimi sistem tarafından tamamlanır.</p>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Ürün Kodu</label>
                            <input type="text" name="code" id="add_code" class="ds-input font-monospace text-uppercase" placeholder="Boşsa otomatik oluşturulur">
                            <div id="add_code_error" class="text-danger fs-8 mt-1 d-none"><i class="bi bi-exclamation-circle"></i> Bu ürün kodu zaten kayıtlı!</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Barkod No</label>
                            <input type="text" name="barcode" id="add_barcode" class="ds-input font-monospace" placeholder="Örn: 869000...">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="ds-label">Ürün Adı</label>
                        <input type="text" name="name" class="ds-input" placeholder="Boşsa Adsız Ürün kullanılır">
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Kategori</label>
                            <select name="category_id" class="ds-select">
                                <option value="">Otomatik (Genel)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Ölçü Birimi</label>
                            <select name="unit" class="ds-select">
                                <option value="">Otomatik (Adet)</option>
                                <?php foreach ($unitChoices as $label): ?>
                                    <option value="<?= e($label) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="ds-label">Başlangıç Stok</label>
                            <input type="number" name="quantity" class="ds-input" value="0" min="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="ds-label">Kritik Eşik</label>
                            <input type="number" name="min_threshold" class="ds-input" value="10" min="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="ds-label">Birim Maliyet (TL) <span class="text-muted fw-normal" style="font-size: 0.75rem;">(KDV Dahil Değildir)</span></label>
                            <input type="number" step="0.01" name="unit_price" class="ds-input" value="0.00" min="0">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Marka / Üretici</label>
                            <input type="text" name="brand" class="ds-input">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Görsel (JPG, PNG)</label>
                            <input type="file" name="product_image" class="ds-input" accept="image/jpeg,image/png,image/webp" style="padding: 0.4rem 1rem;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="ds-label">Teknik Özellikler</label>
                        <input type="text" name="specifications" class="ds-input">
                    </div>

                    <div class="mb-2">
                        <label class="ds-label">Açıklama</label>
                        <textarea name="description" class="ds-input" rows="2"></textarea>
                    </div>

                </div>
                <div class="modal-footer border-top py-3" style="background: rgba(0,0,0,0.01);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" id="btnSubmitAddProduct">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: ÜRÜN DÜZENLE (DS)                   -->
<!-- ========================================== -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--bg-surface-hover);">
                <h5 class="modal-title fw-bold fs-6 text-dark"><i class="bi bi-pencil-square me-2"></i>Ürün Bilgilerini Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/update') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Ürün Kodu <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit_code" class="ds-input font-monospace text-uppercase" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Barkod No</label>
                            <input type="text" name="barcode" id="edit_barcode" class="ds-input font-monospace">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="ds-label">Ürün Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="ds-input" required>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="ds-label">Kategori</label>
                            <select name="category_id" id="edit_category_id" class="ds-select">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="ds-label">Ölçü Birimi</label>
                            <select name="unit" id="edit_unit" class="ds-select">
                                <?php foreach ($unitChoices as $key => $label): ?>
                                <option value="<?= e($label) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="ds-label">Durum</label>
                            <select name="status" id="edit_status" class="ds-select">
                                <option value="active">Aktif</option>
                                <option value="passive">Pasif</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Kritik Stok Eşiği</label>
                            <input type="number" name="min_threshold" id="edit_min_threshold" class="ds-input" min="0" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Birim Maliyet (TL) <span class="text-muted fw-normal" style="font-size: 0.75rem;">(KDV Dahil Değildir)</span></label>
                            <input type="number" step="0.01" name="unit_price" id="edit_unit_price" class="ds-input" min="0">
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Marka</label>
                            <input type="text" name="brand" id="edit_brand" class="ds-input">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Görsel Değiştir</label>
                            <input type="file" name="product_image" class="ds-input" accept="image/jpeg,image/png,image/webp" style="padding: 0.4rem 1rem;">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="ds-label">Teknik Özellikler</label>
                        <input type="text" name="specifications" id="edit_specifications" class="ds-input">
                    </div>
                    <div class="mb-2">
                        <label class="ds-label">Açıklama</label>
                        <textarea name="description" id="edit_description" class="ds-input" rows="2"></textarea>
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
<!-- MODAL: ÜRÜN SİLME (DS)                     -->
<!-- ========================================== -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content ds-card border-0 text-center">
            <form action="<?= base_url('products/delete') ?>" method="POST">
                <input type="hidden" name="id" id="delete_product_id">
                <div class="modal-body p-5">
                    <div class="ds-avatar mb-4" style="background: var(--c-danger-subtle); color: var(--c-danger); width: 64px; height: 64px; font-size: 2rem;">
                        <i class="bi bi-trash"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Emin misiniz?</h5>
                    <p class="text-dark fw-semibold mb-1" id="deleteProductName">--</p>
                    <p class="text-muted fs-7 mb-0">Bu ürün çöp kutusuna taşınacak. Geçmiş kayıtlar sistemde tutulur.</p>
                </div>
                <div class="modal-footer border-top-0 py-3 justify-content-center" style="background: rgba(0,0,0,0.02);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                    <button type="submit" class="ds-btn ds-btn-primary" style="background-color: var(--c-danger); border-color: var(--c-danger);">Evet, Sil</button>
                </div>
            </form>
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

<script>
function prepareEditProductModal(stk) {
    document.getElementById('edit_id').value = stk.id;
    document.getElementById('edit_code').value = stk.code;
    document.getElementById('edit_barcode').value = stk.barcode || '';
    document.getElementById('edit_name').value = stk.name;
    document.getElementById('edit_category_id').value = stk.category_id;
    document.getElementById('edit_unit').value = stk.unit;
    document.getElementById('edit_min_threshold').value = stk.min_threshold;
    document.getElementById('edit_unit_price').value = stk.unit_price;
    document.getElementById('edit_status').value = stk.status || 'Aktif';
    document.getElementById('edit_brand').value = stk.brand || '';
    document.getElementById('edit_specifications').value = stk.specifications || '';
    document.getElementById('edit_description').value = stk.description || '';
}

document.addEventListener('DOMContentLoaded', function() {
    // Toast Messages for Flash Data
    <?php if (!empty($flashSuccess)): ?>
    if(typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success', title: '<?= e($flashSuccess) ?>',
            showConfirmButton: false, timer: 3000, timerProgressBar: true
        });
    }
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
    if(typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'error', title: '<?= e($flashError) ?>',
            showConfirmButton: false, timer: 4000, timerProgressBar: true
        });
    }
    <?php endif; ?>

    // Delete Product
    document.querySelectorAll('.btn-delete-product').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_product_id').value = this.dataset.id;
            document.getElementById('deleteProductName').innerText = this.dataset.name;
            const modal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
            modal.show();
        });
    });

    // Code duplicate check
    const addCodeInput = document.getElementById('add_code');
    if (addCodeInput) {
        addCodeInput.addEventListener('blur', function() {
            const code = this.value.trim();
            const errEl = document.getElementById('add_code_error');
            const submitButton = document.getElementById('btnSubmitAddProduct');
            if (code.length < 2) {
                errEl.classList.add('d-none');
                submitButton.disabled = false;
                return;
            }
            fetch('<?= base_url('api/check-product-code?code=') ?>' + encodeURIComponent(code))
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        errEl.classList.remove('d-none');
                        submitButton.disabled = true;
                    } else {
                        errEl.classList.add('d-none');
                        submitButton.disabled = false;
                    }
                })
                .catch(() => {
                    errEl.classList.add('d-none');
                    submitButton.disabled = false;
                });
        });
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
    lightboxClose.addEventListener('click', closeLightbox);
    lightboxOverlay.addEventListener('click', function(e) {
        if(e.target === lightboxOverlay) closeLightbox();
    });
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
