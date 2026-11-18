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
$activeModule = $_SESSION['active_module'] ?? 'cleaning';
?>
<header class="app-header">
    <div class="d-flex align-items-center gap-3">
        <!-- Sidebar Toggle -->
        <button class="header-btn" id="sidebarToggle" type="button" title="Menüyü Daralt/Genişlet">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Module Dropdown / Page Title -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="moduleDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-main);">
                <div class="ds-avatar me-2" style="background: <?= $activeModule === 'furniture' ? '#8B5A2B' : 'var(--c-primary)' ?>; width: 32px; height: 32px; font-size: 1rem;">
                    <?= $activeModule === 'furniture' ? '🪑' : '🧹' ?>
                </div>
                <div class="d-none d-sm-block">
                    <div class="header-title lh-1">
                        <?= $activeModule === 'furniture' ? 'Mobilya Envanteri' : 'Kırtasiye Envanteri' ?>
                    </div>
                    <small class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Modül Değiştir</small>
                </div>
            </a>
            <ul class="dropdown-menu shadow border-0 mt-2" aria-labelledby="moduleDropdown" style="min-width: 250px;">
                <li>
                    <a class="dropdown-item py-2 fw-medium <?= $activeModule === 'cleaning' ? 'active bg-primary-subtle text-primary' : '' ?>" href="<?= base_url('switch-module?m=cleaning') ?>">
                        ✏️ Kırtasiye Envanter Sistemi
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 fw-medium <?= $activeModule === 'furniture' ? 'active bg-primary-subtle text-primary' : '' ?>" href="<?= base_url('switch-module?m=furniture') ?>">
                        🪑 Mobilya Kayıtları Sistemi
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="header-actions">
        <!-- Global Search -->
        <div class="d-none d-lg-flex position-relative">
            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
            <input type="text" id="globalSearchInput" class="ds-input rounded-pill ps-5" placeholder="Stok Kalemi veya Birim Ara..." style="width: 250px; padding: 0.4rem 1rem;">
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
                                <div class="text-muted fs-8 mt-1"><?= $critCount ?> adet ürün kritik seviyenin altına düştü. Lütfen kontrol edin.</div>
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

        <!-- User Profile -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-main);">
                <div class="d-none d-md-block text-end me-2 lh-1">
                    <span class="fw-bold d-block fs-7"><?= e($fullName) ?></span>
                    <small class="text-muted fs-8"><?= e($roleName) ?></small>
                </div>
                <div class="ds-avatar border">
                    <?= e($initials) ?>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width: 200px; border-radius: var(--radius-md);">
                <li class="px-3 py-2 border-bottom d-md-none">
                    <div class="fw-bold text-dark fs-7"><?= e($fullName) ?></div>
                    <small class="text-muted fs-8"><?= e($email) ?></small>
                </li>
                <li><a class="dropdown-item py-2 fs-7" href="<?= base_url('users') ?>"><i class="bi bi-person me-2 text-muted"></i>Hesabım</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 fs-7 text-danger fw-medium" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Güvenli Çıkış</a></li>
            </ul>
        </div>
    </div>
</header>
