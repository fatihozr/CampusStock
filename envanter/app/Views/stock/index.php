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

            <!-- Page Title & Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-boxes text-primary fs-3"></i>
                        Temizlik Malzemeleri Ürün Kataloğu ve Stok Takibi
                    </h4>
                    <p class="text-muted mb-0 fs-7">Belediye Destek Hizmetleri Birimi ambar sarf malzemeleri ve stok yönetim ekranı</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-success btn-sm rounded-2 d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#stockInModal">
                        <i class="bi bi-arrow-down-circle-fill"></i> Stok Girişi Yap
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#stockOutModal">
                        <i class="bi bi-arrow-up-circle-fill"></i> Stok Çıkışı Yap
                    </button>
                    <button type="button" class="btn btn-primary btn-sm rounded-2 d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-lg"></i> Yeni Ürün Ekle
                    </button>
                </div>
            </div>

            <!-- Summary KPI Pill Tabs -->
            <div class="row g-2 mb-4">
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('stocks?tab=all') ?>" class="card border-0 shadow-sm text-decoration-none rounded-3 <?= ($filters['status_tab'] === 'all') ? 'bg-primary text-white' : 'bg-white text-dark' ?>">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-8 fw-semibold text-uppercase d-block opacity-75">TÜM ÜRÜNLER</span>
                                <span class="fs-4 fw-bold"><?= $pagination['total_items'] ?> Kataloğa Kayıtlı</span>
                            </div>
                            <i class="bi bi-box-seam fs-2 opacity-50"></i>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('stocks?tab=critical') ?>" class="card border-0 shadow-sm text-decoration-none rounded-3 <?= ($filters['status_tab'] === 'critical') ? 'bg-warning text-dark' : 'bg-white text-dark' ?>">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-8 fw-semibold text-uppercase d-block opacity-75 text-warning-emphasis">KRİTİK STOK UYARISI</span>
                                <span class="fs-4 fw-bold text-danger"><?= $critCount ?> Kalem Ürün</span>
                            </div>
                            <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('stocks?tab=out_of_stock') ?>" class="card border-0 shadow-sm text-decoration-none rounded-3 <?= ($filters['status_tab'] === 'out_of_stock') ? 'bg-danger text-white' : 'bg-white text-dark' ?>">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-8 fw-semibold text-uppercase d-block opacity-75">STOKTA TÜKENENLER</span>
                                <span class="fs-4 fw-bold">Sıfır Stok</span>
                            </div>
                            <i class="bi bi-x-circle-fill fs-2 opacity-50"></i>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-3">
                    <a href="<?= base_url('stocks/movements') ?>" class="card border-0 shadow-sm text-decoration-none bg-white text-dark rounded-3 hover-shadow">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-8 fw-semibold text-uppercase d-block text-muted">STOK HAREKETLERİ</span>
                                <span class="fs-6 fw-bold text-primary">Giriş / Çıkış Logları &rarr;</span>
                            </div>
                            <i class="bi bi-journal-text fs-2 text-primary"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Filter & Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white p-3 border-bottom-0">
                    <form method="GET" action="<?= base_url('stocks') ?>" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="<?= e($filters['status_tab']) ?>">
                        
                        <div class="col-12 col-md-5 col-lg-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ürün adı, stok kodu veya barkod ara..." value="<?= e($filters['search']) ?>">
                            </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Tüm Kategoriler (Filtre Yok)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['name']) ?>" <?= ($filters['category'] === $cat['name']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-3 col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Filtrele</button>
                            <?php if (!empty($filters['search']) || !empty($filters['category']) || $filters['status_tab'] !== 'all'): ?>
                                <a href="<?= base_url('stocks') ?>" class="btn btn-outline-secondary btn-sm" title="Filtreleri Temizle"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Products Table -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-8 text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3" style="width: 60px;">GÖRSEL</th>
                                    <th>ÜRÜN KODU</th>
                                    <th>ÜRÜN ADI & AÇIKLAMA</th>
                                    <th>KATEGORİ</th>
                                    <th class="text-center">MEVCUT STOK</th>
                                    <th class="text-center">MİN. EŞİK</th>
                                    <th>BİRİM</th>
                                    <th class="text-end">BİRİM MALİYET</th>
                                    <th class="text-end">TOPLAM DEĞER</th>
                                    <th class="text-center">DURUM</th>
                                    <th class="text-end pe-3">İŞLEMLER</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($stocks)): ?>
                                    <?php foreach ($stocks as $stk): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <?php if (!empty($stk['image_path']) && file_exists(BASE_PATH . '/public/' . $stk['image_path'])): ?>
                                                    <img src="<?= base_url($stk['image_path']) ?>" alt="<?= e($stk['name']) ?>" class="rounded border object-fit-cover" style="width: 44px; height: 44px;">
                                                <?php else: ?>
                                                    <div class="rounded border bg-light text-muted d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark font-monospace text-white px-2 py-1 fs-8"><?= e($stk['code']) ?></span>
                                                <?php if (!empty($stk['barcode'])): ?>
                                                    <div class="text-muted fs-8 font-monospace mt-0.5"><i class="bi bi-upc-scan me-1"></i><?= e($stk['barcode']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('stocks/detail?id=' . $stk['id']) ?>" class="fw-bold text-dark text-decoration-none d-block hover-primary">
                                                    <?= e($stk['name']) ?>
                                                </a>
                                                <?php if (!empty($stk['brand'])): ?>
                                                    <small class="text-muted fs-8"><i class="bi bi-award me-1"></i><?= e($stk['brand']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-2"><?= e($stk['category']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold fs-6 <?= ($stk['quantity'] <= $stk['min_threshold']) ? 'text-danger' : 'text-dark' ?>">
                                                    <?= e($stk['quantity']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-secondary border px-2 py-1"><?= e($stk['min_threshold']) ?></span>
                                            </td>
                                            <td>
                                                <span class="text-secondary fs-7"><?= e($stk['unit']) ?></span>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">
                                                <?= number_format($stk['unit_price'], 2, ',', '.') ?> TL
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                <?= number_format($stk['total_val'], 2, ',', '.') ?> TL
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= e($stk['badge_class']) ?> px-2.5 py-1 rounded-pill fs-8">
                                                    <?= e($stk['stock_status_label']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="<?= base_url('stocks/detail?id=' . $stk['id']) ?>" class="btn btn-outline-info border-0" title="Ürün Detayı">
                                                        <i class="bi bi-eye fs-6"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-success border-0 btn-stock-in" data-id="<?= $stk['id'] ?>" data-name="<?= e($stk['name']) ?>" data-code="<?= e($stk['code']) ?>" data-price="<?= $stk['unit_price'] ?>" title="Stok Girişi">
                                                        <i class="bi bi-plus-circle fs-6"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning border-0 btn-stock-out" data-id="<?= $stk['id'] ?>" data-name="<?= e($stk['name']) ?>" data-code="<?= e($stk['code']) ?>" data-qty="<?= $stk['quantity'] ?>" title="Stok Çıkışı">
                                                        <i class="bi bi-dash-circle fs-6"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary border-0 btn-edit-product" onclick='prepareEditProductModal(<?= json_encode($stk, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' data-bs-toggle="modal" data-bs-target="#editProductModal" title="Düzenle">
                                                        <i class="bi bi-pencil fs-6"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger border-0 btn-delete-product" data-id="<?= $stk['id'] ?>" data-name="<?= e($stk['name']) ?>" title="Sil">
                                                        <i class="bi bi-trash fs-6"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            <i class="bi bi-boxes fs-1 d-block text-muted mb-2"></i>
                                            Aradığınız kriterlere uygun temizlik malzemesi bulunamadı.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="card-footer bg-white py-3 border-top d-flex align-items-center justify-content-between">
                        <div class="fs-7 text-muted">
                            Toplam <strong><?= $pagination['total_items'] ?></strong> üründen <strong><?= count($stocks) ?></strong> adet gösteriliyor.
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                                    <li class="page-item <?= ($p === $pagination['current_page']) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= base_url('stocks?page=' . $p . '&tab=' . e($filters['status_tab']) . '&search=' . urlencode($filters['search']) . '&category=' . urlencode($filters['category'])) ?>">
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
<!-- MODAL 1: YENİ ÜRÜN EKLE                    -->
<!-- ========================================== -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-box-seam me-2"></i>Yeni Temizlik Malzemesi Ürünü Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/store') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Ürün Kodu <span class="text-danger">* (Benzersiz)</span></label>
                            <input type="text" name="code" id="add_code" class="form-control form-control-sm font-monospace text-uppercase" placeholder="Örn: TMZ-00011" required>
                            <div id="add_code_error" class="text-danger fs-8 mt-1 d-none">Bu ürün kodu zaten kayıtlı!</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Barkod No</label>
                            <input type="text" name="barcode" id="add_barcode" class="form-control form-control-sm font-monospace" placeholder="Örn: 8690000000110">
                            <div id="add_barcode_error" class="text-danger fs-8 mt-1 d-none">Bu barkod baska bir üründe kullanılıyor!</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Malzeme Ürün Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Örn: Çamaşır Suyu 5 L Ultra Yoğun" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Kategori <span class="text-danger">*</span></label>
                            <select name="category" class="form-select form-select-sm" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Ölçü Birimi <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select form-select-sm" required>
                                <?php foreach ($unitChoices as $key => $label): ?>
                                    <option value="<?= e($key) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-semibold text-dark">Başlangıç Stok Miktarı</label>
                            <input type="number" name="quantity" class="form-control form-control-sm" value="0" min="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-semibold text-dark">Kritik Stok Eşiği (Minimum)</label>
                            <input type="number" name="min_threshold" class="form-control form-control-sm" value="10" min="0" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-semibold text-dark">Varsayılan Birim Maliyet (TL)</label>
                            <input type="number" step="0.01" name="unit_price" class="form-control form-control-sm" value="0.00" min="0">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 text-muted">Marka / Üretici Firma</label>
                            <input type="text" name="brand" class="form-control form-control-sm" placeholder="Örn: Domestos / Pro">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 text-muted">Ürün Görseli (JPG, PNG, WEBP)</label>
                            <input type="file" name="product_image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 text-muted">Teknik Özellikler / Ambalaj Detayı</label>
                        <input type="text" name="specifications" class="form-control form-control-sm" placeholder="Örn: 5 Litre kilitli bidon ambalaj">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Açıklama / Kullanım Notu</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Zemin temizleme, wc hijyeni vb."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="btnSubmitAddProduct">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: ÜRÜN DÜZENLE                       -->
<!-- ========================================== -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-pencil-square me-2"></i>Temizlik Malzemesi Düzenle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/update') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Ürün Kodu <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="edit_code" class="form-control form-control-sm font-monospace text-uppercase" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Barkod No</label>
                            <input type="text" name="barcode" id="edit_barcode" class="form-control form-control-sm font-monospace">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Malzeme Ürün Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control form-control-sm" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Kategori</label>
                            <select name="category" id="edit_category" class="form-select form-select-sm">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Ölçü Birimi</label>
                            <select name="unit" id="edit_unit" class="form-select form-select-sm">
                                <?php foreach ($unitChoices as $key => $label): ?>
                                    <option value="<?= e($key) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-semibold text-dark">Kritik Stok Eşiği (Minimum)</label>
                            <input type="number" name="min_threshold" id="edit_min_threshold" class="form-control form-control-sm" min="0" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-semibold text-dark">Birim Maliyet (TL)</label>
                            <input type="number" step="0.01" name="unit_price" id="edit_unit_price" class="form-control form-control-sm" min="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fs-7 fw-semibold text-dark">Durum</label>
                            <select name="status" id="edit_status" class="form-select form-select-sm">
                                <option value="Aktif">Aktif</option>
                                <option value="Pasif">Pasif</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 text-muted">Marka</label>
                            <input type="text" name="brand" id="edit_brand" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-7 text-muted">Görsel Değiştir (İsteğe Bağlı)</label>
                            <input type="file" name="product_image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 text-muted">Teknik Özellikler</label>
                        <input type="text" name="specifications" id="edit_specifications" class="form-control form-control-sm">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Açıklama</label>
                        <textarea name="description" id="edit_description" class="form-control form-control-sm" rows="2"></textarea>
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

<!-- ========================================== -->
<!-- MODAL 3: STOK GİRİŞİ (STOK_GIRIS)          -->
<!-- ========================================== -->
<div class="modal fade" id="stockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-arrow-down-circle me-2"></i>Stok Girişi Kaydet (Ambar Alımı)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/stock-in') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Stok Alınacak Ürün <span class="text-danger">*</span></label>
                        <select name="stock_id" id="in_stock_id" class="form-select form-select-sm" required>
                            <option value="">-- Ürün Seçiniz --</option>
                            <?php foreach ($stocks as $s): ?>
                                <option value="<?= $s['id'] ?>" data-price="<?= $s['unit_price'] ?>">
                                    [<?= e($s['code']) ?>] <?= e($s['name']) ?> (Mevcut: <?= $s['quantity'] ?> <?= e($s['unit']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Giriş Miktarı <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control form-control-sm" min="1" required placeholder="Miktar">
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-7 fw-semibold text-dark">Giriş Birim Maliyeti (TL)</label>
                            <input type="number" step="0.01" name="unit_cost" id="in_unit_cost" class="form-control form-control-sm" min="0" placeholder="TL">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Açıklama / Fatura / Evrak No</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Örn: İhale alımı teslimatı ambara giriş yapıldı."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">Stok Girişini Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: STOK ÇIKIŞI (STOK_CIKIS)          -->
<!-- ========================================== -->
<div class="modal fade" id="stockOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-arrow-up-circle me-2"></i>Stok Çıkışı Yap (Yetkili)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/stock-out') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 px-3 fs-7 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                        <span>Negatif stok engelleme kontrolü aktiftir. Mevcut stoktan fazla çıkış yapılamaz.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Stok Çıkılacak Ürün <span class="text-danger">*</span></label>
                        <select name="stock_id" id="out_stock_id" class="form-select form-select-sm" required>
                            <option value="">-- Ürün Seçiniz --</option>
                            <?php foreach ($stocks as $s): ?>
                                <option value="<?= $s['id'] ?>" data-qty="<?= $s['quantity'] ?>">
                                    [<?= e($s['code']) ?>] <?= e($s['name']) ?> (Mevcut: <?= $s['quantity'] ?> <?= e($s['unit']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Çıkış Miktarı <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" id="out_quantity" class="form-control form-control-sm" min="1" required placeholder="Miktar">
                        <div id="out_qty_warning" class="text-danger fs-8 mt-1 d-none">Girilen miktar mevcut stoktan büyük olamaz!</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Çıkış Sebebi / Çıkış Yapılan Birim Notu</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Örn: Ana bina kat temizliği periyodik çıkışı."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4" id="btnSubmitStockOut">Stok Çıkışını Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 5: ÜRÜN SİLME SİLME MODALI           -->
<!-- ========================================== -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-trash me-2"></i>Ürünü Çöp Kutusuna Taşı</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('stocks/delete') ?>" method="POST">
                <input type="hidden" name="id" id="delete_product_id">
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-exclamation-octagon text-danger display-4 mb-3 d-block"></i>
                    <h6 class="fw-bold text-dark mb-2" id="deleteProductName">--</h6>
                    <p class="text-muted fs-7 mb-0">Bu ürün çöp kutusuna taşınacak (Soft-Delete). Geçmiş stok hareketleri korunur.</p>
                </div>
                <div class="modal-footer bg-light py-2 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Evet, Çöp Kutusuna Taşı</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function prepareEditProductModal(stk) {
    document.getElementById('edit_id').value = stk.id;
    document.getElementById('edit_code').value = stk.code;
    document.getElementById('edit_barcode').value = stk.barcode || '';
    document.getElementById('edit_name').value = stk.name;
    document.getElementById('edit_category').value = stk.category;
    document.getElementById('edit_unit').value = stk.unit;
    document.getElementById('edit_min_threshold').value = stk.min_threshold;
    document.getElementById('edit_unit_price').value = stk.unit_price;
    document.getElementById('edit_status').value = stk.status || 'Aktif';
    document.getElementById('edit_brand').value = stk.brand || '';
    document.getElementById('edit_specifications').value = stk.specifications || '';
    document.getElementById('edit_description').value = stk.description || '';
}

document.addEventListener('DOMContentLoaded', function() {
    // Quick Stock In button listener
    document.querySelectorAll('.btn-stock-in').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const price = this.dataset.price;
            document.getElementById('in_stock_id').value = id;
            document.getElementById('in_unit_cost').value = price || 0;
            const modal = new bootstrap.Modal(document.getElementById('stockInModal'));
            modal.show();
        });
    });

    // Quick Stock Out button listener
    document.querySelectorAll('.btn-stock-out').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            document.getElementById('out_stock_id').value = id;
            const modal = new bootstrap.Modal(document.getElementById('stockOutModal'));
            modal.show();
        });
    });

    // Delete Product button listener
    document.querySelectorAll('.btn-delete-product').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('delete_product_id').value = this.dataset.id;
            document.getElementById('deleteProductName').innerText = this.dataset.name;
            const modal = new bootstrap.Modal(document.getElementById('deleteProductModal'));
            modal.show();
        });
    });

    // AJAX Code Duplicate Check for Add Product
    const addCodeInput = document.getElementById('add_code');
    if (addCodeInput) {
        addCodeInput.addEventListener('blur', function() {
            const code = this.value.trim();
            if (code.length < 2) return;
            fetch('<?= base_url('api/check-product-code?code=') ?>' + encodeURIComponent(code))
                .then(res => res.json())
                .then(data => {
                    const errEl = document.getElementById('add_code_error');
                    if (data.exists) {
                        errEl.classList.remove('d-none');
                        document.getElementById('btnSubmitAddProduct').disabled = true;
                    } else {
                        errEl.classList.add('d-none');
                        document.getElementById('btnSubmitAddProduct').disabled = false;
                    }
                });
        });
    }

    // Negative stock client-side validation
    const outQtyInput = document.getElementById('out_quantity');
    const outStockSelect = document.getElementById('out_stock_id');
    if (outQtyInput && outStockSelect) {
        function checkNegativeStock() {
            const selectedOpt = outStockSelect.options[outStockSelect.selectedIndex];
            const maxQty = parseInt(selectedOpt ? selectedOpt.dataset.qty : 0);
            const val = parseInt(outQtyInput.value || 0);
            const warnEl = document.getElementById('out_qty_warning');
            if (val > maxQty) {
                warnEl.innerText = `Yetersiz stok. Mevcut stok: ${maxQty}`;
                warnEl.classList.remove('d-none');
                document.getElementById('btnSubmitStockOut').disabled = true;
            } else {
                warnEl.classList.add('d-none');
                document.getElementById('btnSubmitStockOut').disabled = false;
            }
        }
        outQtyInput.addEventListener('input', checkNegativeStock);
        outStockSelect.addEventListener('change', checkNegativeStock);
    }
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
