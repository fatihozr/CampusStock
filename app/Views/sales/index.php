<?php
use App\Helpers\Auth;
require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div class="content-wrapper p-4" style="min-height: calc(100vh - 65px); background: #F8FAFC;">
    <!-- Başlık ve Hızlı İstatistik Kartları -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>Satış Geçmişi & Kasa Raporu</h4>
            <p class="text-muted small mb-0">Gerçekleşen tüm POS satışlarını, iadeleri ve fiş dökümlerini inceleyin</p>
        </div>
        <a href="<?= base_url('pos') ?>" class="btn btn-primary fw-bold shadow-sm">
            <i class="bi bi-cart-plus me-1"></i>Yeni Satış Yap (POS)
        </a>
    </div>

    <!-- Günlük Kasa / Ciro Özeti -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-bold">GÜNLÜK CİRO</small>
                        <h4 class="fw-bolder text-primary mb-0 mt-1"><?= number_format($dailySummary['total_revenue'], 2, ',', '.') ?> ₺</h4>
                    </div>
                    <div class="p-3 bg-primary-subtle rounded-circle text-primary"><i class="bi bi-cash-coin fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-bold">BRÜT KÂR</small>
                        <h4 class="fw-bolder text-success mb-0 mt-1"><?= number_format($dailySummary['gross_profit'], 2, ',', '.') ?> ₺</h4>
                    </div>
                    <div class="p-3 bg-success-subtle rounded-circle text-success"><i class="bi bi-graph-up-arrow fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-bold">NAKİT / KREDİ KARTI</small>
                        <h5 class="fw-bold text-dark mb-0 mt-1">
                            <span class="text-success"><?= number_format($dailySummary['cash_total'], 0, ',', '.') ?>₺</span> / 
                            <span class="text-primary"><?= number_format($dailySummary['card_total'], 0, ',', '.') ?>₺</span>
                        </h5>
                    </div>
                    <div class="p-3 bg-info-subtle rounded-circle text-info"><i class="bi bi-credit-card-2-front fs-4"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-bold">TOPLAM FİŞ ADEDİ</small>
                        <h4 class="fw-bolder text-dark mb-0 mt-1"><?= $dailySummary['total_sales_count'] ?> Adet</h4>
                    </div>
                    <div class="p-3 bg-warning-subtle rounded-circle text-warning"><i class="bi bi-receipt-cutoff fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Satış Tablosu -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">Son Satışlar</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th>Fiş No</th>
                        <th>Tarih</th>
                        <th>Müşteri</th>
                        <th>Ödeme Türü</th>
                        <th>Kalem</th>
                        <th class="text-end">Tutar</th>
                        <th class="text-center">Durum</th>
                        <th class="text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Henüz kayıtlı satış bulunmamaktadır.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($s['receipt_no']) ?></td>
                                <td class="small text-muted"><?= date('d.m.Y H:i', strtotime($s['created_at'])) ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($s['customer_display']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($s['cashier_display']) ?> (Kasa)</small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $s['payment_label'] ?></span></td>
                                <td><?= $s['item_count'] ?> Çeşit</td>
                                <td class="text-end fw-bold fs-6 text-dark"><?= number_format($s['grand_total'], 2, ',', '.') ?> ₺</td>
                                <td class="text-center"><span class="badge <?= $s['status_badge'] ?>"><?= $s['status_label'] ?></span></td>
                                <td class="text-center">
                                    <a href="<?= base_url('sales/detail?id=' . $s['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>Detay & Fiş
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
