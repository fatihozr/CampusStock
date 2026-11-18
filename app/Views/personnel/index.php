<?php include dirname(__DIR__, 3) . '/includes/header.php'; ?>
<?php include dirname(__DIR__, 3) . '/includes/navbar.php'; ?>

<div class="app-wrapper">
    <!-- Sidebar Partial -->
    <?php include dirname(__DIR__, 3) . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid p-0">

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

            <!-- Page Title & Header Actions -->
            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <div class="ds-stat-icon" style="background: var(--c-primary-subtle); color: var(--c-primary); width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-people-fill"></i></div>
                        Personel Yönetim Rehberi
                    </h4>
                    <p class="text-muted mb-0 fs-7 mt-2">Şirket Personeli ve Kullanıcılar</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="<?= base_url('reports/export?type=personnel&format=xlsx') ?>" class="ds-btn ds-btn-secondary rounded-2 d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Dışa Aktar (Excel)
                    </a>
                    <button class="ds-btn ds-btn-primary rounded-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#personnelFormModal" onclick="prepareAddPersonnelForm()">
                        <i class="bi bi-person-plus"></i> Yeni Personel Ekle
                    </button>
                </div>
            </div>

            <!-- Filter & Search Bar Card -->
            <div class="ds-card mb-4">
                <div class="ds-card-body p-3">
                    <form method="GET" action="<?= base_url('personnel') ?>" class="row g-2 align-items-center">
                        <!-- Search Box -->
                        <div class="col-12 col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 border-light text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="ds-input border-start-0 ps-0 form-control shadow-none" placeholder="Sicil No, Ad Soyad, Unvan, E-Posta veya Telefon..." value="<?= e($filters['search']) ?>">
                            </div>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-6 col-md-4">
                            <select name="department" class="ds-select">
                                <option value="">Tüm Daire Başkanlıkları</option>
                                <?php foreach ($filterOptions['departments'] as $dept): ?>
                                    <option value="<?= e($dept) ?>" <?= $filters['department'] === $dept ? 'selected' : '' ?>><?= e($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-4 col-md-2">
                            <select name="status" class="ds-select">
                                <option value="">Tüm Durumlar</option>
                                <?php foreach ($filterOptions['statuses'] as $st): ?>
                                    <option value="<?= e($st) ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-2 col-md-1 d-flex gap-1">
                            <button type="submit" class="ds-btn ds-btn-primary w-100" style="padding: 0.6rem;"><i class="bi bi-funnel"></i></button>
                            <?php if (!empty($filters['search']) || !empty($filters['department']) || !empty($filters['status'])): ?>
                                <a href="<?= base_url('personnel') ?>" class="ds-btn ds-btn-secondary" style="padding: 0.6rem;" title="Filtreleri Sıfırla"><i class="bi bi-x-lg text-danger"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Personnel Data Table Card -->
            <div class="ds-card">
                <div class="ds-card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-people me-2 text-primary"></i>Kayıtlı Şirket Personelleri
                        <span class="ds-badge ds-badge-primary rounded-pill ms-2"><?= $totalItems ?> Kişi</span>
                    </h6>
                    <small class="text-muted fs-8">Sayfa <?= $currentPage ?> / <?= $totalPages ?></small>
                </div>

                <div class="ds-card-body p-0">
                    <div class="ds-table-container">
                        <table class="ds-table align-middle">
                            <thead style="background: var(--bg-surface);">
                                <tr>
                                    <th>SİCİL / T.C. NO</th>
                                    <th>PERSONEL AD SOYAD</th>
                                    <th>UNVAN & BİRİM</th>
                                    <th>DAİRE BAŞKANLIĞI</th>
                                    <th>İLETİŞİM</th>
                                    <th>DURUM</th>
                                    <th class="text-end pe-4">EYLEMLER</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($personnel)): ?>
                                    <?php foreach ($personnel as $p): ?>
                                        <?php
                                            $statusBadge = match($p['status']) {
                                                'Aktif'  => 'ds-badge-success',
                                                'Pasif'  => 'ds-badge-warning',
                                                'Ayrıldı'=> 'ds-badge-neutral',
                                                default  => 'bg-light text-dark'
                                            };
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?= e($p['sicil_no']) ?></div>
                                                <div class="text-muted fs-8"><i class="bi bi-card-heading me-1"></i><?= mask_tc($p['tc_no']) ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark fs-7"><?= e($p['full_name']) ?></div>
                                                <small class="text-muted fs-8"><?= e($p['room_no']) ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-medium text-dark"><?= e($p['title']) ?></div>
                                                <small class="text-secondary fs-8"><?= e($p['unit']) ?></small>
                                            </td>
                                            <td>
                                                <span class="ds-badge ds-badge-neutral border"><?= e($p['department']) ?></span>
                                            </td>
                                            <td>
                                                <div class="text-dark fs-8">
                                                    <a href="mailto:<?= e($p['email']) ?>" class="text-decoration-none text-dark hover-text-primary" title="E-Posta Gönder">
                                                        <i class="bi bi-envelope me-1 text-primary"></i><?= e($p['email']) ?>
                                                    </a>
                                                </div>
                                                <div class="text-muted fs-8 mt-1">
                                                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $p['phone'])) ?>" class="text-decoration-none text-secondary hover-text-success" title="Telefon Et / Dahili Ara">
                                                        <i class="bi bi-telephone me-1 text-success"></i><?= e($p['phone']) ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ds-badge <?= $statusBadge ?> px-2.5 py-1 rounded-pill"><?= e($p['status']) ?></span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button class="btn btn-outline-info border-0" onclick="showPersonnelDetailModal(<?= $p['id'] ?>)" title="Detay Profili Gör">
                                                        <i class="bi bi-eye fs-6"></i>
                                                    </button>
                                                    <button class="btn btn-outline-primary border-0" onclick='prepareEditPersonnelForm(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' data-bs-toggle="modal" data-bs-target="#personnelFormModal" title="Düzenle">
                                                        <i class="bi bi-pencil fs-6"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger border-0 btn-delete-personnel"
                                                             data-id="<?= $p['id'] ?>"
                                                             data-name="<?= e($p['full_name']) ?>"
                                                             title="Sil">
                                                         <i class="bi bi-trash fs-6"></i>
                                                     </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state py-5">
                                                <div class="empty-state-icon fs-1 mb-3">🔍</div>
                                                <p class="mb-0 text-muted">Aradığınız kriterlere uygun personel kaydı bulunamadı.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card Footer with Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="ds-card-footer bg-transparent py-3 d-flex align-items-center justify-content-between">
                        <small class="text-muted fs-7">
                            Toplam <strong><?= $totalItems ?></strong> personelden <strong><?= (($currentPage - 1) * $perPage) + 1 ?> - <?= min($currentPage * $perPage, $totalItems) ?></strong> arası gösteriliyor.
                        </small>

                        <nav aria-label="Personel Sayfalaması">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= base_url('personnel?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1]))) ?>">Önceki</a>
                                </li>

                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                    <li class="page-item <?= ($p === $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= base_url('personnel?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= base_url('personnel?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1]))) ?>">Sonraki</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- 1. PERSONEL EKLE / DÜZENLE MODALI          -->
<!-- ========================================== -->
<div class="modal fade" id="personnelFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--bg-surface-hover);">
                <h5 class="modal-title fw-bold fs-6 text-dark" id="personnelFormTitle"><i class="bi bi-person-plus me-2 text-primary"></i>Yeni Personel Kaydı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="personnelForm" action="<?= base_url('personnel/store') ?>" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="id" id="p_form_id" value="">
                
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Ad -->
                        <div class="col-12 col-md-6">
                            <label for="p_first_name" class="ds-label">Adı *</label>
                            <input type="text" class="ds-input" id="p_first_name" name="first_name" placeholder="Örn: Ahmet" required>
                        </div>

                        <!-- Soyad -->
                        <div class="col-12 col-md-6">
                            <label for="p_last_name" class="ds-label">Soyadı *</label>
                            <input type="text" class="ds-input" id="p_last_name" name="last_name" placeholder="Örn: Yılmaz" required>
                        </div>

                        <!-- Sicil No -->
                        <div class="col-12 col-md-4">
                            <label for="p_sicil_no" class="ds-label">Kurum Sicil No</label>
                            <input type="text" class="ds-input font-monospace" id="p_sicil_no" name="sicil_no" placeholder="Örn: SIC-1008">
                        </div>

                        <!-- T.C. Kimlik No -->
                        <div class="col-12 col-md-4">
                            <label for="p_tc_no" class="ds-label">T.C. Kimlik No *</label>
                            <input type="text" class="ds-input font-monospace" id="p_tc_no" name="tc_no" maxlength="11" placeholder="11 haneli T.C. No" required>
                        </div>

                        <!-- Unvan -->
                        <div class="col-12 col-md-4">
                            <label for="p_title" class="ds-label">Unvan *</label>
                            <input type="text" class="ds-input" id="p_title" name="title" placeholder="Örn: Mühendis, Şef, Memur" required>
                        </div>

                        <!-- Daire Başkanlığı -->
                        <div class="col-12 col-md-6">
                            <label for="p_department" class="ds-label">Bağlı Olduğu Daire Başkanlığı *</label>
                            <select class="ds-select" id="p_department" name="department" required>
                                <option value="Bilgi İşlem Daire Başkanlığı">Bilgi İşlem Daire Başkanlığı</option>
                                <option value="Yazı İşleri Daire Başkanlığı">Yazı İşleri Daire Başkanlığı</option>
                                <option value="İmar ve Şehircilik Daire Başkanlığı">İmar ve Şehircilik Daire Başkanlığı</option>
                                <option value="Fen İşleri Daire Başkanlığı">Fen İşleri Daire Başkanlığı</option>
                                <option value="Kültür ve Sosyal İşler">Kültür ve Sosyal İşler</option>
                                <option value="Destek Hizmetleri">Destek Hizmetleri</option>
                                <option value="Zabıta Daire Başkanlığı">Zabıta Daire Başkanlığı</option>
                            </select>
                        </div>

                        <!-- Birim / Şeflik -->
                        <div class="col-12 col-md-6">
                            <label for="p_unit" class="ds-label">Alt Birim / Şeflik</label>
                            <input type="text" class="ds-input" id="p_unit" name="unit" placeholder="Örn: Yazılım Şefliği">
                        </div>

                        <!-- Kurumsal E-posta -->
                        <div class="col-12 col-md-6">
                            <label for="p_email" class="ds-label">Kurumsal E-posta *</label>
                            <input type="email" class="ds-input" id="p_email" name="email" placeholder="isim.soyisim@stationeryflow.com" required>
                        </div>

                        <!-- Telefon / Dahili -->
                        <div class="col-12 col-md-6">
                            <label for="p_phone" class="ds-label">Telefon / Dahili</label>
                            <input type="text" class="ds-input" id="p_phone" name="phone" placeholder="0532 000 0000 (D: 1400)">
                        </div>

                        <!-- Oda Numarası -->
                        <div class="col-12 col-md-4">
                            <label for="p_room_no" class="ds-label">Oda Numarası / Lokasyon</label>
                            <input type="text" class="ds-input" id="p_room_no" name="room_no" placeholder="Örn: Kat 2 Oda 204">
                        </div>

                        <!-- Çalışma Durumu -->
                        <div class="col-12 col-md-4">
                            <label for="p_status" class="ds-label">Durumu *</label>
                            <select class="ds-select" id="p_status" name="status" required>
                                <option value="Aktif">Aktif Görevde</option>
                                <option value="Pasif">Pasif / İzinli</option>
                                <option value="Ayrıldı">İşten Ayrıldı</option>
                            </select>
                        </div>

                        <!-- İşe Başlama Tarihi -->
                        <div class="col-12 col-md-4">
                            <label for="p_hire_date" class="ds-label">İşe Başlama Tarihi</label>
                            <input type="date" class="ds-input" id="p_hire_date" name="hire_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3" style="background: rgba(0,0,0,0.01);">
                    <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="ds-btn ds-btn-primary" id="p_btnSubmit">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. PERSONEL DETAY MODALI                   -->
<!-- ========================================== -->
<div class="modal fade" id="personnelDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content ds-card border-0">
            <div class="modal-header border-bottom py-3" style="background: var(--bg-surface-hover);">
                <h5 class="modal-title fw-bold fs-6 text-dark"><i class="bi bi-person-badge me-2 text-info"></i>Personel Detay Kartı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body p-4" id="personnelDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-3" style="background: rgba(0,0,0,0.01);">
                <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. PERSONEL SİLME ONAY MODALI               -->
<!-- ========================================== -->
<div class="modal fade" id="deletePersonnelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content ds-card border-0">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-octagon fs-1"></i>
                </div>
                <h6 class="fw-bold mb-2">Personel Kaydını Sil?</h6>
                <p class="text-muted fs-7 mb-4"><strong id="deletePersonName"></strong> isimli personel sistemden tamamen silinecektir!</p>
                <form action="<?= base_url('personnel/delete') ?>" method="POST">
                    <input type="hidden" name="id" id="delete_personnel_id" value="">
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="ds-btn ds-btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="ds-btn ds-btn-primary" style="background-color: var(--c-danger); border-color: var(--c-danger);">Evet, Sil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS Helpers for Modals -->
<script>
function prepareAddPersonnelForm() {
    document.getElementById('personnelFormTitle').innerHTML = '<i class="bi bi-person-plus me-2 text-primary"></i>Yeni Personel Kaydı Ekle';
    document.getElementById('personnelForm').action = '<?= base_url('personnel/store') ?>';
    document.getElementById('p_form_id').value = '';
    document.getElementById('p_first_name').value = '';
    document.getElementById('p_last_name').value = '';
    document.getElementById('p_sicil_no').value = '';
    document.getElementById('p_tc_no').value = '';
    document.getElementById('p_title').value = '';
    document.getElementById('p_unit').value = '';
    document.getElementById('p_email').value = '';
    document.getElementById('p_phone').value = '';
    document.getElementById('p_room_no').value = '';
    document.getElementById('p_btnSubmit').innerText = 'Kaydet';
}

function prepareEditPersonnelForm(person) {
    document.getElementById('personnelFormTitle').innerHTML = '<i class="bi bi-pencil-square me-2 text-primary"></i>Personel Bilgilerini Düzenle (' + person.sicil_no + ')';
    document.getElementById('personnelForm').action = '<?= base_url('personnel/update') ?>';
    document.getElementById('p_form_id').value = person.id;
    document.getElementById('p_first_name').value = person.first_name;
    document.getElementById('p_last_name').value = person.last_name;
    document.getElementById('p_sicil_no').value = person.sicil_no;
    document.getElementById('p_tc_no').value = person.tc_no;
    document.getElementById('p_title').value = person.title;
    document.getElementById('p_department').value = person.department;
    document.getElementById('p_unit').value = person.unit;
    document.getElementById('p_email').value = person.email;
    document.getElementById('p_phone').value = person.phone;
    document.getElementById('p_room_no').value = person.room_no;
    document.getElementById('p_status').value = person.status;
    document.getElementById('p_hire_date').value = person.hire_date;
    document.getElementById('p_btnSubmit').innerText = 'Güncelle';
}

document.addEventListener('click', function(e) {
    const delBtn = e.target.closest('.btn-delete-personnel');
    if (delBtn) {
        const id = delBtn.dataset.id;
        const name = delBtn.dataset.name;

        document.getElementById('delete_personnel_id').value = id;
        document.getElementById('deletePersonName').innerText = name;
        const deleteModal = new bootstrap.Modal(document.getElementById('deletePersonnelModal'));
        deleteModal.show();
    }
});

function showPersonnelDetailModal(id) {
    const detailModal = new bootstrap.Modal(document.getElementById('personnelDetailModal'));
    detailModal.show();

    fetch('<?= base_url('personnel/detail?id=') ?>' + id)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                const p = res.data;
                const statusClass = (p.status === 'Aktif') ? 'ds-badge-success' : 'ds-badge-neutral';
                document.getElementById('personnelDetailBody').innerHTML = `
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-4">
                                <div class="user-avatar rounded-circle fw-bold d-flex align-items-center justify-content-center me-3 shadow-sm" style="width:64px; height:64px; font-size:1.5rem; background: var(--c-primary-subtle); color: var(--c-primary);">
                                    ${p.first_name.substr(0,1)}${p.last_name.substr(0,1)}
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">${p.full_name}</h5>
                                    <small class="text-muted fs-7">${p.title} &middot; ${p.department}</small>
                                </div>
                            </div>
                            <table class="ds-table table-borderless fs-7 mb-0">
                                <tr><th class="w-35 text-secondary">Kurum Sicil No:</th><td><span class="ds-badge ds-badge-neutral font-monospace">${p.sicil_no || '-'}</span></td></tr>
                                <tr><th class="text-secondary">T.C. Kimlik No:</th><td>${p.tc_no ? (p.tc_no.substr(0,3) + '******' + p.tc_no.substr(-2)) : '-'}</td></tr>
                                <tr><th class="text-secondary">Birim / Şeflik:</th><td><code>${p.unit || '-'}</code></td></tr>
                                <tr><th class="text-secondary">Oda / Lokasyon:</th><td>${p.room_no || '-'}</td></tr>
                                <tr><th class="text-secondary">E-Posta:</th><td>${p.email || '-'}</td></tr>
                                <tr><th class="text-secondary">Telefon:</th><td>${p.phone || '-'}</td></tr>
                                <tr><th class="text-secondary">İşe Başlama:</th><td>${p.hire_date || '-'}</td></tr>
                                <tr><th class="text-secondary">Görev Durumu:</th><td><span class="ds-badge ${statusClass}">${p.status || 'Aktif'}</span></td></tr>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('personnelDetailBody').innerHTML = '<div class="alert alert-danger">Personel detayları yüklenemedi.</div>';
            }
        });
}
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
