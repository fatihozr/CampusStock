<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

            <!-- Page Title & Global Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4 fade-up delay-1">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <div class="ds-stat-icon" style="background: var(--c-primary-subtle); color: var(--c-primary); width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-file-earmark-bar-graph"></i></div>
                        Sistem Raporları ve Analiz Ekranı
                    </h4>
                    <p class="text-muted mb-0 fs-7 mt-2">Destek Hizmetleri malzeme tüketim analizleri, maliyet ve envanter raporlamaları.</p>
                </div>
            </div>

            <!-- Global Filters -->
            <?php
                $qs = '';
                if(!empty($_GET)) {
                    $getArgs = $_GET;
                    unset($getArgs['tab'], $getArgs['type'], $getArgs['format']);
                    $qs = http_build_query($getArgs);
                    if($qs !== '') $qs = '&' . $qs;
                }
            ?>
            <div class="ds-card mb-4 fade-up delay-2" style="position: relative; z-index: 1020;">
                <div class="ds-card-body p-4">
                    <form method="GET" action="<?= base_url('reports') ?>" id="form-global-filter">
                        <input type="hidden" name="tab" id="filter_active_tab" value="<?= e($activeTab) ?>">
                        <div class="row g-4 align-items-end">
                            <div class="col-12 col-md-2">
                                <label class="ds-label">Başlangıç</label>
                                <input type="date" name="start_date" class="ds-input" value="<?= e($filters['start_date'] ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="ds-label">Bitiş</label>
                                <input type="date" name="end_date" class="ds-input" value="<?= e($filters['end_date'] ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="ds-label">Hareket Türü</label>
                                <select name="mov_type" class="ds-select">
                                    <option value="all" <?= ($filters['type'] === 'all') ? 'selected' : '' ?>>Tümü</option>
                                    <option value="in" <?= ($filters['type'] === 'in') ? 'selected' : '' ?>>Girişler</option>
                                    <option value="out" <?= ($filters['type'] === 'out') ? 'selected' : '' ?>>Çıkışlar</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="ds-label">Birim / Daire Başkanlığı</label>
                                <select name="department_id" class="ds-select ds-select-search">
                                    <option value="all" <?= (empty($filters['department_id']) || $filters['department_id'] === 'all') ? 'selected' : '' ?>>Tümü</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= (($filters['department_id'] ?? '') == $dept['id']) ? 'selected' : '' ?>><?= e($dept['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="ds-label">Malzeme</label>
                                <select name="product_id" class="ds-select ds-select-search">
                                    <option value="all" <?= (empty($filters['product_id']) || $filters['product_id'] === 'all') ? 'selected' : '' ?>>Tümü</option>
                                    <?php foreach ($stocks as $stk): ?>
                                        <option value="<?= $stk['id'] ?>" <?= (($filters['product_id'] ?? '') == $stk['id']) ? 'selected' : '' ?>><?= e($stk['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-1 d-flex gap-2">
                                <button type="submit" class="ds-btn ds-btn-primary w-100 p-2" title="Filtrele" style="background-color: var(--text-main); border-color: var(--text-main);"><i class="bi bi-funnel-fill"></i></button>
                                <a href="<?= base_url('reports?tab=' . e($activeTab)) ?>" class="ds-btn ds-btn-secondary p-2" title="Temizle"><i class="bi bi-eraser-fill"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Tab Card -->
            <div class="ds-card fade-up delay-3">
                <div class="ds-card-header pt-3 pb-0 border-bottom-0 px-4">
                    <ul class="nav nav-tabs ds-tabs" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'stocks' || $activeTab === 'inventory') ? 'active' : '' ?>" id="rep-stk-tab" data-bs-toggle="tab" data-bs-target="#rep-stk-pane" type="button">
                                <i class="bi bi-boxes"></i> Stok Envanteri
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'consumption') ? 'active' : '' ?>" id="rep-cons-tab" data-bs-toggle="tab" data-bs-target="#rep-cons-pane" type="button">
                                <i class="bi bi-pie-chart-fill"></i> Tüketim Analizi
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'costs') ? 'active' : '' ?>" id="rep-cst-tab" data-bs-toggle="tab" data-bs-target="#rep-cst-pane" type="button">
                                <i class="bi bi-wallet-fill"></i> Maliyet Raporu
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'movements') ? 'active' : '' ?>" id="rep-mov-tab" data-bs-toggle="tab" data-bs-target="#rep-mov-pane" type="button">
                                <i class="bi bi-graph-up"></i> Çıkış Hareketleri
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'critical') ? 'active' : '' ?>" id="rep-crit-tab" data-bs-toggle="tab" data-bs-target="#rep-crit-pane" type="button">
                                <i class="bi bi-exclamation-triangle-fill"></i> Kritik Uyarılar
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'personnel') ? 'active' : '' ?>" id="rep-prs-tab" data-bs-toggle="tab" data-bs-target="#rep-prs-pane" type="button">
                                <i class="bi bi-people-fill"></i> Personel Rehberi
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="ds-card-body p-0">
                    <div class="tab-content" id="reportTabContent">
                        
                        <!-- TAB 1: STOK ENVANTERİ -->
                        <div class="tab-pane fade <?= ($activeTab === 'stocks' || $activeTab === 'inventory') ? 'show active' : '' ?>" id="rep-stk-pane" role="tabpanel">
                            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="fs-7 text-muted">
                                    <i class="bi bi-info-circle-fill me-1 text-primary"></i> Toplam <strong class="text-dark"><?= count($stocks) ?> kalem</strong> güncel stok durumu.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="ds-btn ds-btn-secondary ds-btn-sm rounded-pill btn-send-mail" data-type="stocks" data-title="Stok Raporu">
                                        <i class="bi bi-envelope-at-fill me-1"></i> E-Posta
                                    </button>
                                    <a href="<?= base_url('reports/export?type=stocks&format=xlsx') ?><?= $qs ?>" class="ds-btn ds-btn-primary ds-btn-sm rounded-pill" style="background-color: var(--c-success); border-color: var(--c-success);">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                                    </a>
                                    <a href="<?= base_url('reports/pdf?type=stocks') ?><?= $qs ?>" target="_blank" class="ds-btn ds-btn-primary ds-btn-sm rounded-pill" style="background-color: var(--text-main); border-color: var(--text-main);">
                                        <i class="bi bi-printer-fill me-1"></i> Yazdır
                                    </a>
                                </div>
                            </div>
                            <div class="ds-table-container">
                                <table class="ds-table">
                                    <thead>
                                        <tr>
                                            <th>Stok Kodu</th>
                                            <th>Malzeme Adı</th>
                                            <th>Kategori</th>
                                            <th class="text-center">Mevcut Miktar</th>
                                            <th class="text-center">Kritik Eşik</th>
                                            <th>Depo / Raf</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($stocks)): ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <div class="empty-state-icon">📋</div>
                                                    <h5>Kayıt Yok</h5>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php foreach ($stocks as $stk): ?>
                                            <tr>
                                                <td><span class="badge bg-light text-dark border font-monospace"><?= e($stk['code']) ?></span></td>
                                                <td class="fw-bold text-dark"><?= e($stk['name']) ?></td>
                                                <td><span class="ds-badge ds-badge-primary"><?= e($stk['category']) ?></span></td>
                                                <td class="text-center fw-bold <?= ($stk['quantity'] <= $stk['min_threshold']) ? 'text-danger' : 'text-success' ?>">
                                                    <?= e($stk['quantity']) ?> <span class="fs-8 fw-normal text-muted"><?= e($stk['unit']) ?></span>
                                                </td>
                                                <td class="text-center text-muted"><?= e($stk['min_threshold']) ?></td>
                                                <td class="text-secondary"><i class="bi bi-tag me-1"></i> <?= e($stk['brand'] ?? 'Belirtilmedi') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: TÜKETİM ANALİZİ -->
                        <div class="tab-pane fade <?= ($activeTab === 'consumption') ? 'show active' : '' ?>" id="rep-cons-pane" role="tabpanel">
                            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="fs-7 text-muted">
                                    <i class="bi bi-pie-chart-fill me-1 text-danger"></i> Birimlerin toplam sarf malzemesi tüketim verileri.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="ds-btn ds-btn-secondary ds-btn-sm rounded-pill btn-send-mail" data-type="consumption" data-title="Birim Tüketim Analizi">
                                        <i class="bi bi-envelope-at-fill me-1"></i> E-Posta
                                    </button>
                                    <a href="<?= base_url('reports/export?type=consumption&format=xlsx') ?><?= $qs ?>" class="ds-btn ds-btn-primary ds-btn-sm rounded-pill" style="background-color: var(--c-success); border-color: var(--c-success);">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                                    </a>
                                </div>
                            </div>

                            <div class="row g-0">
                                <!-- Chart Area -->
                                <div class="col-12 col-xl-4 p-4 border-end" style="background: var(--bg-surface-hover);">
                                    <h6 class="fw-bold text-dark mb-4 fs-6">Birimlere Göre Tüketim Dağılımı (TL)</h6>
                                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                                        <canvas id="consumptionChart"></canvas>
                                    </div>
                                </div>
                                <!-- Table Area -->
                                <div class="col-12 col-xl-8">
                                    <div class="ds-table-container" style="max-height: 500px; overflow-y: auto;">
                                        <table class="ds-table mb-0">
                                            <thead class="sticky-top z-1" style="background: var(--bg-surface);">
                                                <tr>
                                                    <th>Birim / Müdürlük</th>
                                                    <th>Stok Kodu</th>
                                                    <th>Malzeme Adı</th>
                                                    <th class="text-center">Tüketim Miktarı</th>
                                                    <th class="text-end">Toplam Maliyet</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(empty($consumption)): ?>
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="empty-state">
                                                                <div class="empty-state-icon">📥</div>
                                                                <h5>Tüketim kaydı bulunamadı.</h5>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($consumption as $item): ?>
                                                        <tr>
                                                            <td class="fw-bold text-dark"><?= e($item['department_name'] ?? 'Bilinmiyor') ?></td>
                                                            <td><span class="badge bg-light text-dark font-monospace border"><?= e($item['product_code']) ?></span></td>
                                                            <td><?= e($item['product_name']) ?></td>
                                                            <td class="text-center fw-bold text-primary"><span class="ds-badge ds-badge-primary"><?= e($item['total_quantity']) ?> <?= e($item['unit']) ?></span></td>
                                                            <td class="text-end fw-bold text-danger"><?= number_format($item['total_cost'], 2, ',', '.') ?> ₺</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: MALİYET RAPORU -->
                        <div class="tab-pane fade <?= ($activeTab === 'costs') ? 'show active' : '' ?>" id="rep-cst-pane" role="tabpanel">
                            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="fs-7 text-muted">
                                    <i class="bi bi-wallet-fill me-1 text-success"></i> Güncel depo varlıklarının maliyet ve değer raporlaması.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="ds-btn ds-btn-secondary ds-btn-sm rounded-pill btn-send-mail" data-type="costs" data-title="Maliyet Raporu">
                                        <i class="bi bi-envelope-at-fill me-1"></i> E-Posta
                                    </button>
                                    <a href="<?= base_url('reports/export?type=costs&format=xlsx') ?><?= $qs ?>" class="ds-btn ds-btn-primary ds-btn-sm rounded-pill" style="background-color: var(--c-success); border-color: var(--c-success);">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                                    </a>
                                </div>
                            </div>
                            
                            <div class="row g-0">
                                <!-- Chart Area -->
                                <div class="col-12 col-xl-4 p-4 border-end" style="background: var(--bg-surface-hover);">
                                    <h6 class="fw-bold mb-4 fs-6 text-dark">Kategori Maliyet Dağılımı</h6>
                                    <div class="ds-card text-white border-0 mb-4 position-relative overflow-hidden" style="background: var(--c-success);">
                                        <div class="ds-card-body p-4 position-relative z-1">
                                            <h6 class="text-white-50 fw-semibold mb-2">Toplam Ambar Değeri</h6>
                                            <h2 class="fw-bold text-white mb-0"><?= number_format($totalCost, 2, ',', '.') ?> ₺</h2>
                                        </div>
                                        <i class="bi bi-piggy-bank position-absolute text-white" style="font-size: 6rem; right: -10px; bottom: -20px; opacity: 0.2;"></i>
                                    </div>
                                    <div class="chart-container" style="position: relative; height: 250px; width: 100%;">
                                        <canvas id="categoryCostsChart"></canvas>
                                    </div>
                                </div>
                                <!-- Table Area -->
                                <div class="col-12 col-xl-8">
                                    <div class="ds-table-container" style="max-height: 550px; overflow-y: auto;">
                                        <table class="ds-table mb-0">
                                            <thead class="sticky-top z-1" style="background: var(--bg-surface);">
                                                <tr>
                                                    <th>Malzeme</th>
                                                    <th class="text-end">Mevcut Stok</th>
                                                    <th class="text-end">Birim Fiyat</th>
                                                    <th class="text-end">Toplam Değer</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($stocks as $stk): ?>
                                                    <?php $lineTotal = ($stk['current_stock'] ?? 0) * ($stk['unit_price'] ?? 0); ?>
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold text-dark mb-1"><?= e($stk['name']) ?></div>
                                                            <div class="font-monospace text-muted fs-8"><?= e($stk['code']) ?> &middot; <?= e($stk['category']) ?></div>
                                                        </td>
                                                        <td class="text-end fw-medium"><?= e($stk['current_stock']) ?> <span class="fs-8 text-muted"><?= e($stk['unit']) ?></span></td>
                                                        <td class="text-end text-muted"><?= number_format($stk['unit_price'], 2, ',', '.') ?> ₺</td>
                                                        <td class="text-end fw-bold text-success"><?= number_format($lineTotal, 2, ',', '.') ?> ₺</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: STOK HAREKETLERİ -->
                        <div class="tab-pane fade <?= ($activeTab === 'movements') ? 'show active' : '' ?>" id="rep-mov-pane" role="tabpanel">
                            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="fs-7 text-muted">
                                    <i class="bi bi-graph-up me-1 text-info"></i> Sistemdeki tüm giriş/çıkış ve transfer işlemlerinin kayıtları.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="ds-btn ds-btn-secondary ds-btn-sm rounded-pill btn-send-mail" data-type="movements" data-title="??k?? Hareketleri">
                                        <i class="bi bi-envelope-at-fill me-1"></i> E-Posta
                                    </button>
                                    <a href="<?= base_url('reports/export?type=movements&format=xlsx') ?><?= $qs ?>" class="ds-btn ds-btn-primary ds-btn-sm rounded-pill" style="background-color: var(--c-success); border-color: var(--c-success);">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                                    </a>
                                </div>
                            </div>
                            
                            <div class="p-4 border-bottom" style="background: var(--bg-surface-hover);">
                                <h6 class="fw-bold text-dark fs-6 mb-3">Günlük Hareket Hacmi</h6>
                                <div class="chart-container" style="position: relative; height: 200px; width: 100%;">
                                    <canvas id="movementsTimelineChart"></canvas>
                                </div>
                            </div>

                            <div class="ds-table-container" style="max-height:500px; overflow-y: auto;">
                                <table class="ds-table mb-0">
                                    <thead class="sticky-top z-1" style="background: var(--bg-surface);">
                                        <tr>
                                            <th>Tarih</th>
                                            <th>İşlem</th>
                                            <th>Malzeme</th>
                                            <th>İlgili Birim / Kişi</th>
                                            <th class="text-center">Miktar</th>
                                            <th>İşlemi Yapan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($movements)): ?>
                                            <tr>
                                                <td colspan="6">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">📥</div>
                                                        <h5>Hareket kaydı bulunamadı.</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($movements as $item): ?>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="fw-medium text-dark"><?= date('d.m.Y', strtotime($item['created_at'])) ?></div>
                                                        <div class="fs-8"><?= date('H:i', strtotime($item['created_at'])) ?></div>
                                                    </td>
                                                    <td>
                                                        <?php if($item['type'] === 'in'): ?>
                                                            <span class="ds-badge ds-badge-success"><i class="bi bi-arrow-down-left"></i> GİRİŞ</span>
                                                        <?php else: ?>
                                                            <span class="ds-badge ds-badge-danger"><i class="bi bi-arrow-up-right"></i> ÇIKIŞ</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-dark"><?= e($item['product_name']) ?></div>
                                                        <div class="font-monospace text-muted fs-8"><?= e($item['product_code']) ?></div>
                                                    </td>
                                                    <td class="text-muted fw-medium"><?= e($item['department_name'] ?? '-') ?></td>
                                                    <td class="text-center fw-bold fs-6 <?= $item['type'] === 'in' ? 'text-success' : 'text-danger' ?>">
                                                        <?= $item['type'] === 'in' ? '+' : '-' ?><?= e($item['quantity']) ?>
                                                    </td>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($item['first_name'] . ' ' . $item['last_name']) ?>&background=random&size=24" class="rounded-circle shadow-sm" alt="">
                                                            <?= e($item['first_name'] . ' ' . $item['last_name']) ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 5: KRİTİK STOK -->
                        <div class="tab-pane fade <?= ($activeTab === 'critical') ? 'show active' : '' ?>" id="rep-crit-pane" role="tabpanel">
                            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="fs-7 text-danger fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Minimum stok seviyesinin altına düşmüş, acil sipariş verilmesi gereken malzemeler (<?= count($critical) ?>)
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="ds-btn ds-btn-secondary ds-btn-sm rounded-pill btn-send-mail" data-type="critical" data-title="Kritik Stok Uyarısı">
                                        <i class="bi bi-envelope-at-fill me-1"></i> E-Posta
                                    </button>
                                    <a href="<?= base_url('reports/export?type=critical&format=xlsx') ?><?= $qs ?>" class="ds-btn ds-btn-primary ds-btn-sm rounded-pill" style="background-color: var(--c-danger); border-color: var(--c-danger);">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                                    </a>
                                </div>
                            </div>
                            <div class="ds-table-container">
                                <table class="ds-table">
                                    <thead>
                                        <tr>
                                            <th>Malzeme / Kategori</th>
                                            <th class="text-center">Kritik Eşik</th>
                                            <th class="text-center">Mevcut Stok</th>
                                            <th class="text-end">Acil İhtiyaç</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($critical)): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">✅</div>
                                                        <h5 class="text-success">Kritik seviyede stok bulunmamaktadır.</h5>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($critical as $item): ?>
                                                <?php $fark = $item['min_stock'] - $item['current_stock']; ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-dark mb-1"><?= e($item['name']) ?></div>
                                                        <span class="badge bg-danger-subtle text-danger font-monospace border-danger-subtle fs-8"><?= e($item['code']) ?></span>
                                                        <span class="text-muted fs-8 ms-2"><?= e($item['category_name']) ?></span>
                                                    </td>
                                                    <td class="text-center text-secondary fw-bold"><?= e($item['min_stock']) ?> <span class="fs-8 fw-normal"><?= e($item['unit']) ?></span></td>
                                                    <td class="text-center fw-bold text-danger fs-6"><?= e($item['current_stock']) ?> <span class="fs-8 fw-normal"><?= e($item['unit']) ?></span></td>
                                                    <td class="text-end fw-bold text-danger fs-5">
                                                        <span class="ds-badge ds-badge-danger">+ <?= $fark ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 6: PERSONEL REHBERİ -->
                        <div class="tab-pane fade <?= ($activeTab === 'personnel') ? 'show active' : '' ?>" id="rep-prs-pane" role="tabpanel">
                            <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div class="fs-7 text-muted">
                                    <i class="bi bi-people-fill me-1 text-secondary"></i> Sistemde kayıtlı <strong><?= count($personnel) ?> personel</strong> iletişim ve görev rehberi.
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= base_url('reports/export?type=personnel&format=xlsx') ?><?= $qs ?>" class="ds-btn ds-btn-primary ds-btn-sm rounded-pill" style="background-color: var(--c-success); border-color: var(--c-success);">
                                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                                    </a>
                                </div>
                            </div>
                            <div class="ds-table-container">
                                <table class="ds-table">
                                    <thead>
                                        <tr>
                                            <th>Personel</th>
                                            <th>Sicil No</th>
                                            <th>Görev & Birim</th>
                                            <th>İletişim Bilgileri</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($personnel as $p): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['full_name'] ?? ($p['first_name'] . ' ' . $p['last_name'])) ?>&background=random&size=40" class="rounded-circle shadow-sm" alt="">
                                                        <span class="fw-bold text-dark fs-6"><?= e($p['full_name'] ?? ($p['first_name'] . ' ' . $p['last_name'])) ?></span>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-secondary font-monospace"><?= e($p['sicil_no']) ?></span></td>
                                                <td>
                                                    <div class="fw-medium text-dark"><?= e($p['title']) ?></div>
                                                    <div class="text-muted fs-8"><?= e($p['department']) ?> / <?= e($p['unit'] ?? '-') ?></div>
                                                </td>
                                                <td class="text-secondary">
                                                    <div class="d-flex flex-column gap-1">
                                                        <span><i class="bi bi-envelope text-primary me-2"></i><?= e($p['email']) ?></span>
                                                        <span><i class="bi bi-telephone text-success me-2"></i><?= e($p['phone']) ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- MODAL: E-POSTA RAPOR GÖNDERME MODALI       -->
<!-- ========================================== -->
<div class="modal fade" id="sendReportMailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header py-3 border-bottom" style="background: var(--c-primary-subtle);">
                <h5 class="modal-title fw-bold fs-6 text-primary"><i class="bi bi-envelope-paper me-2"></i>Raporu E-Posta İle İlet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="sendReportMailForm" method="POST" action="<?= base_url('reports/send-email') ?>">
                <input type="hidden" name="type" id="mail_report_type" value="stocks">
                <input type="hidden" name="start_date" id="mail_start_date">
                <input type="hidden" name="end_date" id="mail_end_date">
                <input type="hidden" name="mov_type" id="mail_mov_type">
                <input type="hidden" name="department_id" id="mail_department_id">
                <input type="hidden" name="product_id" id="mail_product_id">
                
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="ds-label">Alıcı E-Posta <span class="text-danger">*</span></label>
                        <input type="email" name="to_email" class="ds-input" placeholder="ornek@stationeryflow.com" required value="<?= e($currentUser['email'] ?? '') ?>">
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Konu Başlığı</label>
                            <input type="text" name="subject" id="mail_report_subject" class="ds-input" required value="Sistem Raporu">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="ds-label">Ek Biçimi</label>
                            <select name="format" class="ds-select">
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="pdf" selected>PDF Dosyası (.pdf)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="ds-label">Kısa Not</label>
                        <textarea name="message" class="ds-input" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-3 border-top" style="background: rgba(0,0,0,0.01);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary"><i class="bi bi-send-fill me-2"></i>Gönder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Sekme Yönetimi
    const tabMap = {
        'rep-stk-tab': 'stocks',
        'rep-cons-tab': 'consumption',
        'rep-cst-tab': 'costs',
        'rep-mov-tab': 'movements',
        'rep-crit-tab': 'critical',
        'rep-prs-tab': 'personnel'
    };
    const filterTabInput = document.getElementById('filter_active_tab');
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function (e) {
            if(filterTabInput && tabMap[e.target.id]) filterTabInput.value = tabMap[e.target.id];
        });
    });

    // 2. Mail Gönderimi Modalı Veri Doldurma
    document.querySelectorAll('.btn-send-mail').forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type || 'stocks';
            const title = this.dataset.title || 'Rapor';
            
            document.getElementById('mail_report_type').value = type;
            document.getElementById('mail_report_subject').value = title + ' - ' + new Date().toLocaleDateString('tr-TR');
            
            const urlParams = new URLSearchParams(window.location.search);
            document.getElementById('mail_start_date').value = urlParams.get('start_date') || '';
            document.getElementById('mail_end_date').value = urlParams.get('end_date') || '';
            document.getElementById('mail_mov_type').value = urlParams.get('mov_type') || 'all';
            document.getElementById('mail_department_id').value = urlParams.get('department_id') || '';
            document.getElementById('mail_product_id').value = urlParams.get('product_id') || '';
            
            new bootstrap.Modal(document.getElementById('sendReportMailModal')).show();
        });
    });

    // --- 3. CHART.JS UYGULAMALARI ---

    // Ortak Chart Ayarları
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = "#64748b";
    Chart.defaults.plugins.tooltip.backgroundColor = "rgba(15, 23, 42, 0.9)";
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    
    <?php
        // PHP'den veri hazırlama:
        // A. Consumption Data (Birim Bazlı Maliyet Dağılımı)
        $deptConsumptions = [];
        if(!empty($consumption)) {
            foreach($consumption as $c) {
                $deptName = $c['department_name'] ?? 'Bilinmiyor';
                if(!isset($deptConsumptions[$deptName])) $deptConsumptions[$deptName] = 0;
                $deptConsumptions[$deptName] += (float)$c['total_cost'];
            }
        }
        $deptLabels = array_keys($deptConsumptions);
        $deptData = array_values($deptConsumptions);

        // B. Kategori Bazlı Maliyetler
        $catLabels = array_keys($categoryCosts ?? []);
        $catData = array_values($categoryCosts ?? []);

        // C. Zaman Çizelgesi (Movements Timeline)
        $timelineData = [];
        if(!empty($movements)) {
            foreach(array_reverse($movements) as $m) {
                $date = date('Y-m-d', strtotime($m['created_at']));
                if(!isset($timelineData[$date])) {
                    $timelineData[$date] = ['in' => 0, 'out' => 0];
                }
                if($m['type'] === 'in') {
                    $timelineData[$date]['in'] += (int)$m['quantity'];
                } else {
                    $timelineData[$date]['out'] += (int)$m['quantity'];
                }
            }
        }
        $tlLabels = array_keys($timelineData);
        $tlInData = array_column($timelineData, 'in');
        $tlOutData = array_column($timelineData, 'out');
    ?>

    // A. Birim Tüketim Dağılımı (Bar Chart)
    const ctxCons = document.getElementById('consumptionChart');
    if (ctxCons && <?= !empty($deptLabels) ? 'true' : 'false' ?>) {
        new Chart(ctxCons, {
            type: 'bar',
            data: {
                labels: <?= json_encode($deptLabels) ?>,
                datasets: [{
                    label: 'Tüketim (TL)',
                    data: <?= json_encode($deptData) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { border: {display: false}, grid: { display: false } }
                }
            }
        });
    }

    // B. Kategori Maliyet Dağılımı Grafiği (Doughnut Chart)
    const ctxCat = document.getElementById('categoryCostsChart');
    if (ctxCat && <?= !empty($catLabels) ? 'true' : 'false' ?>) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($catLabels) ?>,
                datasets: [{
                    data: <?= json_encode($catData) ?>,
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, usePointStyle: true, font: {size: 11} } }
                }
            }
        });
    }

    // C. ??k?? Hareketleri Zaman Çizelgesi (Line Chart)
    const ctxTl = document.getElementById('movementsTimelineChart');
    if (ctxTl && <?= !empty($tlLabels) ? 'true' : 'false' ?>) {
        new Chart(ctxTl, {
            type: 'line',
            data: {
                labels: <?= json_encode($tlLabels) ?>,
                datasets: [
                    {
                        label: 'Girişler (+)',
                        data: <?= json_encode($tlInData) ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Çıkışlar (-)',
                        data: <?= json_encode($tlOutData) ?>,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', align: 'end', labels: {boxWidth: 10, usePointStyle: true} } },
                scales: {
                    y: { beginAtZero: true, border: {display: false}, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { border: {display: false}, grid: { display: false } }
                }
            }
        });
    }

});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
