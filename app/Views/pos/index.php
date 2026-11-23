<?php
use App\Helpers\Auth;
require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div class="content-wrapper p-3" style="min-height: calc(100vh - 65px); background: #F1F5F9;">
    <!-- POS Üst Bar -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-5">
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-cart4 text-primary me-2"></i>Kırtasiye Hızlı Satış (POS)</h4>
            <small class="text-muted">Barkod okutarak veya ürün seçerek saniyeler içinde satış yapın</small>
        </div>
        <div class="col-md-7">
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-primary text-white border-primary"><i class="bi bi-upc-scan fs-4"></i></span>
                <input type="text" id="barcodeInput" class="form-control form-control-lg border-primary fw-bold" placeholder="Barkodu buraya okutun veya SKU yazıp Enter'a basın..." autofocus autocomplete="off">
                <button class="btn btn-primary px-4 fw-bold" id="btnBarcodeAdd" type="button"><i class="bi bi-plus-lg me-1"></i>Sepete Ekle</button>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- SOL KOLON: Ürün Kataloğu & Kategori Filtreleri -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <!-- Arama kutusu -->
                        <div class="input-group" style="max-width: 280px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchProductInput" class="form-control bg-light border-start-0" placeholder="Ürün adı, marka veya kod ara...">
                        </div>
                        
                        <!-- Kategori Hap Butonları -->
                        <div class="category-filters d-flex gap-1 overflow-auto pb-1" style="max-width: 100%;">
                            <button class="btn btn-sm btn-outline-primary active cat-filter-btn" data-cat-id="">Tümü</button>
                            <?php foreach ($categories as $cat): ?>
                                <button class="btn btn-sm btn-outline-secondary cat-filter-btn text-nowrap" data-cat-id="<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 overflow-auto" style="max-height: calc(100vh - 230px);" id="productGrid">
                    <div class="row g-2" id="productListContainer">
                        <?php foreach ($featuredProducts as $prod): ?>
                            <div class="col-md-4 col-sm-6 product-card-col" 
                                 data-cat-id="<?= $prod['category_id'] ?>" 
                                 data-name="<?= htmlspecialchars(mb_strtolower($prod['name'], 'UTF-8')) ?>" 
                                 data-code="<?= htmlspecialchars(mb_strtolower($prod['code'], 'UTF-8')) ?>"
                                 data-barcode="<?= htmlspecialchars($prod['barcode'] ?? '') ?>">
                                <div class="card h-100 border product-select-card p-2 text-start position-relative cursor-pointer hover-shadow" 
                                     onclick="addToCart(<?= htmlspecialchars(json_encode($prod)) ?>)"
                                     style="cursor: pointer; transition: all 0.15s ease-in-out;">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($prod['barcode'] ?: $prod['code']) ?></span>
                                        <span class="badge <?= $prod['current_stock'] > $prod['min_stock'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                                            Stok: <?= (int)$prod['current_stock'] ?>
                                        </span>
                                    </div>
                                    <h6 class="card-title fw-bold text-dark text-truncate mb-1" title="<?= htmlspecialchars($prod['name']) ?>">
                                        <?= htmlspecialchars($prod['name']) ?>
                                    </h6>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                        <small class="text-muted text-truncate me-2"><?= htmlspecialchars($prod['brand'] ?? 'Genel') ?></small>
                                        <span class="fw-bold fs-6 text-primary"><?= number_format($prod['sale_price'], 2, ',', '.') ?> ₺</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SAĞ KOLON: Sepet, Müşteri Seçimi ve Ödeme -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100 d-flex flex-column">
                <!-- Müşteri & İskonto Seçimi -->
                <div class="card-header bg-white border-bottom py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-8">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-person-fill text-primary me-1"></i>Müşteri / Cari:</label>
                            <select id="customerSelect" class="form-select form-select-sm" onchange="onCustomerChange()">
                                <option value="" data-discount="0">Genel Müşteri (Perakende - İndirimsiz)</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>" data-discount="<?= $c['discount_rate'] ?>">
                                        <?= htmlspecialchars($c['full_name']) ?> (<?= $c['type_label'] ?>, %<?= (int)$c['discount_rate'] ?> İndirim)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-percent text-success me-1"></i>Özel İskonto:</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="customDiscountRate" class="form-control form-control-sm text-center" value="0" min="0" max="100" onchange="calculateCart()">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sepet Tablosu -->
                <div class="card-body p-0 overflow-auto flex-grow-1" style="max-height: calc(100vh - 430px); min-height: 220px;">
                    <table class="table table-hover align-middle mb-0" id="cartTable">
                        <thead class="table-light sticky-top small text-muted">
                            <tr>
                                <th>Ürün Adı</th>
                                <th class="text-center" style="width: 110px;">Adet</th>
                                <th class="text-end" style="width: 80px;">Birim</th>
                                <th class="text-end" style="width: 90px;">Toplam</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            <!-- Sepet satırları JS ile buraya eklenecek -->
                        </tbody>
                    </table>
                    <div id="cartEmptyMessage" class="text-center py-5 text-muted">
                        <i class="bi bi-basket3 display-4 text-secondary opacity-50 mb-2"></i>
                        <p class="mb-0 fw-medium">Sepetiniz boş.</p>
                        <small>Soldan ürün seçin veya barkod okutun.</small>
                    </div>
                </div>

                <!-- Ödeme Özeti ve Butonlar -->
                <div class="card-footer bg-light border-top p-3 mt-auto">
                    <!-- Fiyat Dağılımı -->
                    <div class="d-flex justify-content-between mb-1 small text-muted">
                        <span>Ara Toplam:</span>
                        <span id="summarySubtotal" class="fw-bold">0,00 ₺</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small text-danger" id="summaryDiscountRow" style="display: none !important;">
                        <span>İskonto / İndirim:</span>
                        <span id="summaryDiscount">-0,00 ₺</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>KDV (%20 Dahil):</span>
                        <span id="summaryTax">0,00 ₺</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-white rounded border mb-3">
                        <span class="fs-5 fw-bold text-dark">ÖDENECEK TUTAR:</span>
                        <span class="fs-4 fw-bolder text-primary" id="summaryGrandTotal">0,00 ₺</span>
                    </div>

                    <!-- Hızlı Nakit / Para Üstü -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white fw-bold">Alınan:</span>
                                <input type="number" id="paidAmountInput" class="form-control fw-bold text-end" placeholder="0,00" step="1" oninput="calculateChange()">
                                <span class="input-group-text">₺</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold text-secondary">Para Üstü:</span>
                                <input type="text" id="changeAmountDisplay" class="form-control fw-bold text-end text-success bg-light" readonly value="0,00 ₺">
                            </div>
                        </div>
                    </div>

                    <!-- Ödeme Butonları -->
                    <div class="row g-2">
                        <div class="col-4">
                            <button class="btn btn-success w-100 py-2 fw-bold d-flex flex-column align-items-center justify-content-center" onclick="completeSale('cash')">
                                <i class="bi bi-cash-stack fs-5 mb-1"></i>
                                <span>NAKİT (F8)</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-primary w-100 py-2 fw-bold d-flex flex-column align-items-center justify-content-center" onclick="completeSale('credit_card')">
                                <i class="bi bi-credit-card fs-5 mb-1"></i>
                                <span>KREDİ KARTI</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button class="btn btn-warning w-100 py-2 fw-bold text-dark d-flex flex-column align-items-center justify-content-center" onclick="completeSale('open_account')">
                                <i class="bi bi-journal-text fs-5 mb-1"></i>
                                <span>VERESİYE</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FİŞ VE SATIŞ BAŞARILI MODALI -->
<div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Satış Tamamlandı!</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 font-monospace small" id="thermalReceiptContent" style="background: #FAFAFA;">
                <!-- Termal Fiş Çıktısı JS ile yazılacak -->
            </div>
            <div class="modal-footer p-2 bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat (Yeni Satış)</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="printReceipt()"><i class="bi bi-printer-fill me-1"></i>Fişi Yazdır</button>
            </div>
        </div>
    </div>
</div>

<!-- POS JAVASCRIPT MOTORU -->
<script>
let cart = [];
let currentGrandTotal = 0;

// Barkod Input Enter Dinleyici
document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        lookupAndAddBarcode(this.value.trim());
    }
});

document.getElementById('btnBarcodeAdd').addEventListener('click', function() {
    const val = document.getElementById('barcodeInput').value.trim();
    if (val) lookupAndAddBarcode(val);
});

// Barkod ile Ürün Bul ve Ekle
async function lookupAndAddBarcode(barcode) {
    if (!barcode) return;
    try {
        const res = await fetch(`<?= base_url('api/v1/products/barcode/') ?>${encodeURIComponent(barcode)}`);
        const json = await res.json();
        if (json.success && json.data) {
            addToCart(json.data);
            document.getElementById('barcodeInput').value = '';
            document.getElementById('barcodeInput').focus();
        } else {
            alert('Ürün bulunamadı: ' + barcode);
        }
    } catch (err) {
        console.error(err);
        alert('Sunucu hatası veya ürün bulunamadı.');
    }
}

// Sepete Ürün Ekle
function addToCart(product) {
    const existingIndex = cart.findIndex(item => item.product_id === product.id);
    if (existingIndex > -1) {
        cart[existingIndex].quantity += 1;
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            barcode: product.barcode || product.code,
            unit_price: parseFloat(product.sale_price),
            quantity: 1,
            unit: product.unit || 'Adet',
            tax_rate: parseFloat(product.tax_rate || 20)
        });
    }
    renderCart();
}

// Sepetten Çıkar
function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

// Miktar Güncelle
function updateQuantity(index, delta) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) {
        removeFromCart(index);
    } else {
        renderCart();
    }
}

// Müşteri Değiştiğinde
function onCustomerChange() {
    const select = document.getElementById('customerSelect');
    const selectedOption = select.options[select.selectedIndex];
    const discount = parseFloat(selectedOption.getAttribute('data-discount') || 0);
    if (discount > 0) {
        document.getElementById('customDiscountRate').value = discount;
    }
    renderCart();
}

// Sepeti Ekrana Çiz
async function renderCart() {
    const tbody = document.getElementById('cartTableBody');
    const emptyMsg = document.getElementById('cartEmptyMessage');
    tbody.innerHTML = '';

    if (cart.length === 0) {
        emptyMsg.style.display = 'block';
        updateSummary(0, 0, 0, 0);
        return;
    }

    emptyMsg.style.display = 'none';

    // Backend API ile sepeti tam hesapla
    const customerId = document.getElementById('customerSelect').value || null;
    const discountRate = parseFloat(document.getElementById('customDiscountRate').value || 0);

    try {
        const response = await fetch('<?= base_url('api/v1/pos/calculate') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                items: cart,
                customer_id: customerId,
                discount_rate: discountRate
            })
        });
        const res = await response.json();

        if (res.success && res.data) {
            const data = res.data;
            data.items.forEach((item, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="fw-bold text-truncate text-dark" style="max-width: 170px;" title="${item.name}">${item.name}</div>
                        <small class="text-muted font-monospace">${item.barcode}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary px-2" onclick="updateQuantity(${idx}, -1)">-</button>
                            <span class="btn btn-light fw-bold disabled px-2">${item.quantity}</span>
                            <button class="btn btn-outline-secondary px-2" onclick="updateQuantity(${idx}, 1)">+</button>
                        </div>
                    </td>
                    <td class="text-end fw-semibold">${parseFloat(item.unit_price).toFixed(2)} ₺</td>
                    <td class="text-end fw-bold text-primary">${parseFloat(item.line_total).toFixed(2)} ₺</td>
                    <td class="text-center">
                        <button class="btn btn-link text-danger p-0" onclick="removeFromCart(${idx})" title="Kaldır"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            updateSummary(data.subtotal, data.discount_total, data.tax_total, data.grand_total);
        }
    } catch (e) {
        console.error('Hesaplama API hatası:', e);
    }
}

function updateSummary(subtotal, discount, tax, grandTotal) {
    currentGrandTotal = grandTotal;
    document.getElementById('summarySubtotal').innerText = subtotal.toFixed(2).replace('.', ',') + ' ₺';
    document.getElementById('summaryTax').innerText = tax.toFixed(2).replace('.', ',') + ' ₺';
    document.getElementById('summaryGrandTotal').innerText = grandTotal.toFixed(2).replace('.', ',') + ' ₺';

    const discRow = document.getElementById('summaryDiscountRow');
    if (discount > 0) {
        discRow.style.setProperty('display', 'flex', 'important');
        document.getElementById('summaryDiscount').innerText = '-' + discount.toFixed(2).replace('.', ',') + ' ₺';
    } else {
        discRow.style.setProperty('display', 'none', 'important');
    }

    calculateChange();
}

function calculateChange() {
    const paidInput = parseFloat(document.getElementById('paidAmountInput').value) || 0;
    const change = Math.max(0, paidInput - currentGrandTotal);
    document.getElementById('changeAmountDisplay').value = change.toFixed(2).replace('.', ',') + ' ₺';
}

// Satışı Tamamla (API Checkout)
async function completeSale(paymentMethod) {
    if (cart.length === 0) {
        alert('Lütfen önce sepete ürün ekleyin.');
        return;
    }

    const customerId = document.getElementById('customerSelect').value || null;
    const discountRate = parseFloat(document.getElementById('customDiscountRate').value || 0);
    const paidAmount = parseFloat(document.getElementById('paidAmountInput').value) || currentGrandTotal;

    if (paymentMethod === 'open_account' && !customerId) {
        alert('Veresiye satış yapabilmek için lütfen yukarıdan bir müşteri / cari seçin!');
        document.getElementById('customerSelect').focus();
        return;
    }

    const payload = {
        items: cart,
        customer_id: customerId,
        discount_rate: discountRate,
        payment_method: paymentMethod,
        paid_amount: paidAmount,
        sale_type: 'pos'
    };

    try {
        const res = await fetch('<?= base_url('api/v1/pos/checkout') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await res.json();

        if (result.success && result.data) {
            showReceiptModal(result.data);
            // Sepeti sıfırla
            cart = [];
            document.getElementById('paidAmountInput').value = '';
            renderCart();
        } else {
            alert('Hata: ' + (result.message || 'Satış tamamlanamadı.'));
        }
    } catch (e) {
        console.error(e);
        alert('Satış gönderilirken ağ/sunucu hatası oluştu.');
    }
}

// Termal Fiş Göster
function showReceiptModal(data) {
    const sale = data.receipt;
    let itemsHtml = '';
    sale.items.forEach(it => {
        itemsHtml += `
            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                <span>${it.quantity} x ${it.product_name.substring(0, 20)}</span>
                <span>${parseFloat(it.line_total).toFixed(2)} TL</span>
            </div>
        `;
    });

    const receiptHtml = `
        <div style="text-align:center; border-bottom:1px dashed #999; padding-bottom:8px; margin-bottom:8px;">
            <h5 style="margin:0; font-weight:bold;">KIRTASİYE DÜNYASI</h5>
            <small>Stok & Satış Noktası</small><br>
            <small>Fiş No: <strong>${sale.receipt_no}</strong></small><br>
            <small>Tarih: ${sale.created_at}</small><br>
            <small>Müşteri: ${sale.customer_display}</small><br>
            <small>Kasiyer: ${sale.cashier_display}</small>
        </div>
        <div style="border-bottom:1px dashed #999; padding-bottom:8px; margin-bottom:8px;">
            ${itemsHtml}
        </div>
        <div style="font-weight:bold; font-size:14px;">
            <div style="display:flex; justify-content:space-between;">
                <span>Ara Toplam:</span>
                <span>${parseFloat(sale.subtotal).toFixed(2)} TL</span>
            </div>
            ${sale.discount_total > 0 ? `
            <div style="display:flex; justify-content:space-between; color:red;">
                <span>İndirim:</span>
                <span>-${parseFloat(sale.discount_total).toFixed(2)} TL</span>
            </div>` : ''}
            <div style="display:flex; justify-content:space-between; font-size:16px; margin-top:4px;">
                <span>GENEL TOPLAM:</span>
                <span style="color:#2563EB;">${parseFloat(sale.grand_total).toFixed(2)} TL</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:4px; font-size:12px; font-weight:normal;">
                <span>Ödeme Türü:</span>
                <span>${sale.payment_label}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:normal;">
                <span>Alınan / Para Üstü:</span>
                <span>${parseFloat(sale.paid_amount).toFixed(2)} / ${parseFloat(sale.change_amount).toFixed(2)} TL</span>
            </div>
        </div>
        <div style="text-align:center; border-top:1px dashed #999; padding-top:8px; margin-top:8px; font-size:11px;">
            Bizi tercih ettiğiniz için teşekkür ederiz!<br>
            İyi günlerde kullanınız.
        </div>
    `;

    document.getElementById('thermalReceiptContent').innerHTML = receiptHtml;
    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

function printReceipt() {
    const printWindow = window.open('', '_blank', 'width=380,height=600');
    printWindow.document.write('<html><head><title>Fiş Yazdır</title><style>body{font-family:monospace; font-size:12px; padding:10px;}</style></head><body>');
    printWindow.document.write(document.getElementById('thermalReceiptContent').innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

// Filtreler (Kategori ve Arama)
document.querySelectorAll('.cat-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-filter-btn').forEach(b => b.classList.remove('btn-primary', 'active'));
        document.querySelectorAll('.cat-filter-btn').forEach(b => b.classList.add('btn-outline-secondary'));
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-primary', 'active');

        const catId = this.getAttribute('data-cat-id');
        filterCatalog(catId, document.getElementById('searchProductInput').value.trim());
    });
});

document.getElementById('searchProductInput').addEventListener('input', function() {
    const activeCat = document.querySelector('.cat-filter-btn.active')?.getAttribute('data-cat-id') || '';
    filterCatalog(activeCat, this.value.trim());
});

function filterCatalog(catId, search) {
    search = search.toLowerCase();
    document.querySelectorAll('.product-card-col').forEach(col => {
        const cardCat = col.getAttribute('data-cat-id');
        const name = col.getAttribute('data-name');
        const code = col.getAttribute('data-code');
        const barcode = col.getAttribute('data-barcode');

        const catMatch = !catId || cardCat === catId;
        const searchMatch = !search || name.includes(search) || code.includes(search) || barcode.includes(search);

        col.style.display = (catMatch && searchMatch) ? 'block' : 'none';
    });
}
</script>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>

