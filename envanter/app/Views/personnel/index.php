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
                    <h4 class="fw-bold mb-1 text-dark">Personel Yönetim Rehberi</h4>
                    <p class="text-muted mb-0 fs-7">Belediye Çalışanları, Unvanlar ve Zimmet Sahibi Personeller</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="<?= base_url('reports/export?type=personnel&format=xlsx') ?>" class="btn btn-outline-secondary btn-sm rounded-2 d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i> Dışa Aktar (Excel)
                    </a>
                    <button class="btn btn-primary btn-sm rounded-2 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#personnelFormModal" onclick="prepareAddPersonnelForm()">
                        <i class="bi bi-person-plus"></i> Yeni Personel Ekle
                    </button>
                </div>
            </div>

            <!-- Filter & Search Bar Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="<?= base_url('personnel') ?>" class="row g-2 align-items-center">
                        <!-- Search Box -->
                        <div class="col-12 col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Sicil No, Ad Soyad, Unvan, E-Posta veya Telefon..." value="<?= e($filters['search']) ?>">
                            </div>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-6 col-md-4">
                            <select name="department" class="form-select form-select-sm bg-light">
                                <option value="">Tüm Müdürlükler</option>
                                <?php foreach ($filterOptions['departments'] as $dept): ?>
                                    <option value="<?= e($dept) ?>" <?= $filters['department'] === $dept ? 'selected' : '' ?>><?= e($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-4 col-md-2">
                            <select name="status" class="form-select form-select-sm bg-light">
                                <option value="">Tüm Durumlar</option>
                                <?php foreach ($filterOptions['statuses'] as $st): ?>
                                    <option value="<?= e($st) ?>" <?= $filters['status'] === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="col-2 col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel"></i></button>
                            <?php if (!empty($filters['search']) || !empty($filters['department']) || !empty($filters['status'])): ?>
                                <a href="<?= base_url('personnel') ?>" class="btn btn-outline-danger btn-sm" title="Filtreleri Sıfırla"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Personnel Data Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-people me-2 text-primary"></i>Kayıtlı Belediye Personelleri
                        <span class="badge bg-primary-subtle text-primary rounded-pill ms-2"><?= $totalItems ?> Kişi</span>
                    </h6>
                    <small class="text-muted fs-8">Sayfa <?= $currentPage ?> / <?= $totalPages ?></small>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light fs-7 text-muted">
                                <tr>
                                    <th>SİCİL / T.C. NO</th>
                                    <th>PERSONEL AD SOYAD</th>
                                    <th>UNVAN & BİRİM</th>
                                    <th>MÜDÜRLÜK</th>
                                    <th>İLETİŞİM</th>
                                    <th>DURUM</th>
                                    <th>ZİMMETLİ CİHAZ</th>
                                    <th class="text-end pe-4">EYLEMLER</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                <?php if (!empty($personnel)): ?>
                                    <?php foreach ($personnel as $p): ?>
                                        <?php
                                            $statusBadge = match($p['status']) {
                                                'Aktif'  => 'bg-success-subtle text-success border border-success-subtle',
                                                'Pasif'  => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                                'Ayrıldı'=> 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                                default  => 'bg-light text-dark'
                                            };
                                            $custodyCount = $p['custody_count'] ?? 0;
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
                                                <span class="badge bg-light text-dark border"><?= e($p['department']) ?></span>
                                            </td>
                                            <td>
                                                <div class="text-dark fs-8">
                                                    <a href="mailto:<?= e($p['email']) ?>" class="text-decoration-none text-dark" title="E-Posta Gönder">
                                                        <i class="bi bi-envelope me-1 text-primary"></i><?= e($p['email']) ?>
                                                    </a>
                                                </div>
                                                <div class="text-muted fs-8 mt-1">
                                                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $p['phone'])) ?>" class="text-decoration-none text-secondary" title="Telefon Et / Dahili Ara">
                                                        <i class="bi bi-telephone me-1 text-success"></i><?= e($p['phone']) ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusBadge ?> px-2.5 py-1 rounded-pill"><?= e($p['status']) ?></span>
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
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 d-block text-muted mb-2"></i>
                                            Aradığınız kriterlere uygun personel kaydı bulunamadı.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card Footer with Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white py-3 border-top d-flex align-items-center justify-content-between">
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
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h5 class="modal-title fw-bold fs-6" id="personnelFormTitle"><i class="bi bi-person-plus me-2"></i>Yeni Personel Kaydı Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <form id="personnelForm" action="<?= base_url('personnel/store') ?>" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="id" id="p_form_id" value="">
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Ad -->
                        <div class="col-12 col-md-6">
                            <label for="p_first_name" class="form-label fw-semibold fs-7 text-secondary">Adı *</label>
                            <input type="text" class="form-control" id="p_first_name" name="first_name" placeholder="Örn: Ahmet" required>
                        </div>

                        <!-- Soyad -->
                        <div class="col-12 col-md-6">
                            <label for="p_last_name" class="form-label fw-semibold fs-7 text-secondary">Soyadı *</label>
                            <input type="text" class="form-control" id="p_last_name" name="last_name" placeholder="Örn: Yılmaz" required>
                        </div>

                        <!-- Sicil No -->
                        <div class="col-12 col-md-4">
                            <label for="p_sicil_no" class="form-label fw-semibold fs-7 text-secondary">Kurum Sicil No</label>
                            <input type="text" class="form-control" id="p_sicil_no" name="sicil_no" placeholder="Örn: SIC-1008">
                        </div>

                        <!-- T.C. Kimlik No -->
                        <div class="col-12 col-md-4">
                            <label for="p_tc_no" class="form-label fw-semibold fs-7 text-secondary">T.C. Kimlik No *</label>
                            <input type="text" class="form-control" id="p_tc_no" name="tc_no" maxlength="11" placeholder="11 haneli T.C. No" required>
                        </div>

                        <!-- Unvan -->
                        <div class="col-12 col-md-4">
                            <label for="p_title" class="form-label fw-semibold fs-7 text-secondary">Unvan *</label>
                            <input type="text" class="form-control" id="p_title" name="title" placeholder="Örn: Mühendis, Şef, Memur" required>
                        </div>

                        <!-- Müdürlük -->
                        <div class="col-12 col-md-6">
                            <label for="p_department" class="form-label fw-semibold fs-7 text-secondary">Bağlı Olduğu Müdürlük *</label>
                            <select class="form-select" id="p_department" name="department" required>
                                <option value="Bilgi İşlem Müdürlüğü">Bilgi İşlem Müdürlüğü</option>
                                <option value="Yazı İşleri Müdürlüğü">Yazı İşleri Müdürlüğü</option>
                                <option value="İmar ve Şehircilik Müdürlüğü">İmar ve Şehircilik Müdürlüğü</option>
                                <option value="Fen İşleri Müdürlüğü">Fen İşleri Müdürlüğü</option>
                                <option value="Kültür ve Sosyal İşler">Kültür ve Sosyal İşler</option>
                                <option value="Destek Hizmetleri">Destek Hizmetleri</option>
                                <option value="Zabıta Müdürlüğü">Zabıta Müdürlüğü</option>
                            </select>
                        </div>

                        <!-- Birim / Şeflik -->
                        <div class="col-12 col-md-6">
                            <label for="p_unit" class="form-label fw-semibold fs-7 text-secondary">Alt Birim / Şeflik</label>
                            <input type="text" class="form-control" id="p_unit" name="unit" placeholder="Örn: Yazılım Şefliği">
                        </div>

                        <!-- Kurumsal E-posta -->
                        <div class="col-12 col-md-6">
                            <label for="p_email" class="form-label fw-semibold fs-7 text-secondary">Kurumsal E-posta *</label>
                            <input type="email" class="form-control" id="p_email" name="email" placeholder="isim.soyisim@belediye.bel.tr" required>
                        </div>

                        <!-- Telefon / Dahili -->
                        <div class="col-12 col-md-6">
                            <label for="p_phone" class="form-label fw-semibold fs-7 text-secondary">Telefon / Dahili</label>
                            <input type="text" class="form-control" id="p_phone" name="phone" placeholder="0532 000 0000 (D: 1400)">
                        </div>

                        <!-- Oda Numarası -->
                        <div class="col-12 col-md-4">
                            <label for="p_room_no" class="form-label fw-semibold fs-7 text-secondary">Oda Numarası / Lokasyon</label>
                            <input type="text" class="form-control" id="p_room_no" name="room_no" placeholder="Örn: Kat 2 Oda 204">
                        </div>

                        <!-- Çalışma Durumu -->
                        <div class="col-12 col-md-4">
                            <label for="p_status" class="form-label fw-semibold fs-7 text-secondary">Durumu *</label>
                            <select class="form-select" id="p_status" name="status" required>
                                <option value="Aktif">Aktif Görevde</option>
                                <option value="Pasif">Pasif / İzinli</option>
                                <option value="Ayrıldı">İşten Ayrıldı</option>
                            </select>
                        </div>

                        <!-- İşe Başlama Tarihi -->
                        <div class="col-12 col-md-4">
                            <label for="p_hire_date" class="form-label fw-semibold fs-7 text-secondary">İşe Başlama Tarihi</label>
                            <input type="date" class="form-control" id="p_hire_date" name="hire_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold" id="p_btnSubmit">Kaydet</button>
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
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark-blue text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-person-badge me-2"></i>Personel Detay Kartı & Zimmet Durumu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body p-4" id="personnelDetailBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. PERSONEL SİLME ONAY MODALI               -->
<!-- ========================================== -->
<div class="modal fade" id="deletePersonnelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-octagon fs-1"></i>
                </div>
                <h6 class="fw-bold mb-2">Personel Kaydını Sil?</h6>
                <p class="text-muted fs-7 mb-4"><strong id="deletePersonName"></strong> isimli personel sistemden tamamen silinecektir!</p>
                <form action="<?= base_url('personnel/delete') ?>" method="POST">
                    <input type="hidden" name="id" id="delete_personnel_id" value="">
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-danger btn-sm px-4 fw-semibold">Evet, Sil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS Helpers for Modals -->
<script>
function prepareAddPersonnelForm() {
    document.getElementById('personnelFormTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Yeni Personel Kaydı Ekle';
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
    document.getElementById('personnelFormTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Personel Bilgilerini Düzenle (' + person.sicil_no + ')';
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
                document.getElementById('personnelDetailBody').innerHTML = `
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-3">
                                <div class="user-avatar rounded-circle bg-primary-gradient text-white fw-bold d-flex align-items-center justify-content-center me-3" style="width:50px; height:50px; font-size:1.2rem;">
                                    ${p.first_name.substr(0,1)}${p.last_name.substr(0,1)}
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">${p.full_name}</h5>
                                    <small class="text-muted fs-7">${p.title} &middot; ${p.department}</small>
                                </div>
                            </div>
                            <div class="row g-2 fs-7 border-top pt-3">
                                <div class="col-6"><strong>Kurum Sicil No:</strong> ${p.sicil_no}</div>
                                <div class="col-6"><strong>T.C. Kimlik No:</strong> ${p.tc_no.substr(0,3)}******${p.tc_no.substr(-2)}</div>
                                <div class="col-6"><strong>Birim / Şeflik:</strong> ${p.unit}</div>
                                <div class="col-6"><strong>Oda / Lokasyon:</strong> ${p.room_no}</div>
                                <div class="col-6"><strong>E-Posta:</strong> ${p.email}</div>
                                <div class="col-6"><strong>Telefon:</strong> ${p.phone}</div>
                                <div class="col-6"><strong>İşe Başlama:</strong> ${p.hire_date}</div>
                                <div class="col-6"><strong>Görev Durumu:</strong> <span class="badge bg-success-subtle text-success">${p.status}</span></div>
                            </div>
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
