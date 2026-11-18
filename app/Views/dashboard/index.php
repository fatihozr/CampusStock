<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

            <!-- Page Title & Header Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4 fade-up delay-1">
                <div>
                    <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">
                        Günaydın 👋
                    </h4>
                    <p class="text-muted mb-0 fs-7">Envanter operasyonlarına genel bakış</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <button class="ds-btn ds-btn-primary hover-lift shadow-sm" data-bs-toggle="modal" data-bs-target="#quickActionModal">
                        <i class="bi bi-lightning-charge-fill"></i> Hızlı İşlem
                    </button>
                </div>
            </div>

            <!-- KPI Cards Grid -->
            <div class="row g-3 mb-4">
                <!-- 1. Toplam Stok Kalemi -->
                <div class="col-12 col-sm-6 col-lg-3 fade-up delay-1">
                    <a href="<?= base_url('products') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift">
                            <div class="ds-card-body d-flex flex-column h-100 justify-content-between">
                                <div class="ds-stat-card">
                                    <div class="ds-stat-icon" style="background: var(--c-primary-subtle); color: var(--c-primary);">
                                        <?= $_SESSION['active_module'] === 'furniture' ? '🪑' : '📦' ?>
                                    </div>
                                    <div class="ds-stat-info">
                                        <p>Toplam Ürün Çeşidi</p>
                                        <h3 class="counter-up" data-count="<?= (int)($stats['total_stock_items'] ?? 0) ?>">0</h3>
                                    </div>
                                </div>
                                <div class="ds-stat-trend text-success mt-3">
                                    <i class="bi bi-arrow-up-right"></i> Sistemde Aktif
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- 2. Kritik Stok Uyarısı -->
                <div class="col-12 col-sm-6 col-lg-3 fade-up delay-2">
                    <a href="<?= base_url('products?tab=critical') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift">
                            <div class="ds-card-body d-flex flex-column h-100 justify-content-between">
                                <div class="ds-stat-card">
                                    <div class="ds-stat-icon" style="background: var(--c-danger-subtle); color: var(--c-danger);">
                                        ⚠️
                                    </div>
                                    <div class="ds-stat-info">
                                        <p>Kritik Stok Uyarısı</p>
                                        <h3 class="text-danger counter-up" data-count="<?= (int)($stats['critical_stock'] ?? 0) ?>">0</h3>
                                    </div>
                                </div>
                                <div class="ds-stat-trend text-danger mt-3">
                                    Tedarik Edilmesi Gereken
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- 3. Personel / Birim -->
                <div class="col-12 col-sm-6 col-lg-3 fade-up delay-3">
                    <a href="<?= base_url('departments') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift">
                            <div class="ds-card-body d-flex flex-column h-100 justify-content-between">
                                <div class="ds-stat-card">
                                    <div class="ds-stat-icon" style="background: var(--c-info-subtle); color: var(--c-info);">
                                        🏢
                                    </div>
                                    <div class="ds-stat-info">
                                        <p>Kayıtlı Başkanlıklar</p>
                                        <h3 class="counter-up" data-count="<?= (int)($stats['active_departments'] ?? 0) ?>">0</h3>
                                    </div>
                                </div>
                                <div class="ds-stat-trend text-muted mt-3">
                                    Hizmet Verilen Birim Sayısı
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- 4. Bekleyen Talepler -->
                <div class="col-12 col-sm-6 col-lg-3 fade-up delay-4">
                    <a href="<?= base_url('requests') ?>" class="text-decoration-none d-block h-100">
                        <div class="ds-card h-100 hover-lift">
                            <div class="ds-card-body d-flex flex-column h-100 justify-content-between">
                                <div class="ds-stat-card">
                                    <div class="ds-stat-icon" style="background: var(--c-warning-subtle); color: var(--c-warning);">
                                        📨
                                    </div>
                                    <div class="ds-stat-info">
                                        <p>Bekleyen Talepler</p>
                                        <h3 class="counter-up" data-count="<?= (int)($stats['pending_requests'] ?? 0) ?>">0</h3>
                                    </div>
                                </div>
                                <div class="ds-stat-trend text-muted mt-3">
                                    Onay Bekleyen İstekler
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Dashboard Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Monthly Trend Chart -->
                <div class="col-12 col-lg-8 fade-up delay-2">
                    <div class="ds-card h-100">
                        <div class="ds-card-header">
                            <h6 class="ds-card-title"><i class="bi bi-graph-up me-2 text-primary"></i> Aylık Stok Hareketleri (Dağıtım)</h6>
                        </div>
                        <div class="ds-card-body">
                            <div style="position: relative; height:250px; width:100%">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Top Departments Chart -->
                <div class="col-12 col-lg-4 fade-up delay-3">
                    <div class="ds-card h-100">
                        <div class="ds-card-header">
                            <h6 class="ds-card-title"><i class="bi bi-bar-chart-fill me-2 text-success"></i> Başkanlıklara Göre Dağıtım</h6>
                        </div>
                        <div class="ds-card-body d-flex align-items-center justify-content-center">
                            <div style="position: relative; height:250px; width:100%">
                                <canvas id="deptChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Split: Recent Activity & Critical Stocks -->
            <div class="row g-4 mb-4">
                <!-- Recent Transactions Table (Sol Kolon) -->
                <div class="col-12 col-lg-8 fade-up delay-3">
                    <div class="ds-card h-100">
                        <div class="ds-card-header">
                            <h6 class="ds-card-title"><i class="bi bi-activity me-2 text-secondary"></i> Son Stok Hareketleri</h6>
                            <a href="<?= base_url('stocks/movements') ?>" class="ds-btn ds-btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                                Tümünü Gör
                            </a>
                        </div>
                        <div class="ds-table-container">
                            <table class="ds-table">
                                <thead>
                                    <tr>
                                        <th>İşlem</th>
                                        <th>Ürün / Başkanlık</th>
                                        <th>Miktar</th>
                                        <th>Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentActivities)): ?>
                                        <?php foreach ($recentActivities as $act): ?>
                                            <tr>
                                                <td>
                                                    <?php if($act['type'] === 'in'): ?>
                                                        <span class="ds-badge ds-badge-success">⬆️ Giriş</span>
                                                    <?php else: ?>
                                                        <span class="ds-badge ds-badge-danger">⬇️ Çıkış</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark fs-7">
                                                        <?= $_SESSION['active_module'] === 'furniture' ? '🪑' : '🧼' ?> <?= e($act['product_name']) ?>
                                                    </div>
                                                    <div class="text-muted fs-8 mt-1">
                                                        🏢 <?= e($act['department_name'] ?? 'Destek Hizmetleri') ?>
                                                    </div>
                                                </td>
                                                <td class="fw-bold <?= $act['type'] === 'in' ? 'text-success' : 'text-danger' ?>">
                                                    <?= $act['type'] === 'in' ? '+' : '-' ?><?= e($act['quantity']) ?>
                                                </td>
                                                <td class="text-muted fs-8">
                                                    <?= date('d.m.Y', strtotime($act['created_at'])) ?><br>
                                                    <small><?= date('H:i', strtotime($act['created_at'])) ?></small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">
                                                <div class="empty-state py-4">
                                                    <div class="empty-state-icon">📋</div>
                                                    <h5>Hareket Bulunmuyor</h5>
                                                    <p>Sistemde kayıtlı herhangi bir stok girişi veya çıkışı yok.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Critical Stocks (Sağ Kolon) -->
                <div class="col-12 col-lg-4 fade-up delay-4">
                    <div class="ds-card h-100">
                        <div class="ds-card-header" style="background: var(--c-danger-subtle);">
                            <h6 class="ds-card-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Kritik Stoklar</h6>
                        </div>
                        <div class="ds-card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php if (!empty($criticalStocks)): ?>
                                    <?php foreach($criticalStocks as $stock): ?>
                                        <li class="list-group-item px-4 py-3 border-bottom border-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="fw-bold text-dark fs-7">
                                                    <?= $_SESSION['active_module'] === 'furniture' ? '🪑' : '🧻' ?> <?= e($stock['name']) ?>
                                                </div>
                                                <span class="ds-badge ds-badge-danger">Kritik</span>
                                            </div>
                                            <div class="d-flex justify-content-between fs-8 text-muted mb-1">
                                                <span>Stok: <?= $stock['current_stock'] ?></span>
                                                <span>Min: <?= $stock['min_stock'] ?></span>
                                            </div>
                                            <div class="ds-progress">
                                                <?php $pct = ($stock['current_stock'] / ($stock['min_stock'] > 0 ? $stock['min_stock'] : 1)) * 100; ?>
                                                <div class="ds-progress-bar bg-danger" style="width: <?= min(100, $pct) ?>%"></div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item p-0 border-0">
                                        <div class="empty-state py-5">
                                            <div class="empty-state-icon" style="color: var(--c-success);">✔️</div>
                                            <h6 class="text-success fw-bold">Tüm Stoklar Yeterli</h6>
                                            <p class="mb-0 fs-8 text-muted">Kritik seviyeye düşen ürün yok.</p>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- HIZLI İŞLEM POPUP MODALI                   -->
<!-- ========================================== -->
<div class="modal fade" id="quickActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--c-primary-subtle);">
                <h5 class="modal-title fw-bold fs-6 text-primary"><i class="bi bi-lightning-charge-fill me-2"></i>Hızlı İşlem Menüsü</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body p-4">
                <div class="list-group list-group-flush fs-7 gap-2">
                    <a href="<?= base_url('requests/create') ?>" class="list-group-item list-group-item-action d-flex align-items-center p-3 rounded-3 shadow-sm border mb-2" style="transition: var(--trans-fast);">
                        <div class="ds-avatar me-3" style="background: var(--c-primary-subtle); color: var(--c-primary);"><i class="bi bi-cart-plus"></i></div>
                        <div>
                            <strong class="d-block text-dark fs-6">Malzeme Talep Et</strong>
                            <small class="text-muted">Biriminiz için malzeme talep formu oluşturun</small>
                        </div>
                    </a>
                    <?php if(\App\Helpers\Auth::hasPermission('admin')): ?>
                        <a href="<?= base_url('procurements/create') ?>" class="list-group-item list-group-item-action d-flex align-items-center p-3 rounded-3 shadow-sm border" style="transition: var(--trans-fast);">
                            <div class="ds-avatar me-3" style="background: var(--c-success-subtle); color: var(--c-success);"><i class="bi bi-receipt"></i></div>
                            <div>
                                <strong class="d-block text-dark fs-6">İhale / Satın Alma Ekle</strong>
                                <small class="text-muted">Sisteme yeni tedarik ve ihale kaydı oluşturun</small>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<!-- Flash Message Toasts (Triggered by JS) -->
<?php if (!empty($flashSuccess)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: '<?= e($flashSuccess) ?>',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: { popup: 'ds-card shadow-lg' }
            });
        }
    });
</script>
<?php endif; ?>

<!-- Include Chart.js if not globally included -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Initialize Dashboard Charts & CountUp -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // CountUp Animation Logic
    const counters = document.querySelectorAll('.counter-up');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const duration = 1500; // ms
        const increment = target / (duration / 16); // 60fps
        
        let current = 0;
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.innerText = Math.ceil(current).toLocaleString('tr-TR');
                requestAnimationFrame(updateCounter);
            } else {
                counter.innerText = target.toLocaleString('tr-TR');
            }
        };
        
        if(target > 0) updateCounter();
    });

    // Charts Initialization
    if (typeof Chart !== 'undefined') {
        
        // Theme color setup for charts
        const isFurniture = <?= $_SESSION['active_module'] === 'furniture' ? 'true' : 'false' ?>;
        const primaryColor = isFurniture ? '#8B5A2B' : '#0284c7';
        
        // Gradient for Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const gradientPrimary = trendCtx.createLinearGradient(0, 0, 0, 400);
        
        if (isFurniture) {
            gradientPrimary.addColorStop(0, 'rgba(139, 90, 43, 0.4)');
            gradientPrimary.addColorStop(1, 'rgba(139, 90, 43, 0.0)');
        } else {
            gradientPrimary.addColorStop(0, 'rgba(2, 132, 199, 0.4)');
            gradientPrimary.addColorStop(1, 'rgba(2, 132, 199, 0.0)');
        }

        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= $chartTrendLabels ?? '["1. Hft", "2. Hft", "3. Hft", "4. Hft"]' ?>,
                datasets: [{
                    label: 'Stok Hareketleri (Dağıtım)',
                    data: <?= $chartTrendData ?? '[12, 19, 3, 5]' ?>,
                    borderColor: primaryColor,
                    backgroundColor: gradientPrimary,
                    borderWidth: 3,
                    pointBackgroundColor: '#FFFFFF',
                    pointBorderColor: primaryColor,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(15, 23, 42, 0.9)' } },
                scales: {
                    y: { beginAtZero: true, border: {display: false}, grid: { borderDash: [4, 4], color: 'rgba(0,0,0,0.05)' } },
                    x: { border: {display: false}, grid: { display: false } }
                },
                animation: { duration: 2000, easing: 'easeOutQuart' }
            }
        });

        const deptCtx = document.getElementById('deptChart');
        if (deptCtx) {
            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: <?= $chartDeptLabels ?? '["Temizlik İşleri", "Fen İşleri", "Park Bahçeler"]' ?>,
                    datasets: [{
                        label: 'Dağıtılan Miktar',
                        data: <?= $chartDeptData ?? '[150, 90, 120]' ?>,
                        backgroundColor: primaryColor,
                        borderRadius: 6,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    indexAxis: 'y', // Horizontal bar chart
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(15, 23, 42, 0.9)' }
                    },
                    scales: {
                        x: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { border: {display: false}, grid: { display: false } }
                    },
                    animation: { duration: 1500 }
                }
            });
        }
    }
});
</script>
