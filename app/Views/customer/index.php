<?php
use App\Helpers\Auth;
require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div class="content-wrapper p-4" style="min-height: calc(100vh - 65px); background: #F8FAFC;">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i>Müşteri & Cari Hesap Yönetimi</h4>
            <p class="text-muted small mb-0">Veliler, öğrenciler, öğretmenler, okullar ve kurumsal carilerin bakiye takibi</p>
        </div>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
            <i class="bi bi-person-plus-fill me-1"></i>Yeni Müşteri / Cari Ekle
        </button>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Müşteri Listesi -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0">Kayıtlı Müşteriler (<?= count($customers) ?>)</h6>
            <form action="<?= base_url('customers') ?>" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="İsim, telefon, okul ara..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th>Müşteri / Kurum Adı</th>
                        <th>Tür</th>
                        <th>İletişim</th>
                        <th>Özel İskonto</th>
                        <th class="text-end">Cari Bakiye (Veresiye)</th>
                        <th class="text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Kayıtlı müşteri bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($c['full_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($c['address'] ?? 'Adres belirtilmedi') ?></small>
                                </td>
                                <td><span class="badge <?= $c['type_badge'] ?>"><?= $c['type_label'] ?></span></td>
                                <td>
                                    <div><i class="bi bi-telephone text-muted me-1"></i><?= htmlspecialchars($c['phone'] ?? '-') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($c['email'] ?? '') ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border">%<?= (int)$c['discount_rate'] ?></span></td>
                                <td class="text-end">
                                    <?php if ($c['balance'] > 0): ?>
                                        <span class="fw-bold text-danger"><?= number_format($c['balance'], 2, ',', '.') ?> ₺ Borç</span>
                                    <?php else: ?>
                                        <span class="text-success fw-medium">0,00 ₺</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($c['balance'] > 0): ?>
                                        <button class="btn btn-sm btn-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#paymentModal"
                                                onclick="preparePayment(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['full_name'])) ?>', <?= $c['balance'] ?>)">
                                            <i class="bi bi-cash me-1"></i>Tahsilat Al
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Bakiye Yok</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Yeni Müşteri Modalı -->
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= base_url('customers/store') ?>" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Yeni Müşteri / Cari Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Müşteri Türü:</label>
                        <select name="customer_type" class="form-select" required>
                            <option value="individual">Bireysel Müşteri (Veli/Genel)</option>
                            <option value="student">Öğrenci</option>
                            <option value="teacher">Öğretmen</option>
                            <option value="school">Okul / Okul Aile Birliği</option>
                            <option value="corporate">Kurumsal Şirket</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Özel İskonto (%):</label>
                        <input type="number" name="discount_rate" class="form-control" value="0" min="0" max="100">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Ad Soyad / Yetkili Adı:</label>
                    <input type="text" name="first_name" class="form-control" required placeholder="Örn: Ahmet Yılmaz">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Kurum / Şirket Adı (Varsa):</label>
                    <input type="text" name="company_name" class="form-control" placeholder="Örn: Atatürk İlkokulu Okul Aile Birliği">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Telefon:</label>
                        <input type="text" name="phone" class="form-control" placeholder="05XXXXXXXXX">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">E-posta:</label>
                        <input type="email" name="email" class="form-control" placeholder="ornek@mail.com">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Adres:</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-primary fw-bold">Müşteriyi Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Tahsilat Modalı -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('customers/payment') ?>" method="POST" class="modal-content">
            <input type="hidden" name="customer_id" id="paymentCustId">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin me-2"></i>Cari Bakiye Tahsilatı</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Müşteri: <strong id="paymentCustName"></strong></p>
                <p class="text-danger">Mevcut Borç Tutarı: <strong id="paymentCustBalance"></strong> ₺</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tahsil Edilen Tutar (TL):</label>
                    <input type="number" name="amount" id="paymentAmountInput" class="form-control form-control-lg fw-bold" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Ödeme Yolu:</label>
                    <select name="payment_method" class="form-select">
                        <option value="cash">Nakit Kasa</option>
                        <option value="credit_card">POS / Kredi Kartı</option>
                        <option value="bank_transfer">Banka Havalesi / EFT</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-success fw-bold">Tahsilatı Kaydet & Stoğu/Kasayı Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
function preparePayment(id, name, balance) {
    document.getElementById('paymentCustId').value = id;
    document.getElementById('paymentCustName').innerText = name;
    document.getElementById('paymentCustBalance').innerText = balance.toFixed(2);
    document.getElementById('paymentAmountInput').value = balance.toFixed(2);
}
</script>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>

