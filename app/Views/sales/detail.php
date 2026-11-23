<?php
use App\Helpers\Auth;
require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div class="content-wrapper p-4" style="min-height: calc(100vh - 65px); background: #F8FAFC;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('sales') ?>" class="btn btn-sm btn-outline-secondary mb-2"><i class="bi bi-arrow-left me-1"></i>Satış Listesine Dön</a>
            <h4 class="fw-bold mb-0">Satış Fişi: <span class="text-primary font-monospace"><?= htmlspecialchars($sale['receipt_no']) ?></span></h4>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Yazdır</button>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Fiş Özeti -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0">Satılan Ürünler</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th>Ürün</th>
                                <th>Barkod</th>
                                <th class="text-center">Miktar</th>
                                <th class="text-end">Birim Fiyat</th>
                                <th class="text-end">KDV</th>
                                <th class="text-end">Satır Toplamı</th>
                                <th class="text-center">İade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sale['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($item['unit']) ?></small>
                                    </td>
                                    <td class="font-monospace small"><?= htmlspecialchars($item['barcode'] ?? '-') ?></td>
                                    <td class="text-center fw-bold">
                                        <?= $item['quantity'] ?>
                                        <?php if ($item['returned_quantity'] > 0): ?>
                                            <span class="badge bg-danger ms-1">-<?= $item['returned_quantity'] ?> İade</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><?= number_format($item['unit_price'], 2, ',', '.') ?> ₺</td>
                                    <td class="text-end small text-muted">%<?= (int)$item['tax_rate'] ?></td>
                                    <td class="text-end fw-bold text-primary"><?= number_format($item['line_total'], 2, ',', '.') ?> ₺</td>
                                    <td class="text-center">
                                        <?php 
                                        $canReturn = $item['quantity'] - $item['returned_quantity'];
                                        if ($canReturn > 0): 
                                        ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#returnModal"
                                                    onclick="prepareReturn(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['product_name'])) ?>', <?= $canReturn ?>)">
                                                İade Al
                                            </button>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Tamamı İade</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Müşteri ve Ödeme Bilgileri -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
                <h6 class="fw-bold border-bottom pb-2 mb-3">İşlem Detayları</h6>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Tarih & Saat:</span>
                    <span class="fw-semibold"><?= date('d.m.Y H:i', strtotime($sale['created_at'])) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Kasiyer:</span>
                    <span class="fw-semibold"><?= htmlspecialchars($sale['cashier_display']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Müşteri:</span>
                    <span class="fw-semibold text-primary"><?= htmlspecialchars($sale['customer_display']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Ödeme Yöntemi:</span>
                    <span class="badge bg-light text-dark border"><?= $sale['payment_label'] ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Durum:</span>
                    <span class="badge <?= $sale['status_badge'] ?>"><?= $sale['status_label'] ?></span>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between mb-1 small text-muted">
                    <span>Ara Toplam:</span>
                    <span><?= number_format($sale['subtotal'], 2, ',', '.') ?> ₺</span>
                </div>
                <?php if ($sale['discount_total'] > 0): ?>
                    <div class="d-flex justify-content-between mb-1 small text-danger">
                        <span>İndirim Tutarı:</span>
                        <span>-<?= number_format($sale['discount_total'], 2, ',', '.') ?> ₺</span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2 small text-muted">
                    <span>KDV Tutarı:</span>
                    <span><?= number_format($sale['tax_total'], 2, ',', '.') ?> ₺</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-light rounded mb-2">
                    <span class="fw-bold">GENEL TOPLAM:</span>
                    <span class="fs-5 fw-bold text-primary"><?= number_format($sale['grand_total'], 2, ',', '.') ?> ₺</span>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Ödenen / Para Üstü:</span>
                    <span><?= number_format($sale['paid_amount'], 2, ',', '.') ?> / <?= number_format($sale['change_amount'], 2, ',', '.') ?> ₺</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İade Modalı -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('sales/return') ?>" method="POST" class="modal-content">
            <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
            <input type="hidden" name="sale_item_id" id="modalReturnItemId">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i>Ürün İadesi Al</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Seçilen Ürün: <strong id="modalReturnProdName"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-bold">İade Edilecek Miktar:</label>
                    <input type="number" name="quantity" id="modalReturnQty" class="form-control" min="1" step="1" required>
                    <small class="text-muted" id="modalReturnMaxMsg"></small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">İade Nedeni:</label>
                    <input type="text" name="reason" class="form-control" placeholder="Örn: Yanlış ürün, müşteri vazgeçti, defolu vb." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" class="btn btn-danger fw-bold">İadeyi Onayla & Stoğa Ekle</button>
            </div>
        </form>
    </div>
</div>

<script>
function prepareReturn(itemId, prodName, maxQty) {
    document.getElementById('modalReturnItemId').value = itemId;
    document.getElementById('modalReturnProdName').innerText = prodName;
    const qtyInput = document.getElementById('modalReturnQty');
    qtyInput.max = maxQty;
    qtyInput.value = maxQty;
    document.getElementById('modalReturnMaxMsg').innerText = `Maksimum iade edilebilir miktar: ${maxQty}`;
}
</script>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>

