<?php
use App\Helpers\Auth;
use App\Models\Stock;

$currentUri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptName !== '/' && str_starts_with($currentUri, $scriptName)) {
    $currentUri = substr($currentUri, strlen($scriptName));
}
$currentPath = '/' . trim($currentUri, '/');
$queryString = $_SERVER['QUERY_STRING'] ?? '';

$stockModel = new Stock();
$criticalCount = $stockModel->getCriticalStockCount();
?>
<aside id="sidebar" class="bg-white border-end shadow-sm">
    <div class="sidebar-content py-3">
        <!-- Sidebar Navigation Menu -->
        <ul class="nav nav-pills flex-column px-2 gap-1">
            
            <!-- ANA MENÜ -->
            <?php if (Auth::hasPermission('dashboard')): ?>
                <li class="nav-header text-uppercase text-muted px-3 mt-1 mb-1 fs-8 fw-bold">
                    Ana Menü
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('dashboard') ?>" class="nav-link <?= ($currentPath === '/' || $currentPath === '/dashboard' || str_starts_with($currentPath, '/dashboard')) ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-speedometer2 me-3 fs-5"></i>
                        <span>Gösterge Paneli</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- STOK YÖNETİMİ -->
            <?php if (Auth::hasPermission('stocks')): ?>
                <li class="nav-header text-uppercase text-muted px-3 mt-3 mb-1 fs-8 fw-bold">
                    Stok Yönetimi
                </li>
                <!-- Temizlik Malzemeleri -->
                <li class="nav-item">
                    <a href="<?= base_url('stocks') ?>" class="nav-link <?= (($currentPath === '/stocks' || str_starts_with($currentPath, '/stocks')) && $currentPath !== '/stocks/movements' && !str_contains($queryString, 'tab=critical')) ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-boxes me-3 fs-5"></i>
                        <span>Temizlik Malzemeleri</span>
                    </a>
                </li>
                <!-- Stok Hareketleri -->
                <li class="nav-item">
                    <a href="<?= base_url('stocks/movements') ?>" class="nav-link <?= ($currentPath === '/stocks/movements') ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-journal-text me-3 fs-5 text-primary"></i>
                        <span>Stok Hareketleri</span>
                    </a>
                </li>
                <!-- Kritik Stok Uyarısı -->
                <li class="nav-item">
                    <a href="<?= base_url('stocks?tab=critical') ?>" class="nav-link <?= (str_starts_with($currentPath, '/stocks') && str_contains($queryString, 'tab=critical')) ? 'active' : '' ?> d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-octagon me-3 fs-5 text-warning"></i>
                            <span>Kritik Stok Uyarısı</span>
                        </div>
                        <?php if ($criticalCount > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= $criticalCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>

            <!-- TANIMLAR -->
            <li class="nav-header text-uppercase text-muted px-3 mt-3 mb-1 fs-8 fw-bold">
                Tanımlar
            </li>
            <!-- Kategoriler -->
            <li class="nav-item">
                <a href="<?= base_url('categories') ?>" class="nav-link <?= ($currentPath === '/categories' || str_starts_with($currentPath, '/categories')) ? 'active' : '' ?> d-flex align-items-center">
                    <i class="bi bi-tags me-3 fs-5 text-info"></i>
                    <span>Kategoriler</span>
                </a>
            </li>
            <!-- Birim & Müdürlükler -->
            <?php if (Auth::hasPermission('personnel')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('departments') ?>" class="nav-link <?= ($currentPath === '/departments' || str_starts_with($currentPath, '/departments')) ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-diagram-3 me-3 fs-5"></i>
                        <span>Birim & Müdürlükler</span>
                    </a>
                </li>
                <!-- Personel Rehberi -->
                <li class="nav-item">
                    <a href="<?= base_url('personnel') ?>" class="nav-link <?= ($currentPath === '/personnel' || str_starts_with($currentPath, '/personnel')) ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-people me-3 fs-5"></i>
                        <span>Personel Rehberi</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- ARAÇLAR -->
            <?php if (Auth::hasPermission('stocks')): ?>
                <li class="nav-header text-uppercase text-muted px-3 mt-3 mb-1 fs-8 fw-bold">
                    Araçlar
                </li>
                <!-- Excel İçe Aktarma -->
                <li class="nav-item">
                    <a href="<?= base_url('import') ?>" class="nav-link <?= ($currentPath === '/import' || str_starts_with($currentPath, '/import')) ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-file-earmark-excel me-3 fs-5 text-success"></i>
                        <span>Excel İçe Aktarma</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- YÖNETİM -->
            <?php if (Auth::hasPermission('reports') || Auth::hasPermission('users')): ?>
                <li class="nav-header text-uppercase text-muted px-3 mt-3 mb-1 fs-8 fw-bold">
                    Yönetim
                </li>
            <?php endif; ?>
            
            <!-- Sistem Raporları -->
            <?php if (Auth::hasPermission('reports')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('reports') ?>" class="nav-link <?= ($currentPath === '/reports' || str_starts_with($currentPath, '/reports')) ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-bar-chart-line me-3 fs-5"></i>
                        <span>Sistem Raporları</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Ayarlar & Yetkiler -->
            <?php if (Auth::hasPermission('users')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('users') ?>" class="nav-link <?= ($currentPath === '/users' || str_starts_with($currentPath, '/users')) ? 'active' : '' ?> d-flex align-items-center">
                        <i class="bi bi-gear me-3 fs-5"></i>
                        <span>Ayarlar & Yetkiler</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Çöp Kutusu -->
            <?php if (Auth::hasPermission('admin') || Auth::hasPermission('users')): ?>
                <li class="nav-item">
                    <a href="<?= base_url('trash') ?>" class="nav-link <?= ($currentPath === '/trash' || str_starts_with($currentPath, '/trash')) ? 'active' : '' ?> d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-trash3 me-3 fs-5 text-danger"></i>
                            <span>Çöp Kutusu</span>
                        </div>
                        <?php
                        $trashModel = new \App\Models\Trash();
                        $trashCount = count($trashModel->getAll());
                        if ($trashCount > 0):
                        ?>
                            <span class="badge bg-danger rounded-pill fs-8"><?= $trashCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </div>
</aside>
