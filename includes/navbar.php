<?php
use App\Helpers\Auth;
use App\Models\Product;

$currentUser = Auth::user() ?? [
    'first_name' => 'Misafir',
    'last_name'  => 'Kullanıcı',
    'role_name'  => 'Giriş Yapılmadı',
    'email'      => 'oturum@kapali'
];

$firstName = $currentUser['first_name'] ?? 'Misafir';
$lastName  = $currentUser['last_name'] ?? '';
$initials  = mb_substr($firstName, 0, 1, 'UTF-8') . (mb_strlen($lastName, 'UTF-8') > 0 ? mb_substr($lastName, 0, 1, 'UTF-8') : '');
$fullName  = trim($firstName . ' ' . $lastName);
$roleName  = $currentUser['role_name'] ?? 'Kullanıcı';
$email     = $currentUser['email'] ?? '';

$productModel = new Product();
$critCount = $productModel->getCriticalStockCount();
$totalNotifs = $critCount;
?>
<header class="app-header">
    <div class="d-flex align-items-center gap-3">
        <!-- Sidebar Toggle -->
        <button class="header-btn" id="sidebarToggle" type="button" title="Menüyü Daralt/Genişlet">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Brand Title -->
        <div class="d-flex align-items-center">
            <div class="ds-avatar me-2" style="background: var(--c-primary); width: 34px; height: 34px; font-size: 1rem;">
                ✏️
            </div>
            <div class="d-none d-sm-block">
                <div class="header-title lh-1">CampusStock Kırtasiye</div>
                <small class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Yönetim Paneli</small>
            </div>
        </div>
    </div>

    <div class="header-actions">
        <!-- Global Search -->
        <div class="d-none d-lg-flex position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="globalSearchInput" class="ds-input rounded-pill ps-5" placeholder="Ürün veya Barkod Ara..." style="width: 250px; padding: 0.4rem 1rem;">
            <div id="globalSearchDropdown" class="position-absolute top-100 start-0 w-100 bg-white border shadow-sm rounded mt-1 d-none" style="z-index: 1020;"></div>
        </div>

        <!-- Theme Toggle -->
        <button class="header-btn" id="themeToggleBtn" type="button" title="Tema Değiştir">
            <i class="bi bi-moon-stars" id="themeIcon"></i>
        </button>

        <!-- Notifications -->
        <div class="dropdown">
            <button class="header-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <?php if ($totalNotifs > 0): ?>
                    <span class="notif-indicator pulse"></span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0 mt-2" style="width: 300px; overflow: hidden; border-radius: var(--radius-md);">
                <li class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: rgba(0,0,0,0.02);">
                    <span class="fw-bold" style="font-size: 0.9rem;">Bildirimler</span>
                    <span class="ds-badge ds-badge-primary"><?= $totalNotifs ?> Yeni</span>
                </li>
                <?php if ($critCount > 0): ?>
                    <li>
                        <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="<?= base_url('products?tab=critical') ?>" style="white-space: normal;">
                            <div class="ds-avatar" style="background: var(--c-danger-subtle); color: var(--c-danger);">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-7">Kritik Stok Uyarısı</div>
                                <div class="text-muted fs-8 mt-1"><?= $critCount ?> adet kırtasiye ürünü kritik seviyenin altına düştü.</div>
                            </div>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="p-4 text-center">
                        <i class="bi bi-bell-slash text-muted fs-3 mb-2 d-block"></i>
                        <span class="text-muted fs-7">Okunmamış bildiriminiz yok.</span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-main);">
                <div class="ds-avatar" style="background: var(--c-primary-subtle); color: var(--c-primary); font-weight: 700;">
                    <?= e($initials ?: 'KY') ?>
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="fw-bold lh-1" style="font-size: 0.85rem;"><?= e($fullName) ?></div>
                    <small class="text-muted" style="font-size: 0.72rem;"><?= e($roleName) ?></small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width: 210px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold fs-7"><?= e($fullName) ?></div>
                    <small class="text-muted"><?= e($email) ?></small>
                </li>
                <li><a class="dropdown-item py-2" href="<?= base_url('dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Kontrol Paneli</a></li>
                <li><a class="dropdown-item py-2" href="<?= base_url('users') ?>"><i class="bi bi-people me-2"></i>Kullanıcılar</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Çıkış Yap</a></li>
            </ul>
        </div>
    </div>
</header>
