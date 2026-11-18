<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-3 p-md-4">
            <!-- Flash Messages -->
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

            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-inbox text-primary me-2"></i> Malzeme Talepleri & Onay Merkezi
                    </h4>
                    <p class="text-muted mb-0 fs-7">Birimlerin temizlik malzemesi talepleri ve onay süreçleri</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="<?= base_url('requests/create') ?>" class="btn btn-primary btn-sm rounded-2 shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Yeni Talep Oluştur
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white p-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Tüm Talepler</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-8 text-muted text-uppercase">
                                <tr>
                                    <th class="ps-3">TALEP NO</th>
                                    <th>TARİH</th>
                                    <th>BİRİM</th>
                                    <th>TALEP EDEN</th>
                                    <th>DURUM</th>
                                    <th class="text-end pe-3">İŞLEMLER</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($requests)): ?>
                                    <?php foreach ($requests as $req): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold font-monospace"><?= e($req['request_no']) ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($req['created_at'])) ?></td>
                                            <td><?= e($req['department_name'] ?? '-') ?></td>
                                            <td><?= e($req['first_name'] . ' ' . $req['last_name']) ?></td>
                                            <td>
                                                <?php if ($req['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1">Onay Bekliyor</span>
                                                <?php elseif ($req['status'] === 'approved'): ?>
                                                    <span class="badge bg-success px-2 py-1">Onaylandı</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger px-2 py-1">Reddedildi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-3">
                                                <!-- Action buttons according to permissions -->
                                                <button class="btn btn-sm btn-outline-info btn-view-items" data-items='<?= json_encode($req['items'] ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Detay Görüntüle">
                                                    <i class="bi bi-eye"></i> Detay
                                                </button>

                                                <?php if (\App\Helpers\Auth::hasPermission('admin') && $req['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-success btn-approve" data-id="<?= $req['id'] ?>" data-no="<?= e($req['request_no']) ?>" title="Onayla">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="<?= $req['id'] ?>" data-no="<?= e($req['request_no']) ?>" title="Reddet">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Kayıtlı talep bulunmamaktadır.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Onay Modalı -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Talebi Onayla</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('requests/approve') ?>" method="POST">
                <input type="hidden" name="id" id="approve_id">
                <div class="modal-body">
                    <p><strong><span id="approve_no"></span></strong> numaralı talebi onaylamak üzeresiniz.</p>
                    <p class="text-muted fs-7">Onay işlemi yapıldığında talep edilen miktar stoktan otomatik düşülecektir.</p>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold">Teslim Alan Kişi Ad Soyad <span class="text-danger">*</span></label>
                        <select name="delivered_to" class="form-select ds-select-search" required placeholder="Personel seçin veya yazın...">
                            <option value="">-- Personel Seçiniz --</option>
                            <?php if(!empty($allPersonnelForSidebar)): foreach($allPersonnelForSidebar as $p): ?>
                                <option value="<?= e($p['first_name'] . ' ' . $p['last_name']) ?>"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <small class="text-muted mt-1 d-block">Ürünleri fiziksel olarak teslim alan personelin adı</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Onayla & Stoktan Düş</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Red Modalı -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Talebi Reddet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('requests/reject') ?>" method="POST">
                <input type="hidden" name="id" id="reject_id">
                <div class="modal-body">
                    <p><strong><span id="reject_no"></span></strong> numaralı talebi reddetmek üzeresiniz.</p>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold">Reddetme Sebebi (İsteğe Bağlı)</label>
                        <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Örn: Stok yetersiz"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Talebi Reddet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detay Modalı -->
<div class="modal fade" id="itemsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Talep İçeriği</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light fs-8 text-muted">
                            <tr>
                                <th>KOD</th>
                                <th>ÜRÜN ADI</th>
                                <th class="text-center">MİKTAR</th>
                                <th>AÇIKLAMA</th>
                            </tr>
                        </thead>
                        <tbody id="itemsList" class="fs-7">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('approve_id').value = this.dataset.id;
            document.getElementById('approve_no').innerText = this.dataset.no;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        });
    });

    document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('reject_id').value = this.dataset.id;
            document.getElementById('reject_no').innerText = this.dataset.no;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        });
    });

    document.querySelectorAll('.btn-view-items').forEach(btn => {
        btn.addEventListener('click', function() {
            try {
                const items = JSON.parse(this.dataset.items);
                const tbody = document.getElementById('itemsList');
                tbody.innerHTML = '';
                
                if (items && items.length > 0) {
                    items.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><span class="badge bg-dark">${item.product_code}</span></td>
                            <td class="fw-bold">${item.product_name}</td>
                            <td class="text-center">${item.quantity}</td>
                            <td class="text-muted">${item.description || '-'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">İçerik detayı bulunamadı.</td></tr>';
                }
                
                new bootstrap.Modal(document.getElementById('itemsModal')).show();
            } catch (e) {
                console.error("Item parse error", e);
            }
        });
    });
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
