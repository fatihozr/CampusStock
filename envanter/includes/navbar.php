<?php
use App\Helpers\Auth;
use App\Models\Stock;

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

$stockModel = new Stock();
$critCount = $stockModel->getCriticalStockCount();
$totalNotifs = $critCount;
?>
<nav class="navbar navbar-expand-lg sticky-top navbar-dark bg-dark-blue shadow-sm">
    <div class="container-fluid px-3">
        <!-- Sidebar Toggle & Brand -->
        <div class="d-flex align-items-center">
            <button class="btn text-white me-3 border-0 p-1" id="sidebarToggle" type="button" title="Menüyü Daralt/Genişlet">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center fw-bold me-0" href="<?= base_url('dashboard') ?>">
                <div class="brand-icon me-2 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                    <i class="bi bi-building"></i>
                </div>
                <span class="d-none d-sm-inline fw-bold fs-6">TEMİZLİK MALZEMELERİ STOK VE DAĞITIM SİSTEMİ</span>
                <span class="badge bg-primary ms-2 rounded-pill fs-7 d-none d-md-inline-block">BELEDİYE DESTEK HİZMETLERİ</span>
            </a>
        </div>

        <!-- Right Side Nav Items -->
        <div class="d-flex align-items-center gap-3">
            <!-- Categorized Global Search Bar -->
            <div class="top-search-wrapper d-none d-lg-block">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-primary fw-bold"><i class="bi bi-search"></i></span>
                    <input type="text" id="globalSearchInput" class="form-control top-search-input border-start-0 ps-1 rounded-end" placeholder="Stok Kalemi, Birim veya Personel Ara..." autocomplete="off">
                </div>
                <div id="globalSearchDropdown" class="top-search-dropdown d-none shadow-lg rounded-3 border bg-white mt-1"></div>
            </div>

            <!-- Theme Toggle Button (Dark/Light Mode) -->
            <button class="btn btn-icon text-white border-0 position-relative p-1" id="themeToggleBtn" type="button" title="Tema Değiştir (Açık / Koyu)" aria-label="Tema Değiştir">
                <i class="bi bi-moon-stars fs-5" id="themeIcon"></i>
            </button>

            <!-- Notifications Dropdown -->
            <div class="dropdown">
                <button class="btn btn-icon text-white position-relative border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-5"></i>
                    <?php if ($totalNotifs > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem;">
                            <?= $totalNotifs ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="width: 310px;">
                    <li class="dropdown-header fw-bold text-dark border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                        <span>Sistem Bildirimleri</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill"><?= $totalNotifs ?> Bildirim</span>
                    </li>
                    <?php if ($critCount > 0): ?>
                        <li>
                            <a class="dropdown-item p-2 rounded small" href="<?= base_url('stocks?tab=critical') ?>">
                                <div class="fw-semibold text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Kritik Stok Uyarısı</div>
                                <div class="text-muted fs-7"><?= $critCount ?> adet stok kritik seviyenin altında!</div>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="p-3 text-center text-muted fs-8">Okunmamış bildiriminiz bulunmuyor.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar rounded-circle bg-primary-gradient text-white fw-bold d-flex align-items-center justify-content-center me-2">
                        <?= e($initials) ?>
                    </div>
                    <div class="d-none d-md-block text-start lh-1">
                        <span class="fw-bold d-block fs-7 text-white"><?= e($fullName) ?></span>
                        <small class="text-white-50 fs-8"><?= e($roleName) ?></small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 fs-7" aria-labelledby="dropdownUser">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-dark"><?= e($fullName) ?></div>
                        <small class="text-muted"><?= e($email) ?></small>
                    </li>
                    <li><a class="dropdown-item py-2" href="<?= base_url('users') ?>"><i class="bi bi-person me-2 text-primary"></i> Hesabım / Ayarlar</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger fw-semibold" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Güvenli Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
