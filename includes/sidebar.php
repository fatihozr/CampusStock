<?php
use App\Helpers\Auth;
use App\Models\Product;
use App\Models\StockRequest;
use App\Models\StockMovement;
use App\Models\Department;
use App\Models\User;

$currentUri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptName !== '/' && str_starts_with($currentUri, $scriptName)) {
    $currentUri = substr($currentUri, strlen($scriptName));
}
$currentPath = '/' . trim($currentUri, '/');
$queryString = $_SERVER['QUERY_STRING'] ?? '';

$productModel = new Product();
$criticalCount = count($productModel->getCriticalStock());
$allStocksForSidebar = $productModel->getAll();

$requestModel = new StockRequest();
$pendingRequests = count($requestModel->getAll('pending'));

$movementModel = new StockMovement();
$pendingMovementsCount = count($movementModel->getPendingMovements());

$departmentModel = new Department();
$departmentsForSidebar = $departmentModel->getAll();

$userModel = new User();
$allPersonnelForSidebar = $userModel->getAllUsers();

$activeModule = $_SESSION['active_module'] ?? 'cleaning';
$moduleName = $activeModule === 'furniture' ? 'Mobilyalar' : 'Kırtasiye Ürünleri';
?>
<aside id="sidebar">
    <!-- Brand / Header -->
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-book-half"></i>
        </div>
        <div class="sidebar-brand-text">
            <div class="sidebar-brand-title">KIRTASİYE</div>
            <div class="sidebar-brand-subtitle">Stok & POS Satış Sis.</div>
        </div>
    </div>

    <div class="sidebar-content py-2" style="overflow-y: auto; height: calc(100vh - 75px);">
        <!-- Navigation Menu -->
        <ul class="sidebar-nav">
            
            <!-- ANA MENÜ -->
            <?php if (Auth::hasPermission('dashboard')): ?>
                <li class="nav-header">Operasyon Merkezi</li>
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($currentPath === '/' || $currentPath === '/dashboard' || str_starts_with($currentPath, '/dashboard')) ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Kontrol Paneli</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- KIRTASİYE SATIŞ & KASA -->
            <li class="nav-header">Satış & Kasa Noktası</li>
            <li class="nav-item">
                <a href="<?= base_url('pos') ?>" class="nav-link <?= ($currentPath === '/pos' || str_starts_with($currentPath, '/pos')) ? 'active' : '' ?>" style="background: rgba(37, 99, 235, 0.1); border-left: 3px solid #2563EB;">
                    <i class="bi bi-cart4 text-primary fs-5"></i>
                    <span class="fw-bold text-primary">Hızlı Satış (POS)</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('sales') ?>" class="nav-link <?= ($currentPath === '/sales' || str_starts_with($currentPath, '/sales')) ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Satış Geçmişi & Fişler</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('customers') ?>" class="nav-link <?= ($currentPath === '/customers' || str_starts_with($currentPath, '/customers')) ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>Müşteriler & Cariler</span>
                </a>
            </li>

            <!-- STOK YÖNETİMİ -->
            <?php if (Auth::hasPermission('inventory') || Auth::hasPermission('stocks')): ?>
                <li class="nav-header">Kırtasiye Stoğu & Depo</li>
                <!-- Ürünler -->
                <li class="nav-item">
                    <a href="<?= base_url('products') ?>" class="nav-link <?= (($currentPath === '/products' || str_starts_with($currentPath, '/products')) && !str_contains($queryString, 'tab=critical')) ? 'active' : '' ?>">
                        <i class="bi bi-boxes"></i>
                        <span>Kırtasiye Ürünleri</span>
                    </a>
                </li>
                <!-- Kategoriler -->
                <li class="nav-item">
                    <a href="<?= base_url('categories') ?>" class="nav-link <?= ($currentPath === '/categories' || str_starts_with($currentPath, '/categories')) ? 'active' : '' ?>">
                        <i class="bi bi-bookmark"></i>
                        <span>Kategoriler</span>
                    </a>
                </li>
                <!-- Markalar -->
                <li class="nav-item">
                    <a href="<?= base_url('brands') ?>" class="nav-link <?= ($currentPath === '/brands' || str_starts_with($currentPath, '/brands')) ? 'active' : '' ?>">
                        <i class="bi bi-tags"></i>
                        <span>Markalar</span>
                    </a>
                </li>
                <!-- Stok Hareketleri -->
                <li class="nav-item">
                    <a href="<?= base_url('products/movements') ?>" class="nav-link <?= ($currentPath === '/products/movements' || str_starts_with($currentPath, '/products/movements')) ? 'active' : '' ?>">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Stok Hareketleri</span>
                    </a>
                </li>

                <!-- Stok Girişi Yap -->
                <?php if (Auth::hasPermission('stock_in') || Auth::isManager()): ?>
                <li class="nav-item mt-2">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#sidebarStockInModal" style="color: var(--c-success);">
                        <i class="bi bi-plus-circle" style="color: var(--c-success);"></i>
                        <span style="color: var(--c-success);">Stok Girişi</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Stok Çıkışı Yap -->
                <?php if (Auth::hasPermission('stock_out') || Auth::isManager()): ?>
                <li class="nav-item mb-2">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#sidebarStockOutModal" style="color: var(--c-danger);">
                        <i class="bi bi-box-arrow-up" style="color: var(--c-danger);"></i>
                        <span style="color: var(--c-danger);">Stok Çıkışı</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Kritik Stok Uyarısı -->
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
                
                <?php if (Auth::isManager()): ?>
                <!-- Stok Hareket Onayları -->
                <li class="nav-item">
                    <a href="<?= base_url('products/approvals') ?>" class="nav-link <?= ($currentPath === '/products/approvals' || str_starts_with($currentPath, '/products/approvals')) ? 'active' : '' ?> d-flex justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle"></i>
                            <span>Onay Bekleyenler</span>
                        </div>
                        <?php if ($pendingMovementsCount > 0): ?>
                            <span class="ds-badge ds-badge-primary"><?= $pendingMovementsCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
            <?php endif; ?>

            <!-- BAŞKANLIKLAR & RAPORLAR -->
            <li class="nav-header">Analiz & Kurumsal</li>
            <?php if (Auth::hasPermission('personnel')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('departments') ?>" class="nav-link <?= ($currentPath === '/departments' || str_starts_with($currentPath, '/departments')) ? 'active' : '' ?>">
                        <i class="bi bi-building"></i>
                        <span>Departmanlar</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if (Auth::hasPermission('reports')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('reports') ?>" class="nav-link <?= ($currentPath === '/reports' || str_starts_with($currentPath, '/reports')) ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Raporlar</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- TALEPLER & ONAY -->
            <li class="nav-header">İşlemler</li>
            <li class="nav-item">
                <a href="<?= base_url('requests/create') ?>" class="nav-link <?= ($currentPath === '/requests/create') ? 'active' : '' ?>">
                    <i class="bi bi-cart-plus"></i>
                    <span>Malzeme Talep Et</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('requests') ?>" class="nav-link <?= ($currentPath === '/requests') ? 'active' : '' ?> d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-inbox"></i>
                        <span>Talepler & Onay</span>
                    </div>
                    <?php if (Auth::hasPermission('admin') && $pendingRequests > 0): ?>
                        <span class="ds-badge ds-badge-primary"><?= $pendingRequests ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- SATIN ALMA -->
            <?php if (Auth::hasPermission('admin')): ?>
                <!-- Birim & Daire Başkanlıkları -->
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'departments' ? 'active' : '' ?>" href="<?= base_url('departments') ?>">
                        <i class="bi bi-diagram-3"></i>
                        <span>Şirket Departmanları</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('procurements') ?>" class="nav-link <?= ($currentPath === '/procurements' || str_starts_with($currentPath, '/procurements')) ? 'active' : '' ?>">
                        <i class="bi bi-receipt"></i>
                        <span>İhale & Satın Alma</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('counts') ?>" class="nav-link <?= ($currentPath === '/counts' || str_starts_with($currentPath, '/counts')) ? 'active' : '' ?>">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Fiziksel Sayım</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- YÖNETİM -->
            <?php if (Auth::hasPermission('users')): ?>
                <li class="nav-header">Sistem Ayarları</li>
                <li class="nav-item">
                    <a href="<?= base_url('users') ?>" class="nav-link <?= ($currentPath === '/users' || str_starts_with($currentPath, '/users')) ? 'active' : '' ?>">
                        <i class="bi bi-gear"></i>
                        <span>Ayarlar</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</aside>

<!-- ========================================== -->
<!-- MODAL: STOK GİRİŞİ (DS)                    -->
<!-- ========================================== -->
<div class="modal fade" id="sidebarStockInModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-success-subtle);">
                <h5 class="modal-title fw-bold fs-6" style="color: var(--c-success);"><i class="bi bi-plus-circle me-2"></i>Yeni Stok Girişi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/stock-in') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="ds-label">Giriş Yapılacak Ürün <span class="text-danger">*</span></label>
                                <select name="product_id" id="sidebar_in_product_id" class="ds-select" required onchange="
                                    let selected = this.options[this.selectedIndex];
                                    document.getElementById('sidebar_in_unit_cost').value = selected.dataset.price || 0;
                                    
                                    let hasImage = selected.dataset.hasImage === '1';
                                    let imgUrl = selected.dataset.imageUrl || '';
                                    let unit = selected.dataset.unit || 'Birim';
                                    
                                    document.getElementById('sidebar_in_unit_text').value = unit;
                                    
                                    if(hasImage && imgUrl !== '') {
                                        document.getElementById('sidebar_in_preview_img').src = imgUrl;
                                        document.getElementById('sidebar_in_image_preview_container').style.display = 'flex';
                                    } else {
                                        document.getElementById('sidebar_in_image_preview_container').style.display = 'none';
                                    }
                                ">
                                    <option value="" data-has-image="1" data-unit="Birim">-- Ürün Seçin --</option>
                                    <?php foreach ($allStocksForSidebar as $s): ?>
                                        <option value="<?= $s['id'] ?>" data-price="<?= $s['unit_price'] ?>" data-unit="<?= e($s['unit']) ?>" data-has-image="<?= empty($s['image_path']) ? '0' : '1' ?>" data-image-url="<?= empty($s['image_path']) ? '' : base_url('public/' . $s['image_path']) ?>">
                                            <?= $activeModule === 'furniture' ? '🪑' : '🧴' ?> <?= e($s['name']) ?> (Mevcut: <?= $s['quantity'] ?> <?= e($s['unit']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label class="ds-label">Miktar <span class="text-danger">*</span></label>
                                    <div class="ds-qty-control">
                                        <button type="button" class="ds-qty-btn" onclick="const i=this.nextElementSibling; if(i.value>1)i.value--"><i class="bi bi-dash"></i></button>
                                        <input type="number" name="quantity" class="ds-qty-input" min="1" value="1" required>
                                        <button type="button" class="ds-qty-btn" onclick="this.previousElementSibling.value++"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <input type="hidden" name="unit" id="sidebar_in_unit_text" value="Adet">
                                </div>
                                <div class="col-6">
                                    <label class="ds-label">Birim Maliyet (₺) <span class="text-muted fw-normal" style="font-size: 0.75rem;">(KDV Dahil Değildir)</span></label>
                                    <input type="number" step="0.01" name="unit_cost" id="sidebar_in_unit_cost" class="ds-input" min="0" placeholder="0.00">
                                </div>
                            </div>



                            <div class="mb-2">
                                <label class="ds-label">Notlar (İsteğe Bağlı)</label>
                                <textarea name="notes" class="ds-input" rows="2" placeholder="Fatura No, Sipariş No vb."></textarea>
                            </div>
                        </div>
                        
                        <!-- Image Preview Column -->
                        <div class="col-md-4">
                            <div id="sidebar_in_image_preview_container" class="w-100 text-center h-100 flex-column justify-content-center align-items-center" style="display: none; background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; border: 1px dashed var(--border-color);">
                                <img id="sidebar_in_preview_img" src="" class="img-fluid rounded shadow-sm" style="max-height: 150px; object-fit: cover; cursor: pointer; transition: transform 0.2s;" alt="Görsel" onclick="openImageModal(this.src)" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Büyütmek için tıklayın">
                            </div>
                        </div>
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
<div class="modal fade" id="sidebarStockOutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-danger-subtle);">
                <h5 class="modal-title fw-bold fs-6" style="color: var(--c-danger);"><i class="bi bi-box-arrow-up me-2"></i>Stok Çıkışı Yap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form action="<?= base_url('products/stock-out') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="ds-label">Çıkış Yapılacak Ürün <span class="text-danger">*</span></label>
                                <select name="product_id" id="sidebar_out_stock_id" class="ds-select" required onchange="
                                    let selected = this.options[this.selectedIndex];
                                    let hasImage = selected.dataset.hasImage === '1';
                                    let imgUrl = selected.dataset.imageUrl || '';
                                    let unit = selected.dataset.unit || 'Birim';
                                    let maxQty = parseInt(selected.dataset.qty || 0);
                                    
                                    document.getElementById('sidebar_out_unit_text').value = unit;
                                    let qtyInput = document.getElementById('sidebar_out_quantity');
                                    qtyInput.max = maxQty;
                                    qtyInput.value = 1;

                                    if(hasImage && imgUrl !== '') {
                                        document.getElementById('sidebar_out_preview_img').src = imgUrl;
                                        document.getElementById('sidebar_out_image_preview_container').style.display = 'flex';
                                    } else {
                                        document.getElementById('sidebar_out_image_preview_container').style.display = 'none';
                                    }
                                ">
                                    <option value="" data-has-image="0" data-unit="Birim" data-qty="0">-- Ürün Seçin --</option>
                                    <?php foreach ($allStocksForSidebar as $s): ?>
                                        <?php if($s['quantity'] > 0): ?>
                                        <option value="<?= $s['id'] ?>" data-qty="<?= $s['quantity'] ?>" data-unit="<?= e($s['unit']) ?>" data-has-image="<?= empty($s['image_path']) ? '0' : '1' ?>" data-image-url="<?= empty($s['image_path']) ? '' : base_url('public/' . $s['image_path']) ?>">
                                            <?= $activeModule === 'furniture' ? '🪑' : '🧼' ?> <?= e($s['name']) ?> (Mevcut: <?= $s['quantity'] ?>)
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="ds-label">Teslim Alan Departman <span class="text-danger">*</span></label>
                                <select name="department_id" class="ds-select" required>
                                    <option value="">-- Departman Seçin --</option>
                                    <?php foreach ($departmentsForSidebar as $dept): ?>
                                        <?php if ($dept['is_active']): ?>
                                            <option value="<?= $dept['id'] ?>">🏢 <?= e($dept['name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label class="ds-label">Miktar <span class="text-danger">*</span></label>
                                    <div class="ds-qty-control">
                                        <button type="button" class="ds-qty-btn" onclick="const i=this.nextElementSibling; if(i.value>1)i.value--"><i class="bi bi-dash"></i></button>
                                        <input type="number" name="quantity" id="sidebar_out_quantity" class="ds-qty-input" min="1" value="1" required>
                                        <button type="button" class="ds-qty-btn" onclick="const i=this.previousElementSibling; if(!i.max || parseInt(i.value) < parseInt(i.max)) i.value++"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <input type="hidden" name="unit" id="sidebar_out_unit_text" value="Adet">
                                </div>
                            </div>



                            <div class="mb-2">
                                <label class="ds-label">Çıkış Notu</label>
                                <textarea name="notes" class="ds-input" rows="2" placeholder="Örn: Ana bina kat temizliği ihtiyacı."></textarea>
                            </div>
                        </div>

                        <!-- Image Preview Column -->
                        <div class="col-md-4">
                            <div id="sidebar_out_image_preview_container" class="w-100 text-center h-100 flex-column justify-content-center align-items-center" style="display: none; background: var(--bg-surface-hover); border-radius: var(--radius-md); padding: 1rem; border: 1px dashed var(--border-color);">
                                <img id="sidebar_out_preview_img" src="" class="img-fluid rounded shadow-sm" style="max-height: 150px; object-fit: cover; cursor: pointer; transition: transform 0.2s;" alt="Görsel" onclick="openImageModal(this.src)" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Büyütmek için tıklayın">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3" style="background: rgba(0,0,0,0.01);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" style="background-color: var(--c-danger); border-color: var(--c-danger);">Çıkışı Onayla</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generic Image Modal -->
<div class="modal fade" id="imageLightboxModal" tabindex="-1" style="z-index: 1060;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <img id="lightboxImage" src="" class="img-fluid rounded shadow" style="max-height: 80vh;" alt="Büyük Görsel">
            </div>
        </div>
    </div>
</div>

<script>
function openImageModal(src) {
    if(!src) return;
    document.getElementById('lightboxImage').src = src;
    var imgModal = new bootstrap.Modal(document.getElementById('imageLightboxModal'));
    imgModal.show();
}
</script>
