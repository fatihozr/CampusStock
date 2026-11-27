<?php
use App\Helpers\Auth;
use App\Models\Product;
use App\Services\MockDataService;

$currentUri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptName !== '/' && str_starts_with($currentUri, $scriptName)) {
    $currentUri = substr($currentUri, strlen($scriptName));
}
$currentPath = '/' . trim($currentUri, '/');
$queryString = $_SERVER['QUERY_STRING'] ?? '';

$productModel = new Product();
$criticalCount = $productModel->getCriticalStockCount();
$allStocksForSidebar = $productModel->getAll();
$returnsCount = count(MockDataService::all('returns'));
?>
<aside id="sidebar">
    <!-- Brand / Header -->
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-book-half"></i>
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-title">CAMPUSSTOCK</div>
            <div class="sidebar-brand-subtitle">Kırtasiye Yönetim</div>
        </div>
    </div>

    <div class="sidebar-content py-2" style="overflow-y: auto; height: calc(100vh - 75px);">
        <ul class="sidebar-nav">

            <!-- ANA MENÜ -->
            <li class="nav-header">Operasyon Merkezi</li>
            <li class="nav-item">
                <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($currentPath === '/' || $currentPath === '/dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Kontrol Paneli</span>
                </a>
            </li>

            <!-- SATIŞ, SİPARİŞ & İADE -->
            <li class="nav-header">Satış, Sipariş & İade</li>
            <li class="nav-item">
                <a href="<?= base_url('pos') ?>" class="nav-link <?= str_starts_with($currentPath, '/pos') ? 'active' : '' ?>" style="background: rgba(37, 99, 235, 0.08); border-left: 3px solid #2563EB;">
                    <i class="bi bi-cart4 text-primary fs-5"></i>
                    <span class="fw-bold text-primary">Hızlı Satış (POS)</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('sales') ?>" class="nav-link <?= str_starts_with($currentPath, '/sales') ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Siparişler & Satışlar</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('returns') ?>" class="nav-link <?= str_starts_with($currentPath, '/returns') ? 'active' : '' ?> d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-arrow-return-left"></i>
                        <span>İade Talepleri</span>
                    </div>
                    <?php if ($returnsCount > 0): ?>
                        <span class="ds-badge ds-badge-warning"><?= $returnsCount ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- KIRTASİYE STOĞU -->
            <li class="nav-header">Kırtasiye Stoğu & Ürünler</li>
            <li class="nav-item">
                <a href="<?= base_url('products') ?>" class="nav-link <?= (str_starts_with($currentPath, '/products') && !str_contains($currentPath, '/movements') && !str_contains($queryString, 'tab=critical')) ? 'active' : '' ?>">
                    <i class="bi bi-boxes"></i>
                    <span>Kırtasiye Ürünleri</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('categories') ?>" class="nav-link <?= str_starts_with($currentPath, '/categories') ? 'active' : '' ?>">
                    <i class="bi bi-bookmark"></i>
                    <span>Kategoriler</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('brands') ?>" class="nav-link <?= str_starts_with($currentPath, '/brands') ? 'active' : '' ?>">
                    <i class="bi bi-tags"></i>
                    <span>Markalar</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('products/movements') ?>" class="nav-link <?= str_starts_with($currentPath, '/products/movements') ? 'active' : '' ?>">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Stok Hareketleri</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#sidebarStockInModal" style="color: var(--c-success);">
                    <i class="bi bi-plus-circle" style="color: var(--c-success);"></i>
                    <span style="color: var(--c-success);">Stok Girişi</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#sidebarStockOutModal" style="color: var(--c-danger);">
                    <i class="bi bi-box-arrow-up" style="color: var(--c-danger);"></i>
                    <span style="color: var(--c-danger);">Stok Çıkışı</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('products?tab=critical') ?>" class="nav-link <?= (str_starts_with($currentPath, '/products') && str_contains($queryString, 'tab=critical')) ? 'active' : '' ?> d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle" style="color: var(--c-warning);"></i>
                        <span>Kritik Stoklar</span>
                    </div>
                    <?php if ($criticalCount > 0): ?>
                        <span class="ds-badge ds-badge-danger"><?= $criticalCount ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- KAMPÜS & ŞUBELER -->
            <li class="nav-header">Kampüs & Şubeler</li>
            <li class="nav-item">
                <a href="<?= base_url('stores') ?>" class="nav-link <?= str_starts_with($currentPath, '/stores') ? 'active' : '' ?>">
                    <i class="bi bi-shop"></i>
                    <span>Üniversite & Kırtasiyeler</span>
                </a>
            </li>

            <!-- KULLANICILAR & RAPORLAR -->
            <li class="nav-header">Müşteri, Rapor & Yönetim</li>
            <li class="nav-item">
                <a href="<?= base_url('customers') ?>" class="nav-link <?= str_starts_with($currentPath, '/customers') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Üyeler & Müşteriler</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('reports') ?>" class="nav-link <?= str_starts_with($currentPath, '/reports') ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Raporlar</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('users') ?>" class="nav-link <?= str_starts_with($currentPath, '/users') ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i>
                    <span>Yöneticiler & Roller</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<!-- MODAL: STOK GİRİŞİ -->
<div class="modal fade" id="sidebarStockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-success-subtle);">
                <h5 class="modal-title fw-bold fs-6" style="color: var(--c-success);"><i class="bi bi-plus-circle me-2"></i>Yeni Stok Girişi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/stock-in') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="ds-label">Giriş Yapılacak Kırtasiye Ürünü <span class="text-danger">*</span></label>
                        <select name="product_id" class="ds-select" required>
                            <option value="">-- Ürün Seçin --</option>
                            <?php foreach ($allStocksForSidebar as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    ✏️ <?= e($s['name']) ?> (Mevcut: <?= $s['quantity'] ?> <?= e($s['unit']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="ds-label">Miktar <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="ds-input" min="1" value="1" required>
                        </div>
                        <div class="col-6">
                            <label class="ds-label">Birim Maliyet (₺)</label>
                            <input type="number" step="0.01" name="unit_cost" class="ds-input" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="ds-label">Notlar (İsteğe Bağlı)</label>
                        <textarea name="notes" class="ds-input" rows="2" placeholder="Fatura No, Tedarikçi vb."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" style="background-color: var(--c-success); border-color: var(--c-success);">Girişi Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: STOK ÇIKIŞI -->
<div class="modal fade" id="sidebarStockOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-danger-subtle);">
                <h5 class="modal-title fw-bold fs-6" style="color: var(--c-danger);"><i class="bi bi-box-arrow-up me-2"></i>Stok Çıkışı Yap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/stock-out') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="ds-label">Çıkış Yapılacak Kırtasiye Ürünü <span class="text-danger">*</span></label>
                        <select name="product_id" class="ds-select" required>
                            <option value="">-- Ürün Seçin --</option>
                            <?php foreach ($allStocksForSidebar as $s): ?>
                                <?php if ($s['quantity'] > 0): ?>
                                    <option value="<?= $s['id'] ?>">
                                        ✏️ <?= e($s['name']) ?> (Mevcut: <?= $s['quantity'] ?> <?= e($s['unit']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="ds-label">Çıkış Miktarı <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="ds-input" min="1" value="1" required>
                    </div>
                    <div class="mb-0">
                        <label class="ds-label">Çıkış Nedeni / Notu</label>
                        <textarea name="notes" class="ds-input" rows="2" placeholder="Örn: Fire, şube transferi veya hasarlı ürün çıkışı."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" style="background-color: var(--c-danger); border-color: var(--c-danger);">Çıkışı Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>
