<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

            <!-- Page Title & Global Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">Sistem Raporları ve İcmal Ekranı</h4>
                    <p class="text-muted mb-0 fs-7">Belediye Destek Hizmetleri Stok ve Personel Raporlama Merkezi</p>
                </div>
            </div>

            <!-- Main Tab Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                    <ul class="nav nav-tabs card-header-tabs border-bottom-0" id="reportTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'stocks' || $activeTab === 'inventory') ? 'active fw-bold' : '' ?>" id="rep-stk-tab" data-bs-toggle="tab" data-bs-target="#rep-stk-pane" type="button">
                                <i class="bi bi-boxes text-success me-1"></i> Stok Raporları (<?= count($stocks) ?>)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= ($activeTab === 'personnel') ? 'active fw-bold' : '' ?>" id="rep-prs-tab" data-bs-toggle="tab" data-bs-target="#rep-prs-pane" type="button">
                                <i class="bi bi-people text-info me-1"></i> Personel Raporları (<?= count($personnel) ?>)
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content" id="reportTabContent">
                        
                        <!-- TAB 1: STOK RAPORLARI -->
                        <div class="tab-pane fade <?= ($activeTab === 'stocks' || $activeTab === 'inventory') ? 'show active' : '' ?>" id="rep-stk-pane" role="tabpanel">
                            <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                                <div class="fs-7 text-muted">
                                    <i class="bi bi-info-circle me-1 text-success"></i> Toplam <strong><?= count($stocks) ?> kalem</strong> stok durumu icmali
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-send-mail" data-type="stocks" data-title="Stok Raporu">
                                        <i class="bi bi-envelope-at me-1"></i> 📧 Mail Gönder
                                    </button>
                                    <a href="<?= base_url('reports/export?type=stocks&format=xlsx') ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-file-earmark-excel me-1"></i> Excel (.xlsx) İndir
                                    </a>
                                    <a href="<?= base_url('reports/pdf?type=stocks') ?>" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="bi bi-printer me-1"></i> PDF / Yazdır
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle table-hover mb-0">
                                    <thead class="table-light fs-7 text-muted">
                                        <tr>
                                            <th>STOK KODU</th>
                                            <th>MALZEME ADI</th>
                                            <th>KATEGORİ</th>
                                            <th>MEVCUT MİKTAR</th>
                                            <th>KRİTİK EŞİK</th>
                                            <th>DEPO & RAF</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-7">
                                        <?php foreach ($stocks as $stk): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary font-monospace"><?= e($stk['code']) ?></span></td>
                                                <td class="fw-bold text-dark"><?= e($stk['name']) ?></td>
                                                <td><span class="badge bg-light text-dark border"><?= e($stk['category']) ?></span></td>
                                                <td class="fw-bold <?= ($stk['quantity'] <= $stk['min_threshold']) ? 'text-danger' : 'text-dark' ?>">
                                                    <?= e($stk['quantity']) ?> <?= e($stk['unit']) ?>
                                                </td>
                                                <td><span class="badge bg-warning-subtle text-dark border"><?= e($stk['min_threshold']) ?> <?= e($stk['unit']) ?></span></td>
                                                <td class="text-secondary"><?= e($stk['warehouse']) ?> (<?= e($stk['rack_location'] ?? 'C31') ?>)</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2: PERSONEL RAPORLARI -->
                        <div class="tab-pane fade <?= ($activeTab === 'personnel') ? 'show active' : '' ?>" id="rep-prs-pane" role="tabpanel">
                            <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                                <div class="fs-7 text-muted">
                                    <i class="bi bi-info-circle me-1 text-info"></i> Toplam <strong><?= count($personnel) ?> personel</strong> rehber icmali
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-send-mail" data-type="personnel" data-title="Personel Raporu">
                                        <i class="bi bi-envelope-at me-1"></i> 📧 Mail Gönder
                                    </button>
                                    <a href="<?= base_url('reports/export?type=personnel&format=xlsx') ?>" class="btn btn-success btn-sm">
                                        <i class="bi bi-file-earmark-excel me-1"></i> Excel (.xlsx) İndir
                                    </a>
                                    <a href="<?= base_url('reports/pdf?type=personnel') ?>" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="bi bi-printer me-1"></i> PDF / Yazdır
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle table-hover mb-0">
                                    <thead class="table-light fs-7 text-muted">
                                        <tr>
                                            <th>SİCİL NO</th>
                                            <th>AD SOYAD</th>
                                            <th>UNVAN</th>
                                            <th>MÜDÜRLÜK & BİRİM</th>
                                            <th>İLETİŞİM</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-7">
                                        <?php foreach ($personnel as $p): ?>
                                            <tr>
                                                <td><code><?= e($p['sicil_no']) ?></code></td>
                                                <td class="fw-bold text-dark"><?= e($p['full_name'] ?? ($p['first_name'] . ' ' . $p['last_name'])) ?></td>
                                                <td><?= e($p['title']) ?></td>
                                                <td><?= e($p['department']) ?> / <?= e($p['unit'] ?? '-') ?></td>
                                                <td class="text-secondary"><?= e($p['email']) ?> - <?= e($p['phone']) ?></td>
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
<!-- MODAL: E-POSTA RAPOR GÖNDERME MODALI        -->
<!-- ========================================== -->
<div class="modal fade" id="sendReportMailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 550px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-envelope-paper me-2"></i>Rapor E-Posta İle Gönder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="sendReportMailForm" method="POST" action="<?= base_url('reports/send-email') ?>">
                <input type="hidden" name="type" id="mail_report_type" value="stocks">
                
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 fs-7 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-paperclip fs-5"></i>
                        <span>Seçilen rapor hazırlanan biçimde e-posta ekinde iletilecektir.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">Alıcı E-Posta Adresi <span class="text-danger">*</span></label>
                        <input type="email" name="to_email" class="form-control form-control-sm" placeholder="ornek@belediye.bel.tr" required value="<?= e($currentUser['email'] ?? '') ?>">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fs-7 text-muted">CC (Bilgi)</label>
                            <input type="email" name="cc_email" class="form-control form-control-sm" placeholder="bilgi@belediye.bel.tr">
                        </div>
                        <div class="col-6">
                            <label class="form-label fs-7 text-muted">Dosya Biçimi</label>
                            <select name="format" class="form-select form-select-sm">
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="pdf" selected>PDF Dosyası (.pdf / .html)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-semibold text-dark">E-Posta Konusu <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="mail_report_subject" class="form-control form-control-sm" required value="Belediye Destek Hizmetleri Raporu">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fs-7 text-muted">Açıklama / Not</label>
                        <textarea name="message" class="form-control form-control-sm" rows="3" placeholder="E-posta içerik notu..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 d-flex align-items-center gap-1" id="btnSubmitMailReport">
                        <i class="bi bi-send-fill me-1"></i> E-Posta Gönder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sendMailBtns = document.querySelectorAll('.btn-send-mail');
    sendMailBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const type = this.dataset.type || 'stocks';
            const title = this.dataset.title || 'Rapor';
            
            document.getElementById('mail_report_type').value = type;
            document.getElementById('mail_report_subject').value = title + ' - ' + new Date().toLocaleDateString('tr-TR');
            
            const modal = new bootstrap.Modal(document.getElementById('sendReportMailModal'));
            modal.show();
        });
    });
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
