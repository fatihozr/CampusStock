<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<style>
.dashboard-kpi-card {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(0, 0, 0, 0.06) !important;
    cursor: pointer;
}
.dashboard-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.12) !important;
    border-color: rgba(37, 99, 235, 0.35) !important;
}
.kpi-icon-box {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 1.25rem;
}
.quick-action-card {
    transition: all 0.2s ease-in-out;
    border: 1px solid #E2E8F0 !important;
}
.quick-action-card:hover {
    transform: translateX(4px);
    background-color: #F8FAFC !important;
    border-color: #CBD5E1 !important;
}
</style>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

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

            <!-- Page Title & Header Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Gösterge Paneli</h4>
                    <p class="text-muted mb-0 fs-7">Belediye Destek Hizmetleri Birimi Stok & Takip Merkezi</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm rounded-2 d-flex align-items-center gap-1 dropdown-toggle px-3 fw-semibold" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download me-1"></i> Rapor İndir
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 fs-7">
                            <li><a class="dropdown-item" href="<?= base_url('reports/export?type=stocks&format=xlsx') ?>"><i class="bi bi-boxes me-2 text-primary"></i> Stok Durum Raporu (.xlsx)</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('reports/pdf?type=stocks') ?>" target="_blank"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Stok Durum Raporu (PDF)</a></li>
                        </ul>
                    </div>

                    <button class="btn btn-primary btn-sm rounded-2 d-flex align-items-center gap-1 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#quickActionModal">
                        <i class="bi bi-lightning-charge me-1"></i> Hızlı İşlem
                    </button>
                </div>
            </div>

            <!-- KPI Cards Grid -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 mb-4">
                <!-- 1. Toplam Stok Kalemi -->
                <div class="col">
                    <a href="<?= base_url('stocks?tab=items') ?>" class="text-decoration-none">
                        <div class="card dashboard-kpi-card h-100 bg-white shadow-sm rounded-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-semibold fs-7">Toplam Stok Kalemi</span>
                                    <div class="kpi-icon-box bg-primary-subtle text-primary">
                                        <i class="bi bi-boxes"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1 text-dark"><?= number_format($stats['total_stock_items'] ?? 0) ?></h3>
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-muted fs-8">Kayıtlı Malzemeler</small>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-8">Stoklar</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- 2. Kritik Stok Uyarısı -->
                <div class="col">
                    <a href="<?= base_url('stocks?tab=critical') ?>" class="text-decoration-none">
                        <div class="card dashboard-kpi-card h-100 bg-white shadow-sm rounded-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-semibold fs-7">Kritik Stok Uyarısı</span>
                                    <div class="kpi-icon-box bg-danger-subtle text-danger">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1 text-danger"><?= number_format($stats['critical_stock'] ?? 0) ?></h3>
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-muted fs-8">Kritik Eşikte</small>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-8">Stok Uyarısı</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- 3. Personel / Birim -->
                <div class="col">
                    <a href="<?= base_url('personnel') ?>" class="text-decoration-none">
                        <div class="card dashboard-kpi-card h-100 bg-white shadow-sm rounded-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted fw-semibold fs-7">Personel / Birim</span>
                                    <div class="kpi-icon-box bg-info-subtle text-info-emphasis">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1 text-dark"><?= number_format($stats['active_departments'] ?? 0) ?></h3>
                                <div class="d-flex align-items-center justify-content-between">
                                    <small class="text-muted fs-8">Belediye Kadrosu</small>
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle fs-8">Rehber</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Content Split: Recent Activity (8 cols) & Quick Actions (4 cols) -->
            <div class="row g-3">
                <!-- Recent Transactions Table (Sol Kolon) -->
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-activity text-primary fs-5"></i>
                                <h6 class="fw-bold mb-0 text-dark">Son Stok Hareketleri</h6>
                            </div>
                            <a href="<?= base_url('stocks?tab=movements') ?>" class="fs-7 text-primary text-decoration-none fw-semibold">
                                Tümünü Gör <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0 d-flex flex-column justify-content-between">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover mb-0">
                                    <thead class="table-light fs-7 text-muted">
                                        <tr>
                                            <th>KOD / İŞLEM</th>
                                            <th>MALZEME</th>
                                            <th>KATEGORİ</th>
                                            <th>HEDEF / AÇIKLAMA</th>
                                            <th>TARİH</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-7">
                                        <?php if (!empty($recentActivities)): ?>
                                            <?php foreach ($recentActivities as $act): ?>
                                                <tr>
                                                    <td class="fw-semibold">
                                                        <span class="badge <?= $act['badge_class'] ?> me-1"><?= e($act['action']) ?></span>
                                                        <div class="text-muted fs-8 mt-1 font-monospace"><?= e($act['id']) ?></div>
                                                    </td>
                                                    <td class="fw-bold text-dark"><?= e($act['item']) ?></td>
                                                    <td><span class="badge bg-light text-dark border"><?= e($act['category']) ?></span></td>
                                                    <td class="text-secondary"><?= e($act['person']) ?></td>
                                                    <td class="text-muted fs-8"><?= e($act['date']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">Henüz kayıtlı bir stok hareketi bulunmuyor.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Shortcuts (Sağ Kolon) -->
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-grid-fill text-success fs-5"></i>
                                <h6 class="fw-bold mb-0 text-dark">Hızlı Modül Erişimi</h6>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-column gap-3 mb-3">
                                <!-- Quick Card 1 -->
                                <a href="<?= base_url('stocks?tab=movements') ?>" class="quick-action-card p-3 rounded-3 text-decoration-none d-flex align-items-center bg-white shadow-2xs">
                                    <div class="kpi-icon-box bg-success text-white me-3 flex-shrink-0">
                                        <i class="bi bi-box-arrow-in-down fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-7 text-dark">Stok Girişi Yap</div>
                                        <small class="text-muted fs-8">Depoya temin edilen malzemeleri kaydet</small>
                                    </div>
                                </a>

                                <!-- Quick Card 2 -->
                                <a href="<?= base_url('stocks?tab=items') ?>" class="quick-action-card p-3 rounded-3 text-decoration-none d-flex align-items-center bg-white shadow-2xs">
                                    <div class="kpi-icon-box bg-primary text-white me-3 flex-shrink-0">
                                        <i class="bi bi-boxes fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-7 text-dark">Stok Kalemleri Listesi</div>
                                        <small class="text-muted fs-8">Mevcut stok ve malzeme durumlarını incele</small>
                                    </div>
                                </a>
                            </div>

                            <!-- System Status Summary Badge -->
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                                    <small class="fw-semibold text-dark fs-7">Sistem & Modüller Aktif</small>
                                </div>
                                <small class="text-muted fs-8">v2.0 Prod</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- HIZLI İŞLEM POPUP MODALI                    -->
<!-- ========================================== -->
<div class="modal fade" id="quickActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-lightning-charge me-2"></i>Hızlı İşlem Seçiniz</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body p-4">
                <div class="list-group list-group-flush fs-7">
                    <a href="<?= base_url('stocks') ?>" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                        <i class="bi bi-boxes fs-4 me-3 text-warning"></i>
                        <div>
                            <strong class="d-block text-dark">Stok Girişi veya Çıkışı Yap</strong>
                            <small class="text-muted">Malzeme stok miktar işlemleri</small>
                        </div>
                    </a>
                    <a href="<?= base_url('import') ?>" class="list-group-item list-group-item-action d-flex align-items-center py-3">
                        <i class="bi bi-file-earmark-excel fs-4 me-3 text-success"></i>
                        <div>
                            <strong class="d-block text-dark">Excel Toplu Veri Yükleme</strong>
                            <small class="text-muted">Excel / CSV dosyasından toplu stok yükle</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
